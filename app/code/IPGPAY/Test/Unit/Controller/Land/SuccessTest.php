<?php
namespace IPGPAY\Test\Unit\Controller\Land;

//namespace IPGPAY\Test\Unit\Model;
use IPGPAY\Gateway\Controller\Land\Success as Success;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class SuccessTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    protected $checkoutSessionMock;

    protected $controller;

    protected $orderMock;

    protected $successObjectManger;

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

        $this->successObjectManger = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->successObjectManger->expects(static::any())
            ->method('get')
            ->with('Magento\Checkout\Model\Session')
            ->willReturn($this->checkoutSessionMock);

        $this->redirectMock = $this->createMock(\Magento\Framework\App\Response\RedirectInterface::class);

        $this->controller = $objectManager->getObject(Success::class, [
            '_objectManager' => $this->successObjectManger,
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

        $redirectResponse = $this->createMock(\Magento\Framework\App\ResponseInterface::class);

        $this->redirectMock->expects($this->once())
            ->method('redirect')
            ->with($redirectResponse, $this->equalTo('checkout/onepage/success'));

        $this->controller->execute();
    }
}
