<?php

namespace WordProof\SDK\Config;

interface AppConfigInterface
{
    /**
     * Your partner name.
     *
     * @default wordproof
     * @return string
     */
    public function getPartner();

    /**
     * The WordProof environment used. Either staging or production.
     *
     * @default production
     * @return string
     */
    public function getEnvironment();

    /**
     * Only used for local development.
     *
     * @return integer
     */
    public function getOauthClient();

    /**
     * Only used for local development.
     *
     * @return string
     */
    public function getWordProofUrl();

    /**
     * Only used for local development.
     *
     * @return string
     */
    public function getScriptsFileOverwrite();
}
