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
namespace IPGPAY\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use \IPGPAY\IPGPAYMagento2\API\Request\Credit as Credit;

class CreditTest extends TestCase
{
    protected $model;
    /**
     * Is called before running a test
     */
    protected function setUp()
    {
        $config = [
            'api_base_url'=>'https://my.ipgholdings.net',
            'api_client_id'=>'4003442',
            'api_key'=>'xYKifLzembIHivJFJveO',
            'notify'=>'0',
            'test_mode'=>'1',
        ];
        $this->model = new Credit($config);
    }

    /**
     * @expectedException \IPGPAY\IPGPAYMagento2\API\Exceptions\InvalidRequestException
     * @expectedExceptionMessage  Invalid Order Id
     */
    public function test_orderId_empty()
    {
        $config = [
            'api_base_url'=>'https://www.test.com',
            'api_client_id'=>'123456',
            'api_key'=>'123456',
            'notify'=>'0',
            'test_mode'=>'1',
        ];
        $credit = new Credit($config);
        $credit->setOrderId(null);
        $credit->setTransId('1234');
        $credit->setAmount('123456');
        $credit->sendRequest();
    }

    /**
     * @expectedException \IPGPAY\IPGPAYMagento2\API\Exceptions\InvalidRequestException
     * @expectedExceptionMessage  Invalid Order Id
     */
    public function test_url_invalidOrderId()
    {
        $config = [
            'api_base_url'=>'https://www.test.com',
            'api_client_id'=>'123456',
            'api_key'=>'123456',
            'notify'=>'0',
            'test_mode'=>'1',
        ];
        $credit = new Credit($config);
        $credit->setOrderId('test');
        $credit->setTransId('1234');
        $credit->setAmount('123456');
        $credit->sendRequest();
    }

    
    /**
     * @expectedException \IPGPAY\IPGPAYMagento2\API\Exceptions\InvalidRequestException
     * @expectedExceptionMessage  Missing Trans Id
     */
    public function test_url_transId_empty()
    {
        $config = [
            'api_base_url'=>'https://www.test.com',
            'api_client_id'=>'123456',
            'api_key'=>'123456',
            'notify'=>'0',
            'test_mode'=>'1',
        ];
        $credit = new Credit($config);
        $credit->setOrderId('12345');

        $credit->setAmount('123456');
        $credit->sendRequest();
    }


    /**
     * @expectedException \IPGPAY\IPGPAYMagento2\API\Exceptions\InvalidRequestException
     * @expectedExceptionMessage  Invalid Trans Id
     */
    public function test_url_invalidTransId()
    {
        $config = [
            'api_base_url'=>'https://www.test.com',
            'api_client_id'=>'123456',
            'api_key'=>'123456',
            'notify'=>'0',
            'test_mode'=>'1',
        ];
        $credit = new Credit($config);
        $credit->setOrderId('12345');
        $credit->setTransId('abc');
        $credit->setAmount('123456');
        $credit->sendRequest();
    }

     /**
      * @expectedException \IPGPAY\IPGPAYMagento2\API\Exceptions\InvalidRequestException
      * @expectedExceptionMessage  Invalid Credit Amount
      */
    public function test_url_invalidAmount()
    {
        $config = [
            'api_base_url'=>'https://www.test.com',
            'api_client_id'=>'123456',
            'api_key'=>'123456',
            'notify'=>'0',
            'test_mode'=>'1',
        ];
        $credit = new Credit($config);
        $credit->setOrderId('12345');
        $credit->setTransId('12345');
        $credit->setAmount('test');
        $credit->sendRequest();
    }


     /**
      * @test
      */
    public function test_default_credit()
    {
        $config = [
            'api_base_url'=>'https://my.ipgholdings.net',
            'api_client_id'=>'4003442',
            'api_key'=>'xYKifLzembIHivJFJveO',
            'notify'=>'0',
            'test_mode'=>'1',
        ];
        $this->model = new Credit($config);
        $this->model->setOrderId('123456');
        $this->model->setTransId('1234');
        $this->model->setAmount('123456');
        $result = $this->model->sendRequest();
        $this->assertEquals($result->ResponseCode, 'OP299');
    }
}
