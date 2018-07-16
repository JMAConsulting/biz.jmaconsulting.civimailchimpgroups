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

}
