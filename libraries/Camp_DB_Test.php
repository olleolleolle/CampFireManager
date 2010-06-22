<?php
/*******************************************************
 * CampFireManager
 * Testing code for primary functions, however, this
 * file hasn't been maintained as the project has
 * progressed. As a result, it may not always produce
 * expected results.
 * Version 0.5 2010-03-19 JonTheNiceGuy
 *******************************************************
 * Version History
 * 0.5 2010-03-19 Migrated from personal SVN store
 * at http://spriggs.org.uk/jon_code/CampFireManager
 * where all prior versions are stored to Google Code at
 * http://code.google.com/p/campfiremanager/
 ******************************************************/

require_once($base_dir . "Camp_DB.php");

class Camp_DB_Test extends Camp_DB {
  // Arrays of data that would *normally* be in a database
  private $_talks=array();
  private $_attendees=array();

  function __construct($db_host, $db_user, $db_pass, $db_base, $db_prefix, $arrAuthDetails, $debug=0) {
    $this->debug=$debug;
    $this->times=$this->getTimes();
    $this->rooms=$this->getRooms();
    $this->config=$this->getConfig();
    list($this->intPersonID, $this->strName, $this->isAdmin)=$this->getMe($arrAuthDetails);
    list($this->now, $this->next)=$this->getNowAndNextTime();
    list($this->talks, $this->talk_data)=$this->readTalkData();
  }

  function getNowAndNextTime($offset='') {
    if($offset=='') {
      $now_time=strtotime('10:00');
    } else {
      $now_time=strtotime('10:00 ' . $offset);
    }

    // Find the "Now" and "Next" time blocks
    $now='0';
    $next='';
    foreach($this->times as $time=>$timestring) {
      $timearray=explode('-', $timestring);
      $intTime=strtotime(date("Y-m-d ") . $timearray[0]);
      if($intTime<$now_time) {$now=$time;}
      if($intTime>=$now_time) {
        $next=$time;
        break;
      }
    }
    $this->doDebug("getNowAndNextTime('$offset'); (returns now: $now, next: $next)");
    return(array('now'=>$now, 'next'=>$next));
  }

  function getMe($me=array(), $strSourceID='') {
    $this->doDebug("getMe(" . print_r($me, TRUE) . ");");
    $this->intPersonID=1;
    $this->strName='Test User';
    $this->isAdmin=0;
    return TRUE;
  }

  function _changeMe($intPersonID) { // For mass loading and unloading - simulates multiple connections
    $this->doDebug("_changeMe('$intPersonID');");
    $this->intPersonID=$intPersonID;
    $this->strName='Test Attendee ' . $intPersonID;
    $this->isAdmin=0;
  }

  function getMicroBloggingAccounts() {
    $this->doDebug("getMicroBloggingAccounts();");
    return(array(1=>array('intMbID'=>1,
                          'strAccount'=>'testuser',
                          'strApiBase'=>'http://identi.ca/api',
                          'strPassword'=>'t3$tu$3r')));
  }

  function getPhones() {
    $this->doDebug("getPhones();");
    return(array(1=>array('intPhoneID'=>1,
                          'strPhone'=>'Test Phone Co.',
                          'strNumber'=>'07777777777',
                          'intSignal'=>100,
                          'strGammuRef'=>'test-source')));
  }

  function getConfig() {
    $this->doDebug("getConfig();");
    return(array('lunch'=>'0',
                 'website'=>'http://Camp.Fire.Manager',
                 'event_title'=>'Test event',
                 'adminkey'=>'abcde12345'));
  }

  function generateNewAdminKey() {
    $this->doDebug("generateNewAdminKey();");
    $this->config['adminkey']=genRandStr(10, 20);
  }

  function getPeople() {
    $this->doDebug("getPeople();");
    return(array(1=>array('intPersonID'=>1,
                          'strPhoneNumber'=>'+441234567890',
                          'strName'=>'Test User',
                          'strContactInfo'=>'mailto:test@example.com http://example.com/~test twitter:testuser',
                          'strDefaultReply'=>'demo-source',
                          'strOpenID'=>'',
                          'strMicroBlog'=>'',
                          'strAuthString'=>'a1b2c3d4e5f6g7h8i9j0',
                          'boolIsAdmin'=>1)));
  }

  function getPerson($s=array()) {
    $this->doDebug("getPerson(" . print_r($s, TRUE) . ");");
    if(count($s)==0) {return FALSE;}
    $w='';
    $people=$this->getPeople();
    foreach($people as $person) {
      foreach($s as $key=>$value) {if($person[$key]==$value) {$return[]=$person; break;}}
    }
    return($return);
  }

  function getRooms() {
    $this->doDebug("getRooms();");
    return(array(1=>array('intRoomID'=>1,
                          'strRoom'=>'Room 1',
                          'intCapacity'=>100),
                 2=>array('intRoomID'=>2,
                          'strRoom'=>'Room 2',
                          'intCapacity'=>50),
                 3=>array('intRoomID'=>3,
                          'strRoom'=>'Room 3',
                          'intCapacity'=>25)));
  }

  function getTimes() {
    $this->doDebug("getTimes();");
    return(array(1=>'09:00-09:45',
                 2=>'10:00-10:45'));
  }

  function getTalks() {
    $this->doDebug("getTalks();");
    return $this->_talks;
  }

