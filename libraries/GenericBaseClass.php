<?php

require_once($base_dir . "common_functions.php");

abstract class GenericBaseClass {
  // SQL Data
  protected $resource;
  protected $prefix;

  protected $_intDebug;

  protected $_strDebug;

  function __construct($db_host, $db_user, $db_pass, $db_base, $db_prefix='', $debug=0) {
    $this->setDebug($debug);
    $this->doDebug("New " . get_class($this) . "($db_host, $db_user, $db_pass, $db_base, $db_prefix, $debug)");
    $this->resource=mysql_connect($db_host, $db_user, $db_pass);
    mysql_select_db($db_base, $this->resource);
    if($db_prefix!='') {$this->prefix=$db_prefix . '_';}

  }

  function setDebug($state=0) {
    $this->_intDebug=$state;
  }

  function doDebug($message, $level=1) {if($this->_intDebug>=$level) {if(isset($_SERVER['SERVER_NAME'])) {echo "<!-- DBG: $message -->\r\n";} else {echo get_class($this) . " - " . date("H:i:s - ") . $message . "\r\n";}}}

  //Perform an SQL Query and return an array of the results
  function qryArray($sql, $index='') {
    $this->doDebug("SQL: ($index) # $sql # ");
    $query=mysql_query($sql, $this->resource);
    if(mysql_errno($this->resource)>0 OR $query===FALSE) {
      $this->doDebug("ERR: " . mysql_error($this->resource) . "");
      return FALSE;
    } 
    $result=array();
    if(mysql_num_rows($query)>0) {
      while($data=mysql_fetch_array($query)) {if($index!='') {$result[$data[$index]]=$data;} else {$result[]=$data;}}
    }
    $this->doDebug("Number of Results: " . count($result));
    $this->doDebug("Results Data: " . print_r($result, TRUE), 2);
    return($result);
  }

  //Perform an SQL query and return a key->data pair result
  function qryMap($index, $data, $table, $group=array(), $limit='') {
    $sql="SELECT $index, $data FROM $table";
    if(is_array($group)) {$sql.=" GROUP BY $index";} elseif($group!='') {$sql.=" GROUP BY $group";}
    if($limit!='') {$sql.=" $limit";}
    $this->doDebug("SQL: # $sql # ");
    $query=mysql_query($sql, $this->resource);
    if(mysql_errno($this->resource)!=0) {
      $this->doDebug("ERR: " . mysql_error($this->resource) . "");
      return FALSE;
    }
    $result=array();
    if(mysql_num_rows($query)>0) {
      while(list($idx, $value)=mysql_fetch_array($query)) {$result[$idx]=$value;}
    }
    $this->doDebug("Number of Results: " . count($result));
    $this->doDebug("Results Data: " . print_r($result, TRUE), 2);
    return($result);
  }

  function boolUpdateOrInsertSql($sql='') {
    if($sql!='') {
      $this->doDebug("SQL: # $sql # ");
      mysql_query($sql, $this->resource);
      if(mysql_errno($this->resource)>0) {
        $this->doDebug("ERR: " . mysql_error($this->resource) . "");
        return FALSE;
      }
      $this->doDebug("ROW: " . mysql_affected_rows($this->resource) . "");
      if(mysql_affected_rows($this->resource)==0) {return FALSE;}
      return TRUE;
    } else {
      $this->doDebug("SQL: #  #");
      return FALSE;
    }
  }

  function escape($string='') {return mysql_real_escape_string($string, $this->resource);}

  function getInsertID() {return mysql_insert_id($this->resource);}
}
