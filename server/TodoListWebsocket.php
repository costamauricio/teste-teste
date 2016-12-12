<?php
namespace App;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use App\ItemService;

class TodoListWebsocket implements MessageComponentInterface {

  private $clients;
  private $service;


  public function __construct() {
    $this->clients = new \SplObjectStorage;
    $this->service = new RedisService();
  }

  public function onOpen(ConnectionInterface $conn) {

    $this->clients->attach($conn);
    $this->message($conn,[
      'event' => 'populate',
      'itens' => $this->service->getAll()
    ]);
  }

  public function onMessage(ConnectionInterface $from, $msg) {

    $data = json_decode($msg);
    $uuid = null;

    switch ($data->event) {
      case 'add':
        $uuid = $this->service->add($data->content, false);
        $this->sendMessage($from, [
          'event' => 'add',
          'item' => [
            'uuid' => $uuid,
            'content' => $data->content,
            'checked' => false
          ]
        ]);
        break;

      case 'remove':
        $uuid = $data->uuid;
        $this->service->remove($uuid);
        $this->sendMessage($from, [
          'event' => 'remove',
          'item' => [
            'uuid' => $uuid
          ]
        ]);
        break;

      case 'check':
        $uuid = $data->uuid;
        $this->service->check($uuid);
        $this->sendMessage($from, [
          'event' => 'check',
          'item' => [
            'uuid' => $uuid
          ]
        ]);
        break;

      case 'uncheck':
        $uuid = $data->uuid;
        $this->service->uncheck($uuid);
        $this->sendMessage($from, [
          'event' => 'uncheck',
          'item' => [
            'uuid' => $uuid
          ]
        ]);
        break;
    }
  }

  public function onClose(ConnectionInterface $conn) {
    $this->clients->detach($conn);
  }

  public function onError(ConnectionInterface $conn, \Exception $e) {
    throw new \Exception("An error has occurred: {$e->getMessage()}\n");

    $conn->close();
  }

  private function sendMessage(ConnectionInterface $from, $msg) {

    foreach ($this->clients as $client) {
      if ($from !== $client || $msg['event'] == 'add') {
        $this->message($client, $msg);
      }
    }
  }

  private function message(ConnectionInterface $to, $msg) {
    $to->send(json_encode((object) $msg));
  }
}
