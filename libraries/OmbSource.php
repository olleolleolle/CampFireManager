<?php
/*******************************************************
 * CampFireManager
 * A class for handling Microblogging sources - not
 * only OpenMicroBlogging! Badly named - sorry!
 * Version 0.5 2010-03-19 JonTheNiceGuy
 *******************************************************
 * Version History
 * 0.5 2010-03-19 Migrated from personal SVN store
 * at http://spriggs.org.uk/jon_code/CampFireManager
 * where all prior versions are stored to Google Code at
 * http://code.google.com/p/campfiremanager/
 ******************************************************/

require_once($base_dir . "ProcessingSources.php");

class OmbSource extends ProcessingSources {
  protected $_api;
  protected $_last_run=0;

  function __construct($strUsername, $strPassword, $strBaseApi='http://identi.ca/api', $strApiType='statusnet', $debug=0) {
    $this->_intDebug=$debug;
    $this->doDebug("New OmbSource($strUsername, $strPassword, $strBaseApi, $strApiType, $debug)");
    $this->_api=new GetTwitterAPI(array('strApiBase'=>$strBaseApi, 'strApiType'=>$strApiType, 'strUsername'=>$strUsername, 'strPassword'=>$strPassword, 'boolDebug'=>$debug));
  }

  function getMessages($since_id=0) {
    $api=$this->_api->showApi();
    $this->doDebug("getMessages($since_id); ({$api['strApiBase']} -> {$api['strUsername']})");
    if($this->_last_run<=strtotime("-1 minute")) {
      $this->_last_run=strtotime("now");
      $msgs=array();
      $omb_inbox=$this->_api->get_inbox($since_id);
      if(count($omb_inbox)>0) {
        foreach($omb_inbox as $timestamp=>$inbox_items) {
          foreach($inbox_items as $inbox_item) {
            if(strApiType!='twitter') {$account=$inbox_item['service_base'] . '/user/' . $inbox_item['person_id'];} else {$account=$inbox_item['service_base'] . '/' . $inbox_item['screen_name'];}
            $msgs[]=array('microblog_account'=>$account, 
                          'microblog_name'=>$inbox_item['real_name'], 
                          'text'=>$inbox_item['text'], 
                          'id'=>$inbox_item['message_id']);
          }
        }
      }
      $this->doDebug("Returns: " . print_r($msgs, TRUE), 2);
      return $msgs;
    } else {
      $this->doDebug("Returns nothing - Paused for rate limit concerns", 2);
      return array();
    }
  }

  function sendMessages($strMessage='', $a='', $b='') {
    $this->doDebug("sendMessages('$strMessage');");
    return($this->_api->send_message($strMessage));
  }
}

class GetTwitterAPI extends GetBaseAPI {
  function get_home_timeline($since_id=0) {
    $this->doDebug("get_home_timeline($since_id)");
    $timeline=$this->get_data($this->cx_data['strApiBase'] . "/statuses/home_timeline.json?since_id={$since_id}&count=" . $this->cx_data['count'], $this->cx_data['strUsername'] . ":" . $this->cx_data['strPassword']);
    if($timeline==FALSE) {return FALSE;} else {
      $objData=json_decode($timeline);
      $data=$this->recastAsArray($objData);
      if(is_array($data) and count($data)>0) {
        $arrApiBase=parse_url($this->cx_data['strApiBase']);
        foreach($data as $entry) {
          $timestamp=strtotime($entry['created_at']);
          if(strtotime("-2 days")<$timestamp) {
            if($entry['in_reply_to_status_id']!='') {
              if($this->cx_data['strApiType']=='twitter') {
                $in_reply_to="/{$entry['in_reply_to_screen_name']}/status/{$entry['in_reply_to_status_id']}";
              } else {
                $in_reply_to="/notice/{$entry['in_reply_to_status_id']}";
              }
            } else {$in_reply_to='';}
            $return[$timestamp][]=array('script_source'=>'home_timeline',
                                        'message_id'=>$entry['id'],
                                        'service_base'=>$arrApiBase['scheme'] . "://" . $arrApiBase['host'],
                                        'person_id'=>$entry['user']['id'],
                                        'screen_name'=>$entry['user']['screen_name'], 
                                        'following'=>$entry['user']['following'],
                                        'text'=>$entry['text'],
                                        'created_at'=>$entry['created_at'],
                                        'in_reply_to_update'=>$in_reply_to,
                                        'in_reply_to_screen_name'=>"@" . $entry['in_reply_to_screen_name']);
          }
        }
        return($return);
      } else {return FALSE;}
    }
  }

