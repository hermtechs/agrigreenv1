<?php


namespace CreativeMail\Managers;

use CreativeMail\Helpers\OptionsHelper;
use CreativeMail\Integrations\Integration;
use CreativeMail\Modules\Contacts\Handlers\ContactFormSevenPluginHandler;
use CreativeMail\Modules\Contacts\Handlers\ElementorPluginHandler;
use CreativeMail\Modules\Contacts\Handlers\GravityFormsPluginHandler;
use CreativeMail\Modules\Contacts\Handlers\NewsLetterContactFormPluginHandler;
use CreativeMail\Modules\Contacts\Handlers\WooCommercePluginHandler;
use CreativeMail\Modules\Contacts\Handlers\WpFormsPluginHandler;
use CreativeMail\Modules\Contacts\Handlers\JetpackPluginHandler;
use CreativeMail\Modules\Contacts\Handlers\NinjaFormsPluginHandler;
use CreativeMail\Modules\Contacts\Handlers\CalderaPluginHandler;
use CreativeMail\Modules\Contacts\Processes\ContactUploadProcess;
use ReflectionClass;

/**
 * Class IntegrationManager
 *
 * The IntegrationManager will manage all the supported integrations with third party plugins.
 *
 * @package CreativeMail\Managers
 */
class IntegrationManager
{
    private $supported_integrations;
    private $active_integrations;

    public function __construct()
    {
        $this->active_integrations = array();

        // Setup the default integrations
        $this->supported_integrations = array(
            new Integration('jetpack', 'Jetpack Forms', 'jetpack/jetpack.php', JetpackPluginHandler::class, false),
            new Integration('jetpack-beta', 'Jetpack Forms (Beta)', 'jetpack-beta-master/jetpack-beta.php', JetpackPluginHandler::class, true),
            new Integration('woocommerce', 'WooCommerce', 'woocommerce/woocommerce.php', WooCommercePluginHandler::class, false),
            new Integration('contact-form-7', 'Contact Form 7', 'contact-form-7/wp-contact-form-7.php', ContactFormSevenPluginHandler::class, false),
            new Integration('newsletter', 'Newsletter', 'newsletter/plugin.php', NewsLetterContactFormPluginHandler::class, false),
            new Integration('wpforms', 'WPForms', 'wpforms/wpforms.php', WpFormsPluginHandler::class, false),
            new Integration('wpformslite', 'WPForms Lite', 'wpforms-lite/wpforms.php', WpFormsPluginHandler::class, true),
            new Integration('gravityforms', 'GravityForms', 'gravityforms/gravityforms.php', GravityFormsPluginHandler::class, false),
            new Integration('elementor', 'Elementor', 'elementor/elementor.php', ElementorPluginHandler::class, false),
            new Integration('ninjaforms', 'Ninja forms', 'ninja-forms/ninja-forms.php', NinjaFormsPluginHandler::class, false),
            new Integration('caldera', 'Caldera Forms', 'caldera-forms/caldera-core.php', CalderaPluginHandler::class, false)
        );
    }

    /**
     * Will register all the required hooks for this manager.
     */
    public function add_hooks()
    {
        $active_plugins = array_filter(
            $this->get_active_plugins(), function ($item) {
                return array_search($item->get_slug(), $this->get_activated_plugins(), true) !== false;
            }
        );

        foreach ($active_plugins as $active_plugin)
        {
            try
            {
                if (array_key_exists($active_plugin->get_slug(), $this->active_integrations) === false) {
                    // use reflection to create instance of class
                    $class = new ReflectionClass($active_plugin->get_integration_handler());
                    $this->active_integrations[$active_plugin->get_slug()] = $class->newInstance();
                }
                // register hooks for integration class
                $this->active_integrations[$active_plugin->get_slug()]->registerHooks();
            } catch (\Exception $e) {
                // silent
            }
        }
    }

    /**
     * Will remove all the registered hooks.
     */
    public function remove_hooks()
    {
        foreach($this->active_integrations as $active_integration){
            $active_integration->unregisterHooks();
        }
    }

    /**
     * Will get all the supported plugins that are installed and active on this WP instance.
     *
     * @return array
     */
    public function get_active_plugins()
    {

        $activated_plugins = array();

        foreach ($this->supported_integrations as $integration) {

            // Check if the plugin is activated
            if (in_array($integration->get_class(), apply_filters('active_plugins', get_option('active_plugins')))) {
                array_push($activated_plugins, $integration);
            }
        }

        return $activated_plugins;
    }

    public function is_plugin_active($slug)
    {
         return array_key_exists($slug, $this->active_integrations);
    }

    /**
     * Stores the plugins that were activated by the user.
     *
     * @param $plugins
     */
    public function set_activated_plugins($plugins)
    {

        // Store the activated plugins
        OptionsHelper::set_activated_plugins(implode(';', $plugins));

        // Remove the hooks and add them again
        $this->remove_hooks();
        $this->add_hooks();

        do_action(CE4WP_SYNCHRONIZE_ACTION, 250);
    }

    /**
     * Gets a list of slugs representing the plugins that were activated by the user.
     *
     * @return array
     */
    public function get_activated_plugins()
    {
        $activated_plugins = OptionsHelper::get_activated_plugins();
        if (is_null($activated_plugins)) {
            $activated_plugins = '';
        }
        if (is_array($activated_plugins)) {
            $activated_plugins = implode(';', $activated_plugins);
        }
        return explode(';', $activated_plugins);
    }

    /**
     * Will return a list of the activated integrations.
     *
     * @return array
     */
    public function get_activated_integrations()
    {
        return array_filter(
            $this->get_active_plugins(), function ($item) {
                return array_search($item->get_slug(), $this->get_activated_plugins(), true) !== false;
            }
        );
    }

    /**
     * Will return a list of all the integrations that we support.
     *
     * @return array A list of all the supported integrations.
     */
    public function get_supported_integrations()
    {
        return $this->supported_integrations;
    }

    public function get_permalinks_enabled()
    {
        return get_option('permalink_structure') !== '';
    }
}
