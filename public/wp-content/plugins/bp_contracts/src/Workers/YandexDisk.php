<?php

namespace PersonalAccount\Workers;
use GuzzleHttp\Client;


// https://github.com/ok2xx/yandex-disk-api


class YandexDisk {
  private $id;
  private $base_uri = 'https://cloud-api.yandex.net/v1/disk/';
  private $auth_type = 'OAuth';
  private $client;
  private $token;
  private $headers;
  protected $returnDecoded = false;
  public function __construct($id)
  {
      $this->id = $id;
      $this->client = new Client();
      
  }
}