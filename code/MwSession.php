<?php

namespace  Mwerkzeug;

use SilverStripe\Control\Controller;

//v3 style legacy sessions
class MwSession
{
    public static function set($name, $val)
    {
        return Controller::curr()->getRequest()->getSession()->set($name, $val);
    }

    public static function get($name)
    {
        return Controller::curr()->getRequest()->getSession()->get($name);
    }

    public static function save()
    {
        $req=Controller::curr()->getRequest();
        return $req->getSession()->save($req);
    }

    public static function clear($name)
    {
        $req=Controller::curr()->getRequest();
        return $req->getSession()->clear($name);
    }
}
