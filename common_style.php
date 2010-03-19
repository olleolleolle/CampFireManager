<?php
/*******************************************************
 * CampFireManager
 * File defining the common style.
 * Version 0.5 2010-03-19 JonTheNiceGuy
 *******************************************************
 * Version History
 * 0.5 2010-03-19 Migrated from personal SVN store
 * at http://spriggs.org.uk/jon_code/CampFireManager
 * where all prior versions are stored to Google Code at
 * http://code.google.com/p/campfiremanager/
 ******************************************************/

$__campfire=array();
// Connect to the databases
require_once("db.php");

$column_widths=100/count($Camp_DB->times);
$row_heights=100/(count($Camp_DB->rooms)+1);

?>
* {font-family: verdana,sans-serif;}
body {font-size:10px; vertical-align:middle; text-align:center;}
.HelpInfo {height:50; width:100%;}
.headerbar {font-weight:bold; font-size:30px; text-align:center;}
.numberbar, .ombbar, .webbar, .menubar, .CommandInfo {font-size:15px; text-align:center;}
.mainbody {width:100%;}
table.WholeDay {font-size:10px; width:100%;height:80%;border-collapse:collapse;}
table.WholeDay,th.Time,td.Entry {border: 1px dotted black;}
td.Entry {width:<?php echo $column_widths; ?>%;}
tr.entry {height:<?php echo $row_heights; ?>%;}
.EntryBody {width:100%; height:100%;}
.TalkID {vertical-align:top;}
.TalkTitle {font-size:13px;text-align:center;}
.Presenter {text-align:right;font-size:9px;}
.Contact {display:none;}
.Location {font-size:11px;text-align:right;}
.Fixed {color:Red;}
.Count {font-size:9px;}
.Label {font-size:9px;}
.Long {background-color:Silver;}
.Time_title {background-color:Gray;}
.Next {background-color:LightBlue;}
.Now {background-color:Yellow;}
div#verify-form {padding: .5em; border: 1px solid #777777; background: #dddddd; margin-top: 1em; padding-bottom: 0em;}
.alert {border: 1px solid #e7dc2b; background: #fff888; }
.success {border: 1px solid #669966; background: #88ff88; }
.error {border: 1px solid #ff0000; background: #ffaaaa; }
table.outer {width:100%; height:80%;}
tr.outer {vertical-align:middle; text-align:center; height:33%}
td.outer {vertical-align:middle; text-align:center; width:33%}
table.inner {width:100%; height:100%;}
small {height:32px;}
UL-ARROW, UR-ARROW, L-ARROW, R-ARROW, DL-ARROW, DR-ARROW {width:32px;}
UL-ARROW, UR-ARROW, U-ARROW {vertical-align:top;}
DL-ARROW, DR-ARROW, D-ARROW {vertical-align:bottom;}
td.TEXT {vertical-align:middle; text-align:center; width:100%;}
tr.inner {vertical-align:middle; text-align:center;}
.Show {display:visible; text-decoration: underline;}
.Hide {display:none;}
.TopRightCorner {position:absolute; top:0; right:0;}
.DrawAttention {background-color:#FF3333; color:White;}
.Times {background-color:#FFA078;}
