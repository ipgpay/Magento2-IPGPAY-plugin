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
namespace IPGPAY\Gateway\Api;

/**
 * Class Functions
 * @package IPGPAY\Gateway\Api
 */
class Functions {
    /**
     * Valid numeric amount. Checks to how many digits the decimal amount has if decimals exists. At most it can be 2.
     *
     * @param $Amount
     * @return bool
     */
    public static function isValidAmount($Amount) {
        if (!is_numeric($Amount)) {
            return FALSE;
        }
        if(strstr($Amount,'.'))
        {
            $dot = strrpos($Amount,'.');
            if($dot==0)
            {
                $centlen = strlen($Amount)-1;
                if ($centlen>2)  return FALSE;
            }
            else
            {
                if($dot>0)
                {
                    $centlen = strlen($Amount)-strrpos($Amount,'.')-1;
                    if($centlen>2) return FALSE;
                }
            }
        }
        return TRUE;
    }

    /**
     * Validate Sql int
     *
     * @param $value
     * @return bool
     */
    public static function isValidSqlInt($value)
    {
        if(preg_match('/^\d+$/', (string)$value) && $value <= 2147483647){
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Validate Sql smallint
     *
     * @param $value
     * @return bool
     */
    public static function isValidSqlSmallInt($value)
    {
        if(preg_match('/^\d+$/', (string)$value) && $value <= 32767){
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Validate Sql bigint
     *
     * @param $value
     * @return bool
     */
    public static function isValidSqlBigInt($value)
    {
        if(preg_match('/^\d+$/', (string)$value) && $value <= 9223372036854775807){
            return TRUE;
        }

        return FALSE;
    }

    /**       
     * Create a signature 
     * 
     * @param $data
     * @param $secret
     * @return string
     */
    public static function createSignature($data, $secret)
    {
        if (isset($data['PS_SIGNATURE'])) unset($data['PS_SIGNATURE']);
        ksort($data, SORT_STRING);
        foreach($data as $key => $value) {
            //We need to decode as in some cases the escaped equivalent is already in the database and when the redirect
            //form submits, the browser turns it into a non escaped version causing signatures not to match when received
            //by the gateway.
            $secret .= sprintf('&%s=%s', $key, html_entity_decode($value, ENT_COMPAT | ENT_HTML5, 'UTF-8'));
        }

        return sha1($secret);
    }

    /**   
     * Check to see if signature is valid
     * 
     * @param $signature
     * @param $data
     * @param $secret
     * @return bool
     */
    public static function isValidSignature($signature, $data, $secret)
    {
        if ($signature == self::createSignature($data, $secret)) {
            return true;    
        }   
        return false;
    }
}