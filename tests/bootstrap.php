<?php
namespace PHPCrystal\PHPCrystalTest;

use PHPCrystal\PHPCrystal\Component\Filesystem\FileHelper;

require (__DIR__ . '/../vendor/autoload.php');

FileHelper::addAlias('app', __DIR__);