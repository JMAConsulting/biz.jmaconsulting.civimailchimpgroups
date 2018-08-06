<?php
/**
 * @file
 * This provides the Group Sync into CiviCRM from Mailchimp.
 */

class CRM_CMC_Form_Sync extends CRM_Core_Form {

  const QUEUE_NAME = 'cmc-pull';
  const END_URL    = 'civicrm/cmc/pull';
  const END_PARAMS = 'state=done';
  /**
   * Function to pre processing
   *
   * @return None
   * @access public
   */
  function preProcess() {
    $enabled = cmccheckRelatedExtensions('uk.co.vedaconsulting.mailchimp');
    if (!$enabled) {
      CRM_Core_Error::fatal(ts('The CiviCRM Mailchimp extension is not enabled/installed. Please enable/install the extension to view this page.'));
    }
    parent::preProcess();
  }

  /**
   * Function to actually build the form
   *
   * @return None
   * @access public
   */
  public function buildQuickForm() {
    $fields = [
      'group' => ts('Group(s)'),
      'unsub' => ts('Unsubscribes'),
      'clean' => ts('On Holds'),
      'optin' => ts('Opt Ins'),
    ];
    foreach ($fields as $field => $title) {
      $this->addElement('checkbox', $field, $title);
    }
    $this->assign('importFields', array_keys($fields));
    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => ts('Import'),
        'isDefault' => TRUE,
      ),
      array(
        'type' => 'cancel',
        'name' => ts('Cancel'),
      ),
    ));
  }

  /**
   * Function to process the form
   *
   * @access public
   *
   * @return None
   */
  public function postProcess() {
    $submitValues = $this->_submitValues;
    $runner = self::getRunner($submitValues);
    if ($runner) {
      // Run Everything in the Queue via the Web.
      $runner->runAllViaWeb();
    } else {
      CRM_Core_Session::setStatus(ts('Nothing to pull. Make sure you have selected required option to pull from Mailchimp.'));
    }
  }

  /**
   * Set up the queue.
   */
  public static function getRunner($submitValues) {
    $syncProcess = array(
      'group' => 'syncMailChimpGroup',
      'unsub' => 'syncMailChimpUnsub',
      'clean' => 'syncMailChimpClean',
      'optin' => 'syncMailChimpOptIn',
    );
    // Setup the Queue
    $queue = CRM_Queue_Service::singleton()->create(array(
      'name'  => self::QUEUE_NAME,
      'type'  => 'Sql',
      'reset' => TRUE,
    ));
    foreach ($syncProcess as $key => $value) {
      if (!empty($submitValues[$key])) {
        $task  = new CRM_Queue_Task(
          ['CRM_CMC_Form_Sync', $value],
          [$key],
          "Import {$key} from MailChimp."
        );
        $queue->createItem($task);
      }
    }
    // Setup the Runner
    $runnerParams = array(
      'title' => ts('Mailchimp Pull Sync: update CiviCRM from Mailchimp'),
      'queue' => $queue,
      'errorMode'=> CRM_Queue_Runner::ERROR_ABORT,
      'onEndUrl' => CRM_Utils_System::url(self::END_URL, self::END_PARAMS, TRUE, NULL, FALSE),
    );

    $runner = new CRM_Queue_Runner($runnerParams);
    return $runner;
  }

  public static function syncMailChimpGroup(CRM_Queue_TaskContext $ctx) {
    CRM_CMC_BAO_CMC::syncGroup();
    return CRM_Queue_Task::TASK_SUCCESS;
  }

  public static function syncMailChimpUnsub(CRM_Queue_TaskContext $ctx) {
    CRM_CMC_BAO_CMC::syncActivities('unsub');
    return CRM_Queue_Task::TASK_SUCCESS;
  }

  public static function syncMailChimpClean(CRM_Queue_TaskContext $ctx) {
    CRM_CMC_BAO_CMC::syncActivities('clean');
    return CRM_Queue_Task::TASK_SUCCESS;
  }

  public static function syncMailChimpOptIn(CRM_Queue_TaskContext $ctx) {
    CRM_CMC_BAO_CMC::syncActivities('optin');
    return CRM_Queue_Task::TASK_SUCCESS;
  }

}