  function get_mentions($since_id=0) {
    $this->doDebug("get_mentions($since_id)");
    $timeline=$this->get_data($this->cx_data['strApiBase'] . "/statuses/mentions.json?since_id={$since_id}&count=" . $this->cx_data['count'], $this->cx_data['strUsername'] . ":" . $this->cx_data['strPassword']);
    if($timeline==FALSE) {return FALSE;} else {
      $objData=json_decode($timeline);
      $data=$this->recastAsArray($objData);
      if(is_array($data) and count($data)>0) {
        $arrApiBase=parse_url($this->cx_data['strApiBase']);
        foreach($data as $entry) {
          $timestamp=strtotime($entry['created_at']);
          if(strtotime("-2 days")<$timestamp) {
            if($entry['in_reply_to_status_id']!='') {
              if($this->cx_data['strApiType']=='twitter') {
                $in_reply_to="/{$entry['in_reply_to_screen_name']}/status/{$entry['in_reply_to_status_id']}";
              } else {
                $in_reply_to="/notice/{$entry['in_reply_to_status_id']}";
              }
            } else {$in_reply_to='';}
            $return[$timestamp][]=array('script_source'=>'mentions',
                                        'message_id'=>$entry['id'], 
                                        'service_base'=>$arrApiBase['scheme'] . "://" . $arrApiBase['host'],
                                        'person_id'=>$entry['user']['id'],
                                        'screen_name'=>$entry['user']['screen_name'], 
                                        'following'=>$entry['user']['following'],
                                        'text'=>$entry['text'],
                                        'created_at'=>$entry['created_at'],
                                        'in_reply_to_update'=>$in_reply_to,
                                        'in_reply_to_screen_name'=>"@" . $entry['in_reply_to_screen_name']);
          }
        }
        return($return);
      } else {return FALSE;}
    }
  }

  function get_inbox($since_id=0) {
    $this->doDebug("get_inbox($since_id)");
    $timeline=$this->get_data($this->cx_data['strApiBase'] . "/direct_messages.json?since_id={$since_id}&count=" . $this->cx_data['count'], $this->cx_data['strUsername'] . ":" . $this->cx_data['strPassword']);
    if($timeline==FALSE) {return FALSE;} else {
      $objData=json_decode($timeline);
      $data=$this->recastAsArray($objData);
      if(is_array($data) and count($data)>0) {
        $arrApiBase=parse_url($this->cx_data['strApiBase']);
        foreach($data as $entry) {
          $timestamp=strtotime($entry['created_at']);
          if(strtotime("-2 days")<$timestamp) {
            $return[$timestamp][]=array('script_source'=>'inbox',
                                        'message_id'=>$entry['id'],
                                        'service_base'=>$arrApiBase['scheme'] . "://" . $arrApiBase['host'],
                                        'person_id'=>$entry['sender']['id'],
                                        'screen_name'=>$entry['sender']['screen_name'], 
                                        'real_name'=>$entry['sender']['name'],
                                        'following'=>$entry['sender']['following'],
                                        'text'=>$entry['text'],
                                        'created_at'=>$entry['created_at']);
          }
        }
        return($return);
      } else {return array();}
    }
  }

  function get_friends() {
    $this->doDebug("get_friends()");
    $friends=$this->get_data($this->cx_data['strApiBase'] . "/statuses/friends.json", $this->cx_data['strUsername'] . ":" . $this->cx_data['strPassword']);
    if($friends==FALSE) {return FALSE;} else {
      $objData=json_decode($friends);
      $data=$this->recastAsArray($objData);
      if(is_array($data) and count($data)>0) {
        foreach($data as $entry) {
          $return[]=$entry;
        }
      }
      return($return);
    }
  }

