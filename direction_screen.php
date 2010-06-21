<?php
/*******************************************************
 * CampFireManager
 * Presentation Screen for placement around site
 * showing directions to and from the rooms where talks
 * occur
 * Version 0.5 2010-03-19 JonTheNiceGuy
 *******************************************************
 * Version History
 * 0.5 2010-03-19 Migrated from personal SVN store
 * at http://spriggs.org.uk/jon_code/CampFireManager
 * where all prior versions are stored to Google Code at
 * http://code.google.com/p/campfiremanager/
 ******************************************************/

if(file_exists("libraries/GenericBaseClass.php")) {$base_dir='libraries/';} else {$base_dir='';}
require_once("db.php");
// Add functions for below rendering and load times and rooms to $times and $rooms
require_once("{$base_dir}common_functions-template.php");
// Add xajax script
require_once("{$base_dir}common_xajax.php");

$directions=array("UL", "U", "UR", "L", "C", "R", "DL", "D", "DR");

// We might need to move a room's direction, or set it initially.
if(isset($_GET['setroomdir']) AND $_GET['roomno']+0>0 AND array_search($_GET['roomdir'], $directions)!==FALSE) {$Camp_DB->setDirections($_GET['roomno'], $_GET['roomdir']);}
?><html>
<head>
<title><?php echo $Camp_DB->config['event_title']; ?></title>
<script type="text/javascript" src="external/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="external/jquery.marquee.js"></script>
<script type="text/javascript" src="external/jquery.clock.js"></script>
<link rel="stylesheet" type="text/css" href="common_style.php" />
<?php $xajax->printJavascript(); ?>
<script type="text/javascript">
  $(function($) {
    $('.clock').jclock();
  });

  function update() {
    xajax_ajaxGetDirections();     
    setTimeout("update()", 10000);
  }
</script>
</head>
<body>
<h1 class="headerbar"><?php echo $Camp_DB->config['event_title']; ?></h1>
<?php echo renderHelp($Camp_DB, TRUE); ?>
<table class="outer">
  <tr class="outer">
    <td id="UL" class="outer">
      <table class="inner">
        <tr class="inner small"><td id="UL-ARROW" class="UL-ARROW"><img src="images/arrow_UL.png"></td><td class="TEXT">&nbsp;</td></TR>
        <tr class="inner big"><td class="UL-ARROW">&nbsp;</td><td id="UL-TEXT" class="TEXT">&nbsp;</td></TR>
      </table>
    </td>

    <td id="U" class="outer">
      <table class="inner">
        <tr class="inner small"><td id="U-ARROW" class="U-ARROW"><img src="images/arrow_U.png"></td></TR>
        <tr class="inner big"><td id="U-TEXT" class="TEXT">&nbsp;</td></TR>
      </table>
    </td>

    <td id="UR" class="outer">
      <table class="inner">
        <tr class="inner small"><td class="TEXT">&nbsp;</td><td id="UR-ARROW" class="UR-ARROW ARROW"><img src="images/arrow_UR.png"></td></TR>
        <tr class="inner big"><td id="UR-TEXT" class="TEXT">&nbsp;</td><td class="UR-ARROW ARROW">&nbsp;</td></TR>
      </table>
    </td>
  </tr>

  <tr class="outer">
    <td id="L" class="outer">
      <table class="inner">
        <tr class="inner onerow"><td id="L-ARROW" class="L-ARROW"><img src="images/arrow_L.png"></td><td id="L-TEXT" class="TEXT">&nbsp;</td></TR>
      </table>
    </td>

    <td id="C" class="outer Times">
      <h1 id="C-TEXT">&nbsp;</h1>
        <div class="Now">The time now is: <span class="clock" /></div>
        <div class="Next">Next talk starts at <span id="next_talk_time"></span></div>
    </td>

    <td id="R" class="outer">
      <table class="inner">
        <tr class="inner onerow"><td id="R-TEXT" class="TEXT">&nbsp;</td><td id="R-ARROW" class="R-ARROW"><img src="images/arrow_R.png"></td></TR>
      </table>
    </td>
  </tr>

  <tr class="outer">
    <td id="DL" class="outer">
      <table class="inner">
        <tr class="inner big"><td class="DL-ARROW">&nbsp;</td><td id="DL-TEXT" class="TEXT">&nbsp;</td></TR>
        <tr class="inner small"><td id="DL-ARROW" class="DL-ARROW"><img src="images/arrow_DL.png"></td><td class="TEXT">&nbsp;</td></TR>
      </table>
    </td>

    <td id="D" class="outer">
      <table class="inner">
        <tr class="inner big"><td id="D-TEXT" class="TEXT">&nbsp;</td></TR>
        <tr class="inner small"><td id="D-ARROW" class="D-ARROW"><img src="images/arrow_D.png"></td></TR>
      </table>
    </td>

    <td id="DR" class="outer">
      <table class="inner">
        <tr class="inner big"><td id="DR-TEXT" class="TEXT">&nbsp;</td><td class="DR-ARROW ARROW">&nbsp;</td></TR>
        <tr class="inner small"><td class="TEXT">&nbsp;</td><td id="DR-ARROW" class="DR-ARROW ARROW"><img src="images/arrow_DR.png"></td></TR>
      </table>
    </td>
  </tr>
</table>
<script>update();</script>
</body>
</html>
