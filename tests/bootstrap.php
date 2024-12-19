<?php

declare(strict_types=1);

$testsDir = str_replace('\\', '/', __DIR__);
$libDir = dirname($testsDir);
$autoload = "{$libDir}/vendor/autoload.php";

if (!is_readable($autoload)) {
    die('Please install via Composer before running tests.');
}

putenv("LIB_PATH={$libDir}");
putenv('SNIFFS_NAMESPACE=SydeCS\\Syde\\Sniffs');
putenv("FIXTURES_PATH={$testsDir}/unit/fixtures");

if (!defined('PHPUNIT_COMPOSER_INSTALL')) {
    define('PHPUNIT_COMPOSER_INSTALL', $autoload);
    include_once $autoload;
}

require_once "{$testsDir}/autoload.php";

unset($libDir, $testsDir, $autoload);

error_reporting(E_ALL);
