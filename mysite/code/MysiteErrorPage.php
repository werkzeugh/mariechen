<?php

use SilverStripe\View\Requirements;
use SilverStripe\Control\Session;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\ORM\DataObject;
use SilverStripe\Control\Controller;

class MysiteErrorPage extends FrontendPage
{
}

class MysiteErrorPageController extends FrontendPageController
{
    public function init()
    {
        // echo "<pre>";
        // debug_print_backtrace() ;
        // die("\n\n<pre>mwuits-debug 2020-02-20_23:17 ".print_r(0, true));
        $url=$_SERVER['REQUEST_URI'];
        $map=[
            '/sale.html'=>'/shop/sale',
            '/sale-de.html'=>'/de/shop/sale',
            '/store.html'=>'/info',
            '/eva-blut-store-verkaufsstellen.html'=>'/de/info',
            '/about-eva-blut.html'=>'/about',
            '/ueber-eva-blut.html'=>'/de/about',
        ];
        if ($next=array_get($map, $url)) {
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: $next");
            exit();
        }

        parent::init();
    }
}
