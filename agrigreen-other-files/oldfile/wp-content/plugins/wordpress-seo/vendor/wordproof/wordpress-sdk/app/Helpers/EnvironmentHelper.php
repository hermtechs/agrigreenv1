<?php

namespace WordProof\SDK\Helpers;

use WordProof\SDK\Config\EnvironmentConfig;

class EnvironmentHelper
{
    public static function url()
    {
        $appConfig = AppConfigHelper::getAppConfig();

        if ($appConfig->getWordProofUrl()) {
            return $appConfig->getWordProofUrl();
        }

        return self::get('url');
    }

    public static function client()
    {
        $appConfig = AppConfigHelper::getAppConfig();

        if ($appConfig->getOauthClient()) {
            return $appConfig->getOauthClient();
        }

        return self::get('client');
    }

    public static function sslVerify()
    {
        return ! EnvironmentHelper::development();
    }

    public static function development()
    {
        return AppConfigHelper::getEnvironment() === 'development';
    }

    public static function get($key)
    {
        $envConfig = self::environmentConfig();

        if ($envConfig && isset($envConfig[$key])) {
            return $envConfig[$key];
        }

        return null;
    }

    private static function environmentConfig()
    {
        $env = AppConfigHelper::getEnvironment();
        return EnvironmentConfig::get($env);
    }
}
