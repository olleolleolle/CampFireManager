<?php 
/*******************************************************
 * CampFireManager
 * Export talk data for import to joind.in
 * Version 0.5 2010-03-19 JonTheNiceGuy
 *******************************************************
 * Version History
 * 0.5 2010-03-19 Migrated from personal SVN store
 * at http://spriggs.org.uk/jon_code/CampFireManager
 * where all prior versions are stored to Google Code at
 * http://code.google.com/p/campfiremanager/
 ******************************************************/

require_once("db.php");
$Camp_DB->collectWholeEventData();
$min_time=99;
$max_time=0;
foreach($Camp_DB->times as $intTimeID=>$strTime) {
  if($intTimeID<$min_time) {$min_time=$intTimeID;}
  if($intTimeID>$max_time) {$max_time=$intTimeID;}
}
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\r\n"; ?>
<event>
  <event_title><?php echo $Camp_DB->config['event_title']; ?></event_title>
  <event_start_date><?php echo $Camp_DB->config['event_start'] . "T" . $Camp_DB->arrTimeEndPoints[$min_time]['s'] . $Camp_DB->config['UTCOffset']; ?></event_start_date>
  <event_end_date><?php echo $Camp_DB->config['event_end'] . "T" . $Camp_DB->arrTimeEndPoints[$max_time]['e'] . $Camp_DB->config['UTCOffset']; ?></event_end_date>
  <event_desc><?php echo $Camp_DB->config['AboutTheEvent']; ?></event_desc>
<?php
if(count($Camp_DB->arrTalks)>0) {
  echo "  <sessions>";
  foreach($Camp_DB->arrTalks as $intTalkID=>$arrTalk) {
    echo "
    <session>
      <session_title>{$arrTalk['strTalkTitle']}</session_title>
      <session_start>{$arrTalk['xsdStart']}</session_start>
      <session_end>{$arrTalk['xsdEnd']}</session_end>
      <session_desc></session_desc>
      <session_speaker>{$arrTalk['strPresenter']}</session_speaker>
    </session>";
  }
  echo "\r\n  </sessions>\r\n";}?>
</event>