  function get_followers() {
    $this->doDebug("get_followers()");
    $followers=$this->get_data($this->cx_data['strApiBase'] . "/statuses/followers.json", $this->cx_data['strUsername'] . ":" . $this->cx_data['strPassword']);
    if($followers==FALSE) {return FALSE;} else {
      $objData=json_decode($followers);
      $data=$this->recastAsArray($objData);
      if(is_array($data) and count($data)>0) {
        foreach($data as $entry) {
          $return[]=$entry;
        }
      }
      return($return);
    }
  }

  function send_message($strMessage) {
    $this->doDebug("sendMessage('$strMessage')");
    return($this->post_data($this->cx_data['strApiBase'] . "/statuses/update.xml", $this->cx_data['strUsername'] . ":" . $this->cx_data['strPassword'], array('status'=>$strMessage)));
  }

  function getMergedTimeline() {
    $this->doDebug("getMergedTimeline()");
    $combined_timelines=array();
    $timeline=$this->get_home_timeline();
    if(is_array($timeline) and count($timeline)>0) {
      foreach($timeline as $timestamp=>$entries) {foreach($entries as $entry) {$combined_timelines[$timestamp][]=$entry;}}
    }
    $timeline=$this->get_mentions();
    if(is_array($timeline) and count($timeline)>0) {
      foreach($timeline as $timestamp=>$entries) {foreach($entries as $entry) {$combined_timelines[$timestamp][]=$entry;}}
    }
    $timeline=$this->get_inbox();
    if(is_array($timeline) and count($timeline)>0) {
      foreach($timeline as $timestamp=>$entries) {foreach($entries as $entry) {$combined_timelines[$timestamp][]=$entry;}}
    }
    krsort($combined_timelines);
    foreach($combined_timelines as $entries) {foreach($entries as $entry) {$timeline[]=$entry;}}
    return($timeline);
  }

}

abstract class GetBaseAPI extends GenericBaseClass {
  protected $cx_data=array('count'=>100);
  function __construct($cx_data=array()) {
    foreach($cx_data as $key=>$value) {
      $this->cx_data[$key]=$value;
    }
    if($cx_data['boolDebug']>0) {$this->setDebug($cx_data['boolDebug']);}
  }

  function showApi() {return(array('strApiBase'=>$this->cx_data['strApiBase'], 'strUsername'=>$this->cx_data['strUsername']));}

  protected function get_data($url, $userpass='') {
    $this->doDebug("get_data('$url', '$userpass')");
    $curl=curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    if($userpass!='') {curl_setopt($curl, CURLOPT_USERPWD, $userpass);}
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_FAILONERROR, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
    $data=curl_exec($curl);
    curl_close($curl);

    if(!$data) {echo "No Data!"; return FALSE;} else {if($data === FALSE) {echo "Error!"; return FALSE;} else {return $data;}}
  }

  protected function post_data($url, $userpass='', $post_data=array()) {
    $this->doDebug("post_data('$url', '$userpass', '" . print_r($post_data, TRUE) . "')");
    $data='';
    foreach($post_data as $key=>$value) {
      if($data!='') {$data.='&';}
      $data.="$key=" . urlencode($value);
    }
    $curl=curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    if($userpass!='') {curl_setopt($curl, CURLOPT_USERPWD, $userpass);}
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_FAILONERROR, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    $data=curl_exec($curl);
    curl_close($curl);

    if(!$data) {echo "No Data!"; return FALSE;} else {if($data === FALSE) {echo "Error!"; return FALSE;} else {return TRUE;}}
  }

  function recastAsArray($objArray) {
    foreach($objArray as $key=>$obj) {
      if(is_object($obj)) {$return[$key]=(array) $this->recastAsArray($obj);} else {$return[$key]=$obj;}
    }
    return($return);
  }
}

?>
