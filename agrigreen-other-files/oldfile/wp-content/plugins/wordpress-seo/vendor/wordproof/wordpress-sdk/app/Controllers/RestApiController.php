<?php

namespace WordProof\SDK\Controllers;

use WordProof\SDK\Helpers\OptionsHelper;
use WordProof\SDK\Helpers\RestApiHelper;
use WordProof\SDK\Helpers\PostMetaHelper;
use WordProof\SDK\Helpers\SchemaHelper;
use WordProof\SDK\Helpers\SettingsHelper;
use WordProof\SDK\Helpers\AuthenticationHelper;
use WordProof\SDK\Helpers\StringHelper;
use WordProof\SDK\Support\Authentication;

class RestApiController
{
    /**
     * Registers the rest api endpoints.
     *
     * @action rest_api_init
     * @throws \Exception
     */
    public function init()
    {
        register_rest_route(RestApiHelper::getNamespace(), RestApiHelper::endpoint('authenticate'), [
            'methods'             => 'POST',
            'callback'            => [$this, 'authenticate'],
            'permission_callback' => [$this, 'canPublishPermission']
        ]);

        register_rest_route(RestApiHelper::getNamespace(), RestApiHelper::endpoint('webhook'), [
            'methods'             => 'POST',
            'callback'            => [$this, 'webhook'],
            'permission_callback' => [$this, 'isValidWebhookRequest']
        ]);

        register_rest_route(RestApiHelper::getNamespace(), RestApiHelper::endpoint('hashInput'), [
            'methods'             => 'GET',
            'callback'            => [$this, 'hashInput'],
            'permission_callback' => function () {
                return true;
            },
        ]);

        register_rest_route(RestApiHelper::getNamespace(), RestApiHelper::endpoint('timestamp'), [
            'methods'             => 'POST',
            'callback'            => [$this, 'timestamp'],
            'permission_callback' => [$this, 'canPublishPermission'],
        ]);

        register_rest_route(RestApiHelper::getNamespace(), RestApiHelper::endpoint('timestamp.transaction.latest'), [
            'methods'             => 'GET',
            'callback'            => [$this, 'showLatestTimestampTransaction'],
            'permission_callback' => [$this, 'canPublishPermission'],
        ]);

        register_rest_route(RestApiHelper::getNamespace(), RestApiHelper::endpoint('settings'), [
            'methods'             => 'GET',
            'callback'            => [$this, 'settings'],
            'permission_callback' => [$this, 'canPublishPermission'],
        ]);

        register_rest_route(RestApiHelper::getNamespace(), RestApiHelper::endpoint('saveSettings'), [
            'methods'             => 'POST',
            'callback'            => [$this, 'saveSettings'],
            'permission_callback' => [$this, 'canPublishPermission'],
        ]);

        register_rest_route(RestApiHelper::getNamespace(), RestApiHelper::endpoint('authentication'), [
            'methods'             => 'GET',
            'callback'            => [$this, 'authentication'],
            'permission_callback' => [$this, 'canPublishPermission'],
        ]);

        register_rest_route(RestApiHelper::getNamespace(), RestApiHelper::endpoint('authentication.destroy'), [
            'methods'             => 'POST',
            'callback'            => [$this, 'destroyAuthentication'],
            'permission_callback' => [$this, 'canPublishPermission'],
        ]);
    }

    /**
     * Returns an object containing the settings.
     *
     * @return \WP_REST_Response Returns the settings.
     */
    public function settings()
    {
        $data = SettingsHelper::get();
        $data->status = 200;

        return new \WP_REST_Response($data, $data->status);
    }

    /**
     * Save the settings.
     *
     * @return \WP_REST_Response Returns the settings.
     */
    public function saveSettings(\WP_REST_Request $request)
    {
        $data = $request->get_params();

        $settings = $data['settings'];
        $snakeCaseSettings = [];
        foreach ($settings as $key=> $value) {
            $key = StringHelper::toUnderscore($key);
            $snakeCaseSettings[$key] = $value;
        }

        OptionsHelper::set('settings', $snakeCaseSettings);

        $data = (object)[];
        $data->status = 200;

        return new \WP_REST_Response($data, $data->status);
    }

    /**
     * Returns if the user is authenticated.
     *
     * @return \WP_REST_Response Returns if the user is authenticated.
     */
    public function authentication()
    {
        $data = (object)[
            'is_authenticated' => AuthenticationHelper::isAuthenticated(),
            'status'           => 200
        ];

        return new \WP_REST_Response($data, $data->status);
    }

