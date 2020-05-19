<?php

namespace  Mwerkzeug;
use SilverStripe\Control\Director;
use SilverStripe\View\Requirements;

class MwRequirements
{

    function javascript($relativePath,$a=null,$b=null)
    {

        $absolutePath = preg_replace('/\?.*/', '', Director::baseFolder() . '/' . $relativePath);
        $exists = file_exists($absolutePath);

        if ($exists) {
            Requirements::javascript($relativePath, $a, $b);
        }
    }

    function CSS($relativePath,$a=null,$b=null)
    {

        $absolutePath = preg_replace('/\?.*/', '', Director::baseFolder() . '/' . $relativePath);
        $exists = file_exists($absolutePath);

        if ($exists) {
            Requirements::CSS($relativePath, $a, $b);
        }
    }

}