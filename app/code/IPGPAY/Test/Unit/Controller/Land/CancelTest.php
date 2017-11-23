<?php
namespace IPGPAY\Test\Unit\Controller\Land;

use IPGPAY\Gateway\Controller\Land\Cancel as Cancel;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class CancelTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    protected $checkoutSessionMock;

    protected $controller;

    protected $orderMock;

    protected $cancelObjectManger;

    protected $redirectMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->checkoutSessionMock = $this
            ->getMockBuilder(\Magento\Checkout\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->checkoutSessionMock->expects(static::once())
            ->method('getLastRealOrder')
            ->willReturn($this->orderMock);

        $this->checkoutSessionMock->expects(static::once())
            ->method('restoreQuote')
            ->willReturn(false);

        $this->cancelObjectManger = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cancelObjectManger->expects(static::any())
            ->method('get')
            ->with('Magento\Checkout\Model\Session')
            ->willReturn($this->checkoutSessionMock);

        $this->redirectMock = $this->createMock(\Magento\Framework\App\Response\RedirectInterface::class);

        $this->cancelObjectManger->expects(static::any())
            ->method('get')
            ->with('Magento\Checkout\Model\Session')
            ->willReturn($this->checkoutSessionMock);

        $this->controller = $objectManager->getObject(Cancel::class, [
            '_objectManager' => $this->cancelObjectManger,
            '_redirect'      => $this->redirectMock,
        ]);
    }

    /**
     * @test
     */
    public function testExecute()
    {
        $this->orderMock->expects(static::once())
            ->method('getRealOrderId')
            ->willReturn(1);

        $this->orderMock->expects(static::once())
            ->method('cancel')
            ->willReturnSelf();

        $this->orderMock->expects(static::once())
            ->method('addStatusToHistory')
            ->willReturnSelf();

        $redirectResponse = $this->createMock(\Magento\Framework\App\ResponseInterface::class);

        $this->redirectMock->expects($this->once())
            ->method('redirect')
            ->with($redirectResponse, $this->equalTo('checkout'));

        $this->controller->execute();
    }
}
