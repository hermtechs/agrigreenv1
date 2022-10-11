<?php

namespace CreativeMail\Managers;

use CreativeMail\CreativeMail;
use CreativeMail\Helpers\EnvironmentHelper;
use CreativeMail\Helpers\OptionsHelper;
use CreativeMail\Helpers\SsoHelper;
use CreativeMail\Modules\DashboardWidgetModule;
use CreativeMail\Modules\FeedbackNoticeModule;
use Exception;

/**
 * The AdminManager will manage the admin section of the plugin.
 *
 * @ignore
 */
class AdminManager
{

    protected $instance_name;
    protected $instance_uuid;
    protected $instance_handshake_token;
    protected $instance_id;
    protected $instance_url;
    protected $instance_callback_url;
    protected $dashboard_url;

    const ADMIN_NOTICES_HOOK = 'admin_notices';
    const ADMIN_INIT_HOOK = 'admin_init';
    const ADMIN_ENQUEUE_SCRIPTS_HOOK = 'admin_enqueue_scripts';

    const ADMIN_AJAX_NONCE = 'ajax-nonce';
    const ADMIN_NONCE = 'nonce';

    const DOMAIN_CE4WP = 'ce4wp';

    /**
     * AdminManager constructor.
     */
    public function __construct()
    {
        $this->instance_name = rawurlencode(get_bloginfo('name'));
        $this->instance_handshake_token = OptionsHelper::get_handshake_token();
        $this->instance_uuid = OptionsHelper::get_instance_uuid();
        $this->instance_id = OptionsHelper::get_instance_id();
        $this->instance_url = rawurlencode(get_bloginfo('wpurl'));
        $this->instance_callback_url = rawurlencode(get_bloginfo('wpurl') . '?rest_route=/creativemail/v1/callback');
        $this->dashboard_url = EnvironmentHelper::get_app_url() . 'marketing/dashboard?wp_site_name=' . $this->instance_name
                               . '&wp_site_uuid=' . $this->instance_uuid
                               . '&wp_callback_url=' . $this->instance_callback_url
                               . '&wp_instance_url=' . $this->instance_url
                               . '&wp_version=' . get_bloginfo('version')
                               . '&plugin_version=' . CE4WP_PLUGIN_VERSION;
    }

    /**
     * Will register all the hooks for the admin portion of the plugin.
     */
    public function add_hooks()
    {
        add_action('admin_menu', array( $this, 'build_menu' ));
        add_action(self::ADMIN_ENQUEUE_SCRIPTS_HOOK, array( $this, 'add_assets' ));
        add_action(self::ADMIN_NOTICES_HOOK,  array($this, 'add_admin_notice_permalink' ));
        add_action(self::ADMIN_NOTICES_HOOK,  array($this, 'add_admin_notice_review' ));
        add_action(self::ADMIN_NOTICES_HOOK,  array($this, 'add_admin_get_started_banner' ));
        add_action(self::ADMIN_NOTICES_HOOK,  array($this, 'add_admin_feedback_notice' ));
        add_action(self::ADMIN_INIT_HOOK, array($this, 'activation_redirect' ));
        add_action(self::ADMIN_INIT_HOOK, array($this, 'ignore_review_notice' ));

        add_filter('admin_footer_text', array( $this, 'admin_footer_text' ), 1 );
        add_action('wp_ajax_woocommerce_ce4wp_rated', array( $this, 'mark_as_rated' ) );
        add_action('wp_dashboard_setup', array( $this, 'add_admin_dashboard_widget' ) );

        // sso request
        add_action('wp_ajax_ce4wp_request_sso', [$this, 'request_single_sign_on_url'] );

        // deactivation footer
        //add_action(self::ADMIN_ENQUEUE_SCRIPTS_HOOK, [$this, 'deactivation_modal_js'], 20);
        //add_action(self::ADMIN_ENQUEUE_SCRIPTS_HOOK, [$this, 'deactivation_modal_css']);
        //add_action('admin_footer', [$this, 'show_deactivation_modal']);
        //add_action('wp_ajax_ce4wp_deactivate_survey', [$this, 'deactivate_survey_post'] );
    }

    private function check_nonce()
    {
        $nonce = $_POST[self::ADMIN_NONCE];

        if (!wp_verify_nonce($nonce,self::ADMIN_AJAX_NONCE))
        {
            die (admin_url('admin.php?page=creativemail'));
        }
    }
    private function create_nonce() {
        return wp_create_nonce(self::ADMIN_AJAX_NONCE);
    }

