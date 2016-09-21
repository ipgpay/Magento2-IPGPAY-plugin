<?php
/**
  * @version $Id$
  * @copyright Copyright (c) 2002 - 2013 IPG Holdings Limited (a company incorporated in Cyprus).
  * All rights reserved. Use is strictly subject to licence terms & conditions.
  * This computer software programme is protected by copyright law and international treaties.
  * Unauthorised reproduction, reverse engineering or distribution of the programme, or any part of it, may
  * result in severe civil and criminal penalties and will be prosecuted to the maximum extent permissible at law.
  * For further information, please contact the copyright owner by email copyright@ipgholdings.net
**/
IPGPAY::register();

class IPGPAY
{
    private static $map = [];

    public static function loadClass($class) {
        if ('\\' == $class[0]) {
            $class = substr($class, 1);
        }

        if (false !== $pos = strrpos($class, '\\')) {
            // namespaced class name
            $classPath = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 0, $pos)) . DIRECTORY_SEPARATOR;
            $className = substr($class, $pos + 1);
        } else {
            // PEAR-like class name
            $classPath = null;
            $className = $class;
        }

        $classPath .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
        foreach (self::$map as $prefix => $dirs) {
            if (0 === strpos($class, $prefix)) {
                foreach ($dirs as $dir) {
                    if (file_exists($dir . DIRECTORY_SEPARATOR . $classPath)) {
                        include_once $dir . DIRECTORY_SEPARATOR . $classPath;
                        return;
                    } else {
                        user_error($dir . DIRECTORY_SEPARATOR . $classPath. " does not exist!");
                    }
                }
            } else {
                user_error("Unable to resolve class $class");
            }
        }
    }

    public static function register() {
        self::$map = [
            'IPGPAY' => [ 0 => __DIR__.'/..' ],
        ];
        spl_autoload_register(array(__CLASS__, 'loadClass'), true);
    }
}