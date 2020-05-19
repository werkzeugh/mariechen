<?php

use SilverStripe\Control\HTTPRequest;

/*
* put into Director.php:

protected static function handleRequest(SS_HTTPRequest $request, Session $session) {
    krsort(Director::$rules);

    if(isset(array_get($_REQUEST,'debug'))) Debug::show(Director::$rules);

    foreach(Director::$rules as $priority => $rules) {

        // ArrayList fix for MwPortal - Url-Handling ---------- BEGIN
        if($priority===50)
        {
            $request = MwVhostMapper::handleRequestHook($request,$session);
        }
        // ArrayList fix for MwPortal - Url-Handling ---------- END

        */

class MwVhostMapper
{
    private static $rules = array();
    private static $baseurl;
    private static $basehost;
    private static $prefix=null;
    public static $conf = array();


    public function getMwLink()
    {
        return "mwlink://MwFile-{$this->ID}";
    }


    public static function conf($key)
    {
        return self::$conf[$key];
    }

    public static function setConf($key, $value)
    {
        self::$conf[$key]=$value;
    }


    public static function init()
    {
    }


    public function setBaseHost($value)
    {
        self::$basehost=$value;
    }

    public function setPrefix($value)
    {
        self::$prefix=$value;
    }

    public static function getAbsoluteUrl($relativeurl)
    {
        if (self::$basehost) {
            if ($bu=self::getCurrentBaseUrl()) {
                $relativeurl=$bu.$relativeurl;
            }

            //loop thru all rules
            foreach (MwVhostMapper::$rules as $prio => $rules) {
                foreach ($rules as $matchkey => $url) {
                    if (strstr($relativeurl, $url) && preg_match('#^'.$url.'(.*)$#', $relativeurl, $m)) {
                        return "http://$matchkey".self::$basehost.$m[1];
                    }
                }
            }
        }

        return $relativeurl;
    }


    public static function getCurrentPrefix()
    {
        if (self::conf('hidePrefix')) {
            return '';
        }

        return self::$prefix;
    }

    public static function getCurrentBaseUrl()
    {
        if (self::conf('hideBaseUrl')) {
            return '';
        }

        if (MwVhostMapper::$rules) {
            if (!isset(MwVhostMapper::$baseurl)) {
                krsort(MwVhostMapper::$rules);
                $host=array_get($_SERVER, 'HTTP_HOST');
                //loop thru all rules
                foreach (MwVhostMapper::$rules as $prio => $rules) {
                    foreach ($rules as $matchkey => $url) {
                        if (strstr($matchkey, '/')) {
                            $match=preg_match($matchkey, $host);
                        } else {
                            $match=strstr($host, $matchkey);
                        }

                        if ($match) {
                            //remove trailing and leading slashes from baseurl:
                            $url=preg_replace('#^/#', '', $url);
                            $url=preg_replace('#/$#', '', $url);
                            self::$baseurl=$url;
                            break;
                        }
                    }
                    if (isset(self::$baseurl)) {
                        break;
                    }
                }
            }

            return self::$baseurl;
        }
    }


    public static $already_rewritten=0;

    public static function handleRequestHook4Director($request)
    {
        if ($cb=self::conf('callback')) {
            $cb($request);
        }
        
        //only handle root-urls in Director-Hook
        if (!$request->getURL() && $baseurl=self::getCurrentBaseUrl()) {
            $url=$baseurl.'/'.$request->getURL();
            session_start();
            $newRequest = new HTTPRequest(
                (isset($_SERVER['X-HTTP-Method-Override'])) ? $_SERVER['X-HTTP-Method-Override'] : array_get($_SERVER, 'REQUEST_METHOD'),
                $url,
                $_GET,
                array_merge((array)$_POST, (array)$_FILES),
                @file_get_contents('php://input')
            );
            $newRequest->setSession($request->getSession());
            self::$already_rewritten=1;

            $request= $newRequest;
        }
        return $request;
    }

    public static function handleRequestHook4ModelAsController($request)
    {

        // handle rest of urls in ModelAsController-Hook
        // 'normal' routes get parsed inbetween handleRequestHook4Director and handleRequestHook4ModelAsController

        // die("\n\n<pre>mwuits-debug 2019-02-28_11:57 ".print_r("CALLBACK", 1));
        if ($cb=self::conf('callback2')) {
            $request=$cb($request);
        }
        
        if (!self::$already_rewritten && $baseurl=self::getCurrentBaseUrl()) {
            $url=$baseurl.'/'.$request->getURL();
            $_GET['url']="/".$url;
            session_start();

            $newRequest = new HTTPRequest(
                (isset($_SERVER['X-HTTP-Method-Override'])) ? $_SERVER['X-HTTP-Method-Override'] : array_get($_SERVER, 'REQUEST_METHOD'),
                $url,
                $_GET,
                array_merge((array)$_POST, (array)$_FILES),
                @file_get_contents('php://input')
            );
            $newRequest->setHeaders($request->getHeaders());
            $newRequest->fixParamsForMwVhostMapper();
            $newRequest->setSession($request->getSession());
           
            $request=$newRequest;
        }

        return $request;
    }



    public static function addRules($priority, $rules)
    {
        MwVhostMapper::$rules[$priority] = isset(MwVhostMapper::$rules[$priority]) ? array_merge($rules, (array)MwVhostMapper::$rules[$priority]) : $rules;
    }

    public static function getRules()
    {
        return MwVhostMapper::$rules;
    }
}
