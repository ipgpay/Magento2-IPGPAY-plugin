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
namespace IPGPAY\Gateway\Api\Response;

class Error extends ResponseAbstract {
    public $Errors = array();

    /**
     * Construct the error response
     * Set the response code and response text to that of the first error
     * Keep a list of the full errors
     *
     * @param \SimpleXMLElement $Xml
     */
    function __construct (\SimpleXMLElement $Xml) {
        parent::__construct($Xml);
        if (!isset($Xml->response) && isset($Xml->errors)) {
            $this->Response = self::RESPONSE_ERROR;
            foreach ($Xml->errors as $error) {
                if (empty($this->ResponseCode)) {
                    $this->ResponseCode = (string)$error->error->code;
                    $this->ResponseText = (string)$error->error->text;
                }
                $this->Errors[(string)$error->error->code] = (string)$error->error->text;
            }
        }
    }
}