<?php
/*******************************************************
 * CampFireManager
 * Establish initial database connections and define
 * where the gammu_smsd database is stored for other
 * files to load
 * Version 0.5 2010-03-19 JonTheNiceGuy
 *******************************************************
 * Version History
 * 0.5 2010-03-19 Migrated from personal SVN store
 * at http://spriggs.org.uk/jon_code/CampFireManager
 * where all prior versions are stored to Google Code at
 * http://code.google.com/p/campfiremanager/
 ******************************************************/

//Database Connection Values
$db_CampFire=array(
  "host"=>"localhost", 
  "user"=>"CampFire", 
  "pass"=>"CampFire", 
  "base"=>"CampFire",
  "prefix"=>""
);
$db_Phone=array(
  "host"=>"localhost", 
  "user"=>"gammu", 
  "pass"=>"gammu", 
  "base"=>"gammu"
);

//Load class files
if(!isset($base_dir)) {if(file_exists("libraries/GenericBaseClass.php")) {$base_dir='libraries/';} else {$base_dir='';}}
require_once("{$base_dir}Camp_DB_Test.php");
require_once("{$base_dir}SmsSource.php");
require_once("{$base_dir}OmbSource.php");

//Initialize Class
if(!is_array($__campfire)) {$__campfire=array();}
$Camp_DB=new Camp_DB($db_CampFire['host'], $db_CampFire['user'], $db_CampFire['pass'], $db_CampFire['base'], $db_CampFire['prefix'], $__campfire, $debug);
