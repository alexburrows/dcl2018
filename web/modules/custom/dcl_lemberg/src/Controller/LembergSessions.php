<?php

/**
 * @file
 * Contains \Drupal\dcl_lamberg\Controller\LembergSessions.
 */
namespace Drupal\dcl_lemberg\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Url;

class LembergSessions extends ControllerBase {

  /**
   * Build sessions data.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function getSessions() {
    $updated = NULL;

    $params = \Drupal::request()->query->all();

    if (isset($params['since'])) {
      $updated = $params['since'];
    }

    // Start json array
    $json_array['days'] = array();

    // Get schedules.
  $schedule_items = dcl_lemberg_get_schedules();


foreach ($schedule_items as $day => $date) {

  // Get the sessions.
  $sessions_nodes = dcl_lemberg_get_sessions_from_schedule($day, $updated);

  $sessions_array = array();

  foreach ($sessions_nodes as $session) {

    // Set to/from time.
    $time = dcl_lemberg_format_session_time($date, $session->field_schedule_item_time->target_id);
   //   var_dump($time);
     // Default values;
     $place = NULL;
     $exp = NULL;
     $track = NULL;
     $speaker = NULL;

      if (preg_match("/Lunch/", $session->title->value)) {
        $type = 4;
      }
      elseif (preg_match("/Coffee/", $session->title->value)) {
        $type = 3;
      }
      elseif (preg_match("/Keynote/", $session->title->value)) {
        $type = 2;
        $place = 'Main hall';
      }
      elseif (preg_match("/Registration/", $session->title->value)) {
        $type = 8;
      }
      elseif (preg_match("/Free slot/", $session->title->value)) {
        $type = 9;
      }
      else {
        $type = 1;
      }

      $options = ['absolute' => TRUE];
      $url = \Drupal\Core\Url::fromRoute('entity.node.canonical', ['node' => $session->nid->value], $options);
      $url = $url->toString();

    switch ($session->field_session_skill_level->target_id) {
      case 7: // Beginner.
        $level_id = 1;
        break;
      case 8: // Intermediate.
        $level_id = 2;
        break;
      case 9: // Expert.
        $level_id = 3;
        break;
    }


    if ($type === 1) {

      $room_id = isset($session->field_schedule_item_room->target_id) ? $session->field_schedule_item_room->target_id : '';
      if (empty($room_id)) {
        unset($room_id);
      }

      $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($session->field_schedule_item_room->target_id);
      $place = $term->name->value;

     // $place = \Drupal\taxonomy\Entity\Term::load($session->field_schedule_item_room->target_id)->get('name')->value;
      $exp = $level_id;
      $track = $session->field_session_track->target_id;
      $speaker = $session->getOwnerId();
    }

    $sessions_array[] = array(
      'eventId' => $session->nid->value,
      'name' => $session->title->value,
      'type' => $type,
      'text' => $session->field_session_content->value,
      'place' => $place,
      'experienceLevel' => $exp,
      'from' => $time['from'],
      'to' => $time['to'],
      'speakers' => $speaker,
      'track' => $track,
      'link' => $url,
      );
  }
  $json_array['days'][] = array('date' => dcl_lemberg_format_schedule($date), 'events' => $sessions_array);
}

// Now get deleted/removed sessions.
$deleted_sessions = dcl_lemberg_get_declined_sessions($updated);

  foreach($deleted_sessions as $session) {
    // Create session type
    $type = 1;

    $deleted_sessions_array[] = array(
      'eventId' => $session->nid->value,
      );


    $json_array['deleted'] = $deleted_sessions_array;
  }



  return new JsonResponse($json_array);

  }
}

function dcl_lemberg_get_schedules($date){


  return array (
    'Saturday' => '04-03-2017',
    'Sunday' => '05-03-2017',
    );
}

/**
 * Custom function to format the time for schedule
 */
function dcl_lemberg_format_schedule($date){
 // return $date;
  $new_date = date('d-m-Y', strtotime($date));
  return $new_date;
}

/**
 * Custom function to format the time for sessions
 */
function dcl_lemberg_format_session_time($date, $time){
  try {
    //kint($time);
    $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($time);
    $name = $term->getName();
    //$name = Entity::load
    // Get the term name.
    //$name = \Drupal\taxonomy\Entity\Term::load($time)->get('name')->value;
   // $name = \Drupal\taxonomy\Entity\Term::loadMultiple($time)->get('name')->value;
   // kint($name);
    //var_dump($name);
    // Split it into two values.
    $time = explode(' - ', $name);
   // var_dump($time);
    // Format the time.
    $timestamp_from = strtotime($date . $time[0]);
    $timestamp_to = strtotime($date . $time[1]);
    // Return array of start and end time.
    $time_array = array(
      'from' => date('c',$timestamp_from),
      'to' => date('c', $timestamp_to),
    );
    return $time_array;
  }
  catch (Exception $e) {
    throw $e->getMessage();
  }
}

/**
 * Custom function to get all sessions on a schedule.
 */
function dcl_lemberg_get_sessions_from_schedule($day, $updated = NULL) {


  if ($day === 'Saturday') {
    $day_tid = 101;
  }
  else {
     $day_tid = 106;
  }

  // Huge mega query of death.
  // Get sessions and scheduled items by day (Saturday/Sunday).
  $query = \Drupal::entityQuery('node');
    $query->condition('status', 1);
    $query->condition('field_schedule_item_day', $day_tid);

    if ($updated) {
      $query->condition('changed', $updated,'>');
    }

    $entity_ids = $query->execute();

  if ($entity_ids) {

    $sessions = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadMultiple($entity_ids);

    //$sessions = entity_load_multiple('node', $entity_ids);

     return $sessions;
  }
  return array();

}

function dcl_lemberg_get_declined_sessions($updated = NULL) {

  // Huge mega query of death.
  $bundle = 'session'; // or $bundle='my_bundle_type';
   $query = \Drupal::entityQuery('node');
    $query->condition('status', 1);
    $query->condition('type', $bundle);
    $query->condition('field_session_status', 1);

    if ($updated) {
      $query->condition('changed', $updated,'>');
    }

    $entity_ids = $query->execute();

  if ($entity_ids) {
    $sessions = entity_load_multiple('node', $entity_ids);

     return $sessions;
  }
  return array();

}
