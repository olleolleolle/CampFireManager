<?php

// This is a daemon service - it must not stop!
set_time_limit(0);
// Establish Database Connections
// This makes sure we startup the $Camp_DB and the $Phone_DB classes
$__campfire=array();
$__phone=TRUE;

$debug=0;
$counter=0;
require_once("db.php");
$Phone_DB=new SmsSource($db_Phone['host'], $db_Phone['user'], $db_Phone['pass'], $db_Phone['base'], '', $debug);
$run=TRUE;
while($run) {
  if(count($Camp_DB->times)>0 and count($Camp_DB->rooms)>0 and $Camp_DB->next_time!='') {
    $arrFreeSlots=array();
    $arrFillSlots=array();
    $total_slots=0;

    foreach($Camp_DB->times as $intTimeID=>$strTime) {
      if($intTimeID>=$Camp_DB->next_time) {
        foreach($Camp_DB->rooms as $intRoomID=>$arrRoom) {
          $total_slots++;
          if($Camp_DB->arrTalkSlots[$intTimeID][$intRoomID]==0) {
            $arrFreeSlots[]=$intTimeID;
          } elseif($Camp_DB->arrTalkSlots[$intTimeID][$intRoomID]>0 and
                   $Camp_DB->arrTalks[$Camp_DB->arrTalkSlots[$intTimeID][$intRoomID]]['intTimeID']==$intTimeID) {
            $arrFillSlots[]=$Camp_DB->arrTalkSlots[$intTimeID][$intRoomID];
          }
        }
      }
    }

    $rate=0;
    if(($total_slots*0.25)<count($arrFreeSlots)) {
      $rate=25;
    } elseif(($total_slots*0.5)<count($arrFreeSlots)) {
      $rate=50;
    } elseif(($total_slots*0.75)<count($arrFreeSlots)) {
      $rate=75;
    } elseif(($total_slots)<count($arrFreeSlots)) {
      $rate=100;
    }

    $compare=rand(0,100);
    $state=FALSE;
    if($compare>$rate) {
      $state=TRUE;
    }
    if($state==TRUE) {
      $rand=rand(1,2);
      for($me=0; $me<=$rand; $me++) {
        $intPersonID=rand(1000,1199);
        $strPerson="Robot $intPersonID";
        $strPersonPhone="+44777777" . $intPersonID;
        $Camp_DB->getMe(array('number'=>$strPersonPhone));
        $me=$Camp_DB->allMyDetails();
        if(substr($me['strName'],0,7)=='Someone') {
          PhoneQueueInsert("I $strPerson", $strPersonPhone);
        }
        CreateTalk($strPersonPhone, $arrFreeSlots);
      }
    }
    $rand=rand(0,3);
    for($me=0; $me<=$rand; $me++) {
      $intPersonID=rand(1000,1199);
      $strPersonPhone="+44777777" . $intPersonID;
      $r_attend=rand(0, count($arrFillSlots)-1);
      if(isset($arrFillSlots[$r_attend])) {
        PhoneQueueInsert("A " . $arrFillSlots[$r_attend], $strPersonPhone);
      }
    }
  }
  if($counter>100) {$run=FALSE;}
  $counter++;
  sleep(rand(1,5));
  $Camp_DB->refresh();
}

function CreateTalk($number, $FreeSlots) {
  $msg='P ';
  $msg.=$FreeSlots[rand(1, count($FreeSlots)-1)] . ' ';
  $length_chance=rand(0,10);
  if($length_chance>9) {$msg.='2 ';} else {$msg.='1 ';}
  $msg.=MakeRandomString();
  PhoneQueueInsert($msg, $number);
}

function MakeRandomString() {
  $len=rand(10, 75);
  $msg='';
  for($pos=0; $pos<$len; $pos++) {
    $strings=str_split("ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890!?.,              ");
    $msg.=$strings[rand(0,count($strings)-1)];
  }
  return($msg);
}

function PhoneQueueInsert($msg, $number) {
  echo "SMS from $number: $msg\r\n";
  global $Camp_DB, $Phone_DB;
  $arrMessageChunks=str_split($Camp_DB->escape(stripslashes($msg)), 160);
  if(count($arrMessageChunks)>1) {
    foreach($arrMessageChunks as $chunkid=>$chunk) {
      $UDH=str_pad(dechex(rand(0, 255)), 2, "0");
      $UDH_Parts=str_pad(dechex(count($arrMessageChunks)), 2, "0");
      $UDH_This_Part=str_pad(dechex($chunkid+1), 2, "0");
      $Phone_DB->boolUpdateOrInsertSql("INSERT INTO inbox (SenderNumber, UDH, TextDecoded, RecipientID, Processed) VALUES ('$number', '050003{$UDH}{$UDH_Parts}{$UDH_This_Part}', '" . $Camp_DB->escape(stripslashes($chunk)) . "', 'DemoData', 'false')");
    }
  } else {
    foreach($arrMessageChunks as $chunkid=>$chunk) {
      $Phone_DB->boolUpdateOrInsertSql("INSERT INTO inbox (SenderNumber, TextDecoded, RecipientID, Processed) VALUES ('$number', '" . $Camp_DB->escape(stripslashes($chunk)) . "', 'DemoData', 'false')");
    }
  }
}
