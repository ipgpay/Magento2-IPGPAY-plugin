<?php
/**
 * @copyright Copyright (c) 2017 IPG Group Limited
 * All rights reserved.
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE.txt file for details.
 **/

namespace IPGPAY\IPGPAYMagento2\API;

use \IPGPAY\IPGPAYMagento2\API\Exceptions;

use \Psr\Log;

class ParamSigner
{
    private $secret;
    private $params;
    private $lifetime      = 24;
    private $signatureType = 'PSSHA1';

    protected $_logger;

    /**
     * log injection - or not.
     */
    public function __construct()
    {
        $this->_logger = \Magento\Framework\App\ObjectManager::getInstance()->get('\Psr\Log\LoggerInterface');
    }

    /**
     * Set the shared secret
     * @param string $secret
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;
        if (!$this->is_utf8($secret)) {
            $this->_logger->addError('secret is not encoding by UTF-8');
        }
    }

    /**
     * Set the amount of time this URL will be valid for in hours.
     * @param integer $lifetime
     */
    public function setLifeTime($lifetime)
    {
        $this->lifetime = $lifetime;
    }

    /**
     * Set the signature type
     * @param string $signatureType 'sha1' or 'md5'
     * @throws Exceptions\InvalidSignatureTypeException
     */
    public function setSignatureType($signatureType)
    {
        if ($this->_checkSignatureType($signatureType)) {
            $this->signatureType = $signatureType;
        } else {
            $this->_logger->addError('Invalid signatureType');
            throw new Exceptions\InvalidSignatureTypeException("Invalid signatureType : $signatureType");
        }
    }
    /**
     * Set a URL parameter
     * @param string $param
     * @param string $value
     */
    public function setParam($param, $value)
    {
        if ($param != 'PS_SIGNATURE') {

            $this->params[$param] = $value;
            if (!$this->is_utf8($value)) {
                $this->_logger->addError('params[' . $param . '] value is not encoding by UTF-8');
            }
        }
    }

    /**
     * Set multple URL parameters at once using an array
     * @param array $paramArray Associative array of parameters
     */
    public function setParams($paramArray)
    {
        foreach ($paramArray as $param => $value) {
            $this->setParam($param, $value);
        }
    }

    /**
     * Clear the param list
     */
    public function clearParams()
    {
        $this->params = [];
    }

    /**
     * Get the signed query string
     * @throws Exceptions\InvalidSignatureTypeException
     * @return string
     */
    public function getQueryString()
    {
        return $this->getSignature(true);
    }

    /**
     * Sign the params and return them
     * @throws Exceptions\InvalidSignatureTypeException
     */
    public function getSignedParams()
    {
        $Sig = $this->getSignature();
        return $this->params + ['PS_SIGNATURE' => $Sig];
    }

    /**
     * Get the signature for the query string
     * @param boolean $queryString default FALSE
     * @throws Exceptions\InvalidSignatureTypeException
     * @return string
     */
    public function getSignature($queryString = false)
    {
        if (empty($this->secret)) {
            user_error("Paramsigner secret is empty!", E_USER_ERROR);
        }
        $this->setParam('PS_EXPIRETIME', time() + (3600 * $this->lifetime));
        $this->setParam('PS_SIGTYPE', $this->signatureType);
        $sigstring    = $this->secret;
        $urlencstring = '';
        ksort($this->params, SORT_STRING);
        foreach ($this->params as $key => $value) {
            $sigstring .= "&" . $key . '=' . $value;
            $urlencstring .= "&" . urlencode($key) . '=' . urlencode($value);
        }
        switch ($this->params['PS_SIGTYPE']) {
            case 'md5':
            case 'MD5':
            case 'PSMD5':
            case 'psmd5':
                $signature = md5($sigstring);
                break;
            case 'PSSHA1':
            case 'pssha1':
            case 'sha1':
            case 'SHA1':
                $signature = sha1($sigstring);
                break;
            default:
                $this->_logger->addError('Unknown key signatureType');
                throw new Exceptions\InvalidSignatureTypeException('Unknown key signatureType');
        }
        if ($queryString) {
            return 'PS_SIGNATURE=' . urlencode($signature) . $urlencstring;
        } else {
            return $signature;
        }
    }
    /**
     * Generate a signed query string in one shot
     * @param array $paramArray Associative array of name/value pairs to be signed
     * @param string $secret The secret key for the client if not previously set.
     * @param string $signatureType Signature type if not previously set or default, 'sha1' or 'md5'
     * @return string
     * @throws Exceptions\InvalidSignatureTypeException
     */
    public function generateQueryString($paramArray, $secret = null, $signatureType = null)
    {
        if ($secret !== null) {
            $this->setSecret($secret);
        }
        if ($signatureType != null) {
            $this->setSignatureType($signatureType);
        }
        $this->clearParams();
        foreach ($paramArray as $name => $value) {
            $this->setParam($name, $value);
        }

        return $this->getQueryString();
    }

