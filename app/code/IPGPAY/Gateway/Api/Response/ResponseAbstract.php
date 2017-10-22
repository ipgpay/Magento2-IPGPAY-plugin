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

use IPGPAY\Gateway\Api\Exceptions\InvalidResponseException;

/**
 * Class ResponseAbstract
 * @package IPGPAY\Response
 */
abstract class ResponseAbstract
{
    /**
     *
     */
    const RESPONSE_APPROVED = 'A';
    /**
     *
     */
    const RESPONSE_DECLINED = 'D';
    /**
     *
     */
    const RESPONSE_ERROR = 'E';

    /**
     * @var string
     */
    public $Response;
    /**
     * @var string
     */
    public $ResponseCode;
    /**
     * @var string
     */
    public $ResponseText;
    /**
     * @var string
     */
    public $TransId;
    /**
     * @var string
     */
    public $ResponseString = '';

    /**
     * Based on a response string, construct and return the relevant response object
     *
     * @param $ResponseString
     * @return Success|Declined|Error
     * @throws InvalidResponseException
     */
    public static function factory($ResponseString)
    {
        try {
            $Xml = new \SimpleXMLElement($ResponseString);
        } catch (\Exception $e) {
            throw new InvalidResponseException("Invalid response: $ResponseString, reason: ".$e->getMessage());
        }

        if (isset($Xml->response)) {
            switch ($Xml->response) {
                case self::RESPONSE_APPROVED:
                    return new Success($Xml);
                    break;
                case self::RESPONSE_DECLINED:
                    return new Declined($Xml);
                    break;
                case self::RESPONSE_ERROR:
                default:
                    return new Error($Xml);
                    break;
            }
        } else {
            return new Error($Xml);
        }
    }

    /**
     * @param \SimpleXMLElement $Xml
     */
    function __construct(\SimpleXMLElement $Xml)
    {
        if (isset($Xml->response)) {
            $this->Response = (string)$Xml->response;
        }
        if (isset($Xml->responsecode)) {
            $this->ResponseCode = (string)$Xml->responsecode;
        }
        if (isset($Xml->responsecode)) {
            $this->ResponseText = (string)$Xml->responsetext;
        }
        if (isset($Xml->trans_id)) {
            $this->TransId = (string)$Xml->trans_id;
        }
    }
}
