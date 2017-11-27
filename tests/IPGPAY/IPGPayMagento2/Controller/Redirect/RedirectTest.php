<?php

use IPGPAY\IPGPAYMagento2\Controller\Redirect\Index;
use Magento\Authorizenet\Controller\Directpost\Payment\Redirect;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use \Magento\Framework\Controller\ResultFactory;

class RedirectTest extends TestCase
{

    protected $objectManager;
    protected $checkoutSessionMock;
    protected $controller;
    protected $orderMock;
    protected $redirectObjectManger;
    protected $redirectMock;
    protected $contextMock;
    protected $orderAddressMock;
    protected $shippingAddressMock;
    protected $orderItemMock;
    protected $scoreConfigMock;
    protected $resultFactory;
    protected $jsonResut;
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->orderMock           = $this->createMock(\Magento\Sales\Model\Order::class);
        $this->orderAddressMock    = $this->createMock(\Magento\Sales\Model\Order\Address::class);
        $this->shippingAddressMock = $this->createMock(\Magento\Sales\Model\Order\Address::class);
        $this->orderItemMock       = $this->createMock(\Magento\Sales\Model\Order\Item::class);
        $this->scoreConfigMock     = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);

        $this->resultFactory = $this->createMock(\Magento\Framework\Controller\ResultFactory::class);
        $this->jsonResut     = $this->createMock(\Magento\Framework\Controller\Result\Json::class);
        $this->resultFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->jsonResut));

        $this->checkoutSessionMock = $this
            ->getMockBuilder(\Magento\Checkout\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->checkoutSessionMock->expects(static::once())
            ->method('getLastRealOrder')
            ->willReturn($this->orderMock);

        $this->redirectObjectManger = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->redirectObjectManger->expects(static::any())
            ->method('get')
            ->with('Magento\Checkout\Model\Session')
            ->willReturn($this->checkoutSessionMock);

        $this->request = static::getMockForAbstractClass(RequestInterface::class);

        $this->view = static::getMockForAbstractClass(ViewInterface::class);

        $this->coreRegistry = static::getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->setMethods(['register'])
            ->getMock();

        $this->controller = $objectManager->getObject(Index::class, [
            'request'        => $this->request,
            'view'           => $this->view,
            'coreRegistry'   => $this->coreRegistry,
            'scopeConfig'    => $this->scoreConfigMock,
            'resultFactory'  => $this->resultFactory,
            '_objectManager' => $this->redirectObjectManger,
        ]);
    }

    /**
     * @test
     */
    public function testExecute()
    {
        $this->orderAddressMock = $this->createPartialMock(
            \Magento\Sales\Model\Order\Address::class,
            ['getFirstname', 'getLastname', 'getCompany', 'getCity', 'getRegion', 'getPostcode', 'getCountryId', 'getEmail', 'getTelephone']
        );

        $this->orderAddressMock->expects($this->at(0))->method('getFirstname')->will($this->returnValue('firstname'));
        $this->orderAddressMock->expects($this->at(1))->method('getLastname')->will($this->returnValue('lastname'));
        $this->orderAddressMock->expects($this->at(2))->method('getCompany')->will($this->returnValue('company'));
        $this->orderAddressMock->expects($this->at(3))->method('getCity')->will($this->returnValue('city'));
        $this->orderAddressMock->expects($this->at(4))->method('getRegion')->will($this->returnValue('region'));
        $this->orderAddressMock->expects($this->at(5))->method('getPostcode')->will($this->returnValue('postcode'));
        $this->orderAddressMock->expects($this->at(6))->method('getCountryId')->will($this->returnValue('countryid'));
        $this->orderAddressMock->expects($this->at(7))->method('getEmail')->will($this->returnValue('email'));
        $this->orderAddressMock->expects($this->at(8))->method('getTelephone')->will($this->returnValue('telephone'));

        $this->shippingAddressMock->expects($this->at(0))->method('getFirstname')->will($this->returnValue('firstname'));
        $this->shippingAddressMock->expects($this->at(1))->method('getLastname')->will($this->returnValue('lastname'));
        $this->shippingAddressMock->expects($this->at(2))->method('getCompany')->will($this->returnValue('company'));
        $this->shippingAddressMock->expects($this->at(3))->method('getCity')->will($this->returnValue('city'));
        $this->shippingAddressMock->expects($this->at(4))->method('getRegion')->will($this->returnValue('region'));
        $this->shippingAddressMock->expects($this->at(5))->method('getPostcode')->will($this->returnValue('postcode'));
        $this->shippingAddressMock->expects($this->at(6))->method('getCountryId')->will($this->returnValue('countryid'));
        $this->shippingAddressMock->expects($this->at(7))->method('getEmail')->will($this->returnValue('email'));
        $this->shippingAddressMock->expects($this->at(8))->method('getTelephone')->will($this->returnValue('telephone'));

        $this->orderMock->expects($this->at(0))->method('getBillingAddress')->will($this->returnValue($this->orderAddressMock));
        $this->orderMock->expects($this->at(1))->method('getShippingAddress')->will($this->returnValue($this->shippingAddressMock));
        $this->orderMock->expects($this->at(4))->method('getAllItems')->will($this->returnValue([$this->orderItemMock]));
        $this->orderMock->expects($this->at(5))->method('getIncrementId')->will($this->returnValue('123456'));
        $this->orderMock->expects($this->at(6))->method('getOrderCurrencyCode')->will($this->returnValue('USD'));

        $this->scoreConfigMock->expects($this->at(0))->method('getValue')->with('payment/ipgpay_ipgpaymagento2/account_id', 'store')->will($this->returnValue('123456'));
        $this->scoreConfigMock->expects($this->at(1))->method('getValue')->with('payment/ipgpay_ipgpaymagento2/test_mode', 'store')->will($this->returnValue(1));
        $this->scoreConfigMock->expects($this->at(2))->method('getValue')->with('payment/ipgpay_ipgpaymagento2/payment_form_id', 'store')->will($this->returnValue('123456'));
        $this->scoreConfigMock->expects($this->at(3))->method('getValue')->with('general/store_information/name', 'store')->will($this->returnValue('name'));
        $this->scoreConfigMock->expects($this->at(4))->method('getValue')->with('payment/ipgpay_ipgpaymagento2/create_customers', 'store')->will($this->returnValue(1));
        $this->scoreConfigMock->expects($this->at(5))->method('getValue')->with('payment/ipgpay_ipgpaymagento2/request_expiry', 'store')->will($this->returnValue(24));
        $this->scoreConfigMock->expects($this->at(6))->method('getValue')->with('payment/ipgpay_ipgpaymagento2/secret_key', 'store')->will($this->returnValue('123456'));
        $this->scoreConfigMock->expects($this->at(7))->method('getValue')->with('payment/ipgpay_ipgpaymagento2/payment_form_url', 'store')->will($this->returnValue('payment.ipgholdings.net'));
        $this->jsonResut->expects($this->once(0))
            ->method('setData')
            ->with($this->stringContains('client_id=123456&create_customer=1&customer_address=&customer_address2=&customer_city=city&customer_company=company&customer_country=countryid&customer_email=email&customer_first_name=firstname&customer_last_name=lastname&customer_phone=telephone&customer_postcode=postcode&customer_state=region&form_id=123456&merchant_name=name&order_currency=USD&order_reference=123456&shipping_city=city&shipping_company=company&shipping_country=countryid&shipping_email=email&shipping_first_name=firstname&shipping_last_name=lastname&shipping_phone=telephone&shipping_postcode=postcode&shipping_state=region&test_transaction=1'));
        $this->controller->execute();
    }
}
