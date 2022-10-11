<?php

namespace WordProof\SDK\Config;

class DefaultAppConfig implements AppConfigInterface
{
    /**
     * @return string
     */
    public function getPartner()
    {
        return 'wordproof';
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return 'production';
    }

    /**
     * @return null
     */
    public function getOauthClient()
    {
        return null;
    }

    /**
     * @return null
     */
    public function getWordProofUrl()
    {
        return null;
    }

    /**
     * @return null
     */
    public function getScriptsFileOverwrite()
    {
        return null;
    }
}
