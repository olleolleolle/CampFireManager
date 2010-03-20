<?php
/*******************************************************
 * CampFireManager
 * An xajax interface to various functions.
 * Version 0.5 2010-03-19 JonTheNiceGuy
 *******************************************************
 * Version History
 * 0.5 2010-03-19 Migrated from personal SVN store
 * at http://spriggs.org.uk/jon_code/CampFireManager
 * where all prior versions are stored to Google Code at
 * http://code.google.com/p/campfiremanager/
 ******************************************************/

// Connect to the databases
require_once("db.php");
// Added XAJAX
require_once("external/xajax_core/xajax.inc.php");
$xajax = new xajax();
$xajax->configure('javascript URI','external/');
$xajax->registerFunction("ajaxPopulateTable");
$xajax->registerFunction("ajaxGetDirections");

function ajaxGetDirections() {
  global $Camp_DB;

  $return=new xajaxResponse();
  $d=$Camp_DB->getDirectionTemplate();

  foreach(array("UL", "U", "UR", "L", "C", "R", "DL", "D", "DR") as $direction) {
    if($d[$direction]!='') {
      $return->assign("{$direction}-TEXT", "innerHTML", $d[$direction]);
      $return->assign("{$direction}-ARROW", "innerHTML", "<img src=\"images/arrow_{$direction}.png\">");
    } else {
      $return->assign("{$direction}-TEXT", "innerHTML", "&nbsp;");
      $return->assign("{$direction}-ARROW", "innerHTML", "&nbsp;");
    }
  }
  $return->assign("next_talk_time", "innerHTML", $Camp_DB->arrTimeEndPoints[$Camp_DB->next_time]['s'] . ":00");
  return($return);
}

function ajaxPopulateTable() {
  global $Camp_DB, $includeCountData, $includeProposeLink, $sms_limit, $baseurl;
  
  $return=new xajaxResponse();
  session_start();
  if(isset($_SESSION['openid'])) {
    $counts=TRUE;
    $propose=FALSE;
  } else {
    $counts=FALSE; 
    $propose=TRUE;
  }
  $return->assign("mainbody", "innerHTML", $Camp_DB->getTimetableTemplate($propose, $counts));
  $return->assign("sms_list", "innerHTML", $Camp_DB->getSmsTemplate($sms_limit));

  return $return;
}

$xajax->processRequest();
