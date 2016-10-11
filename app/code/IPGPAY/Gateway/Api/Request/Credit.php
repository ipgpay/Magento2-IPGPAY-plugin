<?php
/**
  * @version $Id$
  * @copyright Copyright (c) 2002 - 2013 IPG Holdings Limited (a company incorporated in Cyprus).
  * All rights reserved. Use is strictly subject to licence terms & conditions.
  * This computer software programme is protected by copyright law and international treaties.
  * Unauthorised reproduction, reverse engineering or distribution of the programme, or any part of it, may
  * result in severe civil and criminal penalties and will be prosecuted to the maximum extent permissible at law.
  * For further information, please contact the copyright owner by email copyright@ipgholdings.net
**/
namespace IPGPAY\Gateway\Api\Request;

use IPGPAY\Gateway\Api\Exceptions\InvalidRequestException;
use IPGPAY\Gateway\Api\Functions;

/**
 * Class Credit
 * @package IPGPAY\Request
 */
class Credit extends RequestAbstract {
    /**
     * @var
     */
    protected $OrderId; //Mandatory
    /**
     * @var
     */
    protected $TransId; //Mandatory
    /**
     * @var
     */
    protected $Amount; //Optional
    /**
     * @var
     */
    protected $Reason; //Optional
    /**
     * @var
     */
    protected $Reference; //Optional

    /**
     * Set the Order Id
     *
     * @param $OrderId
     * @throws InvalidRequestException
     */
    public function setOrderId($OrderId) {
        if (!Functions::isValidSqlInt($OrderId)) {
            throw new InvalidRequestException("Invalid Order Id");
        }
        $this->OrderId = $OrderId;
    }

    /**
     * Set the Trams Id
     *
     * @param $TransId
     * @throws InvalidRequestException
     */
    public function setTransId($TransId) {
        if (!Functions::isValidSqlBigInt($TransId)) {
            throw new InvalidRequestException("Invalid Trans Id");
        }
        $this->TransId = $TransId;
    }

    /**
     * Set the amount
     *
     * @param $Amount
     * @throws InvalidRequestException
     */
    public function setAmount($Amount) {
        if (!Functions::isValidAmount($Amount)) {
            throw new InvalidRequestException("Invalid Credit Amount");
        }
        $this->Amount = $Amount;
    }

    /**
     * Set the reason for the void
     *
     * @param $Reason
     */
    public function setReason($Reason) {
        $this->Reason = $Reason;
    }

    /**
     * Set the reason for the void
     * Truncate to 100 chars
     *
     * @param $Reference
     */
    public function setReference($Reference) {
        if (strlen($Reference) > 100) {
            $Reference = substr($Reference,0,100);
        }
        $this->Reference = $Reference;
    }

    /**
     * Validate the credit request parameters
     *
     * @throws InvalidRequestException
     */
    protected function validate() {
        parent::validate();
        if (empty($this->OrderId)) {
            throw new InvalidRequestException("Missing Order Id");
        }
        if (empty($this->TransId)) {
            throw new InvalidRequestException("Missing Trans Id");
        }
    }

    /**
     * Build the request params
     *
     * @return array
     */
    protected function buildRequestParams() {
        $Request = array();
        $Request['client_id'] = $this->APIClientId;
        $Request['api_key'] = $this->APIKey;
        $Request['order_id'] = $this->OrderId;
        $Request['trans_id'] = $this->TransId;
        if (!empty($this->Amount)) {
            $Request['amount'] = $this->Amount;
        }
        if (!empty($this->Reason)) {
            $Request['reason'] = $this->Reason;
        }
        if (!empty($this->Reference)) {
            $Request['reference'] = $this->Reference;
        }
        $Request['notify'] = $this->Notify;
        if ($this->TestMode) {
            $Request['test_transaction'] = '1';
        } else {
            $Request['test_transaction'] = '0';
        }
        $this->RequestParams = $Request;
        return $Request;
    }

    /**
     * Return the request URL for the credit request
     *
     * @return string
     */
    protected function getRequestUrl() {
        return rtrim($this->APIBaseUrl,'/').'/service/order/credit';
    }
}