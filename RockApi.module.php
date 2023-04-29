<?php

namespace ProcessWire;

use RockApi\Response;

/**
 * @author Bernhard Baumrock, 07.12.2022
 * @license Licensed under MIT
 * @link https://www.baumrock.com
 */
class RockApi extends WireData implements Module
{
  public $url = "";
  private $http;

  public static function getModuleInfo()
  {
    return [
      'title' => 'RockApi',
      'version' => '0.0.1',
      'summary' => 'Base module to create custom modules that use 3rd party APIs',
      'autoload' => false,
      'singular' => false,
      'icon' => 'code',
    ];
  }

  /**
   * You can provide relative urls relative to the base url
   * Usage: $api->get('/ping');
   */
  public function get($url): Response
  {
    $url = $this->url($url);
    $response = $this->response($this->http()->get($url));
    $response->method = 'GET';
    $response->url = $url;
    return $response;
  }

  public function http(): WireHttp
  {
    if ($this->http) return $this->http;
    /** @var WireHttp $http */
    $http = $this->wire(new WireHttp());
    $http->setHeader('Content-Type', 'application/json');
    $http->setHeader('Accept', 'application/json');
    return $this->http = $http;
  }


  public function post($url, $data): Response
  {
    if (!is_string($data)) $data = json_encode($data);
    return $this->response($this->http()->post($this->url($url), $data));
  }

  /**
   * Return full url for sending request
   *
   * This takes care of adding/removing slashes between parts of the url.
   */
  public function url($url): string
  {
    if (strpos($url, "http") === 0) return $url;
    return rtrim($this->url, "/") . "/" . ltrim($url, "/");
  }

  /**
   * Convert object into a WireData object
   */
  public function response($object): Response
  {
    require_once __DIR__ . "/Response.php";
    if (is_string($object)) $object = json_decode($object);
    $response = $this->wire(new Response($this->http()));
    $response->status = $this->http()->getHttpCode();
    $response->result = $object;
    return $response;
  }

  public function __debugInfo()
  {
    return [
      'url' => $this->url,
    ];
  }
}
