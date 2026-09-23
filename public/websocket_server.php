<?php
require_once 'vendor/autoload.php';
require_once 'wp-load.php';

use PersonalAccount\Chat;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

$chat = new Chat();

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            $chat
        )
    ),
    8080,
);

$server->run();