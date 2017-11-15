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
 * Class Settle
 * @package IPGPay\Request
 */
class Settle extends RequestAbstract
{
    /**
     * @var
     */
    protected $OrderId; //Mandatory
    /**
     * @var
     */
    protected $ShipperId; //Optional
    /**
     * @var
     */
    protected $TrackId; //TrackId
    /**
     * @var
     */
    protected $Amount; //Optional

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
     * Set the Shipper Id
     * Truncate to 40 chars
     *
     * @param $ShipperId
     */
    public function setShipperId($ShipperId)
    {
        if (strlen($ShipperId) > 40) {
            $ShipperId = substr($ShipperId, 0, 40);
        }
        $this->ShipperId = $ShipperId;
    }

    /**
     * Set the Order Id
     * Truncate to 40 chars
     *
     * @param $TrackId
     */
    public function setTrackId($TrackId)
    {
        if (strlen($TrackId) > 40) {
            $TrackId = substr($TrackId, 0, 40);
        }
        $this->TrackId = $TrackId;
    }

    /**
     * Set the amount
     *
     * @param $Amount
     * @throws InvalidRequestException
     */
    public function setAmount($Amount)
    {
        if (!Functions::isValidAmount($Amount)) {
            throw new InvalidRequestException("Invalid Settle Amount");
        }
        $this->Amount = $Amount;
    }

    /**
     * Validate the settle request parameters
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
        if (!empty($this->Amount)) {
            $Request['amount'] = $this->Amount;
        }
        if (!empty($this->ShipperId)) {
            $Request['shipper_id'] = $this->ShipperId;
        }
        if (!empty($this->TrackId)) {
            $Request['track_id'] = $this->TrackId;
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
     * Return the request URL for the settlement request
     *
     * @return string
     */
    protected function getRequestUrl()
    {
        return rtrim($this->APIBaseUrl, '/').'/service/order/settle';
    }
}
