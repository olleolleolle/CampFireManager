<?php

require_once($base_dir . "common_functions.php");

abstract class GenericBaseClass {
  // SQL Data
  protected $resource;
  protected $prefix;

  protected $_intDebug;

  protected $_strDebug;

  function __construct($db_host, $db_user, $db_pass, $db_base, $db_prefix='', $debug=0) {
    $this->resource=mysql_connect($db_host, $db_user, $db_pass);
    mysql_select_db($db_base, $this->resource);
    if($db_prefix!='') {$this->prefix=$db_prefix . '_';}

    $this->_intDebug=$debug;
  }

  function setDebug($state=0) {$this->_intDebug=$state;}

  function doDebug($message, $level=1) {if($this->_intDebug>=$level) {if(isset($_SERVER['SERVER_NAME'])) {echo "<!-- DBG: $message -->\r\n";} else {echo $message . "\r\n";}}}

  //Perform an SQL Query and return an array of the results
  function qryArray($sql, $index='') {
    $this->doDebug("SQL: ($index) # $sql # ");
    $query=mysql_query($sql, $this->resource);
    if(mysql_err_no>0 OR $query===FALSE) {
      $this->doDebug("ERR: " . mysql_error($this->resource) . "\r\n");
      return FALSE;
    } 
    $result=array();
    if(mysql_num_rows($query)>0) {
      while($data=mysql_fetch_array($query)) {if($index!='') {$result[$data[$index]]=$data;} else {$result[]=$data;}}
    }
    $this->doDebug(" (" . count($result) . ")\r\n");
    return($result);
  }

  //Perform an SQL query and return a key->data pair result
  function qryMap($index, $data, $table, $group=array(), $limit='') {
    $sql="SELECT $index, $data FROM $table";
    if(is_array($group)) {$sql.=" GROUP BY $index";} elseif($group!='') {$sql.=" GROUP BY $group";}
    if($limit!='') {$sql.=" $limit";}
    $this->doDebug("SQL: # $sql # ");
    $query=mysql_query($sql, $this->resource);
    if(mysql_err_no!=0) {
      $this->doDebug("ERR: " . mysql_error($this->resource) . "\r\n");
      return FALSE;
    }
    $result=array();
    if(mysql_num_rows($query)>0) {
      while(list($idx, $value)=mysql_fetch_array($query)) {$result[$idx]=$value;}
    }
    $this->doDebug(" (" . count($result) . ")\r\n");
    return($result);
  }

  function boolUpdateOrInsertSql($sql='') {
    if($sql!='') {
      $this->doDebug("SQL: # $sql # ");
      mysql_query($sql, $this->resource);
      if(mysql_errno($this->resource)>0) {
        $this->doDebug("ERR: " . mysql_error($this->resource) . "\r\n");
        return FALSE;
      }
      $this->doDebug("ROW: " . mysql_affected_rows($this->resource) . "\r\n");
      if(mysql_affected_rows($this->resource)==0) {return FALSE;}
      return TRUE;
    } else {
      $this->doDebug("SQL: #  #\r\n");
      return FALSE;
    }
  }

  function escape($string='') {return mysql_real_escape_string($string, $this->resource);}

  function getInsertID() {return mysql_insert_id($this->resource);}
}
?>
