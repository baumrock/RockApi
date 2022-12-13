<?php

namespace RockApi;

use ProcessWire\WireData;
use ProcessWire\WireHttp;

class Response extends WireData
{
  /** @var WireHttp */
  private $http;

  public function __construct(WireHttp $http)
  {
    $this->http = $http;
  }

  public function hasStatus($status): bool
  {
    return $this->http->getHttpCode() == $status;
  }
}
