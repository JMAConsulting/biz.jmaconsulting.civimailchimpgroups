<?php

require_once 'civimailchimpgroups.civix.php';

/**
 * Implementation of hook_civicrm_config
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function civimailchimpgroups_civicrm_config(&$config) {
  _civimailchimpgroups_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function civimailchimpgroups_civicrm_xmlMenu(&$files) {
  _civimailchimpgroups_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function civimailchimpgroups_civicrm_install() {
  _civimailchimpgroups_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function civimailchimpgroups_civicrm_uninstall() {
  _civimailchimpgroups_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function civimailchimpgroups_civicrm_enable() {
  _civimailchimpgroups_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function civimailchimpgroups_civicrm_disable() {
  _civimailchimpgroups_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function civimailchimpgroups_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _civimailchimpgroups_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function civimailchimpgroups_civicrm_managed(&$entities) {
  _civimailchimpgroups_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_caseTypes
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function civimailchimpgroups_civicrm_caseTypes(&$caseTypes) {
  _civimailchimpgroups_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function civimailchimpgroups_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _civimailchimpgroups_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implementation of hook_civicrm_check
 */
function civimailchimpgroups_civicrm_check(&$messages) {
  $cmcMessages = cmc_check_dependencies(TRUE);

  foreach ($cmcMessages as &$message) {
    $message->setLevel(5);
  }

  $messages += $cmcMessages;
}

/**
 * Checks all dependencies for the extension
 *
 * @returns array  Array with one CRM_Utils_Check_Message object for each unmet dependency
 */
function cmc_check_dependencies($display = TRUE) {
  $messages = array();

  $enabled = checkRelatedExtensions('uk.co.vedaconsulting.mailchimp');
  if (!$enabled) {
    $messages[] = new CRM_Utils_Check_Message(
      'cmc_mailchimp',
        ts('This extension requires the CiviCRM Mailchimp extension to be downloaded and installed.'),
        ts('CiviCRM Mailchimp groups requirements not met')
    );
    // Now display a nice alert for all these messages
    if ($display) {
      foreach ($messages as $message) {
        CRM_Core_Session::setStatus($message->getMessage(), $message->getTitle(), 'error');
      }
    }
  }
  return $messages;
}

/**
 * Function to check if related extension is enabled/disabled
 *
 * return array of enabled extensions 
 */
function checkRelatedExtensions($name) {
  $enableDisable = NULL;
  $sql = "SELECT is_active FROM civicrm_extension WHERE full_name = '{$name}'";
  $enableDisable = CRM_Core_DAO::singleValueQuery($sql);
  return $enableDisable;
}