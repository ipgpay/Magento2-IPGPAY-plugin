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
namespace IPGPAY\Request;

use IPGPAY;

/**
 * Class RequestAbstract
 * @package IPGPAY\Request
 */
abstract class RequestAbstract {
    /**
     * @var mixed
     */
    protected $APIBaseUrl;
    /**
     * @var mixed
     */
    protected $APIClientId;
    /**
     * @var mixed
     */
    protected $APIKey;
    /**
     * @var mixed|string
     */
    protected $TestMode = '0';
    /**
     * @var mixed|string
     */
    protected $Notify = '1';
    /**
     * @var array
     */
    protected $RequestParams = array();

    /**
     * Build the request params
     * To be implemented by the extended classes
     *
     * @return mixed
     */
    abstract protected function buildRequestParams();

    /**
     * Get the IPGPAY for the request
     *
     * @return mixed
     */
    abstract protected function getRequestUrl();

    /**
     * @param array $config
     */
    function __construct(array $config = array()) {
        if (empty($config)) {
            $this->APIBaseUrl = IPGPAY\Config::API_BASE_URL;
            $this->APIClientId = IPGPAY\Config::API_CLIENT_ID;
            $this->APIKey = IPGPAY\Config::API_KEY;
            $this->Notify = IPGPAY\Config::NOTIFY;
            $this->TestMode = IPGPAY\Config::TEST_MODE;
        } else {
            $this->APIBaseUrl = $config['api_base_url'];
            $this->APIClientId = $config['api_client_id'];
            $this->APIKey = $config['api_key'];
            $this->Notify = $config['notify'];
            $this->TestMode = $config['test_mode'];
        }
    }


    /**
     * Validate the request parameters
     *
     * @throws IPGPAY\Exceptions\InvalidRequestException
     */
    protected function validate() {
        if (empty($this->APIBaseUrl)) {
            throw new IPGPAY\Exceptions\InvalidRequestException("API URL is missing.");
        } elseif (filter_var($this->APIBaseUrl, FILTER_VALIDATE_URL) === FALSE) {
            throw new IPGPAY\Exceptions\InvalidRequestException("API URL is invalid.");
        }

        if (empty($this->APIClientId)) {
            throw new IPGPAY\Exceptions\InvalidRequestException("API Client Id is missing.");
        } else {
            if (!IPGPAY\Functions::isValidSqlInt($this->APIClientId)) {
                throw new IPGPAY\Exceptions\InvalidRequestException("Invalid API Client Id");
            }
        }

        if (empty($this->APIKey)) {
            throw new IPGPAY\Exceptions\InvalidRequestException("API Key is missing.");
        }
    }

    /**
     * Validate, build request params and send to IPGPAY
     * Return a response
     *
     * @return IPGPAY\Response\ResponseAbstract
     * @throws IPGPAY\Exceptions\CommunicationException
     */
    public function sendRequest() {
        $this->validate();
        $this->buildRequestParams();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->getRequestUrl());
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->RequestParams));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $Response = curl_exec($ch);

        if (curl_errno($ch)) {
            curl_close($ch);
            throw new IPGPAY\Exceptions\CommunicationException("cURL error: ".curl_errno($ch)." ".curl_error($ch));
        }

        curl_close($ch);
        return IPGPAY\Response\ResponseAbstract::factory($Response);
    }
}