<?php

use SilverStripe\Control\HTTPApplication;
use SilverStripe\Control\HTTPRequestBuilder;
use SilverStripe\Core\CoreKernel;
use SilverStripe\Core\Startup\ErrorControlChainMiddleware;
use SilverStripe\Core\Config\Config;

define('ASSETS_DIR', 'files');

require __DIR__ . '/vendor/autoload.php';

// Build request and detect flush
$request = HTTPRequestBuilder::createFromEnvironment();
// Default application
$kernel = new CoreKernel(BASE_PATH);
$app = new HTTPApplication($kernel);
$app->addMiddleware(new ErrorControlChainMiddleware($app));


if (array_key_exists('confshow', $_GET)) {
    $config = Config::inst()->getAll();
    echo("\n\n<pre>mwuits-debug 2018-01-24_16:13 ".print_r($config, 1));
    exit();
}

$response = $app->handle($request);

if (array_key_exists('confshow', $_GET)) {
    $config = Config::inst()->getAll();
    echo("\n\n<pre>mwuits-debug 2018-01-24_16:13 ".print_r($config, 1));
}

$response->output();
