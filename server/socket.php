<?php

require "../vendor/autoload.php";

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use App\TodoListWebsocket;

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new TodoListWebsocket()
        )
    ),
    5001
);

$server->run();
