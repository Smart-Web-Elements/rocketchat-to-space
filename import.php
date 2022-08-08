<?php

use Dotenv\Dotenv;
use Swe\RTS\Collector;
use Swe\RTS\Importer;
use Swe\RTS\Settings;

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$settings = new Settings();
$settings->setRocketChatUrl($_ENV['ROCKET_CHAT_URL']);
$settings->setRocketChatUser($_ENV['ROCKET_CHAT_USER']);
$settings->setRocketChatPassword($_ENV['ROCKET_CHAT_PASSWORD']);
$settings->setSpaceUrl($_ENV['SPACE_URL']);
$settings->setSpaceClientId($_ENV['SPACE_CLIENT_ID']);
$settings->setSpaceClientSecret($_ENV['SPACE_CLIENT_SECRET']);

$userMapping = json_decode(file_get_contents(__DIR__ . '/user-mapping.json'), true);

$collector = new Collector($settings, $userMapping);
$importer = new Importer($settings, $argv[1] ?? '', (int)$argv[2]);

$collector->collect();
$importer->import();