<?php
/*******************************************************
 * CampFireManager
 * Processing daemon
 * Version 0.5 2010-03-19 JonTheNiceGuy
 *******************************************************
 * Version History
 * 0.5 2010-03-19 Migrated from personal SVN store
 * at http://spriggs.org.uk/jon_code/CampFireManager
 * where all prior versions are stored to Google Code at
 * http://code.google.com/p/campfiremanager/
 ******************************************************/


// This is a daemon service - it must not stop!
set_time_limit(0);
// Establish Database Connections
// This makes sure we startup the $Camp_DB and the $Phone_DB classes
$__campfire=array();
$__phone=TRUE;

$debug=1;

// Connect to the databases
require_once("db.php");
$MBlog_Accounts=$Camp_DB->getMicroBloggingAccounts();

$sources=array();
$sources['Phone']=new SmsSource($db_Phone['host'], $db_Phone['user'], $db_Phone['pass'], $db_Phone['base'], $debug);
if(is_array($MBlog_Accounts) and count($MBlog_Accounts)>0) {
  foreach($MBlog_Accounts as $intMbID=>$MBlog_Account) {
    $strApiType='statusnet';
    if($MBlog_Account['strApiBase']=="http://twitter.com" OR $MBlog_Account['strApiBase']=="https://twitter.com") {$strApiType='twitter';}
    $sources[$intMbID]=new OmbSource($MBlog_Account['strAccount'], $MBlog_Account['strPassword'], $MBlog_Account['strApiBase'], $strApiType, $debug);
  }
}

while(true) {
  sleep(5);
  $Camp_DB->fixRooms($camp);

  $msgs=array();

  foreach($sources as $source_id=>$source) {
    $Camp_DB->doDebug("Processing source $source_id : " . print_r($source, TRUE), 2);
    $source_status=$source->getStatus();
    foreach($source_status as $service=>$status) {$Camp_DB->updatePhoneData($service, $status);}
    if($source_id=='Phone') {
      $msgs=array_merge($msgs, $source->getMessages());
    } else {
      $high_id=$Camp_DB->getLastMbUpdate($source_id);
      $old_high_id=$high_id;
      $got_msgs=$source->getMessages($high_id);
      $Camp_DB->doDebug("Got messages: " . print_r($got_msgs, TRUE), 2);
      if(count($got_msgs)>0) {
        foreach($got_msgs as $intMsgID=>$msg) {
          $msg['omb_ac']=$source_id;
          if($msg['id']>$high_id) {$high_id=$msg['id'];}
          $msgs[]=$msg;
        }
      }
      if($high_id!=$old_high_id) {$Camp_DB->setLastMbUpdate($source_id, $high_id);}
    }
  }

  foreach($msgs as $msg) {
    $Camp_DB->getMe($msg);

    $commands=explode(" ", $msg['text']);
    $command_data=array_slice($commands, 1);

    switch(strtoupper(substr($msg['text'], 0, 2))) {
      // I [Your Name] <Contact details in the format service:detail>
      case "I ": // Identify
        $Camp_DB->updateIdentityInfo($command_data);
        break;
      case "O ": // Pair a microblogging account or phone number with an OpenID account
        $Camp_DB->mergeContactDetails($commands[1]);
        break;
      // F [Time slot] [Length] [Title]
      case "F ": // Propose a fixed slot talk
      // P [Time slot] [Length] [Title]
      case "P ": // Propose a talk
        $Camp_DB->insertTalk($command_data);
        break;
      // C [TalkID] [Time Slot] <Reason>
      case "C ": // Cancel a talk
        $Camp_DB->cancelTalk($command_data);
        break;
      // E [TalkID] [Time Slot] [New Title]
      case "E ": // Edit a talk's title
        $Camp_DB->editTalk($commands);
        break;
      // A [TalkID]  // R [TalkID]
      case "A ": // I will Attend a talk
      case "R ": // Remove me from a talk
        for($i=0; $i<=count($commands); $i=$i+2) {
          switch(strtoupper($commands[$i])) {
            case "A":
              $Camp_DB->attendTalk($commands[$i+1]);
              break;
            case "R":
              $Camp_DB->declineTalk($commands[$i+1]);
              break;
          }
        }
        break;
      default:
    }
  }
}

?>
