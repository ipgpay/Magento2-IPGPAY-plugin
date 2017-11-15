<?php
/**
 * @version $Id$
 * @copyright Copyright (c) 2002 - 2016 IPG Holdings Limited (a company incorporated in Cyprus).
 * All rights reserved. Use is strictly subject to licence terms & conditions.
 * This computer software programme is protected by copyright law and international treaties.
 * Unauthorised reproduction, reverse engineering or distribution of the programme, or any part of it, may
 * result in severe civil and criminal penalties and will be prosecuted to the maximum extent permissible at law.
 * For further information, please contact the copyright owner by email copyright@ipgholdings.net
 **/
namespace IPGPay\Test\Unit\Model;

use \IPGPay\IPGPayMagento2\Model\IPGPay as IPGPay;
use PHPUnit\Framework\TestCase;
use IPGPay\IPGPayMagento2\Api\Request\Settle as Settle;

class RequestAbstractTest extends TestCase
{
    protected $model;
    /**
     * Is called before running a test
     */
    protected function setUp()
    {
    }

    /**
     * @expectedException \IPGPay\IPGPayMagento2\Api\Exceptions\InvalidRequestException
     * @expectedExceptionMessage  API URL is missing
     */
    public function test_url_empty()
    {
        $config = [
            'api_base_url'=>'',
            'api_client_id'=>'123456',
            'api_key'=>'123456',
            'notify'=>'0',
            'test_mode'=>'1',
        ];
        $request = new Settle($config);
        $request->sendRequest();
    }

    /**
     * @expectedException \IPGPay\IPGPayMagento2\Api\Exceptions\InvalidRequestException
     * @expectedExceptionMessage  API URL is invalid
     */
    public function test_url_invaild()
    {
        $config = [
            'api_base_url'=>'test.com',
            'api_client_id'=>'123456',
            'api_key'=>'123456',
            'notify'=>'0',
            'test_mode'=>'1',
        ];
        $request = new Settle($config);
        $request->sendRequest();
    }

    /**
     * @expectedException \IPGPay\IPGPayMagento2\Api\Exceptions\InvalidRequestException
     * @expectedExceptionMessage  API Client Id is missing
     */
    public function test_url_clientId_empty()
    {
        $config = [
            'api_base_url'=>'https://www.test.com',
            'api_client_id'=>'',
            'api_key'=>'123456',
            'notify'=>'0',
            'test_mode'=>'1',
        ];
        $request = new Settle($config);
        $request->sendRequest();
    }


    /**
     * @expectedException \IPGPay\IPGPayMagento2\Api\Exceptions\InvalidRequestException
     * @expectedExceptionMessage  API Key is missing.
     */
    public function test_url_apikey_empty()
    {
        $config = [
          'api_base_url'=>'https://www.test.com',
          'api_client_id'=>'123456',
          'api_key'=>'',
          'notify'=>'0',
          'test_mode'=>'1',
        ];
        $request = new Settle($config);
        $request->sendRequest();
    }
}
