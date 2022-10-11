<?php

namespace CreativeMail\Modules\Contacts\Handlers;

define('CE4WP_CAL_EVENTTYPE', 'WordPress - Caldera Forms');

use CreativeMail\Modules\Contacts\Models\ContactModel;
use CreativeMail\Modules\Contacts\Models\OptActionBy;

class CalderaPluginHandler extends BaseContactFormPluginHandler
{

    private function GetNameFromForm($entry)
    {
        if ($this->isNullOrEmpty($entry)){
            return null;
        }

        $name = null;
        foreach ($entry as $field) {
            if($field->slug === "first_name") {
                $name["firstname"] = $field->value;
                continue;
            }
            if($field->slug === "last_name"){
                $name["lastname"] = $field->value;
                return $name;
            }
        }
        return $name;
    }

    private function GetEmailFromForm($entry)
    {
        if ($this->isNullOrEmpty($entry)){
            return null;
        }
        foreach ($entry as $field) {
            if($field->slug === "email_address" ) {
                return $field->value;
            }
        }
        return null;
    }

    public function convertToContactModel($contact)
    {
        $email = $contact->email;
        if ($this->isNullOrEmpty($email)) {
            return null;
        }

        $contactModel = new ContactModel();

        $contactModel->setEventType(CE4WP_CAL_EVENTTYPE);

        $contactModel->setOptIn(true);
        $contactModel->setOptOut(false);
        $contactModel->setOptActionBy(OptActionBy::Owner);
        $contactModel->setEmail($email);

        if ($this->isNotNullOrEmpty($contact->firstname)) {
            $contactModel->setFirstName($contact->firstname);
        }
        if ($this->isNotNullOrEmpty($contact->lastname)) {
            $contactModel->setLastName($contact->lastname);
        }

        return $contactModel;
    }

    public function ceHandleCalderaFormSubmission($form, $referrer, $process_id, $entryid)
    {
        try {
            global $wpdb;
            $calderaContact = new \stdClass();
            $entryData = $wpdb->get_results($wpdb->prepare("SELECT slug, `value` FROM wp_cf_form_entry_values WHERE entry_id = {$entryid}"));
            $nameValues = $this->GetNameFromForm($entryData);
            $calderaContact->firstname = array_key_exists("firstname", $nameValues) ? $nameValues["firstname"] : null;
            $calderaContact->lastname = array_key_exists("lastname", $nameValues) ? $nameValues["lastname"] : null;
            $calderaContact->email = $this->GetEmailFromForm($entryData);
            if (empty($calderaContact->email)) {
                return;
            }
            $this->upsertContact($this->convertToContactModel($calderaContact));
        } catch (\Exception $exception) {
            // silent exception
        }
    }

    public function registerHooks()
    {
        add_action('caldera_forms_submit_complete', array($this, 'ceHandleCalderaFormSubmission'), 60, 4); //make sure the prio is set as to run after caldera itself otherwise data is not present in db
        // add hook function to synchronize
        add_action(CE4WP_SYNCHRONIZE_ACTION, array($this, 'syncAction'));
    }

    public function unregisterHooks()
    {
        remove_action('caldera_forms_submit_complete', array($this, 'ceHandleCalderaFormSubmission'));
        // remove hook function to synchronize
        remove_action(CE4WP_SYNCHRONIZE_ACTION, array($this, 'syncAction'));
    }

    public function syncAction($limit = null)
    {
        if (!is_int($limit) || $limit <= 0) {
            $limit = null;
        }

        // Relies on plugin => GravityForms
        if (in_array('caldera-forms/caldera-core.php', apply_filters('active_plugins', get_option('active_plugins'))) && defined( 'CFCORE_VER'))
        {
            global $wpdb;

            $contactsArray = array();
            $entryIds = $wpdb->get_results($wpdb->prepare("SELECT id FROM wp_cf_form_entries WHERE status = 'active'"));

            //loop through the entries and extract necessary data
            foreach ($entryIds as $entry) {
                $contact = new \stdClass();
                $entryData = $wpdb->get_results($wpdb->prepare("SELECT slug, `value` FROM wp_cf_form_entry_values WHERE entry_id = {$entry->id}"));
                $contact->email = $this->GetEmailFromForm($entryData);
                if (empty($contact->email)) {
                    continue;
                }

                $nameValues = $this->GetNameFromForm($entryData);
                $contact->firstname = array_key_exists("firstname", $nameValues) ? $nameValues["firstname"] : null;
                $contact->lastname = array_key_exists("lastname", $nameValues) ? $nameValues["lastname"] : null;

                //Convert to contactModel
                $contactModel = $this->convertToContactModel($contact);
                if ($this->isNotNullOrEmpty($contactModel)){
                    array_push($contactsArray, $contactModel);
                }

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
}
