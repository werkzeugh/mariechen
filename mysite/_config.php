<?php

//use SilverStripe\ORM\Connect\MySQLDatabase;
use SilverStripe\i18n\i18n;
//use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Security\Member;
use SilverStripe\ORM\DataObject;

//use SilverStripe\Control\Director;
//use SilverStripe\View\SSViewer;

//MySQLDatabase::set_connection_charset('utf8');

// Set the site locale
i18n::set_locale('de_DE');


ini_set('date.timezone', 'Europe/Vienna');  //mwuits
 
// enable nested URLs for this site (e.g. page/sub-page/)
//SiteTree::enable_nested_urls();

//DataObject::add_extension('Member', 'MwUserRole');
//DataObject::add_extension(Member::class, 'MysiteUserRole');

Member::add_extension(MysiteUserRole::class);

MwShop::setConf('CheckoutPage', 'de/cart');
MwShop::setConf('MerchantEmail', 'werkzeugh@gmail.com');
MwShop::setConf('SofortConfigKey', '62263:140692:9f2b1b70c2f0ea3e73b7c7ed43829547');


MwFile::add_extension(MysiteFileExtension::class);


MwPage::setConf('allowedPageClasses', 'Article,TagNode');
MwPage::setConf('UseFramesInBE', true);

MwUser::setConf('disable_usernames', true);
MwUser::setConf('sendActivationMailAfterCreation', 1);
// MwUser::setConf('mail_sender','absender@mail.com');
MwUser::setConf('usePlainLayoutForLogin', true);



MwVhostMapper::addRules(
    100,
    [
   'wwwneu.' => 'home',
   'www.'    => 'home',
   'mariechen.'    => 'home',
    ]
);

$callback2 =
            function ($request) {
                $url=$request->getUrl();
                if (preg_match("#^(de|en|fr)(.*)$#", $url, $m)) {
                    $lang=$m[1];
                    $url=$m[2];
                    $request->setUrl($url);
                    $headers=$request->getHeaders();
                    $headers['mw-language']=$lang;
                    MwVhostMapper::setPrefix($lang."/");
                    $request->setHeaders($headers);
                }
                // die("\n\n<pre>mwuits-debug 2020-01-31_09:52 ".print_r($request->getUrl(), true));
                return $request;
            };

    MwVhostMapper::setConf('callback2', $callback2);
