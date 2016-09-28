<?php
/**
 * @version $Id$
 * @copyright Copyright (c) 2002 - 2011 IPG Holdings Limited (a company incorporated in Cyprus).
 * All rights reserved. Use is strictly subject to licence terms & conditions.
 * This computer software programme is protected by copyright law and international treaties.
 * Unauthorised reproduction, reverse engineering or distribution of the programme, or any part of it, may
 * result in severe civil and criminal penalties and will be prosecuted to the maximum extent permissible at law.
 * For further information, please contact the copyright owner by email copyright@ipgholdings.net
 **/
namespace IPGPAY\Gateway\Api;

/**
 * Class Constants
 * @package IPGPAY\Gateway\Model
 */
class Constants 
{
    /**
     *
     */
    const TRANSACTION_MODE_AUTH = 'auth';
    /**
     *
     */
    const TRANSACTION_MODE_SALE = 'sale';
    /**
     *
     */
    const TRANSACTION_STATE_APPROVED = 'Approved';
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
    const REBILL_TYPE_MERCHANT_MANAGED = 2;
    /**
     * @var array
     */
    public static $NonDecimalCurrencies = array("JPY", "VND", "KRW");
    /**
     * @var array
     */
    public static $ThreeDecimalCurrencies = array("KWD","OMR","BHD");
}
