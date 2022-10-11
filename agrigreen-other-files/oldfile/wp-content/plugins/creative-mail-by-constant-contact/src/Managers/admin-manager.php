<?php

namespace CreativeMail\Managers;

use CreativeMail\CreativeMail;
use CreativeMail\Helpers\EnvironmentHelper;
use CreativeMail\Helpers\OptionsHelper;
use CreativeMail\Helpers\SsoHelper;
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
        add_action('admin_enqueue_scripts', array( $this, 'add_assets' ));
        add_action('admin_notices',  array($this, 'add_admin_notice_permalink' ));
        add_action('admin_notices',  array($this, 'add_admin_notice_review' ));
        add_action('admin_init', array($this, 'activation_redirect' ));
        add_action('admin_init', array($this, 'ignore_review_notice' ));
        add_filter('admin_footer_text', array( $this, 'admin_footer_text' ), 1);
        add_action('wp_ajax_woocommerce_ce4wp_rated', array( $this, 'mark_as_rated' ));
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
     * @param  string $footer_text
     * @return string
     */
    public function admin_footer_text( $footer_text )
    {
        if ($this->is_cm_screen_and_show_footer())
        {
            $footer_text = sprintf(
                esc_html__('If you like %1$s please leave us a %2$s rating. A huge thanks in advance!', 'ce4wp'),
                sprintf('<strong>%s</strong>', esc_html__('Creative Mail', 'ce4wp')),
                '<a href="https://wordpress.org/plugins/creative-mail-by-constant-contact/#reviews?rate=5#new-post" target="_blank" class="ce4wp-rating-link" data-rated="' . esc_attr__('Thank You', 'ce4wp') . '">&#9733;&#9733;&#9733;&#9733;&#9733;</a>'
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
        add_menu_page('Creative Mail', esc_html__('Creative Mail'), 'manage_options', 'creativemail', $main_action, 'data:image/svg+xml;base64,' . base64_encode($icon), '99.68491');

        $sub_actions = array(
            array(
                'title'    => esc_html__('Settings', 'ce4wp'),
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
        try {
            $sso_link = $this->get_sso_link();
            if(isset($sso_link)) {
                $this->dashboard_url = $sso_link;
            }
        }
        catch(Exception $ex) {
        }

        include CE4WP_PLUGIN_DIR . 'src/views/dashboard.php';
    }

    /**
     * Generates an SSO link for the current user.
     *
     * @since 1.1.5
     */
    public function get_sso_link()
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
                return SsoHelper::generate_sso_link($instance_id, $instance_api_key, $connected_account_id);
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
