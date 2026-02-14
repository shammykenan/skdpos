<?php
require __DIR__ . '/vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class CoffeeShopWS implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage();
        echo "WebSocket server started...\n";
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New client connected: {$conn->remoteAddress}\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        echo "Received: $msg\n";

        // Broadcast to all clients except sender
        foreach ($this->clients as $client) {
            if ($from !== $client) {
                $client->send($msg);
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "Client disconnected: {$conn->remoteAddress}\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Error: {$e->getMessage()}\n";
        $conn->close();
    }
}

// Run server
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

$port = 8081; // Choose unused port
$host = '0.0.0.0'; // LAN + localhost access

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new CoffeeShopWS()
        )
    ),
    $port,
    $host
);

echo "Server running at ws://$host:$port\n";
$server->run();
