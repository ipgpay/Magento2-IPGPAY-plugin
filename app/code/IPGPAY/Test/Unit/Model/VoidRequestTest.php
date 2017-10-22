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

use \IPGPAY\Gateway\Model\IPGPAY as IPGPAY;
use \Magento\Payment\Model\Info as Info;
use \IPGPAY\Gateway\Api\Request\VoidRequest as VoidRequest;

class VoidRequestTest extends \PHPUnit\Framework\TestCase
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
        $this->model = new VoidRequest($config);
    }

    /**
     * @expectedException \IPGPAY\Gateway\Api\Exceptions\InvalidRequestException
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
        $voidRequest = new VoidRequest($config);
        $voidRequest->setOrderId(null);
        $voidRequest->setReason('test reason');
    }

    /**
     * @test
     */
    public function test_default_voidRequest()
    {
        $this->model->setOrderId('123456');
        $this->model->setReason('test reason');

        $result = $this->model->sendRequest();
        $this->assertEquals($result->ResponseCode, 'OP299');
    }
}
