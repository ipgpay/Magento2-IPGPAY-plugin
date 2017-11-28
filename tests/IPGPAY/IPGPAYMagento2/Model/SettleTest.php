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

use PHPUnit\Framework\TestCase;
use \IPGPAY\IPGPAYMagento2\API\Request\Settle;

class SettleTest extends TestCase
{
    protected $model;
    /**
     * Is called before running a test
     */
    protected function setUp()
    {
        $config = [
            'api_base_url'  => 'https://my.ipgholdings.net',
            'api_client_id' => '4003442',
            'api_key'       => 'xYKifLzembIHivJFJveO',
            'notify'        => '0',
            'test_mode'     => '1',
        ];
        $this->model = new Settle($config);
    }

    /**
     * @expectedException \IPGPAY\IPGPAYMagento2\API\Exceptions\InvalidRequestException
     * @expectedExceptionMessage  Invalid Order Id
     */
    public function test_orderId_empty()
    {
        $config = [
            'api_base_url'  => 'https://www.test.com',
            'api_client_id' => '123456',
            'api_key'       => '123456',
            'notify'        => '0',
            'test_mode'     => '1',
        ];
        $settle = new Settle($config);
        $settle->setOrderId(null);
        $settle->setShipperId('123456');
        $settle->setTrackId('123456');
        $settle->setAmount(100);
        $settle->sendRequest();
    }

    /**
     * @expectedException \IPGPAY\IPGPAYMagento2\API\Exceptions\InvalidRequestException
     * @expectedExceptionMessage  Invalid Order Id
     */
    public function test_url_invalidOrderId()
    {
        $config = [
            'api_base_url'  => 'https://www.test.com',
            'api_client_id' => '123456',
            'api_key'       => '123456',
            'notify'        => '0',
            'test_mode'     => '1',
        ];
        $settle = new Settle($config);
        $settle->setOrderId('test');
        $settle->setShipperId('1234');
        $settle->setTrackId('123456');
        $settle->setAmount(100);
        $settle->sendRequest();
    }

    /**
     * @test
     */
    public function test_default_settle()
    {
        $config = [
            'api_base_url'  => 'https://my.ipgholdings.net',
            'api_client_id' => '4003442',
            'api_key'       => 'xYKifLzembIHivJFJveO',
            'notify'        => '0',
            'test_mode'     => '1',
        ];
        $this->model = new Settle($config);
        $this->model->setOrderId('1394562');
        $this->model->setShipperId('123456');
        $this->model->setTrackId('123456');
        $this->model->setAmount('100');
        $result = $this->model->sendRequest();
        $this->assertEquals($result->ResponseCode, 'OP299');
    }
}
