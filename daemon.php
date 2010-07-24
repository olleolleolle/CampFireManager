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
$sources['Phone']=new SmsSource($db_Phone['host'], $db_Phone['user'], $db_Phone['pass'], $db_Phone['base'], '', $debug);
if(is_array($MBlog_Accounts) and count($MBlog_Accounts)>0) {
  foreach($MBlog_Accounts as $intMbID=>$MBlog_Account) {
    $strApiType='statusnet';
    if($MBlog_Account['strApiBase']=="http://twitter.com" OR $MBlog_Account['strApiBase']=="https://twitter.com") {$strApiType='twitter';}
    $sources[$intMbID]=new OmbSource($MBlog_Account['strAccount'], $MBlog_Account['strPassword'], $MBlog_Account['strApiBase'], $strApiType, $debug);
  }
}

while(true) {
  sleep(2);
  if($Camp_DB->fixRooms()) {
    // fixRooms returns true if it fixed a room, thus, let's broadcast that.
    if(isset($Camp_DB->config['FixRoomOffset'])) {$offset=$Camp_DB->config['FixRoomOffset'];} else {$offset="-15 minutes";}
    $nowandnext_talk_time=$Camp_DB->getNowAndNextTime($offset);
    $next_talk_time=$nowandnext_talk_time['now'];
    $broadcast_talks=array();
    foreach($Camp_DB->arrTalkSlots[$next_talk_time] as $fix_intRoomID=>$fix_intTalkID) {if($fix_intTalkID>0) {
      $broadcast_talks[$fix_intTalkID]['strTalkTitle']=$Camp_DB->arrTalks[$fix_intTalkID]['strTalkTitle'];
      $broadcast_talks[$fix_intTalkID]['strRoom']=$Camp_DB->rooms[$Camp_DB->arrTalks[$fix_intTalkID]['intRoomID']]['strRoom'];
      $contact=$Camp_DB->getContactDetails($Camp_DB->arrTalks[$fix_intTalkID]['intPersonID'], TRUE);
      if(isset($contact['twitter']) and $contact['twitter']!='') {
        $broadcast_talks[$fix_intTalkID]['strPerson']='@' . $contact['twitter'];
      } elseif(isset($contact['identica']) and $contact['identica']!='') {
        $broadcast_talks[$fix_intTalkID]['strPerson']='@' . $contact['identica'];
      } else {
        $broadcast_talks[$fix_intTalkID]['strPerson']=$contact['strName'];
      }
    }}
    foreach($broadcast_talks as $fix_intTalkID=>$broadcast_talk) {
      $message_pre="Talk Fixed: ";
      $message_post=" in {$broadcast_talk['strRoom']} by {$broadcast_talk['strPerson']} at {$Camp_DB->arrTimeEndPoints[$next_talk_time]['s']}" . $Camp_DB->config['hashtag'];
      $title_length=140 - strlen($message_pre . $message_post);
      $message=$message_pre . substr($broadcast_talk['strTalkTitle'], 0, $title_length) . $message_post;
      foreach($sources as $source_id=>$source) {if($source_id>0) {
        $source->sendMessages($message);
      }}
    }
  }

  $msgs=array();

  foreach($sources as $source_id=>$source) {
    $Camp_DB->doDebug("Processing source $source_id : " . print_r($source, TRUE), 2);
    $source_status=$source->getStatus();
    foreach($source_status as $service=>$status) {$Camp_DB->updatePhoneData($service, $status);}
    if($source_id>0) {
      $high_id=$Camp_DB->getLastMbUpdate($source_id);
      $old_high_id=$high_id;
    } else {$high_id=0; $old_high_id=0;}
    $got_msgs=$source->getMessages($high_id);
    $source->doDebug("Got messages: " . print_r($got_msgs, TRUE), 2);
    if(count($got_msgs)>0) {
      foreach($got_msgs as $intMsgID=>$msg) {
        $msgs[]=$msg;
        if($source_id>0) {
          $msg['strDefaultReply']=$source_id;
          if($msg['id']>$high_id) {$high_id=$msg['id'];}
        }
      }
    }
    if($source_id>0) {
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
        for($i=0; $i<=count($commands)-1; $i++) {
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
