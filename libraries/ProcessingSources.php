<?php
/*******************************************************
 * CampFireManager
 * Abstracted functions for Processing Sources
 * Version 0.5 2010-03-19 JonTheNiceGuy
 *******************************************************
 * Version History
 * 0.5 2010-03-19 Migrated from personal SVN store
 * at http://spriggs.org.uk/jon_code/CampFireManager
 * where all prior versions are stored to Google Code at
 * http://code.google.com/p/campfiremanager/
 ******************************************************/

require_once($base_dir . "GenericBaseClass.php");

abstract class ProcessingSources extends GenericBaseClass {
  abstract function getMessages();
  abstract function sendMessages($message='', $service='', $destination='');

  function getStatus() {
    $this->doDebug("getStatus()\r\n");
    return array();
  }
}

?>
