<?php

namespace App;

use Predis\Client;

class RedisService {

  private $client;

  public function __construct() {

    $this->client = new Client([
      'scheme' => 'tcp',
      'host' => 'redis',
      'port' => 6379
    ]);
  }

  public function add($content, $checked) {

    $client = $this->client;
    $uuid = uniqid('');

    $keys = $this->client->keys("todo:*");

    $keys = array_map(function($hash) use($client) {
      return $client->hget($hash, 'ord');
    }, $keys);

    $this->client->hmset("todo:{$uuid}", [
        'content' => $content,
        'checked' => $checked,
        'ord' => empty($keys) ? 1 : max($keys)+1
      ]);

    return $uuid;
  }

  public function remove($uuid) {
    $this->client->del("todo:{$uuid}");
  }

  public function check($uuid) {
    $this->client->hset("todo:{$uuid}", 'checked', true);
  }

  public function uncheck($uuid) {
    $this->client->hset("todo:{$uuid}", 'checked', false);
  }

  public function getAll() {

    $todo = [];

    foreach ($this->client->keys("todo:*") as $key) {
      $todo[] = array_merge(['uuid' => str_replace("todo:", '', $key)], $this->client->hgetall($key));
    }

    return $todo;
  }
}
