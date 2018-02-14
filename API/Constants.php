<?php
/**
 * @copyright Copyright (c) 2017 IPG Group Limited
 * All rights reserved.
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE.txt file for details.
 **/
namespace IPGPAY\IPGPAYMagento2\API;

/**
 * Class Constants
 * @package IPGPAY\IPGPAYMagento2\Model
 */
class Constants
{
    /**
     * Transaction modes
     */
    const TRANSACTION_MODE_AUTH = 'auth';
    /**
     *
     */
    const TRANSACTION_MODE_SALE = 'sale';
    /**
     * Transaction states
     */
    const TRANSACTION_STATE_APPROVED = 'Approved';
    /**
     *
     */
    const TRANSACTION_STATE_PENDING = 'Pending';
    /**
     *
     */
    const TRANSACTION_STATE_CREDITED = 'Credited';
    /**
     *
     */
    const TRANSACTION_STATE_VOIDED = 'Voided';
    /**
     *
     */
    const TRANSACTION_STATE_SETTLED = 'Settled';
    /**
     * Notification types
     */
    const NOTIFICATION_TYPE_ORDER = 'order';
    /**
     *
     */
    const NOTIFICATION_TYPE_ORDER_PENDING = 'orderpending';
    /**
     *
     */
    const NOTIFICATION_TYPE_VOID = 'void';
    /**
     *
     */
    const NOTIFICATION_TYPE_SETTLE = 'settle';
    /**
     *
     */
    const NOTIFICATION_TYPE_CREDIT = 'credit';
    /**
     *
     */
    const NOTIFICATION_TYPE_REBILL_SUCCESS = 'rebillsuccess';
    /**
     *
     */
    const NOTIFICATION_TYPE_ORDER_FAILURE = 'orderfailure';
    /**
     *
     */
    const NOTIFICATION_RESPONSE_SUCCESSFUL = "OK";
    /**
     *
     */
    const REBILL_TYPE_MERCHANT_MANAGED = 2;
    /**
     * @var array
     */
    public static $NonDecimalCurrencies = ["JPY", "VND", "KRW"];
    /**
     * @var array
     */
    public static $ThreeDecimalCurrencies = ["KWD", "OMR", "BHD"];
}
