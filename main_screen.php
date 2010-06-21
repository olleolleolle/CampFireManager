<?php
/*******************************************************
 * CampFireManager
 * Main presentation screen to show what's happening
 * on the day.
 * Version 0.5 2010-03-19 JonTheNiceGuy
 *******************************************************
 * Version History
 * 0.5 2010-03-19 Migrated from personal SVN store
 * at http://spriggs.org.uk/jon_code/CampFireManager
 * where all prior versions are stored to Google Code at
 * http://code.google.com/p/campfiremanager/
 ******************************************************/

if(file_exists("libraries/GenericBaseClass.php")) {$base_dir='libraries/';} else {$base_dir='';}
// Connect to the databases
require_once("db.php");
// Add functions for below rendering and load times and rooms to $times and $rooms
require_once("{$base_dir}common_functions-template.php");
// Add xajax script
require_once("{$base_dir}common_xajax.php");
?>
<html>
<head><title><?php echo $Camp_DB->config['event_title']; ?></title>
<link rel="stylesheet" type="text/css" href="common_style.php" />
<style type="text/css">.Contact {display:none;}</style>
<script type="text/javascript" src="external/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="external/jquery.marquee.js"></script>
<script type="text/javascript" src="external/jquery.clock.js"></script>
<?php $xajax->printJavascript(); ?>
<script type="text/javascript">
  $(function($) {
    $('.clock').jclock();
  });
  function update() {
    xajax_ajaxPopulateTable();     
    setTimeout("update()", 10000);
  }
</script>
</head>
<body>
<h1 class="headerbar"><?php echo $Camp_DB->config['event_title']; ?></h1><span class="clock TopRightCorner"></span>

<?php
echo renderHelp($Camp_DB, TRUE);
?>
  <div id="debug"></div>

  <div id="mainbody" class="mainbody"></div>
  <div id="sms_list" class="sms_list"></div>

<script type="text/javascript">update();</script>
</body>
</html>
