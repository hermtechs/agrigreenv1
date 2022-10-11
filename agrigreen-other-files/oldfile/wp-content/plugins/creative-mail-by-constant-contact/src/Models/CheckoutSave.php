<?php

namespace CreativeMail\Models;

class CheckoutSave
{
    /**
     * Billing email address
     *
     * @var string
     */
    public $billing_email;
    /**
     * Checkout data
     *
     * @var string
     */
    public $data;
    /**
     * Date timestamp
     *
     * @var string
     */
    public $timestamp;
    /**
     * User ID
     *
     * @var int
     */
    public $user_id;
    /**
     * Universal Identifier
     *
     * @var string
     */
    public $uuid;
}