    function request_single_sign_on_url()
    {
        // Check for nonce security
        $this->check_nonce();

        $linkReference = $_POST['link_reference'];
        $linkParameters = $_POST['link_parameters'];

        $sso = $this->get_sso_link($linkReference, $linkParameters);

        if (is_null($sso)) {
            $redirectUrl = EnvironmentHelper::get_app_gateway_url('wordpress/v1.0/instances/open?clearSession=true&redirectUrl=');
            $onboardingUrl = EnvironmentHelper::get_app_url() . 'marketing/onboarding/signup?wp_site_name=' . $this->instance_name
                . '&wp_site_uuid=' . $this->instance_uuid
                . '&wp_handshake=' . $this->instance_handshake_token
                . '&wp_callback_url=' . $this->instance_callback_url
                . '&wp_instance_url=' . $this->instance_url
                . '&wp_version=' . get_bloginfo('version')
                . '&plugin_version=' . CE4WP_PLUGIN_VERSION;
            $referred_by = OptionsHelper::get_referred_by();
            if (isset($referred_by)) {
                $utm_campaign = '';
                if (is_array($referred_by) && array_key_exists('plugin', $referred_by) && array_key_exists('source', $referred_by)) {
                    $utm_campaign = $referred_by['plugin'] . $referred_by['source'];
                } else if (is_string($referred_by)) {
                    $utm_campaign = str_replace(';', '|', $referred_by);
                }
                $onboardingUrl .= '&utm_source=wordpress&utm_medium=plugin&utm_campaign=' . $utm_campaign;
            }
            echo $redirectUrl . rawurlencode($onboardingUrl);
            die();
        }
        echo $sso;
        die();
    }

    function deactivate_survey_post()
    {
        // Check for nonce security
        $this->check_nonce();

        $instance_id = OptionsHelper::get_instance_id();
        $instance_api_key = OptionsHelper::get_instance_api_key();
        $connected_account_id = OptionsHelper::get_connected_account_id();

        parse_str($_POST['data'], $post_data);

        $arguments = array(
            'method' => 'POST',
            'headers' => array(
                'x-api-key' => $instance_api_key,
                'x-account-id' => $connected_account_id,
                'content-type' => 'application/json'
            ),
            'body' => wp_json_encode(
                array(
                    'instance_id' => $instance_id,
                    'survey_id' => 1,
                    'value' => $post_data['ce4wp_deactivation_option'],
                    'message' => $post_data['other']
                )
            )
        );

        wp_remote_post(EnvironmentHelper::get_app_gateway_url() . 'wordpress/v1.0/survey', $arguments);

        return true;
    }

    private function should_show_deactivation_modal() {
        if (!function_exists('get_current_screen')) {
            return false;
        }
        $screen = get_current_screen();
        if (is_null($screen)) {
            return false;
        }
        return (in_array($screen->id, ['plugins', 'plugins-network'], true));
    }

    function deactivation_modal_js() {
        if (!$this->should_show_deactivation_modal()) {
            return;
        }
        wp_enqueue_script('ce4wp_deactivate_survey', CE4WP_PLUGIN_URL.'assets/js/deactivation.js', null,null,true);
        wp_localize_script('ce4wp_deactivate_survey', 'ce4wp_data', array(
            'url' => admin_url('admin-ajax.php'),
            'nonce' => $this->create_nonce()
        ));
    }

    function deactivation_modal_css() {
        if (!$this->should_show_deactivation_modal()) {
            return;
        }
        wp_enqueue_style('ce4wp_deactivate_survey', CE4WP_PLUGIN_URL.'assets/css/deactivation.css', null,null,null);
    }

    function show_deactivation_modal() {
        if (!$this->should_show_deactivation_modal()) {
            return;
        }
        printf('<div class="ce4wp-deactivate-survey-modal" id="ce4wp-deactivate-survey">
          <div class="ce4wp-deactivate-survey-wrap">
            <div class="ce4wp-deactivate-survey">
                <h2>%s</h2>
                <form method="post" id="ce4wp-deactivate-survey-form">
                    <fieldset>
                    <span><input type="radio" name="ce4wp_deactivation_option" value="0"> %s</span>
                    <span><input type="radio" name="ce4wp_deactivation_option" value="1"> %s</span>
                    <span><input type="radio" name="ce4wp_deactivation_option" value="2"> %s</span>
                    <span><input type="radio" name="ce4wp_deactivation_option" value="3"> %s</span>
                    <span><input type="radio" name="ce4wp_deactivation_option" value="4"> %s</span>
                    <span><input type="radio" name="ce4wp_deactivation_option" value="5"> %s</span>
                    <span><input type="radio" name="ce4wp_deactivation_option" value="6"> %s: <input type="text" name="other" /></span>
                    <br>
                    <span><input type="submit" class="button button-primary" value="Submit"></span>
                    </fieldset>
                </form>
                <p id="ce4wp-deactivate-survey-form-success">%s</p>
                <a class="button" id="ce4wp-deactivate-survey-close">%s</a>
            </div>
          </div>
        </div>',
            __('Why are you deactivating Creative Mail?', self::DOMAIN_CE4WP),
            __('I no longer send newsletters', self::DOMAIN_CE4WP),
            __('I do not like the email designer', self::DOMAIN_CE4WP),
            __('I could not get the plugin to work', self::DOMAIN_CE4WP),
            __('My version of PHP is not supported', self::DOMAIN_CE4WP),
            __('Emails are not sending or arriving', self::DOMAIN_CE4WP),
            __('Its a temporary deactivation', self::DOMAIN_CE4WP),
            __('Other', self::DOMAIN_CE4WP),
            __('Thank you', self::DOMAIN_CE4WP),
            __('Close this window and deactivate Creative Mail', self::DOMAIN_CE4WP)
        );
    }

