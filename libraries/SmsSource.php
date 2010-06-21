<?php
/*******************************************************
 * CampFireManager
 * A class for handling SMS messages from Gammu
 * Version 0.5 2010-03-19 JonTheNiceGuy
 *******************************************************
 * Version History
 * 0.5 2010-03-19 Migrated from personal SVN store
 * at http://spriggs.org.uk/jon_code/CampFireManager
 * where all prior versions are stored to Google Code at
 * http://code.google.com/p/campfiremanager/
 ******************************************************/

require_once($base_dir . "ProcessingSources.php");

class SmsSource extends ProcessingSources {
  function getStatus() {return $this->qryMap('ID', 'Signal', 'phones');}

  function getMessages($null='') {
    $this->doDebug("getMessages(); (phones)");
    $msgs=array();
    $UDH=array();

    // Retrieve message data from the database
    $messages=$this->qryArray("SELECT ID, SenderNumber, UDH, TextDecoded, RecipientID FROM inbox WHERE Processed='false'", 'ID');
    foreach($messages as $id=>$message) {

      // The UDH value is set when receiving or sending long length messages.
      if($message['UDH']!='') {
        // UDH starts 050003 (chars 0-5)
        // then has a "unique message byte" (chars 6-7)
        // then the number of messages in this long message (chars 8-9)
        // lastly this message number (chars 10-11)
        $UDH[substr($message['UDH'], 6, 2)][substr($message['UDH'], 10, 2)+0]=$id;
        ksort($UDH[substr($message['UDH'], 6, 2)]);
        if(count($UDH[substr($message['UDH'], 6, 2)])==substr($message['UDH'], 8, 2)) {
          $text='';
          foreach($UDH[substr($message['UDH'], 6, 2)] as $mid) {
            $text.=$messages[$mid]['TextDecoded'];
            if(!$this->boolUpdateOrInsertSql("UPDATE inbox SET Processed='true' WHERE ID='$mid'")) {die("Unable to update table");}
          }
          $msgs[]=array('phone'=>$message['RecipientID'], 'number'=>$message['SenderNumber'], 'text'=>$text);
        }
      } else {
          if(!$this->boolUpdateOrInsertSql("UPDATE inbox SET Processed='true' WHERE ID='{$message['ID']}'")) {die("Unable to update table");}
          $msgs[]=array('phone'=>$message['RecipientID'], 'number'=>$message['SenderNumber'], 'text'=>$message['TextDecoded']);
      }
    }
    $this->doDebug("Returns: " . count($msgs), 2);
    return $msgs;
  }

  function sendMessages($strMessage='', $strPhone='', $strPhoneNumber='') {
    $this->doDebug("sendMessages('$strMessage', '$strPhone', '$strPhoneNumber');");
    $arrMessageChunks=str_split($this->escape(stripslashes($strMessage)), 160);
    $this->doDebug("Chunked Message Parts: " . var_dump($arrMessageChunks, true), 2);
    if(count($arrMessageChunks)==1) {
      $this->boolUpdateOrInsertSql("INSERT INTO outbox (DestinationNumber, TextDecoded, CreatorID, Coding, SenderID) VALUES ('" . $this->escape(stripslashes($strPhoneNumber)) . "', '" . $this->escape(stripslashes($strMessage)) . "', 'CampFireManager','Default_No_Compression', '" . $this->escape(stripslashes($strPhone)) . "')");
    } else {
      foreach($arrMessageChunks as $chunkid=>$chunk) {
        $chunk_id=$chunkid+1;
        $UDH=str_pad(dechex(rand(0, 255)), 2, "0");
        $UDH_Parts=str_pad(dechex(count($arrMessageChunks)), 2, "0");
        $UDH_This_Part=str_pad(dechex($chunkid+1), 2, "0");
        if($chunkid==0) {
          $this->boolUpdateOrInsertSql("INSERT INTO outbox (CreatorID, 
                                                            MultiPart, 
                                                            DestinationNumber, 
                                                            UDH, 
                                                            TextDecoded, 
                                                            Coding, 
                                                            SenderID
                                        ) VALUES ('CampFireManager', 
                                                  'true', 
                                                  '" . $this->escape(stripslashes($strPhoneNumber)) . "', 
                                                  '050003{$UDH}{$UDH_Parts}{$UDH_This_Part}', 
                                                  '" . $this->escape(stripslashes($chunk)) . "', 
                                                  'Default_No_Compression',
                                                  '" . $this->escape(stripslashes($strPhone)) . "')");
          $mp_id=$this->getInsertID();
        } else {
          $this->boolUpdateOrInsertSql("INSERT INTO outbox_multipart (SequencePosition,
                                                                      UDH,
                                                                      TextDecoded,
                                                                      ID,
                                                                      Coding
                                        ) VALUES ('$chunk_id', 
                                                  '050003{$UDH}{$UDH_Parts}{$UDH_This_Part}', 
                                                  '" . $this->escape(stripslashes($chunk)) . "',
                                                  '$mp_id',
                                                  'Default_No_Compression')");
        }
      }
    }
  }
}


