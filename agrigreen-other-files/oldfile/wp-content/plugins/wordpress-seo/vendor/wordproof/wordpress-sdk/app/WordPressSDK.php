<?php

namespace WordProof\SDK;

use WordProof\SDK\Config\DefaultAppConfig;
use WordProof\SDK\Config\AppConfigInterface;
use WordProof\SDK\Controllers\NoticeController;
use WordProof\SDK\Controllers\PostEditorDataController;
use WordProof\SDK\Controllers\PostEditorTimestampController;
use WordProof\SDK\Controllers\RestApiController;
use WordProof\SDK\Controllers\AuthenticationController;
use WordProof\SDK\Controllers\CertificateController;
use WordProof\SDK\Controllers\SettingsController;
use WordProof\SDK\Controllers\TimestampController;
use WordProof\SDK\Support\Loader;
use WordProof\SDK\Translations\DefaultTranslations;
use WordProof\SDK\Translations\TranslationsInterface;

class WordPressSDK
{
    /**
     * The version of this SDK
     * @var string
     */
    public $version = '1.0.23';

    /**
     * @var null|WordPressSDK
     */
    private static $instance = null;

    /**
     * Loader responsible for the WordPress hooks
     * @var Loader
     */
    private $loader;

    /**
     * Appconfig object
     * @var AppConfigInterface
     */
    public $appConfig;

    /**
     * Translations object
     * @var TranslationsInterface
     */
    private $translations;

    /**
     * WordPressSDK constructor.
     *
     * @return WordPressSDK|void
     *
     * @throws \Exception
     */
    public function __construct(AppConfigInterface $appConfig = null, TranslationsInterface $translations = null)
    {
        if (defined('WORDPROOF_TIMESTAMP_SDK_VERSION')) {
            return;
        }

        if (!headers_sent() && !session_id()) {
            session_start();
        }

        $this->loader = new Loader();
        $this->appConfig = $appConfig ?: new DefaultAppConfig();
        $this->translations = $translations ?: new DefaultTranslations();

        $this->authentication();
        $this->api();
        $this->timestamp();
        $this->settings();
        $this->postEditorData();
        $this->notices();

        if (!defined('WORDPROOF_TIMESTAMP_SDK_FILE')) {
            define('WORDPROOF_TIMESTAMP_SDK_FILE', __FILE__);
        }

        if (!defined('WORDPROOF_TIMESTAMP_SDK_VERSION')) {
            define('WORDPROOF_TIMESTAMP_SDK_VERSION', $this->version);
        }

        return $this;
    }

    /**
     * Singleton implementation of WordPress SDK.
     *
     * @param AppConfigInterface|null $appConfig
     * @param TranslationsInterface|null $translations
     * @return WordPressSDK|null Returns the WordPress SDK instance.
     * @throws \Exception
     */
    public static function getInstance(AppConfigInterface $appConfig = null, TranslationsInterface $translations = null)
    {
        if (self::$instance === null) {
            self::$instance = new WordPressSDK($appConfig, $translations);
        }

        return self::$instance;
    }

    /**
     * Runs the loader and initializes the class.
     *
     * @return $this
     */
    public function initialize()
    {
        $this->loader->run();
        return $this;
    }

    /**
     * Initializes the authentication feature.
     */
    private function authentication()
    {
        $class = new AuthenticationController();

        $this->loader->add_action('wordproof_authenticate', $class, 'authenticate');

        $this->loader->add_action('admin_menu', $class, 'addRedirectPage');
        $this->loader->add_action('admin_menu', $class, 'addSelfDestructPage');
        $this->loader->add_action('load-admin_page_wordproof-redirect-authenticate', $class, 'redirectOnLoad');
    }

    /**
     * Initializes the api feature.
     */
    private function api()
    {
        $class = new RestApiController();

        $this->loader->add_action('rest_api_init', $class, 'init');
    }

    /**
     * Adds hooks to timestamp posts on new inserts or on a custom action.
     */
    private function timestamp()
    {
        $class = new TimestampController();

        $this->loader->add_action('added_post_meta', $class, 'syncPostMetaTimestampOverrides', \PHP_INT_MAX, 4);
        $this->loader->add_action('updated_post_meta', $class, 'syncPostMetaTimestampOverrides', \PHP_INT_MAX, 4);

        $this->loader->add_action('rest_after_insert_post', $class, 'timestampAfterRestApiRequest');
        $this->loader->add_action('wp_insert_post', $class, 'timestampAfterPostRequest', \PHP_INT_MAX, 2);

        $this->loader->add_action('wordproof_timestamp', $class, 'timestamp');

        $this->loader->add_action('elementor/document/before_save', $class, 'beforeElementorSave');
    }

    /**
     * Adds admin pages that redirect to the WordProof My settings page.
     */
    private function settings()
    {
        $class = new SettingsController();

        $this->loader->add_action('wordproof_settings', $class, 'redirect');

        $this->loader->add_action('admin_menu', $class, 'addRedirectPage');
        $this->loader->add_action('load-admin_page_wordproof-redirect-settings', $class, 'redirectOnLoad');
    }

    /**
     * Registers and localizes post editor scripts.
     */
    private function postEditorData()
    {
        $class = new PostEditorDataController($this->translations);

        $this->loader->add_action('admin_enqueue_scripts', $class, 'addScript');
        $this->loader->add_action('elementor/editor/before_enqueue_scripts', $class, 'addScriptForElementor');
    }

    /**
     * Initializes the notices feature.
     */
    private function notices()
    {
        $class = new NoticeController($this->translations);

        $this->loader->add_action('admin_notices', $class, 'show');
    }

    /**
     * Optional feature to include the schema and certificate to the page.
     *
     * @return $this
     */
    public function certificate()
    {
        $class = new CertificateController();

        $this->loader->add_action('wp_enqueue_scripts', $class, 'enqueue');
        $this->loader->add_action('wp_head', $class, 'head');
        $this->loader->add_filter('the_content', $class, 'certificateTag');

        return $this;
    }

    /**
     * Optional feature to timestamp with JS in the post editor.
     *
     * @return $this
     */
    public function timestampInPostEditor()
    {
        $class = new PostEditorTimestampController();

        // Gutenberg
        $this->loader->add_action('init', $class, 'registerPostMeta', \PHP_INT_MAX);
        $this->loader->add_action('enqueue_block_editor_assets', $class, 'enqueueBlockEditorScript');

        // Classic editor
        $this->loader->add_action('add_meta_boxes', $class, 'addMetaboxToClassicEditor');
        $this->loader->add_action('save_post', $class, 'saveClassicMetaboxPostMeta');
        $this->loader->add_action('admin_enqueue_scripts', $class, 'enqueueClassicEditorScript');

        // Elementor
        $this->loader->add_action('elementor/editor/after_enqueue_scripts', $class, 'enqueueElementorEditorScript');
        $this->loader->add_action('elementor/documents/register_controls', $class, 'registerControl');
        $this->loader->add_action('elementor/editor/after_save', $class, 'elementorSave');

        return $this;
    }
}