    /**
     * Generate a signed URL from an existing URL
     * @param string $secret The secret key for the client
     * @param string $url URL to be signed
     * @param string $signatureType Signature type, 'sha1' (default) or 'md5'
     * @throws Exceptions\InvalidSignatureTypeException
     * @return string
     */
    public function signURL($url, $secret = null, $signatureType = null)
    {
        //$p=parse_url($url);
        $p    = $this->parse_utf8_url($url);
        $temp = [];
        parse_str($p['query'], $temp);
        $querystring = $this->generateQueryString($temp, $secret, $signatureType);
        return $p['scheme'] . '://' . $p['host'] . $p['path'] . '?' . $querystring;
    }

    private function parse_utf8_url($url)
    {
        static $keys = [
            'scheme'   => 0,
            'user'     => 0,
            'pass'     => 0,
            'host'     => 0,
            'port'     => 0,
            'path'     => 0,
            'query'    => 0,
            'fragment' => 0,
        ];
        if (is_string($url) && preg_match(
            '~^((?P<scheme>[^:/?#]+):(//))?((\3|//)?(?:(?P<user>[^:]+):(?P<pass>[^@]+)@)?(?
        P<host>[^/?:#]*))(:(?P<port>\d+))?' .
            '(?P<path>[^?#]*)(\?(?P<query>[^#]*))?(#(?P<fragment>.*))?~u',
            $url,
            $matches
        )) {
            foreach ($matches as $key => $value) {
                if (!isset($keys[$key]) || empty($value)) {
                    unset($matches[$key]);
                }
            }
            return $matches;
        }
        return false;
    }

    public function is_utf8($value)
    {
        $length = strlen($value);
        for ($i = 0; $i < $length; $i++) {
            $c = ord($value[$i]);

            if ($c < 0x80) {
                $n = 0;
            } elseif (($c & 0xE0) == 0xC0) {
                $n = 1;
            } elseif (($c & 0xF0) == 0xE0) {
                $n = 2;
            } elseif (($c & 0xF8) == 0xF0) {
                $n = 3;
            } elseif (($c & 0xFC) == 0xF8) {
                $n = 4;
            } elseif (($c & 0xFE) == 0xFC) {
                $n = 5;
            } else {
                return false;
            }

            for ($j = 0; $j < $n; $j++) {
                if ((++$i == $length) || ((ord($value[$i]) & 0xC0) != 0x80)) {
                    return false;
                }

            }
        }
        return true;
    }

    private function _checkSignatureType($value)
    {
        if ($value == 'md5') {
            return true;
        }
        if ($value == 'MD5') {
            return true;
        }
        if ($value == 'PSMD5') {
            return true;
        }
        if ($value == 'psmd5') {
            return true;
        }
        if ($value == 'sha1') {
            return true;
        }
        if ($value == 'SHA1') {
            return true;
        }
        if ($value == 'PSSHA1') {
            return true;
        }
        if ($value == 'pssha1') {
            return true;
        }
        return false;
    }

    public function paramAuthenticate($paramArray, $secret = false)
    {
        if (false === $secret) {
            $secret = $this->secret;
        }
        $sentSignature = @$paramArray['PS_SIGNATURE'];
        unset($paramArray['PS_SIGNATURE']);
        $string = '';
        ksort($paramArray, SORT_STRING);
        foreach ($paramArray as $key => $value) {
            $string .= "&" . $key . '=' . $value;
        }
        switch (@$paramArray['PS_SIGTYPE']) {
            case 'MD5':
            case 'md5':
            case 'PSMD5':
            case 'psmd5':
                $signature = md5($secret . $string);
                break;
            case 'sha1':
            case 'SHA1':
            case 'PSSHA1':
            case 'pssha1':
                $signature = sha1($secret . $string);
                break;
            default:
                return false;
        }
        if ($sentSignature !== $signature) {
            return false;
        }
        unset($paramArray['PS_SIGTYPE']);
        unset($paramArray['PS_EXPIRETIME']);
        return $paramArray;
    }
}
