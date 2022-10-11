<?php

namespace WordProof\SDK\Support;

use WordProof\SDK\Helpers\AdminHelper;
use WordProof\SDK\Helpers\AuthenticationHelper;
use WordProof\SDK\Helpers\EnvironmentHelper;
use WordProof\SDK\Helpers\OptionsHelper;
use WordProof\SDK\Helpers\PostTypeHelper;
use WordProof\SDK\Helpers\SettingsHelper;
use WordProof\SDK\Helpers\TransientHelper;
use WordProof\SDK\Helpers\AppConfigHelper;

class Authentication
{
    private static $callbackEndpoint = 'wordproof/v1/oauth/callback';

    public static function authorize($redirectUrl = null)
    {
        $state = wp_generate_password(40, false);
        $codeVerifier = wp_generate_password(128, false);
        $originalUrl = AdminHelper::currentUrl();

        TransientHelper::set('wordproof_authorize_state', $state, 1200);
        TransientHelper::set('wordproof_authorize_code_verifier', $codeVerifier, 1200);
        TransientHelper::set('wordproof_authorize_current_url', $redirectUrl ?: $originalUrl);

        $encoded = base64_encode(hash('sha256', $codeVerifier, true));
        $codeChallenge = strtr(rtrim($encoded, '='), '+/', '-_');

        $data = [
            'client_id'             => EnvironmentHelper::client(),
            'redirect_uri'          => self::getCallbackUrl(),
            'response_type'         => 'code',
            'scope'                 => '',
            'state'                 => $state,
            'code_challenge'        => $codeChallenge,
            'code_challenge_method' => 'S256',
            'partner'               => AppConfigHelper::getPartner(),
        ];

        /**
         * Login with user if v2 plugin data exist.
         */
        $accessToken = TransientHelper::get('wordproof_v2_authenticate_with_token');
        if ($accessToken) {
            $data = array_merge($data, ['access_token_login' => $accessToken]);
        } else {
            $data = array_merge($data, ['confirm_account' => true]);
        }

        self::redirect('/wordpress-sdk/authorize', $data);
    }

    /**
     * Retrieve the access token with the state and code.
     *
     * @param string $state The state from remote
     * @param string $code The code from remote
     * @return \WP_REST_Response
     * @throws \Exception
     */
    public static function token($state, $code)
    {
        $localState = TransientHelper::getOnce('wordproof_authorize_state');
        $codeVerifier = TransientHelper::getOnce('wordproof_authorize_code_verifier');

        if (strlen($localState) <= 0 || $localState !== $state) {
            throw new \Exception('WordProof: No state found.');
        }

        $data = [
            'grant_type'    => 'authorization_code',
            'client_id'     => EnvironmentHelper::client(),
            'redirect_uri'  => self::getCallbackUrl(),
            'code_verifier' => $codeVerifier,
            'code'          => $code,
        ];

        $response = Api::post('/api/wordpress-sdk/token', $data);

        if (isset($response->error) && $response->error === 'invalid_grant') {
            $data = (object)[
                'status'  => 401,
                'message' => 'invalid_grant'
            ];
            return new \WP_REST_Response($data, $data->status);
        }

        if (!isset($response->access_token)) {
            $data = (object)[
                'status'  => 401,
                'message' => 'no_access_token'
            ];
            return new \WP_REST_Response($data, $data->status);
        }

        OptionsHelper::setAccessToken($response->access_token);

        $data = [
            'webhook_url'          => get_rest_url(null, 'wordproof/v1/webhook'),
            'url'                  => get_site_url(),
            'available_post_types' => PostTypeHelper::getPublicPostTypes(),
            'partner'              => AppConfigHelper::getPartner(),
            'local_settings'       => (array) SettingsHelper::get(),
        ];

        /**
         * Use existing source if user was authenticated in v2 of the plugin.
         */
        $sourceId = TransientHelper::getOnce('wordproof_v2_get_existing_source');
        if ($sourceId) {
            $data = array_merge($data, ['source_id' => intval($sourceId)]);
        }

        $response = Api::post('/api/wordpress-sdk/source', $data);

        OptionsHelper::setSourceId($response->source_id);

        $data = (object)[
            'status'           => 200,
            'message'          => 'authentication_success',
            'source_id'        => OptionsHelper::get('source_id'),
            'is_authenticated' => AuthenticationHelper::isAuthenticated()
        ];
        return new \WP_REST_Response($data, $data->status);
    }

    private static function getCallbackUrl()
    {
        return get_rest_url(null, self::$callbackEndpoint);
    }

    public static function redirect($endpoint, $parameters)
    {
        $location = EnvironmentHelper::url() . $endpoint . '?' . http_build_query($parameters);
        header("Location: " . $location);
    }
}
