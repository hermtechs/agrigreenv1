<?php

namespace WordProof\SDK\Translations;

interface TranslationsInterface
{
    public function getNoBalanceNotice();

    public function getTimestampSuccessNotice();

    public function getTimestampFailedNotice();

    public function getWebhookFailedNotice();

    public function getNotAuthenticatedNotice();

    public function getOpenSettingsButtonText();

    public function getOpenAuthenticationButtonText();
}
