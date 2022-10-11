<?php

namespace CreativeMail\Modules\Contacts\Handlers;

define('CE4WP_NL_EVENTTYPE', 'WordPress - NewsLetter');

use CreativeMail\Modules\Contacts\Models\ContactModel;
use CreativeMail\Modules\Contacts\Models\OptActionBy;

class NewsLetterContactFormPluginHandler extends BaseContactFormPluginHandler
{
    public function convertToContactModel($user)
    {
        $contactModel = new ContactModel();

        $contactModel->setEventType(CE4WP_NL_EVENTTYPE);
        $contactModel->setOptIn(true);
        $contactModel->setOptActionBy(OptActionBy::Visitor);

        $email = $user->email;
        if ($this->isNotNullOrEmpty($email)) {
            $contactModel->setEmail($email);
        }

        $name = $user->name;
        if ($this->isNotNullOrEmpty($name)) {
            $contactModel->setFirstName($name);
        }

        $surname = $user->surname;
        if ($this->isNotNullOrEmpty($surname)) {
            $contactModel->setLastName($surname);
        }

        return $contactModel;
    }

    public function ceHandleContactNewsletterSubmit($user)
    {
        try {
            $this->upsertContact($this->convertToContactModel($user));
        }
        catch (\Exception $exception) {
            // silent exception
        }
    }

    public function registerHooks()
    {
        add_action('newsletter_user_confirmed', array($this, 'ceHandleContactNewsletterSubmit'));
        // add hook function to synchronize
        add_action(CE4WP_SYNCHRONIZE_ACTION, array($this, 'syncAction'));
    }

    public function unregisterHooks()
    {
        remove_action('newsletter_user_confirmed', array($this, 'ceHandleContactNewsletterSubmit'));
        // remove hook function to synchronize
        remove_action(CE4WP_SYNCHRONIZE_ACTION, array($this, 'syncAction'));
    }

    public function syncAction($limit = null)
    {
        if (!is_int($limit) || $limit <= 0) {
            $limit = null;
        }

        global $wpdb;

        $query = 'select * from wp_newsletter order by id desc';

        if ($limit != null) {
            $query .= " LIMIT %d";
            $query = $wpdb->prepare($query, $limit);
        }
        else {
            $query = $wpdb->prepare($query);
        }

        $result = $wpdb->get_results($query);

        $backfillArray = array();

        if (isset($result) && !empty($result)) {
            foreach ($result as $contact) {
                $contactModel = new ContactModel();
                $contactModel->setEventType(CE4WP_NL_EVENTTYPE);
                $contactModel->setOptIn($contact->status !== "U");
                $contactModel->setOptOut($contact->status === "U");
                $contactModel->setOptActionBy(OptActionBy::Visitor);

                $email = $contact->email;
                if ($this->isNotNullOrEmpty($email)) {
                    $contactModel->setEmail($email);
                }

                $name = $contact->name;
                if ($this->isNotNullOrEmpty($name)) {
                    $contactModel->setFirstName($name);
                }

                $surname = $contact->surname;
                if ($this->isNotNullOrEmpty($surname)) {
                    $contactModel->setLastName($surname);
                }

                if ($this->isNotNullOrEmpty($contactModel->getEmail())) {
                    array_push($backfillArray, $contactModel);
                }
            }
        }

        if (!empty($backfillArray)) {

            $batches = array_chunk($backfillArray, CE4WP_BATCH_SIZE);
            foreach($batches as $batch){
                try {
                    $this->batchUpsertContacts($batch);
                } catch (\Exception $exception) {
                    // silent exception
                }
            }
        }
    }

    function __construct()
    {
        parent::__construct();
    }
}
