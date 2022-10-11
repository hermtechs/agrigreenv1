<?php

namespace WordProof\SDK\Support;

use WordProof\SDK\Helpers\AuthenticationHelper;
use WordProof\SDK\Helpers\OptionsHelper;
use WordProof\SDK\Helpers\AppConfigHelper;

class Settings
{
    public static function redirect($redirectUrl = null)
    {
        if (!AuthenticationHelper::isAuthenticated()) {
            return false;
        }

        $options = OptionsHelper::all();

        if (!$options->source_id) {
            return false;
        }

        $endpoint = "/sources/" . $options->source_id . "/settings";

        if (AppConfigHelper::getPartner() === 'yoast') {
            $endpoint = '/yoast/dashboard';
        }

        Authentication::redirect($endpoint, [
            'redirect_uri' => $redirectUrl,
            'partner' => AppConfigHelper::getPartner(),
            'source_id' => $options->source_id,
            'access_token_login' => $options->access_token
        ]);
    }
}
