<?php
/**
 * @copyright Copyright (c) 2017 IPG Group Limited
 * All rights reserved.
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE.txt file for details.
 **/
namespace IPGPAY\Test\Unit\Api;

use \IPGPAY\Gateway\Api\ParamSigner as ParamSigner;

class ParamSignerTest extends \PHPUnit\Framework\TestCase
{
    protected $model;

    protected function setup()
    {
        $this->model = new ParamSigner();
    }

    public function test_inputData_isUtf8()
    {

        $shouldTrueValue = 'PS_SIGNATURE=2dc27ecd802200d5bbba612e832b555246b493e1&PS_EXPIRETIME=1510211197&PS_SIGTYPE=PSSHA1&amount=3.03&auth_code=123456&card_brand=MasterCard&card_category=Credit&card_exp_month=11&card_exp_year=32&card_holder_name=hell&card_issuing_bank=&card_issuing_country=US&card_number=550000******0004&card_sub_category=&client_id=4003442&customer_address=gfd&customer_address2=&customer_city=hellowood&customer_company=&customer_country=CN&customer_email=admin%40example.com&customer_first_name=Mary&customer_id=5593&customer_last_name=Kate&customer_phone=155268452144&customer_postcode=130021&customer_state=Jilin&domain=ipgholdings.net&item_1_code=Princess-Dress&item_1_description=&item_1_digital=1&item_1_id=91399363&item_1_name=Princess-Dress&item_1_pass_through=&item_1_qty=3&item_1_rebill=0&item_1_unit_price_USD=1.01&merchant_user_id=&notification_type=order&order_currency=USD&order_datetime=2017-11-08+07%3A06%3A36&order_id=91068413&order_reference=000000023&pass_through=&payment_method=creditcard&response=A&response_code=OP000&response_text=ApproveTEST&shipping_address=&shipping_address2=&shipping_city=&shipping_company=&shipping_country=&shipping_first_name=&shipping_last_name=&shipping_phone=&shipping_postcode=&shipping_state=&test_transaction=1&trans_id=319056443&trans_type=auth';

        $shouldFalseValue = 'Q29udGVudC10eXBlOiB0ZXh0L2h0bWw7IGNoYXJzZXQ9aXNvLTg4NTktMQpDb250ZW50LWxhbmd1YWdlOiBmcgoKPEhFQUQ+CjxUSVRMRT5GcmVuY2ggLyBGcmFu52FpcyAoSVNPIExhdGluLTEgLyBJU08gODg1OS0xKTwvVElUTEU+CjwvSEVBRD4KPEJPRFk+CjxIMT5GcmVuY2ggLyBGcmFu52FpcyAoSVNPIExhdGluLTEgLyBJU08gODg1OS0xKTwvSDE+CkJvbmpvdXIsIFNhbHV0CjwvQk9EWT4K';

        $this->assertTrue($this->model->is_utf8($shouldTrueValue));

        $this->assertFalse($this->model->is_utf8(base64_decode($shouldFalseValue)));
    }
}
