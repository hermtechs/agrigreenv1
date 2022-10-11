<?php

namespace WordProof\SDK\Helpers;

use WordProof\SDK\WordPressSDK;

class AppConfigHelper
{
    /**
     * Returns the partner set during initialization.
     *
     * @return string|null
     */
    public static function getPartner()
    {
        $appConfig = self::getAppConfig();

        if ($appConfig) {
            return $appConfig->getPartner();
        }

        return null;
    }

    /**
     * Returns the environment set during initialization.

     * @return string|null
     */
    public static function getEnvironment()
    {
        $appConfig = self::getAppConfig();

        if ($appConfig) {
            return $appConfig->getEnvironment();
        }

        return null;
    }

    public static function getAppConfig()
    {
        $sdk = WordPressSDK::getInstance();

        if ($sdk) {
            return $sdk->appConfig;
        }

        return null;
    }
}
