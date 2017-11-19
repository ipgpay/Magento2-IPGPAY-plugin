<?php
namespace IPGPAY\Test\Unit\Controller\Notification;

use IPGPAY\IPGPAYMagento2\Controller\Notification\Handle as Handle;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class HandleTest extends TestCase
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
    protected $paymentMock;
    protected $orderCommentSender;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->orderMock = $this-> getMockBuilder(\Magento\Sales\Model\Order::class)
        ->disableOriginalConstructor()
        ->getMock();

        $this->paymentMock = $this -> getMockBuilder(\Magento\Sales\API\Data\OrderInterface::class)
        ->disableOriginalConstructor()
        ->getMock();

        $this->handleObjectManger = $this-> getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
        ->disableOriginalConstructor()
        ->getMock();

        $this->handleObjectManger ->expects(static::any())
        ->method('get')
        ->with('Magento\Sales\Model\Order')
        ->willReturn($this->orderMock);
        
        $this->orderCommentSender = $this-> getMockBuilder(\Magento\Sales\Model\Order\Email\Sender\OrderCommentSender::class)
        ->disableOriginalConstructor()
        ->getMock();
        
        $this->handleObjectManger ->expects(static::any())
        ->method('create')
        ->with('Magento\Sales\Model\Order\Email\Sender\OrderCommentSender')
        ->willReturn($this->orderCommentSender);

        $this->scoreConfigMock = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);

        $this->scoreConfigMock->expects(static::any())
        ->method('getValue')
        ->with('payment/ipgpay_ipgpaymagento2/secret_key', 'store')
        ->willReturn('QuSfYgaVWoUS');

        $request = [
            'PS_SIGNATURE'=>'019a727c78e05c2d2c2db48b2524f39908d2de91',
            'PS_SIGTYPE' => 'PSSHA1',
            'notification_type' => 'orderpending',
            'order_reference' => '123',
            'trans_id' => '123',
            'trans_type' => 'sale',
            'order_datetime' => '2018-08-09 12:00:00',
            'order_currency' => 'USD',
            'amount' => 100.00
        ];

        $this->orderMock ->expects(static::any())
        ->method('loadByIncrementId')
        ->with($request['order_reference'])
        ->willReturnSelf();

        $this->orderMock ->expects(static::any())
        ->method('getPayment')
        ->willReturnSelf();

        $this->orderMock ->expects(static::any())
        ->method('addStatusToHistory')
        ->willReturnSelf();

        
        $this->orderMock ->expects(static::any())
        ->method('addStatusHistoryComment')
        ->with('Payment pending email sent to customer')
        ->willReturnSelf();
        
        
        $this->controller = $objectManager->getObject(Handle::class, [
            '_objectManager' => $this->handleObjectManger,
            '_scopeConfig' => $this->scoreConfigMock
        ]);
    }
 
    /**
     * @test
     */
    public function testExecute()
    {
        $result =  $this->controller->execute();
        $this->assertEquals('OK', $result);
    }
}