  protected function _createTalk($intTimeID, $intRoomID, $intPersonID, $strTalkTitle, $boolFixed, $intLength) {
    $this->doDebug("_createTalk('$intTimeID', '$intRoomID', '$intPersonID', '$strTalkTitle', '$boolFixed', '$intLength');");
    $this_talk=$this->getInsertID()+1;
    $this->_talks[$this_talk]=array('intTalkID'=>$this_talk,
                          'intTimeID'=>$intTimeID,
                          'intRoomID'=>$intRoomID,
                          'intPersonID'=>$intPersonID,
                          'strTalkTitle'=>$strTalkTitle,
                          'boolFixed'=>$boolFixed,
                          'intLength'=>$intLength);
    return $this->getInsertID();
  }

  protected function _editTalk($intTalkID, $strTalkTitle) {
    $this->doDebug("_editTalk('$intTalkID', '$strTalkTitle');");
    $this->_talks[$intTalkID]['strTalkTitle']=$strTalkTitle;
  }

  protected function _deleteTalk($intTalkID) {
    $this->doDebug("_deleteTalk('$intTalkID');");
    unset($this->_talks[$intTalkID]);
    foreach($this->_attendees as $intAttendID=>$attendee) {
      if($attendee['intTalkID']==$intTalkID) {$unsetters[]=$intAttendID;}
    }
    foreach($unsetters as $unsetid) {unset($this->_attendees[$unsetid]);}
  }

  function getAttendeesCount() {
    $this->doDebug("getAttendeesCount();");
    foreach($this->_attendees as $attendee) {
      $return[$attendee['intTalkID']]++;
    }
    return($return);
  }

  function getAttendees() {
    $this->doDebug("getAttendees();");
    return $this->_attendees;
  }

  function getTalksIAmAttending() {
    $return=array();
    $this->doDebug("getTalksIAmAttending();");
    foreach($this->_attendees as $attendee) {
      if($attendee['intPersonID']==$this->intPersonID) {$return[]=$attendee['intTalkID'];}
    }
    return($return);    
  }

  protected function _attendTalk($intTalkID) {
    $this->doDebug("_attendTalk('$intTalkID');");
    $mytalks=$this->getTalksIAmAttending();
    if(!in_array($intTalkID, $mytalks)) {
      $this->_attendees[]=array('intTalkID'=>$intTalkID, 'intPersonID'=>$this->intPersonID);
    }
  }

  protected function _declineTalk($intTalkID) {
    $this->doDebug("_declineTalk('$intTalkID');");
    foreach($this->_attendees as $intAttendID=>$attendee) {
      if($attendee['intPersonID']==$this->intPersonID AND $attendee['intTalkID']==$intTalkID) {
        $unsetters[]=$intAttendID;
      }
    } 
    foreach($unsetters as $unsetid) {
      unset($this->_attendees[$unsetid]);
    }
  }

  protected function _moveTalkRoom($intTalkID, $intRoomID) {
    $this->doDebug("_moveTalkRoom('$intTalkID', '$intRoomID');");
    $this->_talks[$intTalkID]['intRoomID']=$intRoomID;
  }

  protected function _setRoom($intTalkID, $intRoomID) {
    $this->doDebug("_setRoom(intTalkID=>'$intTalkID', intRoomID=>'$intRoomID');");
    $this->_talks[$intTalkID]['intRoomID']=$intRoomID;
  }

  function getPresenters() {
    $this->doDebug("getPresenters();");
    return(array(1=>array('intPersonID'=>1,
                          'strName'=>'Test User',
                          'strContactInfo'=>'mailto:test@example.com http://example.com/~test twitter:testuser')));
  }

  function getScreens($s='') {
    $this->doDebug("getScreens('$s');");
    return(array(1=>'Test Screen'));
  }

  function showStatusScreen($number=50) {
    return(array(1=>'Something happened',
                 2=>'Something else happened',
                 3=>'lsdkjsdvnjdfvnjunndfdfvagdg a aegasdg g agt awgvsdg asvdx c r4',
                 4=>'dfnv ngmaw;win lizlngfv dlvxfdvu;x',
                 5=>'bnnjeujdfvnfdvl lsndfvmlunjmvzdfn cbln udfvxn',
                 6=>'ndfvkmmsdvnfmnjsjdfvinj vxjfzsdvxcji vndfzvimxcn',
                 7=>'kvnj jrnfdvc jinzdfvuc j',
                 8=>'jukjhnuikjhujhyu8y7ygtygft6ygfrtgbn bghjnbhjnbhuj'));
  }

  function getDirections($s='') {
    $this->doDebug("getDirections('$s');");
    return(array(1=>array('intDirectionID'=>1,
                          'intScreenID'=>1,
                          'intDestRoomID'=>1,
                          'intDirectionURDL'=>'U'),
                 2=>array('intDirectionID'=>2,
                          'intScreenID'=>1,
                          'intDestRoomID'=>2,
                          'intDirectionURDL'=>'L')));
  }

  function boolUpdateOrInsertSql($sql='') {
    $this->doDebug("boolUpdateOrInsertSql('$sql')");
    if($sql!='') {return TRUE;} else {return FALSE;}
  }

  function escape($string='') {return($string);}

  function getInsertID() {
    $maxTalkID=0;
    foreach($this->_talks as $intTalkID=>$talk) {
      if($intTalkID>$maxTalkID) {$maxTalkID=$intTalkID;}
    }
    $this->doDebug("getInsertID(); (returns $maxTalkID)");
    return($maxTalkID);
  }
}
