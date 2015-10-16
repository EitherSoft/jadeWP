<?php

/**
 * jadeWP class loader
 *
 * @package jadeWP
 *
 * @link https://github.com/jonnSmith/jadeWP for the canonical source repository
 * @copyright Copyright (c) 2015, iskra.ua
 * @license http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @author jonnSmith <eugenpushkaroff@gmail.com>
 *
 */

namespace jadeWP;

spl_autoload_register(function($class_name) {

    if(mb_strpos($class_name, __NAMESPACE__) !== false) {
        $filename = ABSPATH . ltrim(str_replace("\\", DIRECTORY_SEPARATOR, $class_name), DIRECTORY_SEPARATOR) . ".php";
        if(is_file($filename)) {
            return include_once $filename;
        }
    }
    return false;
});