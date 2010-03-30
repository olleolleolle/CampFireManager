<?php 
session_start();
if(isset($_SESSION['redirect'])) {unset($_SESSION['redirect']);}
if(!isset($_SESSION['openid']) and isset($_GET['login'])) {
  $_SESSION['redirect']='m';
  header("Location: ..");
}
$base_dir="../libraries/";
require_once("../db.php"); 
?>
<html>
<head>
<title>
<?php echo $Camp_DB->config['event_title']; ?>
</title>
</head>
<body>
<?php
// Find the "Now" and "Next" time blocks
$now_and_next=$Camp_DB->getNowAndNextTime();
$now=$now_and_next['now'];
$next=$now_and_next['next'];

$is_lunch=0;
$now_talks='';
if($now>0) {
  foreach($Camp_DB->arrTalkSlots[$now] as $intRoomID=>$intTalkID) {
    if($intTalkID==-1) {
      $is_lunch=1;
    } elseif($intTalkID==0) {
    } elseif(isset($Camp_DB->arrTalks[$intTalkID]['boolIsFixed'])) {
      $now_talks.=$Camp_DB->rooms[$intRoomID]["strRoom"] . ' - ' . $Camp_DB->arrTalks[$intTalkID]['strTalkTitle'] . '<br />';
    } else {
      $now_talks.=$Camp_DB->arrTalks[$intTalkID]['strTalkTitle'] . '<br />';
    }
  }
}
if($is_lunch==1) {$now_talks="Lunch<br />" . $now_talks;}

$is_lunch=0;
$next_talks='';
if($next>0) {
  foreach($Camp_DB->arrTalkSlots[$next] as $intRoomID=>$intTalkID) {
    if($intTalkID==-1) {
      $is_lunch=1;
    } elseif($intTalkID==0) {
    } elseif(isset($Camp_DB->arrTalks[$intTalkID]['boolIsFixed'])) {
      $next_talks.=$Camp_DB->rooms[$intRoomID]["strRoom"] . ' - ' . $Camp_DB->arrTalks[$intTalkID]['strTalkTitle'] . '<br />';
    } else {
      $next_talks.=$Camp_DB->arrTalks[$intTalkID]['strTalkTitle'] . '<br />';
    }
  }
}
if($is_lunch==1) {$next_talks="Lunch<br />" . $next_talks;}

if($now_talks!='') {echo "Talks on now: (started at " . $Camp_DB->arrTimeEndPoints[$now]['s'] . "):<br />$now_talks";}
if($next_talks!='') {echo "Talks on next (starts at " . $Camp_DB->arrTimeEndPoints[$next]['s'] . "):<br />$next_talks";}
if(!isset($_SESSION['openid'])) {
  echo "<a href=\"?login\">Login to CampFireManager</a>";
} else {
  $Camp_DB->getMe(array('OpenID'=>$_SESSION['openid'], 'OpenID_Name'=>$_SESSION['name'], 'OpenID_Mail'=>$_SESSION['email']));
  echo "<a href=\"$baseurl?list\">List all unfinished talks</a><br />"; 
  echo "<a href=\"$baseurl?propose\">Propose a talk</a><br />"; 
  echo "<a href=\"$baseurl?my\">List all my unfinished talks</a><br />"; 
  echo "<a href=\"$baseurl?contact\">Change my contact details</a><br />";
  if(isset($_GET['list'])) {

  } elseif(isset($_GET['my'])) {

  } elseif(isset($_GET['propose'])) {

  } elseif(isset($_GET['contact'])) {

  }
}
