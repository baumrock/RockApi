<?php

namespace ProcessWire;

use RockApi\Response;

/**
 * @author Bernhard Baumrock, 07.12.2022
 * @license Licensed under MIT
 * @link https://www.baumrock.com
 */
class RockApi extends WireData implements Module, ConfigurableModule
{
  public $url = "";
  private $http;
  private $headers = [];

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

  /** HTTP methods */

  /**
   * You can provide urls relative to the base url
   *
   * The get() method is a magic method for PW so your $url must start
   * with either http or with / to indicate that it is an api request
   *
   * Usage:
   * $api->get('/ping');
   * $api->get('https://...');
   */
  public function get($url): mixed
  {
    $apiCall = false;
    if (str_starts_with($url, "/")) $apiCall = true;
    elseif (str_starts_with($url, "http://")) $apiCall = true;
    elseif (str_starts_with($url, "https://")) $apiCall = true;
    if (!$apiCall) return parent::get($url);

    $url = $this->url($url);
    $http = $this->http();
    // this prevents the following error
    // Raw data option with CURL not supported for GET
    $http->setData(null);
    $response = $this->response($http->get($url));
    $response->method = 'GET';
    $response->url = $url;
    return $response;
  }

  public function post($url, $data): Response
  {
    if (!is_string($data)) $data = json_encode($data);
    $url = $this->url($url);
    $response = $this->response($this->http()->post($url, $data));
    $response->url = $url;
    $response->method = 'POST';
    return $response;
  }

  public function put($url, $data): Response
  {
    $url = $this->url($url);
    $data = json_encode($data);
    $response = $this->response(
      $this->http()->send($url, $data, 'PUT')
    );
    $response->url = $url;
    $response->method = 'PUT';
    return $response;
  }

  public function delete($url): Response
  {
    $url = $this->url($url);
    $response = $this->response($this->http(false)->send($url, [], 'DELETE'));
    $response->url = $url;
    $response->method = 'DELETE';
    return $response;
  }

  /** END HTTP methods */

  public function http($json = true): WireHttp
  {
    if ($this->http) return $this->http;
    /** @var WireHttp $http */
    $http = $this->wire(new WireHttp());
    if ($json) {
      $http->setHeader('Content-Type', 'application/json');
      $http->setHeader('Accept', 'application/json');
    }
    foreach ($this->headers as $k => $v) $http->setHeader($k, $v);
    return $this->http = $http;
  }

  /**
   * Return full url for sending request
   *
   * This takes care of adding/removing slashes between parts of the url.
   */
  public function url($url): string
  {
    // if we dont add anything to the base url we return the base url as it is
    // this prevents it from adding a trailing slash where we might not want it
    // support for example setUrl(base.com/foo) and then ->post("", "data")
    if (!$url) return $this->url;

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

  public function setHeaders(array $headers): void
  {
    $this->headers = $headers;
  }

  public function setUrl($url)
  {
    $this->url = rtrim($url, "/");
  }

  /**
   * Config inputfields
   * @param InputfieldWrapper $inputfields
   */
  public function getModuleConfigInputfields($inputfields)
  {
    $name = strtolower($this);
    $inputfields->add([
      'type' => 'markup',
      'label' => 'Documentation & Updates',
      'icon' => 'life-ring',
      'value' => "<p>Hey there, coding rockstars! 👋</p>
        <ul>
          <li><a class=uk-text-bold href=https://www.baumrock.com/modules/$name/docs>Read the docs</a> and level up your coding game! 🚀💻😎</li>
          <li><a class=uk-text-bold href=https://www.baumrock.com/rock-monthly>Sign up now for our monthly newsletter</a> and receive the latest updates and exclusive offers right to your inbox! 🚀💻📫</li>
          <li><a class=uk-text-bold href=https://github.com/baumrock/$name>Show some love by starring the project</a> and keep me motivated to build more awesome stuff for you! 🌟💻😊</li>
          <li><a class=uk-text-bold href=https://paypal.me/baumrockcom>Support my work with a donation</a>, and together, we'll keep rocking the coding world! 💖💻💰</li>
        </ul>",
    ]);
    return $inputfields;
  }

  public function __debugInfo()
  {
    return [
      'url' => $this->url,
    ];
  }
}
