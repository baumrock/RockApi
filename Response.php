<?php

namespace RockApi;

use ProcessWire\WireData;
use ProcessWire\WireHttp;

class Response extends WireData
{
  /** @var WireHttp */
  private $http;
  public $url;
  public $method;

  public function __construct(WireHttp $http)
  {
    $this->http = $http;
  }

  public function hasStatus($status): bool
  {
    return $this->http->getHttpCode() == $status;
  }

  public function __debugInfo()
  {
    return [
      'url' => $this->url,
      'method' => $this->method,
      'status' => $this->status,
      'result' => $this->result,
    ];
  }
}
