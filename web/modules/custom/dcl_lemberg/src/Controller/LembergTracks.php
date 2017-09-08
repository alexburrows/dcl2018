<?php

/**
 * @file
 * Contains \Drupal\example\Controller\LembergTracks.
 */
namespace Drupal\dcl_lemberg\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

class LembergTracks extends ControllerBase {
  public function getTracks() {
    // Start json array
  $json_array['tracks'] = array();

  $types = dcl_lemberg_get_tracks_query();

  foreach ($types as $key => $type) {
    $types_array[] = array (
      'trackID' => $type->tid,
      'trackName' => $type->name,
      );
  }

  $json_array['tracks'] = $types_array;

  return new JsonResponse($json_array);
  }
}

/**
 * Custom function to get all tracks.
 */
function dcl_lemberg_get_tracks_query(){

  $query = db_select('taxonomy_term_field_data', 'td');
  $query->fields('td', array('tid', 'name'));
  $query->condition('td.vid', 'track','=');

  $result = $query->execute()->fetchAll();

  if ($result) {
     return $result;
  }
  return array();

}
