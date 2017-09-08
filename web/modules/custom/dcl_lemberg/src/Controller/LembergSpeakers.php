<?php

/**
 * @file
 * Contains \Drupal\dcl_lamberg\Controller\LembergSpeakers.
 */
namespace Drupal\dcl_lemberg\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

class LembergSpeakers extends ControllerBase {
  public function getSpeakers() {
    $updated = NULL;

    $params = \Drupal::request()->query->all();

    if (isset($params['since'])) {
      $updated = $params['since'];
    }

    // Start json array
    $json_array['speakers'] = array();

    // Get the speakers.
    $speaker_users = dcl_lemberg_get_speakers_query($updated);

    $sessions_array = array();

    foreach($speaker_users as $speaker) {
      $speakers_array[] = array(
        'speakerId' => $speaker->uid,
        'first_name' => $speaker->name,
        'avatarImageURL' => file_create_url($speaker->uri),
        'organizationName' => $speaker->field_attendee_company_value,
      );
    }

    $json_array['speakers'] = $speakers_array;

    return new JsonResponse($json_array);
  }
}


/**
 * Custom function to get all speakers on a schedule.
 */
function dcl_lemberg_get_speakers_query($updated){

  $query = db_select('users_field_data', 'u');
  $query->distinct('u.uid');
  $query->join('user__field_attendee_company', 'com', 'com.entity_id=u.uid');
  $query->join('node_field_data', 'n', 'u.uid=n.uid');
  $query->join('user__user_picture', 'pic', 'u.uid=pic.entity_id');
  $query->join('file_managed', 'file', 'file.fid=pic.user_picture_target_id');
  $query->join('node__field_session_status', 'ss', 'ss.entity_id=n.nid');

  $query->fields('u', array('uid', 'name'))
  ->fields('com', array('field_attendee_company_value'))
  ->fields('file', array('uri'));

  $query->condition('ss.field_session_status_value', 2,'=');

  if ($updated) {
    $query->condition('n.changed', $updated,'>');
  }

  $result = $query->execute()->fetchAll();

  if ($result) {
     return $result;
  }
  return array();

}
