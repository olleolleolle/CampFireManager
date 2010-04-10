<?php
/*******************************************************
 * CampFireManager
 * Admin Console
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
require_once("db.php");
if(!isset($Camp_DB->config['adminkey'])) {$Camp_DB->generateNewAdminKey();}
if(!isset($Camp_DB->config['supportkey'])) {$Camp_DB->generateNewSupportKey();}
// You're only allowed here if you've already logged in
if(!isset($_SESSION['openid'])) {
  $_SESSION['redirect']='admin.php';
  header("Location: $baseurl");
} else {
  $Camp_DB->getMe(array('OpenID'=>$_SESSION['openid'], 'OpenID_Name'=>$_SESSION['name'], 'OpenID_Mail'=>$_SESSION['email']));
}
if($Camp_DB->getAdmins()==0) { // If there's no-one here yet, you get it by default!!
  header("Location: $baseurl?state=Oa&AuthString={$Camp_DB->config['adminkey']}");
} elseif($Camp_DB->checkAdmin()==1) { // Otherwise you'll only get it if you're in the admin list
  $config_fields=array( 'lunch'=>'Lunchtime',
                        'website'=>"The public URL of this site. Leave blank if you don't want public access.",
                        'event_title'=>'What is the title of your event?',
                        'FixRoomOffset'=>'Relative to the start time of a session, at what point is the room allocated to a talk fixed?',
                        'UTCOffset'=>'The UTC offset for the timezone, e.g. +00:00 for GMT or -08:00 for Pacific Standard Time.',
                        'timezone_name'=>'This is the name of your timezone (e.g. Europe/London)',
                        'AboutTheEvent'=>'Please provide some details about the content of your event.',
                        'hashtag'=>"Optional: What do you want people (including this script) to use as the hashtag for today?, including the # sign itself.");

  if(isset($_POST['update_config'])) {foreach($config_fields as $value=>$description) {$Camp_DB->setConfig($value, stripslashes($_POST[$value]));}}
  if(isset($_POST['update_times'])) {
    foreach($Camp_DB->times as $value=>$description) {$Camp_DB->updateTime($value, $_POST['time_' . $value]);}
    if($_POST['time_new']!='') {$Camp_DB->updateTime('', $_POST['time_new']);}
  }
  if(isset($_POST['update_rooms'])) {
    foreach($Camp_DB->rooms as $value=>$description) {$Camp_DB->updateRoom($value, $_POST['room_' . $value], $_POST['capacity_' . $value]);}
    if($_POST['room_new']!='' AND $_POST['capacity_new']!='') {$Camp_DB->updateRoom('', $_POST['room_new'], $_POST['capacity_new']);}
  }
  if(!isset($Camp_DB->config['adminkey'])) {$Camp_DB->generateNewAdminKey();}
  if(isset($_POST['update_phones'])) {
    $arrPhones=$Camp_DB->getPhones();
    foreach($arrPhones as $intPhoneID=>$arrPhone) {$Camp_DB->updatePhone($intPhoneID, $_POST['phone_number_' . $intPhoneID], $_POST['phone_network_' . $intPhoneID], $_POST['phone_gammu_' . $intPhoneID]);}
    if($_POST['phone_number_new']!='' AND $_POST['phone_network_new']!='' AND $_POST['phone_gammu_new']!='') {$Camp_DB->updatePhone('', $_POST['phone_number_new'], $_POST['phone_network_new'], $_POST['phone_gammu_new']);}
  }
  if(isset($_POST['update_microblogs'])) {
    $arrMbs=$Camp_DB->getMicroBloggingAccounts();
    foreach($arrMbs as $intMbID=>$arrMb) {$Camp_DB->updateMb($intMbID, $_POST['mb_api_' . $intMbID], $_POST['mb_user_' . $intMbID], $_POST['mb_pass_' . $intMbID]);}
    if($_POST['mb_api_new']!='' AND $_POST['mb_user_new']!='' AND $_POST['mb_pass_new']!='') {$Camp_DB->updateMb('', $_POST['mb_api_new'], $_POST['mb_user_new'], $_POST['mb_pass_new']);}
  }
  $arrPhones=$Camp_DB->getPhones();
  $arrMbs=$Camp_DB->getMicroBloggingAccounts();
  $Camp_DB->refresh();
  echo "<html>
<head>
<title>{$Camp_DB->config['event_title']}</title>
<link rel=\"stylesheet\" type=\"text/css\" href=\"common_style.php\" />
</head>
<body>
<form method=\"post\" action=\"{$baseurl}admin.php\" class=\"WholeDay\">
<input type=\"hidden\" name=\"update_config\" value=\"TRUE\">
<table>
  <tr><td><a href=\"$baseurl\" class=\"Label\">Back to main screen</a></td><td class=\"right\"><a href=\"{$baseurl}joind_in.php\" class=\"Label\">Joind.in XML file</a></td></tr>
  <tr><th colspan=\"2\">Admin Console for Config Options (empty boxes will unset those values in the database)</th></tr>
  <tr><td class=\"Label\">Next Admin Key (note: each use will change this value)</td><td class=\"Data\">{$Camp_DB->config['adminkey']}</td></tr>
  <tr><td class=\"Label\">Next Support Key (note: each use will change this value)</td><td class=\"Data\">{$Camp_DB->config['supportkey']}</td></tr>
  <tr><td class=\"Label\">Lunch Time</td><td class=\"Data\"><select name=\"lunch\">";
  foreach($Camp_DB->times as $time=>$description) {
    if($Camp_DB->config['lunch']==$time) {
      echo "<option value=\"$time\" selected=\"selected\">$description</option>";
    } else {
      echo "<option value=\"$time\">$description</option>";
    }
  }
  echo "</select></td></tr>";
  foreach($config_fields as $value=>$description) {
    if($value!='lunch') {echo "  <tr><td class=\"Label\">$description</td><td class=\"Data\"><input type=\"text\" name=\"$value\" size=\"25\" value=\"" . $Camp_DB->config[$value] . "\"></td></tr>";}
  }
  echo "<tr><td colspan=\"2\"><input type=\"submit\" value=\"Update Configuration\"></form>";
  echo "
<tr><td><form method=\"post\" action=\"{$baseurl}admin.php\" class=\"WholeDay\">
<input type=\"hidden\" name=\"update_phones\" value=\"TRUE\">
<table>
  <tr><th colspan=\"3\">SMS Devices (accessed by Gammu)</th></tr>
  <tr><th>Phone Number</th><th>Phone Network</th><th>SMS Engine Identifier</th></tr>";
  foreach($arrPhones as $intPhoneID=>$arrPhone) {echo "
  <tr>
    <td class=\"Data\"><input type=\"text\" name=\"phone_number_$intPhoneID\" size=\"15\" value=\"{$arrPhone['strNumber']}\"></td>
    <td class=\"Data\"><input type=\"text\" name=\"phone_network_$intPhoneID\" size=\"15\" value=\"{$arrPhone['strPhone']}\"></td>
    <td class=\"Data\"><input type=\"text\" name=\"phone_gammu_$intPhoneID\" size=\"15\" value=\"{$arrPhone['strGammuRef']}\"></td>
  </tr>";}
  echo "
  <tr><th colspan=\"3\">New SMS Device</th></tr>
  <tr>
    <td class=\"Data\"><input type=\"text\" name=\"phone_number_new\" size=\"15\" value=\"\"></td>
    <td class=\"Data\"><input type=\"text\" name=\"phone_network_new\" size=\"15\" value=\"\"></td>
    <td class=\"Data\"><input type=\"text\" name=\"phone_gammu_new\" size=\"15\" value=\"\"></td>
  </tr>";
  echo "<tr><td colspan=\"3\"><input type=\"submit\" value=\"Update Phones\">";
  echo "</table></form></td>
<td><form method=\"post\" action=\"{$baseurl}admin.php\" class=\"WholeDay\">
<input type=\"hidden\" name=\"update_microblogs\" value=\"TRUE\">
<table>
  <tr><th colspan=\"3\">Microbloggging Accounts (via Twitter APIs)</th></tr>
  <tr><th>API</th><th>Username</th><th>Password</th></tr>";
  foreach($arrMbs as $intMbID=>$arrMb) {echo "
  <tr>
    <td class=\"Data\"><input type=\"text\" name=\"mb_api_$intMbID\" size=\"15\" value=\"{$arrMb['strApiBase']}\"></td>
    <td class=\"Data\"><input type=\"text\" name=\"mb_user_$intMbID\" size=\"15\" value=\"{$arrMb['strAccount']}\"></td>
    <td class=\"Data\"><input type=\"password\" name=\"mb_pass_$intMbID\" size=\"15\" value=\"{$arrMb['strPassword']}\"></td>
  </tr>";}
  echo "
  <tr><th colspan=\"4\">New Microblog</th></tr>
  <tr>
    <td class=\"Data\"><input type=\"text\" name=\"mb_api_new\" size=\"15\" value=\"\"></td>
    <td class=\"Data\"><input type=\"text\" name=\"mb_user_new\" size=\"15\" value=\"\"></td>
    <td class=\"Data\"><input type=\"password\" name=\"mb_pass_new\" size=\"15\" value=\"\"></td>
  </tr>";
  echo "<tr><td colspan=\"4\"><input type=\"submit\" value=\"Update Microblogs\">";
  echo "</table></form></td>
<tr><td><form method=\"post\" action=\"{$baseurl}admin.php\" class=\"WholeDay\">
<input type=\"hidden\" name=\"update_times\" value=\"TRUE\">
<table>
  <tr><th colspan=\"2\">Time Options (please sort these manually by time)</th></tr>
  <tr><th>Time Slot</th><th>Period in the format HH:MM-HH:MM</th></tr>";
  foreach($Camp_DB->times as $time=>$description) {echo "
  <tr>
    <td class=\"Label\">$time</td>
    <td class=\"Data\"><input type=\"text\" name=\"time_$time\" size=\"10\" value=\"$description\"></td>
  </tr>";}
  echo "
  <tr>
    <td class=\"Label\">New Time Slot</td>
    <td class=\"Data\"><input type=\"text\" name=\"time_new\" size=\"10\" value=\"\"></td>
  </tr>";
  echo "<tr><td colspan=\"2\"><input type=\"submit\" value=\"Update Times\">";
  echo "</table></form></td>
<td><form method=\"post\" action=\"{$baseurl}admin.php\" class=\"WholeDay\">
<input type=\"hidden\" name=\"update_rooms\" value=\"TRUE\">
<table>
  <tr><th colspan=\"3\">Room Options (please sort these manually by capacity)</th></tr>
  <tr><th>Room ID</th><th>Name</th><th>Capacity</th></tr>";
  foreach($Camp_DB->rooms as $roomid=>$room) {echo "
  <tr>
    <td class=\"Label\">Room $roomid</td>
    <td class=\"Data\"><input type=\"text\" name=\"room_$roomid\" size=\"25\" value=\"{$room['strRoom']}\"></td>
    <td class=\"Data\"><input type=\"text\" name=\"capacity_$roomid\" size=\"4\" value=\"{$room['intCapacity']}\"></td>
  </tr>";}
  echo "
  <tr>
    <td class=\"Label\">New Room</td>
    <td class=\"Data\"><input type=\"text\" name=\"room_new\" size=\"25\" value=\"\"></td>
    <td class=\"Data\"><input type=\"text\" name=\"capacity_new\" size=\"4\" value=\"\"></td>
  </tr>";
  echo "<tr><td colspan=\"3\"><input type=\"submit\" value=\"Update Configuration\">";
  echo "</table></form></td></tr></table>";
} else {
  header("Location: $baseurl");
}

?>
