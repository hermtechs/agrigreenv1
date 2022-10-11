<?php
/**
 * Created by PhpStorm.
 * User: Martijn
 * Date: 2020-02-10
 * Time: 13:42
 */

namespace CreativeMail\Modules\Contacts\Handlers;

define('CE4WP_WC_EVENTTYPE', 'WordPress - WooCommerce');

use CreativeMail\Modules\Contacts\Models\ContactModel;

class WooCommercePluginHandler extends BaseContactFormPluginHandler
{
    public function convertToContactModel($orderId)
    {
        $contactModel = new ContactModel();
        $products_detail = get_post_meta($orderId);

        if (isset($products_detail)) {
            if ($this->isNotNullOrEmpty($products_detail["_billing_first_name"])) {
                $contactModel->setFirstName($products_detail["_billing_first_name"][0]);
            }
            if ($this->isNotNullOrEmpty($products_detail["_billing_last_name"])) {
                $contactModel->setLastName($products_detail["_billing_last_name"][0]);
            }

            if ($this->isNotNullOrEmpty($products_detail["_billing_email"])) {
                $contactModel->setEmail($products_detail["_billing_email"][0]);
            }

            if ($this->isNotNullOrEmpty($contactModel->getEmail())) {
                $contactModel->setEventType(CE4WP_WC_EVENTTYPE);
                $contactModel->setOptActionBy(2);
                $contactModel->setOptIn(false);
                $contactModel->setOptOut(false);
            }

            if (array_key_exists('ce_checkout_consent_checkbox', $_POST)) {
              $checkbox_value = esc_attr($_POST['ce_checkout_consent_checkbox']);
            } else if ($this->isNotNullOrEmpty($products_detail["ce_checkout_consent_checkbox"])) {
              $checkbox_value = $products_detail["ce_checkout_consent_checkbox"];
            } else if (array_key_exists('ce_checkout_consent', $_POST)) {
              // In the database the value is saved as ce_checkout_consent instead of ce_checkout_consent_checkbox
              $checkbox_value = esc_attr($_POST['ce_checkout_consent']);
            } else if ($this->isNotNullOrEmpty($products_detail["ce_checkout_consent"])) {
              $checkbox_value = $products_detail["ce_checkout_consent"];
            }

            if (!is_null($checkbox_value)) {
                $contactModel->setOptActionBy(1);
                $contactModel->setOptIn((bool)$checkbox_value);
            }

        }
        return $contactModel;
    }

    //    public function ceHandlerWooCommerceNewCustomer($customer_id, $new_customer_data) {
    //      try {
    //        $this->upsertContact($this->convertToContactModel($new_customer_data));
    //      }
    //      catch (\Exception $exception) {
    //        // silent exception
    //      }
    //    }

    function ceHandlerWooCommerceNewOrder($order_id)
    {
        try {
            $order = wc_get_order($order_id);
            $this->upsertContact($this->convertToContactModel($order->ID));
        }
        catch (\Exception $exception) {
            // silent exception
        }
    }

    public function registerHooks()
    {
        add_action('woocommerce_new_order', array($this, 'ceHandlerWooCommerceNewOrder'));
        // hook function to synchronize
        add_action(CE4WP_SYNCHRONIZE_ACTION, array($this, 'syncAction'));
        //add_action('woocommerce_created_customer', array($this,'ceHandlerWooCommerceNewCustomer'));
    }

    public function unregisterHooks()
    {
        remove_action('woocommerce_new_order', array($this, 'ceHandlerWooCommerceOrder'));
        // remove hook function to synchronize
        remove_action(CE4WP_SYNCHRONIZE_ACTION, array($this, 'syncAction'));
        //remove_action('woocommerce_created_customer', array($this,'ceHandlerWooCommerceNewCustomer'));
    }

    public function syncAction($limit = null)
    {
        if(!is_int($limit) || $limit <= 0) {
            $limit = null;
        }

        $backfillArray = array();

        $args = array(
            'posts_per_page' => -1,
            'post_type' => 'shop_order',
            'post_status'=> array_keys(wc_get_order_statuses())
        );

        if ($limit != null) {
            $args['numberposts'] = $limit;
        }

        $products_orders = get_posts($args);

        foreach ( $products_orders as $products_order ) {

            $contactModel = $this->convertToContactModel($products_order->ID);

            if($this->isNotNullOrEmpty($contactModel->getEmail())) {
                array_push($backfillArray, $contactModel);
            }

        }

        if (!empty($backfillArray)) {

            $batches = array_chunk($backfillArray, CE4WP_BATCH_SIZE);
            foreach($batches as $batch){
                $this->batchUpsertContacts($batch);
            }
        }
    }

    function __construct()
    {
        parent::__construct();
    }
}
