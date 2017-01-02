<?php

require "vendor/autoload.php";

$output = "[%datetime%] %channel%.%level_name%: %message%\n";
$formatter = new \Monolog\Formatter\LineFormatter($output);

$streamHandler = new \Monolog\Handler\StreamHandler('php://stdout', \Monolog\Logger::DEBUG);
$streamHandler->setFormatter($formatter);

$logger = new \Monolog\Logger('PerfectsBot', [$streamHandler]);

$bot = new \drupol\perfectsbot\Perfects();
$bot->setLogger($logger);
$bot->start();