<?php

namespace IPGPAY\Test\Unit\API;

use Psr\Log\LoggerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use \IPGPAY\IPGPAYMagento2\API\ParamSigner as ParamSigner;

class ParamSignerTest extends TestCase
{
	protected $model;
	protected $loggerMock;

	protected function setup(){
		$this->loggerMock = $this->createMock(LoggerInterface::class);
		$this->model = new ParamSigner($this->loggerMock);
	}

	public function test_inputData_isUtf8() {

		$value = 'dfjbls';

		$this->assertTrue($this->model->is_utf8($value));
	}
}