<?php
/**
 * @version $Id$
 * @copyright Copyright (c) 2002 - 2011 IPG Holdings Limited (a company incorporated in Cyprus).
 * All rights reserved. Use is strictly subject to licence terms & conditions.
 * This computer software programme is protected by copyright law and international treaties.
 * Unauthorised reproduction, reverse engineering or distribution of the programme, or any part of it, may
 * result in severe civil and criminal penalties and will be prosecuted to the maximum extent permissible at law.
 * For further information, please contact the copyright owner by email copyright@ipgholdings.net
 **/
namespace IPGPAY\Gateway\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use \Magento\Framework\App\ObjectManager;

class ConfigProvider implements ConfigProviderInterface 
{
    public function getConfig()
    {
        $config = [
            'payment' => [
                'ipgpay_gateway' => [
                    'redirectUrl' => $this->getMethodRedirectUrl()
                ]
            ]
        ];
        return $config;
    }

    /**
     * Return redirect URL for method
     *
     * @return mixed
     */
    protected function getMethodRedirectUrl()
    {
        $urlInterface = ObjectManager::getInstance()->get('Magento\Framework\UrlInterface');
        return $urlInterface->getUrl('ipgpay/redirect/go');
    }
}
