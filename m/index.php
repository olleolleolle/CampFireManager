<?php 
/*******************************************************
 * CampFireManager
 * Public facing Mobile web page
 * Version 0.5 2010-03-19 JonTheNiceGuy
 *******************************************************
 * Version History
 * 0.5 2010-03-19 Migrated from personal SVN store
 * at http://spriggs.org.uk/jon_code/CampFireManager
 * where all prior versions are stored to Google Code at
 * http://code.google.com/p/campfiremanager/
 ******************************************************/

if(session_id()==='') {session_start();}
if(isset($_SESSION['redirect'])) {unset($_SESSION['redirect']);}
if(!isset($_SESSION['openid']) and isset($_GET['login'])) {
  $_SESSION['redirect']='m';
  header("Location: ..");
}
$base_dir="../libraries/";
require_once("../db.php");
require_once("{$base_dir}CampUtils.php");
?><html>
<head>
<title>
<?php echo $Camp_DB->config['event_title']; ?>
</title>
</head>
<body>
<?
if(isset($_SESSION['openid'])) {$Camp_DB->getMe(array('OpenID'=>$_SESSION['openid'], 'OpenID_Name'=>CampUtils::arrayGet($_SESSION, 'name', ''), 'OpenID_Mail'=>CampUtils::arrayGet($_SESSION, 'email', '')));}
switch(CampUtils::arrayGet($_REQUEST, 'state', '')) {
  case "Id":
    $data=array();
    $data[]=$Camp_DB->escape(stripslashes($_REQUEST['name']));
    foreach($Camp_DB->contact_fields as $proto) {
      if(isset($_REQUEST[$proto]) and $_REQUEST[$proto]!='') {
        $data[]=$proto . ":" . $Camp_DB->escape(stripslashes($_REQUEST[$proto]));
      }
    }
    $Camp_DB->updateIdentityInfo($data);
    break;
  case "Pr":
    $Camp_DB->insertTalk(array($_REQUEST['slot'], $_REQUEST['length'], $_REQUEST['title']), 0);
    break;
  case "Ca":
    $Camp_DB->cancelTalk(array($_REQUEST['talkid'], $Camp_DB->arrTalks[$_REQUEST['talkid']]['intTimeID'], $Camp_DB->escape(htmlentities($_REQUEST['reason']))));
    break;
  case "Ed":
    $Camp_DB->editTalk(array($_REQUEST['talkid'], $Camp_DB->arrTalks[$_REQUEST['talkid']]['intTimeID'], $Camp_DB->escape(htmlentities($_REQUEST['ntitle']) . ' ')));
    break;
}
?>
<h1><?php echo $Camp_DB->config['event_title']; ?></h1>
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
if($now_talks!='' and $next_talks!='') {echo '<h2>On Now and Next</h2>';}
if($now_talks!='') {echo "<h3>Talks on now: (started at " . $Camp_DB->arrTimeEndPoints[$now]['s'] . "):</h3>$now_talks";}
if($next_talks!='') {echo "<h3>Talks on next (starts at " . $Camp_DB->arrTimeEndPoints[$next]['s'] . "):</h3>$next_talks";}
if(!isset($_SESSION['openid'])) {
  echo "<a href=\"?login\">Login to CampFireManager</a>";
} else {
  if(isset($_GET['list']) or isset($_GET['my'])) {
    if(isset($_GET['list'])) {echo "<h2>List talks yet to start</h2>";} else {echo "<h2>List my talks yet to start</h2>";}
    $person=$Camp_DB->allMyDetails();
    foreach($Camp_DB->times as $intTimeID=>$arrTime) {
      if($intTimeID>$now) {
        foreach($Camp_DB->arrTalkSlots[$intTimeID] as $intRoomID=>$intTalkID) {
          if(!isset($showtalk[$intTalkID]) and $intTalkID>0) {
            if(!isset($_GET['my']) or $Camp_DB->arrTalks[$intTalkID]['intPersonID']==$person['intPersonID']) {
              echo "Talk $intTalkID: " . $Camp_DB->arrTalks[$intTalkID]['strTalkTitle'];
              if($Camp_DB->arrTalks[$intTalkID]['intPersonID']==$person['intPersonID']) {
                echo " <a href=\"$baseurl?edit=$intTalkID\">Edit</a> | <a href=\"$baseurl?delete=$intTalkID\">Delete</a>";
              } else {
                echo " by " . $Camp_DB->arrTalks[$intTalkID]['strPresenter'];
              }
              echo "<br />";
            }
            $showtalk[$intTalkID]=TRUE;
          }
        }
      }
    }
  } elseif(isset($_GET['edit']) and $_GET['edit']>0) {
    $intTalkID=0+$_GET['edit'];
    echo "<h2>Edit my talk title</h2>";
    echo "You are about to retitle the talk \"{$Camp_DB->arrTalks[$intTalkID]['strTalkTitle']}\" starting at {$Camp_DB->arrTimeEndPoints[$Camp_DB->arrTalks[$intTalkID]['intTimeID']]['s']}.<br />";
    echo "<form method=\"post\" action=\"$baseurl\" class=\"DrawAttention\"><a href=\"$baseurl\">Click here to cancel</a> or set the new title to:<input type=\"text\" name=\"ntitle\" /><input type=\"submit\" value=\"Go\" /><input type=\"hidden\" name=\"state\" value=\"Ed\" /><input type=\"hidden\" name=\"talkid\" value=\"$intTalkID\" /></form>";
  } elseif(isset($_GET['delete']) and $_GET['delete']>0) {
    $intTalkID=0+$_GET['delete'];
    echo "<h2>Delete my talk</h2>";
    echo "You are about to delete the talk \"{$Camp_DB->arrTalks[$intTalkID]['strTalkTitle']}\" starting at {$Camp_DB->arrTimeEndPoints[$Camp_DB->arrTalks[$intTalkID]['intTimeID']]['s']}.<br />";
    echo "<form method=\"post\" action=\"$baseurl\" class=\"DrawAttention\"><a href=\"$baseurl\">Click here to cancel</a> or click <input type=\"submit\" value=\"here to delete the talk\" /><input type=\"hidden\" name=\"state\" value=\"Ca\" /><input type=\"hidden\" name=\"talkid\" value=\"$intTalkID\" />. You can optionally provide a reason here: <input type=\"text\" name=\"reason\" /></form>";
  } elseif(isset($_GET['propose'])) {
    echo "<h2>Propose a talk</h2>";
      echo "\r\n<form method=\"post\" action=\"$baseurl\" class=\"DrawAttention\">\r\n<input type=\"hidden\" name=\"state\" value=\"Pr\">Starting talk at <select name=\"slot\">";
      foreach($Camp_DB->times as $intTimeID=>$arrTime) {if($intTimeID>$now) {echo "<option value=\"$intTimeID\">" . $Camp_DB->arrTimeEndPoints[$intTimeID]['s'] . "</option>";}}
      echo "</select> and for <select name=\"length\">";
      for($l=1; $l<=count($Camp_DB->times)-$now; $l++) {echo "<option value=\"$l\">$l</option>";}
      echo "</select> slots about: <input type=\"text\" name=\"title\" /><input type=\"submit\" value=\"Go\" />";
  } elseif(isset($_GET['contact'])) {
    echo "<h2>Change my contact details</h2>";
    $details=$Camp_DB->getContactDetails(0, TRUE);
    echo "<form method=\"post\" action=\"$baseurl\" class=\"DrawAttention\"><input type=\"hidden\" name=\"state\" value=\"Id\" />" .
         "<div class=\"data_Name\"><span class=\"Label\">Name:</span> <span=\"Data\"><input type=\"text\" name=\"name\" value=\"{$details['strName']}\" /></span></div>";
    foreach($Camp_DB->contact_fields as $proto) {
      echo "<div class=\"data_$proto\"><span class=\"Label\">$proto:</span><span=\"Data\"><input type=\"text\" name=\"$proto\" value=\"{$details[$proto]}\" /></span></div>";
    }
    echo "<input type=\"submit\" value=\"Go\" />";
  }
  echo "<h2>Options</h2>";
  echo "<a href=\"$baseurl?propose\">Propose a talk</a><br />"; 
  echo "<a href=\"$baseurl?list\">List talks which are yet to start</a><br />"; 
  echo "<a href=\"$baseurl?my\">List my talks which are yet to start</a><br />"; 
  echo "<a href=\"$baseurl?contact\">Change my contact details</a><br />";
}
?>
</body>
</html>
