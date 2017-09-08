<?php

/**
 * @file
 * Contains \Drupal\example\Controller\LembergTypes.
 */
namespace Drupal\dcl_lemberg\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

class LembergTypes extends ControllerBase {
  public function getTypes() {
    // Start json array
  $json_array['types'] = array();

  $types = dcl_lember_get_types_array();

  foreach ($types as $key => $type) {
    $types_array[] = array (
      'typeID' => $key,
      'typeName' => $type['name'],
      );
  }

  $json_array['types'] = $types_array;

  return new JsonResponse($json_array);
  }
}

/**
 * Custom function to get all tracks.
 */
function dcl_lember_get_types_array(){

$array = array (
  1 => array (
    'name' => 'Speach',
    ),
  2 => array (
    'name' => 'Speach of day',
    ),
  3 => array (
    'name' => 'Coffee break',
    ),
  4 => array (
    'name' => 'Lunch',
    ),
  5 => array (
    'name' => '24h',
    ),
  6 => array (
    'name' => 'Group',
    ),
  7 => array (
    'name' => 'Walking',
    ),
  8 => array (
    'name' => 'Registration',
    ),
  9 => array (
    'name' => 'Free slot',
    ),
  );

  return $array;

}
