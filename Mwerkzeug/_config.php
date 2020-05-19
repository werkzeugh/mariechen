<?php

use SilverStripe\i18n\i18n;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\Connect\MySQLDatabase;
use SilverStripe\Control\Director;
use SilverStripe\View\SSViewer;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Versioned\Versioned;
use SilverStripe\Core\Injector\Injector;


use Psr\Log\LoggerInterface;

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);

i18n::config()->update('missing_default_warning', false);


$logger = Injector::inst()->get(LoggerInterface::class);

$_SERVER['URL']=$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

$processor=new \Monolog\Processor\WebProcessor(
    null,
    [
        'ip'          => 'REMOTE_ADDR',
        'http_method' => 'REQUEST_METHOD',
        'url'         => 'URL',
    ]
);
 $logger->pushProcessor($processor);




if (! function_exists('value')) {
  /**
   * Return the default value of the given value.
   *
   * @param  mixed  $value
   * @return mixed
   */
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}

if (! function_exists('array_get')) {
  /**
   * Get an item from an array using "dot" notation.
   *
   * @param  array   $array
   * @param  string  $key
   * @param  mixed   $default
   * @return mixed
   */
    function array_get($array, $key, $default = null)
    {
        if (is_null($key)) {
            return $array;
        }

        if (isset($array[$key])) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (! is_array($array) || ! array_key_exists($segment, $array)) {
                return value($default);
            }

            $array = $array[$segment];
        }

        return $array;
    }
}





/*
DataObject::$create_table_options[MySQLDatabase::class] = 'ENGINE=MyISAM';

if(array_get($_SERVER,'REMOTE_ADDR')=='127.0.0.1' || strstr(array_get($_SERVER,'HTTP_HOST'),'dev'))
{
#  Director::set_environment_type('dev');
}

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT );

if(Director::isDev())
{
  if(strstr(array_get($_SERVER,'HTTP_REFERER'),'flush=1'))
  {
      array_get($_GET,'flush') = 1;
      array_get($_REQUEST,'flush') = 1;
  }
  SSViewer::set_source_file_comments(TRUE);
}



SSViewer::setOption('rewriteHashlinks', false);

//DataObject::add_extension(SiteTree::class, 'MwHidden');

DataObject::$create_table_options[MySQLDatabase::class] = 'ENGINE=MyISAM';

MwPage::setConf('JsTreeWidth','300');

SS_Cache::set_cache_lifetime('any', 6000);

Versioned::set_stage("Live");

// add this to your own project:
// DataObject::add_extension('Member', 'MwUserRole');
// OR
// DataObject::add_extension('Member', 'MysiteUserRole');


// add this to your rules if you want to override:
// 'User/$Action/$ID/$OtherID'   => 'MysiteUser_Controller',
*/