    /**
     * Logout the user and return if the user is authenticated.
     *
     * @return \WP_REST_Response Returns if the user is authenticated.
     */
    public function destroyAuthentication()
    {
        AuthenticationHelper::logout();

        return $this->authentication();
    }

    /**
     * Send a post request to WordProof to timestamp a post.
     *
     * @param \WP_REST_Request $request The Rest Request.
     * @return bool|void Returns if the request was successful.
     */
    public function timestamp(\WP_REST_Request $request)
    {
        $data = $request->get_params();
        $postId = intval($data['id']);

        return TimestampController::timestamp($postId);
    }

    public function showLatestTimestampTransaction(\WP_REST_Request $request)
    {
        $data = $request->get_params();
        $postId = intval($data['id']);

        $transactions = PostMetaHelper::get($postId, '_wordproof_blockchain_transaction', false);
        $transaction = array_pop($transactions);

        return new \WP_REST_Response((object)$transaction);
    }

    /**
     * Returns the hash input of a post.
     *
     * @param \WP_REST_Request $request The Rest Request.
     * @return \WP_REST_Response The hash input of a post.
     */
    public function hashInput(\WP_REST_Request $request)
    {
        $data = $request->get_params();

        $postId = intval($data['id']);
        $hash = sanitize_text_field($data['hash']);

        $hashInput = PostMetaHelper::get($postId, '_wordproof_hash_input_' . $hash);

        return new \WP_REST_Response((object)$hashInput);
    }

    /**
     * Retrieves the access token when the code and state are retrieved in the frontend.
     *
     * @throws \Exception
     */
    public function authenticate(\WP_REST_Request $request)
    {
        $state = sanitize_text_field($request->get_param('state'));
        $code = sanitize_text_field($request->get_param('code'));

        return Authentication::token($state, $code);
    }

    /**
     * Handles webhooks sent by WordProof.
     *
     * @param \WP_REST_Request $request The Rest Request.
     * @return bool|null|\WP_REST_Response|void The value returned by the action undertaken.
     *
     * TODO: Improve
     */
    public function webhook(\WP_REST_Request $request)
    {
        $response = json_decode($request->get_body());

        /**
         * Handle webhooks with type and data
         */
        if (isset($response->type) && isset($response->data)) {
            switch ($response->type) {
                case 'source_settings':
                    return OptionsHelper::set('settings', $response->data);
                case 'ping':
                    $data = (object)['status' => 200, 'source_id' => OptionsHelper::sourceId()];
                    return new \WP_REST_Response($data, $data->status);
                case 'logout':
                    AuthenticationHelper::logout();
                    break;
                case 'dump_item':

                    $key = '_wordproof_hash_input_' . $response->data->hash;
                    PostMetaHelper::update($response->data->uid, $key, json_decode($response->data->hash_input));

                    $this->setBlockchainTransaction($response->data);

                    break;
                default:
                    break;
            }
        }

        /**
         * Handle timestamping webhooks without type
         */
        if (isset($response->uid) && isset($response->schema)) {
            $this->setBlockchainTransaction($response);
        }
    }

    /**
     * @param $response
     *
     * TODO: Improve
     */
    private function setBlockchainTransaction($response)
    {
        $postId = intval($response->uid);

        $blockchainTransaction = SchemaHelper::getBlockchainTransaction($response);
        PostMetaHelper::add($postId, '_wordproof_blockchain_transaction', $blockchainTransaction);

        $schema = SchemaHelper::getSchema($postId);
        PostMetaHelper::update($postId, '_wordproof_schema', $schema);
    }

    /**
     * Checks if the user has permission to publish a post.
     *
     * @return bool Returns if a user has permission to publish.
     */
    public function canPublishPermission()
    {
        return current_user_can('publish_posts') && current_user_can('publish_pages');
    }

    /**
     * Validates if the webhook is valid and signed with the correct secret.
     *
     * @param \WP_REST_Request $request The Rest Request.
     * @return bool If the webhook can be accepted.
     */
    public static function isValidWebhookRequest(\WP_REST_Request $request)
    {
        $hashedToken = hash('sha256', OptionsHelper::accessToken());
        $hmac = hash_hmac('sha256', $request->get_body(), $hashedToken);

        return $request->get_header('signature') === $hmac;
    }
}
