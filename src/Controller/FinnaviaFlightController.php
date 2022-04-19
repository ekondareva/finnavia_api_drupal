<?php

namespace Drupal\finnavia_flight_info\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Controller\ControllerBase;

class FinnaviaFlightController extends ControllerBase
{
  public function getFlightInfo($type, $airport)
  {
    $client = \Drupal::service('finnavia_flight_info.flightinfoclient');
    $flights = $client->fetchData($type, $airport);
    $flights = json_decode($flights);

    $output_html = '';
    if (count($flights) == 0) {
      $output_html = '<div>No flights</div>';
    }
    foreach ($flights as $flight) {
      $output_html .= '<div class="flight">';
      $airport = (string)$flight->airport;
      $fltnr = (string)$flight->fltnr;
      $route_name = (string)$flight->routeName;
      $sdt = (string)$flight->sdt;
      $gate = (string)$flight->gate;
      $prt = (string)$flight->prt;
      $output_html .= '<div class="flights__airport">'. $airport .'</div>';
      $output_html .= '<div class="flights__flight_number">'. $fltnr .'</div>';
      $output_html .= '<div class="flights__flight_route">'. $route_name .'</div>';
      $output_html .= '<div class="flights__date">'. $sdt .'</div>';
      $output_html .= '<div>'. $gate .'</div>';
      $output_html .= '<div>'. $prt .'</div>';
      $output_html .= '</div>';
    }

    $response = new AjaxResponse();

    $response->addCommand(
      new HtmlCommand(
        '.flights__box',
        $output_html));

    return $response;
  }

  public function renderFlightInfo() {
    $client = \Drupal::service('finnavia_flight_info.flightinfoclient');
    $flights = $client->fetchData();

    $flights_data = json_decode($flights);

    return [
      '#theme' => 'finnavia_flight_block',
      '#airport' => 'all',
      '#type' => 'all',
      '#flights' => $flights_data
    ];
  }
}
