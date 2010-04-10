<?php
/*******************************************************
 * CampFireManager
 * Support Staff web page
 * Version 0.5 2010-03-19 JonTheNiceGuy
 *******************************************************
 * Version History
 * 0.5 2010-03-19 Migrated from personal SVN store
 * at http://spriggs.org.uk/jon_code/CampFireManager
 * where all prior versions are stored to Google Code at
 * http://code.google.com/p/campfiremanager/
 ******************************************************/

session_start();
if(isset($_SESSION['redirect'])) {unset($_SESSION['redirect']);}
$base_dir="../libraries/";
require_once("../db.php");
$contact_fields=array('mailto', 'twitter', 'linkedin', 'identica', 'statusnet', 'facebook', 'irc', 'http', 'https');
if(!isset($Camp_DB->config['adminkey'])) {$Camp_DB->generateNewAdminKey();}
if(!isset($Camp_DB->config['supportkey'])) {$Camp_DB->generateNewSupportKey();}
// You're only allowed here if you've already logged in
if(!isset($_SESSION['openid'])) {
  $_SESSION['redirect']='support';
  header("Location: ..");
} else {
  $Camp_DB->getMe(array('OpenID'=>$_SESSION['openid'], 'OpenID_Name'=>$_SESSION['name'], 'OpenID_Mail'=>$_SESSION['email']));
}
if($Camp_DB->getSupport()==0 AND $Camp_DB->getAdmins()==0) {
  header("Location: ../?state=Oa&AuthString={$Camp_DB->config['supportkey']}");
} elseif($Camp_DB->checkAdmin()==1 OR $Camp_DB->checkSupport()==1) {
  $Camp_DB->setDebug(2);
  echo "<html><head><title>{$Camp_DB->config['event_title']}</title><link rel=\"stylesheet\" type=\"text/css\" href=\"../common_style.php\" /></head><body>";
  echo "<table width=\"100%\"><tr><td><a href=\"..\" class=\"Label\">Back to main screen</a></td>";
  if(isset($_GET['logout'])) {unset($_SESSION['support_user']);}
  $arrMbs=$Camp_DB->getMicroBloggingAccounts();
  $arrPhones=$Camp_DB->getPhones();
  if(isset($_REQUEST['authcode']) and $_REQUEST['authcode']!='') {
    if(!$Camp_DB->getMe(array('strAuthString'=>$_REQUEST['authcode']))) {
      $status=array();
    } else {$Camp_DB->setSupportUser();}
  } elseif(isset($_SESSION['support_user'])) {
    $Camp_DB->getMe(array('intPersonID'=>$_SESSION['support_user']));
  } elseif(count($arrPhones)>0 AND isset($_POST['phonenumber']) AND $_POST['phonenumber']!='') {
    if(!$Camp_DB->getMe(array('number'=>$_POST['phonenumber']))) {
      $status=$Camp_DB->getPerson(array('strPhoneNumber'=>'%' . $_POST['phonenumber']));
    } else {$Camp_DB->setSupportUser();}
  } elseif(count($arrMbs)>0 AND isset($_POST['microblog']) AND $_POST['microblog']!='') {
    if(!$Camp_DB->getMe(array('microblog_account'=>$_POST['microblog']))) {
      $status=$Camp_DB->getPerson(array('strMicroBlog'=>'%' . $_POST['microblog']));
    } else {$Camp_DB->setSupportUser();}
  } elseif(isset($_POST['contact']) AND $_POST['contact']!='') {
    $status=$Camp_DB->getPerson(array('strContactInfo'=>'%' . $_POST['contact'] . '%'));
  } elseif(isset($_POST['name']) AND $_POST['name']!='') {
    $status=$Camp_DB->getPerson(array('strName'=>'%' . $_POST['name'] . '%'));
  }
  if(isset($_SESSION['support_user'])) {
    $people=$Camp_DB->getPerson(array('intPersonID'=>$_SESSION['support_user']));
    foreach($people as $person) {}
    echo "<td class=\"right\"><a href=\"{$baseurl}?logout\" class=\"Label\">Stop supporting this attendee</a></td>";
    $auth_code=$Camp_DB->getAuthCode();
  }
  echo "</tr></table>";
  if(isset($status)) {
    echo "<table><tr><th>Auth Code</th><th>Person's Name</th>";
    if(count($arrPhones)>0) {echo "<th>Last 6 digits of the phone number</th>";}
    echo "<th>Contact Details</th></tr>";
    foreach($status as $intPersonID=>$arrPerson) {
      echo "<tr><td><a href=\"$baseurl?authcode={$arrPerson['strAuthString']}\">{$arrPerson['strAuthString']}</a></td><td>{$arrPerson['strName']}</td>";
      if(count($arrPhones)>0) {echo "<td>" . substr($arrPerson['strPhoneNumber'], -6) . "</td>";}
      echo "<td>{$arrPerson['strContactInfo']}</td></tr>";
    }
    echo "</table>";
  } elseif(!isset($_SESSION['support_user'])) {
    echo "<form method=\"post\" action=\"$baseurl\">AuthCode: <input name=\"authcode\" size=\"10\" value=\"$auth_code\" /><br />";
    echo "Person's Name: <input name=\"name\" size=\"20\" /> <br />";
    echo "Contact Detail: <input name=\"contact\" size=\"20\" /> <br />";
    if(count($arrPhones)>0) {echo "Phone number used to send SMS to service: <input name=\"phonenumber\" size=\"20\" /><br />";}
    if(count($arrMbs)>0) {echo "Microblogging Account Name used to update service: <input name=\"microblog\" size=\"20\" /> <br />";}
    echo "<input type=\"submit\" value=\"Find\" />";
    echo "</form>";
    echo "<form method=\"post\" action=\"$baseurl\"><input type=\"hidden\" name=\"authcode\" value=\".\" /><input type=\"submit\" value=\"Create new authcode\" /></form>";
  } else {
    if(isset($_REQUEST['contact'])) {
      if(isset($_POST['update'])) {
        $data=array();
        $data[]=$Camp_DB->escape($_REQUEST['name']);
        foreach($contact_fields as $proto) {if(isset($_REQUEST[$proto])) {$data[]=$proto . ":" . $Camp_DB->escape($_REQUEST[$proto]);}}
        $Camp_DB->updateIdentityInfo($data);
        $Camp_DB->refresh();
        $people=$Camp_DB->getPerson(array('intPersonID'=>$_SESSION['support_user']));
        foreach($people as $person) {}
      }
    }
    echo "This is: <b>{$person['strName']}</b> with an AuthCode: <b>$auth_code</b><br />";
    if($person['strContactInfo']!='' or (count($arrPhones)>0 and $person['strPhoneNumber']!='')) {echo "Contact methods are: ";}
    if(count($arrPhones)>0 and $person['strPhoneNumber']!='') {echo "phone:" . substr($person['strPhoneNumber'], -6);}
    if($person['strContactInfo']!='' and (count($arrPhones)>0 and $person['strPhoneNumber']!='')) {echo " ";}
    if($person['strContactInfo']!='') {echo "{$person['strContactInfo']}";}
    if($person['strContactInfo']!='' or (count($arrPhones)>0 and $person['strPhoneNumber']!='')) {echo "<br />";}
    echo "<a href=\"?contact\">Amend contact details</a> | <a href=\"?propose\">Propose a talk</a> | <a href=\"?edit\">Edit a talk</a> | <a href=\"?cancel\">Cancel a talk</a> | <a href=\"?fix\">Fix a talk</a> | <a href=\"?attend\">Attend a talk</a> | <a href=\"?decline\">Decline a talk</a><br />"; 
    if(isset($_REQUEST['contact'])) {
      $details=$Camp_DB->getContactDetails(0, TRUE);
      echo "\r\n<form method=\"post\" action=\"$baseurl?contact\">\r\nYour name:\r\n<div class=\"data_Name\"><span class=\"Label\">Name:</span> <span=\"Data\"><input type=\"text\" name=\"name\" value=\"{$person['strName']}\" /></span></div>";
      foreach($contact_fields as $proto) {
        echo "\r\n<div class=\"data_$proto\"><span class=\"Label\">$proto:</span> <span=\"Data\"><input type=\"text\" name=\"$proto\" value=\"{$details[$proto]}\" /></span></div>";
      }
      echo "\r\n<input type=\"submit\" name=\"update\" value=\"Go\"/></form>";
    } elseif(isset($_REQUEST['propose'])) {
      if(isset($_POST['update'])) {$Camp_DB->insertTalk(array($_POST['slot'], $_POST['length'], $_POST['title']), 0);}
      echo "\r\n<form method=\"post\" action=\"$baseurl?propose\">\r\nPropose a new talk, starting at slot: <input type=\"text\" size=\"2\" name=\"slot\"/>\r\n and with a length of \r\n<select name=\"length\">";
      $left=count($Camp_DB->times);
      for($l=1; $l<=$left; $l++) {echo "<option value=\"$l\">$l</option>";}
      echo "</select> \r\nslots. The talk will be about: \r\n<input type=\"text\" size=\"50\" name=\"title\" />\r\n<input type=\"submit\" name=\"update\" value=\"Go\"/></form>";
    } elseif(isset($_REQUEST['edit'])) {
      if(isset($_POST['update'])) {$Camp_DB->editTalk(array($_REQUEST['talkid'], $Camp_DB->arrTalks[$_REQUEST['talkid']]['intTimeID'], $Camp_DB->escape(htmlentities($_REQUEST['ntitle']) . ' ')));}

// TODO: Make this list all my talks, and edit from that

      echo "<form method=\"post\" action=\"$baseurl?edit\">\r\nRetitle a talk with a talk ID of: <input type=\"text\" size=\"2\" name=\"talkid\" />\r\n With the new title <input type=\"text\" size=\"50\" name=\"ntitle\" />\r\n<input type=\"submit\" name=\"update\" value=\"Go\"/></form>";
    } elseif(isset($_REQUEST['cancel'])) {
      if(isset($_POST['update'])) {$Camp_DB->cancelTalk(array($_REQUEST['talkid'], $Camp_DB->arrTalks[$_REQUEST['talkid']]['intTimeID'], $Camp_DB->escape(htmlentities($_REQUEST['reason']))));}

// TODO: Make this list all my talks, and cancel from that

      echo "\r\n<form method=\"post\" action=\"$baseurl?cancel\">Cancel a talk with a talk ID of: <input type=\"text\" size=\"2\" name=\"talkid\" value=\"{$_GET['talkid']}\" />\r\n Because <input type=\"text\" size=\"50\" name=\"reason\" />\r\n<input type=\"submit\" name=\"update\" value=\"Go\"/></form>";
    } elseif(isset($_REQUEST['fix'])) {

// TODO: Make this list all my talks, and fix the talk from that

    } elseif(isset($_REQUEST['attend'])) {

// TODO: Make this list all talks that aren't mine, and attend from those (prolly by rendering the calendar!).

    } elseif(isset($_REQUEST['decline'])) {
// TODO: Make this list all talks that I'm showing as attending, and decline from that.
    }
  }
  echo "</body></html>";
} else {
  header("Location: ..");
}
