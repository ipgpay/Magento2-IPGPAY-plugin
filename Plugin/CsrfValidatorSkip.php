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
namespace IPGPAY\IPGPAYMagento2\Plugin;

class CsrfValidatorSkip
{
        public function aroundValidate(
                $subject,
                \Closure $proceed,
                $request,
                $action
        ) {
                // requests to this plugin are expected to originate from the IPGPAY servers.
                // as such, they cannot include the CSRF token. The requests are signed and 
                // verified using the RequestSigner module.
                if ($request->getModuleName() == 'ipgpay') {
                        return;
                }
                $proceed($request, $action);
        }
}