    function add_admin_notice_review()
    {

        $install_date = get_option('ce4wp_install_date');
        if (!$install_date) {
            return false;
        }

        $install_date = date_create($install_date);
        $date_now     = date_create(date('Y-m-d G:i:s'));
        $date_diff    = date_diff($install_date, $date_now);

        if ($date_diff->format("%d") < 7 ) {

            return false;
        }

        if (! get_option('ce4wp_ignore_review_notice') ) {

            echo '<div class="updated"><p>';

            /* translators: text. */
            printf(
                __('Awesome, you\'ve been using <a href="admin.php?page=creativemail">Creative Mail</a> for more than 1 week. May we ask you to give it a 5-star rating on WordPress? | <a href="%2$s" target="_blank">Ok, you deserved it</a> | <a href="%1$s">I already did</a> | <a href="%1$s">No, not good enough</a>', 'ce4wp'), '?ce4wp-ignore-notice=0',
                'https://wordpress.org/plugins/creative-mail-by-constant-contact/'
            );
            echo "</p></div>";
        }
    }

    public function ignore_review_notice()
    {
        if (isset($_GET['ce4wp-ignore-notice']) && '0' == $_GET['ce4wp-ignore-notice'] ) {
            update_option('ce4wp_ignore_review_notice', 'true');
        }
    }

    public function mark_as_rated()
    {

        update_option('ce4wp_admin_footer_text_rated', 1);

        wp_die();
    }

    /**
     * Changes the admin footer text on admin pages.
     *
     * @param string $footer_text
     *
     * @return string
     */
    public function admin_footer_text( $footer_text )
    {
        if ($this->is_cm_screen_and_show_footer())
        {
            $footer_text = sprintf(
                esc_html__('If you like %1$s please leave us a %2$s rating. A huge thanks in advance!', self::DOMAIN_CE4WP),
                sprintf('<strong>%s</strong>', esc_html__('Creative Mail', self::DOMAIN_CE4WP)),
                '<a href="https://wordpress.org/plugins/creative-mail-by-constant-contact/#reviews?rate=5#new-post" target="_blank" class="ce4wp-rating-link" data-rated="' . esc_attr__('Thank You', self::DOMAIN_CE4WP) . '">&#9733;&#9733;&#9733;&#9733;&#9733;</a>'
            );
        }

        return $footer_text;
    }


    function is_cm_screen_and_show_footer() {
        $screen = get_current_screen();

        if (! empty($screen)
            && ('toplevel_page_creativemail' === $screen->id  || 'creative-mail_page_creativemail_settings' === $screen->id )
            && ! get_option('ce4wp_admin_footer_text_rated')
        ) {
            return true;
        }
        return false;
    }

    /**
     * Redirects the user after plugin activation.
     */
    function activation_redirect()
    {
        if (intval(get_option('ce4wp_activation_redirect', false)) === wp_get_current_user()->ID ) {
            // Make sure we don't redirect again after this one
            delete_option('ce4wp_activation_redirect');
            wp_safe_redirect(admin_url('admin.php?page=creativemail'));
            exit;
        }
    }

    /**
     * Will add all the required assets for the admin portion of the plugin.
     */
    public function add_assets()
    {
        wp_register_style('ce4wp_admin', CE4WP_PLUGIN_URL . 'assets/css/admin.css', null, CE4WP_PLUGIN_VERSION);
        wp_enqueue_style('ce4wp_admin');
        wp_enqueue_style('ce4wp-font-poppins', 'https://fonts.googleapis.com/css?family=Poppins:400,500');
        wp_enqueue_script('wp-api');

        if ($this->is_cm_screen_and_show_footer())
        {
            wp_enqueue_script('ce4wp_admin_footer_rating', CE4WP_PLUGIN_URL . 'assets/js/footer_rating.js', null, CE4WP_PLUGIN_VERSION, true);
        }
    }

