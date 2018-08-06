<?php

class CRM_CMC_BAO_CMC extends CRM_Mailchimp_Sync {

  public static function getResponse() {
    $api = CRM_Mailchimp_Utils::getMailchimpApi();
    $batch_size = 1000;
      $response = $api->get("/lists", [
        'count' => $batch_size,
        'status' => 'subscribed',
        'fields' => 'total_items,lists.id,lists.name',
      ]);
      return $response->data->lists;
    return $fetch_batch;
  }

  public static function getActivities($op, $params) {
    $api = CRM_Mailchimp_Utils::getMailchimpApi();
    $batch_size = 1000;
    $hash = md5($params['email']);
    $list = $params['list'];
    if ($op != 'optin') {
      $response = $api->get("/lists/$list/members/$hash/activity", [
        'count' => $batch_size,
      ]);
      return [$response->data->list_id => $response->data->activity];
    }
    else {
      $response = $api->get("/lists/$list/members/$hash", [
        'count' => $batch_size,
        'fields' => 'timestamp_opt,list_id',
      ]);
      return [$response->data->list_id => $response->data->timestamp_opt];
    }
  }

  public static function syncGroup() {
    $response = self::getResponse();
    foreach ($response as $values) {
      $id = CRM_Core_DAO::singleValueQuery(
        "SELECT entity_id FROM civicrm_value_mailchimp_settings
         WHERE mc_list_id = '{$values->id}'"
      );
      $result = civicrm_api3('Group', 'create', [
        'title' => $values->name,
        'id' => $id,
        'source' => ts("Constant Contact"),
        'is_active' => TRUE,
        'group_type' => "Mailing List",
        'visibility' => ["User and User Admin Only"],
      ]);
      $sql = "INSERT INTO civicrm_value_mailchimp_settings (mc_list_id, entity_id)
        VALUES ('{$values->id}', {$result['id']})
        ON DUPLICATE KEY UPDATE entity_id = {$result['id']}
      ";
      CRM_Core_DAO::executeQuery($sql);
    }
  }

  public static function syncActivities($activity) {
    if ($activity == 'unsub') {
      $dao = CRM_Core_DAO::executeQuery("SELECT c.id as contact_id, e.email, m.mc_list_id, gc.group_id FROM civicrm_contact c
        INNER JOIN civicrm_email e ON e.contact_id = c.id
        INNER JOIN civicrm_group_contact gc ON gc.contact_id = c.id AND gc.status = 'Removed'
        INNER JOIN civicrm_value_mailchimp_settings m ON m.entity_id = gc.group_id");
    }
    if ($activity == 'clean') {
      $dao = CRM_Core_DAO::executeQuery("SELECT c.id as contact_id, e.email, m.mc_list_id FROM civicrm_contact c
        INNER JOIN civicrm_email e ON e.contact_id = c.id AND on_hold = 1
        INNER JOIN civicrm_group_contact gc ON gc.contact_id = c.id
        INNER JOIN civicrm_value_mailchimp_settings m ON m.entity_id = gc.group_id");
    }
    if ($activity == 'optin') {
      $dao = CRM_Core_DAO::executeQuery("SELECT c.id as contact_id, e.email, m.mc_list_id, gc.group_id FROM civicrm_contact c
        INNER JOIN civicrm_email e ON e.contact_id = c.id
        INNER JOIN civicrm_group_contact gc ON gc.contact_id = c.id AND gc.status = 'Added'
        INNER JOIN civicrm_value_mailchimp_settings m ON m.entity_id = gc.group_id");
    }
    while ($dao->fetch()) {
      $params = ['email' => $dao->email, 'list' => $dao->mc_list_id];
      $response = self::getActivities($activity, $params);
      if ($activity == 'optin') {
        if (empty($response[$dao->mc_list_id])) {
          $date = date('Y-m-d H:i:s');
        }
        else {
          $date = date('Y-m-d H:i:s', strtotime($response[$dao->mc_list_id]));
        }
        CRM_Core_DAO::executeQuery("UPDATE civicrm_subscription_history SET date = '{$date}'
          WHERE contact_id = {$dao->contact_id} AND group_id = {$dao->group_id}");
      }
      foreach ($response as $values) {
        foreach ($values as $value) {
          if ($activity == 'unsub' && $value->action == 'unsub') {
            $date = date('Y-m-d H:i:s', strtotime($value->timestamp));
            CRM_Core_DAO::executeQuery("UPDATE civicrm_subscription_history SET date = '{$date}'
              WHERE contact_id = {$dao->contact_id} AND group_id = {$dao->group_id}");
          }
          if ($activity == 'clean' && $value->action == 'bounce') {
            $date = date('Y-m-d H:i:s', strtotime($value->timestamp));
            CRM_Core_DAO::executeQuery("UPDATE civicrm_email SET hold_date = '{$date}'
              WHERE contact_id = {$dao->contact_id} AND email = {$dao->email}");
          }
        }
      }
    }
  }

}
