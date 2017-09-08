<?php

/**
 * @file
 * Contains \Drupal\example\Controller\LembergLevels.
 */
namespace Drupal\dcl_lemberg\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

class LembergLevels extends ControllerBase {
  public function getLevels() {

   // Start json array
  $json_array['levels'] = array();

  // Get the speakers.
  $levels = dcl_lemberg_get_levels_query();

  $levels_array = array();

  foreach($levels as $level) {
    switch ($level->tid) {

      case 7: // Beginner.
        $id = 1;
        break;
      case 8: // Intermediate.
        $id = 2;
        break;
      case 9: // Expert.
        $id = 3;
        break;
    }

    $levels_array[] = array(
      'levelId' => $id,
      'levelName' => $level->name,
      );
  }

  $json_array['levels'] = $levels_array;

  return new JsonResponse($json_array);
  }
}

/**
 * Custom function to get all sessions on a schedule.
 */
function dcl_lemberg_get_levels_query(){

  $query = db_select('taxonomy_term_field_data', 'td');
  $query->fields('td', array('tid', 'name'));
  $query->condition('td.vid', 'skill_level','=');

  $result = $query->execute()->fetchAll();

  if ($result) {
     return $result;
  }
  return array();

}
