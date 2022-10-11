<?php

namespace WordProof\SDK\Config;

class EnvironmentConfig extends Config
{
    /**
     * Returns an array with the environment config.
     *
     * @return array
     */
    protected static function values()
    {
        return [
            'staging' => [
                'url'    => 'https://staging.wordproof.com',
                'client' => 78
            ],
            'production' => [
                'url'    => 'https://my.wordproof.com',
                'client' => 79
            ],
        ];
    }
}
