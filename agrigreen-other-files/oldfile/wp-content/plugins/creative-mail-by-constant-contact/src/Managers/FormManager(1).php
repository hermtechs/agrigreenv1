<?php


namespace CreativeMail\Managers;

use CreativeMail\Clients\CreativeMailClient;

/**
 * Class CheckoutManager
 *
 * @package CreativeMail\Managers
 */
class FormManager
{
    const NONCE = 'nonce';
    const DOMAIN = 'ce4wp';
    const TELEPHONE = 'telephone';
    const FIRSTNAME = 'first_name';
    const LASTNAME = 'last_name';
    const EMAIL = 'email';
    const CONSENT = 'consent';
    const LISTID = 'list_id';

    private $creative_mail_client;

    /**
     * DashboardWidgetModule constructor.
     */
    public function __construct()
    {
        $this->creative_mail_client = new CreativeMailClient();
    }

    /**
     * Add hooks
     *
     * @since 1.4.0+
     */
    public function add_hooks()
    {
            add_action('wp_ajax_ce4wp_form_submission', array($this, 'submit_contact'));
            add_action('wp_ajax_nopriv_ce4wp_form_submission', array($this, 'submit_contact'));
            add_action('wp_ajax_ce4wp_get_all_custom_lists', array($this, 'get_all_custom_lists'));
    }


    /**
     * AJAX handler for subscribed contact.
     *
     * @since 1.4.0+
     */
    public function submit_contact()
    {
        $data = filter_input_array(INPUT_POST, [
            self::NONCE => FILTER_SANITIZE_STRING,
            self::FIRSTNAME => FILTER_SANITIZE_STRING,
            self::TELEPHONE => FILTER_SANITIZE_STRING,
            self::LASTNAME => FILTER_SANITIZE_STRING,
            self::EMAIL => FILTER_SANITIZE_EMAIL,
            self::CONSENT => FILTER_SANITIZE_STRING,
            self::LISTID => FILTER_SANITIZE_STRING
        ]);

        if (empty($data[self::NONCE]) || !wp_verify_nonce($data[self::NONCE], 'ce4wp_form_submission')) {
            wp_send_json_error(esc_html__('Invalid nonce.', self::DOMAIN));
        }

        do_action('ce4wp_contact_submission', $data);

        wp_send_json_success();
    }

    public function get_all_custom_lists()
    {
        $data = filter_input_array(INPUT_POST, [
            self::NONCE => FILTER_SANITIZE_STRING
        ]);

        if (empty($data[self::NONCE]) || !wp_verify_nonce($data[self::NONCE], 'ce4wp_get_lists')) {
            wp_send_json_error(esc_html__('Invalid nonce.', self::DOMAIN));
        }
        $list = $this->creative_mail_client->get_all_custom_lists();
        if(isset($list)) {
            wp_send_json_success($list);
        }
        else {
            wp_send_json_error(esc_html__('Could not retrieve data.', self::DOMAIN));
        }
    }
}
