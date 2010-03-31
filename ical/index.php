<?php 
/*******************************************************
 * CampFireManager
 * Public facing web page
 * Version 0.5 2010-03-19 JonTheNiceGuy
 *******************************************************
 * Version History
 * 0.5 2010-03-19 Migrated from personal SVN store
 * at http://spriggs.org.uk/jon_code/CampFireManager
 * where all prior versions are stored to Google Code at
 * http://code.google.com/p/campfiremanager/
 ******************************************************/

if(file_exists("../libraries/GenericBaseClass.php")) {$base_dir='../libraries/';} else {$base_dir='../';}
require_once("{$base_dir}../db.php");
require_once($base_dir . "../external/iCalcreator/iCalcreator.class.php");

$Camp_DB->fixRooms();
$Camp_DB->collectWholeEventData();

$vcal=new vcalendar();
$vcal->setConfig('unique_id', $_SERVER['SERVER_NAME']);
$vcal->setProperty('method', 'PUBLISH');
$vcal->setProperty('x-wr-calname',$Camp_DB->config['event_title']); 
$vcal->setProperty('X-WR-CALDESC',$Camp_DB->config['AboutTheEvent']); 
$vcal->setProperty('X-WR-TIMEZONE',$Camp_DB->config['timezone_name']); 

if(count($Camp_DB->arrTalks)>0) {
  foreach($Camp_DB->arrTalks as $intTalkID=>$arrTalk) {
    if($arrTalk['boolFixed']==1) {
      $vevent = new vevent(); 
      // create an event calendar component 
      $vevent->setProperty('dtstart',array('year'=>date("Y", strtotime($arrTalk['datTalk'])),
                                            'month'=>date("m", strtotime($arrTalk['datTalk'])),
                                            'day'=>date("d", strtotime($arrTalk['datTalk'])),
                                            'hour'=>date("H", strtotime($Camp_DB->arrTimeEndPoints[$arrTalk['intTimeID']]['s'])),
                                            'min'=>date("i", strtotime($Camp_DB->arrTimeEndPoints[$arrTalk['intTimeID']]['s'])),
                                            'sec'=>'0'));
      $vevent->setProperty('dtend',array('year'=>date("Y", strtotime($arrTalk['datTalk'])),
                                         'month'=>date("m", strtotime($arrTalk['datTalk'])),
                                         'day'=>date("d", strtotime($arrTalk['datTalk'])),
                                         'hour'=>date("H", strtotime($Camp_DB->arrTimeEndPoints[$arrTalk['intTimeID'] + ($arrTalk['intLength'] - 1)]['e'])),
                                         'min'=>date("i", strtotime($Camp_DB->arrTimeEndPoints[$arrTalk['intTimeID'] + ($arrTalk['intLength'] - 1)]['e'])),
                                         'sec'=>'0'));
      $vevent->setProperty('LOCATION',$Camp_DB->rooms[$arrTalk['intRoomID']]['strName']);
      $vevent->setProperty('summary','"' . $arrTalk['strTalkTitle'] . '" by ' . $arrTalk['strPresenter']); 
      $vevent->setProperty("organiser",'CampFireManager'); 
      $vcal->setComponent ($vevent);
    }
  }
}
$vcal->returnCalendar();
?>
