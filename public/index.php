<?php
/**
 * Welcome to Celestial!
 * Official repository: https://github.com/libra-php/celestial.git
 * Created with <3
 * Copyright (c) 2022 William Hleucka. All Rights Reserved.
 */
session_start();
$global_start = microtime(true);
require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/utils/functions.php";
new Celestial\Kernel\Main();

