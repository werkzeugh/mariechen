<?php
use SilverStripe\i18n\i18n;
use SilverStripe\Security\PermissionProvider;

/**
 *
 */

// add to sapphire/core/Core.php:
//  DEPRECATED !!!  see Mw_i18nMessageProvider.php

class Mw_i18n extends i18n implements PermissionProvider
{
    
    static function _t($entity, $string = "_DEFAULTSTRING_", $priority = 40, $context = "", $injection = "")
    {


        if ($string=="_DEFAULTSTRING_" || is_array($string)) {
             return MwLang::get($entity);
        }

        if ($string=="_DEFAULTSTRING_") {
            $string="";
        }

        return MwStaticText::translate($entity, $string, $priority, $context, $injection);
    }


    function providePermissions()
    {
        return array(
            "EDIT_STATIC_TEXTS" => "edit static texts",
        );
    }
}