    /**
     * Will build the menu for WP-Admin
     */
    public function build_menu()
    {
        // Did the user complete the entire setup?
        $main_action = OptionsHelper::get_instance_id() !== null
            ? array( $this, 'show_dashboard' )
            : array( $this, 'show_setup' );

        // Create the root menu item
        $icon = file_get_contents(CE4WP_PLUGIN_DIR . 'assets/images/icon.svg');
        add_menu_page('Creative Mail', esc_html__('Creative Mail', self::DOMAIN_CE4WP), 'manage_options', 'creativemail', $main_action, 'data:image/svg+xml;base64,' . base64_encode($icon), '99.68491');

        $sub_actions = array(
            array(
                'title'    => esc_html__('Settings', self::DOMAIN_CE4WP),
                'text'     => 'Settings',
                'slug'     => 'creativemail_settings',
                'callback' => array( $this, 'show_settings_page' )
            )
        );

        foreach ($sub_actions as $sub_action) {
            add_submenu_page('creativemail', 'Creative Mail - ' . $sub_action['title'], $sub_action['text'], 'manage_options', $sub_action['slug'], $sub_action['callback']);
        }
    }

    public function add_admin_notice_permalink()
    {
        if (CreativeMail::get_instance()->get_integration_manager()->is_plugin_active('woocommerce')) {
            if (! CreativeMail::get_instance()->get_integration_manager()->get_permalinks_enabled() ) {
                print( '<div class="notice notice-error is-dismissible"><p>Ohoh, pretty permalinks are disabled. To enable the CreativeMail WooCommerce integration <a href="/wp-admin/options-permalink.php">please update your permalink settings</a>.</p></div>');
                return;
            }
        }
    }

    public function add_admin_get_started_banner()
    {
        $ce_has_account = OptionsHelper::get_instance_id() != null;

        global $pagenow;
        if ( $pagenow == 'plugins.php' && !$ce_has_account ) {
            include CE4WP_PLUGIN_DIR . 'src/views/admin-get-started-banner.php';
        }
    }

    public function add_admin_feedback_notice()
    {
        global $pagenow;
        global $post_type;

        if ( $pagenow == 'edit.php' && $post_type == 'feedback' ) {
            $feedback_notice_module = new FeedbackNoticeModule();
            $feedback_notice_module->display();
        }
    }

    public function add_admin_dashboard_widget()
    {
        $widget_title = wp_kses(
        /* translators: Placeholder is a CreativeMail logo. */
            __( 'Email Marketing <span class="floater">By<div class="ce4wp_dashboard_icon"></div></span>', 'ce4wp'),
            array( 'span' => array( 'class' => array() ), 'div' => array( 'class' => array() ) )
        );

        add_meta_box(
            'ce4wp_admin_dashboard_widget',
            $widget_title,
            array( $this, 'show_ce4wp_admin_dashboard_widget' ),
            'dashboard',
            'normal',
            'high'
        );
    }

    public function show_ce4wp_admin_dashboard_widget()
    {
        $dashboard_widget_module = new DashboardWidgetModule();
        $dashboard_widget_module->show();
    }

    /**
     * Renders the onboarding flow.
     */
    public function show_setup()
    {
        include CE4WP_PLUGIN_DIR . 'src/views/onboarding.php';
    }

    /**
     * Renders the consent screen.
     */
    public function show_consent()
    {
        include CE4WP_PLUGIN_DIR . 'src/views/consent.php';
    }

    /**
     * Renders the Creative Mail dashboard when the site is connected to an account.
     */
    public function show_dashboard()
    {
        wp_enqueue_script('ce4wp_dashboard', CE4WP_PLUGIN_URL.'assets/js/dashboard.js', null,CE4WP_PLUGIN_VERSION);
        wp_localize_script('ce4wp_dashboard', 'ce4wp_data', array(
            'url' => admin_url('admin-ajax.php'),
            'nonce' => $this->create_nonce()
        ));

        include CE4WP_PLUGIN_DIR . 'src/views/dashboard.php';
    }

    /**
     * Generates an SSO link for the current user.
     *
     * @param $linkReference      string|null
     * @param $linkParameters     array|null
     *
     * @return string|null
     * @since  1.1.5
     */
    public function get_sso_link(string $linkReference = null, array $linkParameters = null)
    {
        // Only if you are running in wp-admin
        if(!current_user_can('administrator')) {
            return null;
        }

        // If all the three values are available, we can use the SSO flow
        $instance_id = OptionsHelper::get_instance_id();
        $instance_api_key = OptionsHelper::get_instance_api_key();
        $connected_account_id = OptionsHelper::get_connected_account_id();

        if (isset($instance_id) && isset($instance_api_key) && isset($connected_account_id)) {
            try {
                return SsoHelper::generate_sso_link($instance_id, $instance_api_key, $connected_account_id, $linkReference, $linkParameters);
            }
            catch(Exception $ex) {
            }
        }

        return null;
    }

    /**
     * Renders the settings page for this plugin.
     */
    public function show_settings_page()
    {
        include CE4WP_PLUGIN_DIR . 'src/views/settings.php';
    }
}
