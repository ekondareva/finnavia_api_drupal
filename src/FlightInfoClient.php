<?php

namespace Drupal\finnavia_flight_info;

use DateTime;
use Drupal\Core\Site\Settings;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

class FlightInfoClient
{
  use StringTranslationTrait;

  const FINNAVIA_URL = 'https://api.finavia.fi/flights/public/v0/flights';

  /**
   * The API client object.
   *
   * @var \GuzzleHttp\Client
   */
  protected $client;

  /**
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  protected $lang;

  protected $api_id;
  protected $api_key;
  protected $headers;

  /**
   * {@inheritdoc}
   */
  public function __construct(LoggerChannelFactoryInterface $logger_factory)
  {
    $this->api_id = Settings::get('finnavia.api_id', '');
    $this->api_key = Settings::get('finnavia.api_key', '');
    $this->loggerFactory = $logger_factory;
    $this->logger = $logger_factory->get('finnavia_flight_info');

    $this->headers = [
      'Accept' => 'application/xml',
      'app_id' => $this->api_id,
      'app_key' => $this->api_key,
    ];

    $this->client = new Client();
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
  {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.factory'),
    );
  }

  /*
   * Fetches flights information from Finnavia api.
   * @param $type 'all', 'arr' for arrivals, 'dep' for departures.
   * @param $airport string, airport code.
   */
  public function fetchData($type='all', $airport='all')
  {
    $cid = 'finnavia_' . $airport . '_' . $type;
    if ($cache = \Drupal::cache()->get($cid)) {
      $output = $cache->data;
    } else {
      $output = $this->getFlightInfo($type, $airport);
      // Cache results for an hour.
      \Drupal::cache()->set($cid, $output, \Drupal::time()->getRequestTime() + 3600);
    }
    return $output;
  }


  /**
   * @param $type 'all', 'arr' for arrivals, 'dep' for departures.
   * @param $airport string, airport code.
   * @return false|string|null
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  private function getFlightInfo($type, $airport)
  {
//    Example:
//    curl -X GET --header 'Accept: application/xml'
// --header 'app_id: API_ID' --header 'app_key: API_KEY'
// 'https://api.finavia.fi/flights/public/v0/flights/all/RVN'

    $request_url = sprintf('%s/%s/%s', self::FINNAVIA_URL, $type, $airport);
    // Do the request.
    try {
      $request = $this->client->request('GET', $request_url, [
        'headers' => $this->headers,
      ]);
      $response = $request->getBody();
      $xmlResponse = simplexml_load_string($response);

      // Process results.
      $flights = [];
      // Process arrival flights.
      foreach($xmlResponse->arr->body->flight as $flight) {
        $date = new DateTime($flight->sdt[0]);
        $flightObject = [
          "airport" => (string)$flight->h_apt[0],
          "fltnr" => (string)$flight->fltnr[0],
          "sdt" => $date->format('Y-m-d H:i'),
          "acreg" => (string)$flight->acreg[0],
          "actype" => (string)$flight->actype[0],
          "gate" => (string)$flight->gate[0],
          "prm" => (string)$flight->prm[0],
          "prt" => (string)$flight->prt[0],
          "estD" => (string)$flight->est_d[0],
          "callsign" => (string)$flight->callsign[0],
          "route" => (string)$flight->route_1[0],
          "routeName" => (string)$flight->route_n_1[0]
        ];
        $flights[] = $flightObject;
      }
      // Process departure flights.
      foreach($xmlResponse->dep->body->flight as $flight) {
        $date = new DateTime($flight->sdt[0]);
        $flightObject = [
          "airport" => (string)$flight->h_apt[0],
          "fltnr" => (string)$flight->fltnr[0],
          "sdt" => $date->format('Y-m-d H:i'),
          "acreg" => (string)$flight->acreg[0],
          "actype" => (string)$flight->actype[0],
          "gate" => (string)$flight->gate[0],
          "prm" => (string)$flight->prm[0],
          "prt" => (string)$flight->prt[0],
          "estD" => (string)$flight->est_d[0],
          "callsign" => (string)$flight->callsign[0],
          "route" => (string)$flight->route_1[0],
          "routeName" => (string)$flight->route_n_1[0]
        ];
        $flights[] = $flightObject;
      }

      $response = $flights;
    } catch (RequestException $e) {
      $this->logger->error($e->getMessage());
      return NULL;
    }

    return json_encode($response);
  }
}
