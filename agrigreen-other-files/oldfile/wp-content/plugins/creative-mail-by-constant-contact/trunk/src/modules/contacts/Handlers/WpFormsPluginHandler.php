<?php


namespace CreativeMail\Modules\Contacts\Handlers;

define('CE4WP_WPF_EVENTTYPE', 'WordPress - WPForms');

use CreativeMail\Modules\Contacts\Models\ContactModel;
use CreativeMail\Modules\Contacts\Models\OptActionBy;
use function Sodium\add;

class WpFormsPluginHandler extends BaseContactFormPluginHandler
{
    private function get_form_type_field($formData, $type)
    {
        foreach ($formData as $field) {
            if(array_key_exists('type', $field) && $field['type'] === $type) {
                return $field;
            }
        }
        return null;
    }

    private function convertEntryStringToFormData($entry)
    {
        $formdata = array();
        $entry = json_decode($entry->fields, true);
        foreach ($entry as $field) {
            if(array_key_exists('type', $field)) {
                $formdata[$field["type"]] = $field["value"];
            }
        }
        return $entry;
    }

    public function convertToContactModel($formData)
    {
        $contactModel = new ContactModel();

        $contactModel->setEventType(CE4WP_WPF_EVENTTYPE);
        $contactModel->setOptIn(true);
        $contactModel->setOptOut(false);
        $contactModel->setOptActionBy(OptActionBy::Visitor);

        $emailField = $this->get_form_type_field($formData, 'email');
        if (array_key_exists('value', $emailField)) {
            if ($this->isNotNullOrEmpty($emailField['value'])) {
                $contactModel->setEmail($emailField['value']);
            }
        }

        $nameField = $this->get_form_type_field($formData, 'name');
        if (array_key_exists('first', $nameField)) {
            if ($this->isNotNullOrEmpty($nameField['first'])) {
                $contactModel->setFirstName($nameField['first']);
            }
        }
        if (array_key_exists('last', $nameField)) {
            if ($this->isNotNullOrEmpty($nameField['last'])) {
                $contactModel->setLastName($nameField['last']);
            }
        }

        return $contactModel;
    }

    public function ceHandleWpFormsProcessComplete($fields)
    {
        try {
            $this->upsertContact($this->convertToContactModel($fields));
        }
        catch (\Exception $exception) {
            // silent exception
        }
    }

    public function registerHooks()
    {
        // https://wpforms.com/developers/wpforms_process_complete/
        add_action('wpforms_process_complete', array($this, 'ceHandleWpFormsProcessComplete'), 10, 4);
        add_action(CE4WP_SYNCHRONIZE_ACTION, array($this, 'syncAction'));
    }

    public function unregisterHooks()
    {
        remove_action('wpforms_process_complete', array($this, 'ceHandleWpFormsProcessComplete'));
    }

    public function syncAction($limit = null)
    {
        if (!is_int($limit) || $limit <= 0) {
            $limit = null;
        }

        // Relies on plugin => wpforms paid or pro
        if (in_array('wpforms/wpforms.php', apply_filters('active_plugins', get_option('active_plugins')))
            || in_array('wpforms-lite/wpforms.php', apply_filters('active_plugins', get_option('active_plugins')))
        ) { //this is a guess, have to test first

            //Get form submissions from the wpforms db
            global $wpdb;
            $contactsArray = array();

            //get the form entries
            $entriesQuery = 'SELECT fields FROM wp_wpforms_entries';
            $entryResult = $wpdb->get_results($wpdb->prepare($entriesQuery));

            //Loop through entries and create the contacts
            foreach ($entryResult as $entry) {
                $entryData = $this->convertEntryStringToFormData($entry);
                $contact = $this->convertToContactModel($entryData);
                array_push($contactsArray, $contact);

                if (isset($limit) && count($contactsArray) >= $limit) {
                    break;
                }
            }

            if (!empty($contactsArray)) {
                $batches = array_chunk($contactsArray, CE4WP_BATCH_SIZE);
                foreach ($batches as $batch) {
                    try {
                        $this->batchUpsertContacts($batch);
                    } catch (\Exception $exception) {
                        // silent exception
                    }
                }
            }
        }
    }

    function __construct()
    {
        parent::__construct();
    }
}
