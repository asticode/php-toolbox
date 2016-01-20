<?php

// Enable Composer autoloader
/** @var \Composer\Autoload\ClassLoader $oAutoloader */
$oAutoloader = require dirname(__DIR__) . '/vendor/autoload.php';

// Register test classes
$oAutoloader->addPsr4('Asticode\Toolbox\Tests\\', __DIR__);
