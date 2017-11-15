<?php
/**
 * @copyright Copyright (c) 2017 IPG Group Limited
 * All rights reserved.
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE.txt file for details.
 **/
namespace IPGPay\IPGPayMagento2\Api\Request;

use IPGPay\IPGPayMagento2\Api\Exceptions\InvalidRequestException;
use IPGPay\IPGPayMagento2\Api\Functions;

/**
 * Class CancelRebill
 * @package IPGPay\Request
 */
class CancelRebill extends RequestAbstract
{
    /**
     * @var
     */
    protected $OrderId; //Mandatory
    /**
     * @var
     */
    protected $Reason; //Optional
    /**
     * @var
     */
    protected $ItemId; //Mandatory

    /**
     * Set the Order Id
     *
     * @param $OrderId
     * @throws InvalidRequestException
     */
    public function setOrderId($OrderId)
    {
        if (!Functions::isValidSqlInt($OrderId)) {
            throw new InvalidRequestException("Invalid Order Id");
        }
        $this->OrderId = $OrderId;
    }

    /**
     * @param $ItemId
     * @throws InvalidRequestException
     */
    public function setItemId($ItemId)
    {
        if (!Functions::isValidSqlInt($ItemId)) {
            throw new InvalidRequestException("Invalid Item Id");
        }
        $this->ItemId = $ItemId;
    }

    /**
     * Set the reason for the void
     * Truncate to 100 chars
     *
     * @param $Reason
     */
    public function setReason($Reason)
    {
        if (strlen($Reason) > 100) {
            $Reason = substr($Reason, 0, 100);
        }
        $this->Reason = $Reason;
    }

    /**
     * Validate the void request parameters
     *
     * @throws InvalidRequestException
     */
    protected function validate()
    {
        parent::validate();
        if (empty($this->OrderId)) {
            throw new InvalidRequestException("Missing Order Id");
        }
    }

    /**
     * Build the request params
     *
     * @return array
     */
    protected function buildRequestParams()
    {
        $Request = [];
        $Request['client_id'] = $this->APIClientId;
        $Request['api_key'] = $this->APIKey;
        $Request['order_id'] = $this->OrderId;
        if (!empty($this->Reason)) {
            $Request['reason'] = $this->Reason;
        }
        $Request['notify'] = $this->Notify;
        $Request['item_id'] = $this->ItemId;
        $this->RequestParams = $Request;
        return $Request;
    }

    /**
     * Return the request URL for the void request
     *
     * @return string
     */
    protected function getRequestUrl()
    {
        return rtrim($this->APIBaseUrl, '/').'/service/order/void';
    }
}
