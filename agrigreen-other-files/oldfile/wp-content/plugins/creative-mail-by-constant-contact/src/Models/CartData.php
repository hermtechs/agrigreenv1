<?php

namespace CreativeMail\Models;

class CartData
{
    /**
     * Coupons array
     *
     * @var array
     */
    public $coupons;
    /**
     * Coupons currency
     *
     * @var string
     */
    public $currency;
    /**
     * Coupons currency symbol
     *
     * @var string
     */
    public $currency_symbol;
    /**
     * Cart items
     *
     * @var array
     */
    public $products;
    /**
     * User data
     *
     * @var User
     */
    public $user;
}
