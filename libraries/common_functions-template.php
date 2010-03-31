<?php
/*******************************************************
 * CampFireManager
 * Common Template, however, this may be superceeded by
 * /common_style.php
 * Version 0.5 2010-03-19 JonTheNiceGuy
 *******************************************************
 * Version History
 * 0.5 2010-03-19 Migrated from personal SVN store
 * at http://spriggs.org.uk/jon_code/CampFireManager
 * where all prior versions are stored to Google Code at
 * http://code.google.com/p/campfiremanager/
 ******************************************************/

if(!isset($Camp_DB)) {die("You need to bring in the class which handles the database first");}

// Get Time & Room information
$times=$Camp_DB->getTimes();
$rooms=$Camp_DB->getRooms();

if(count($times)>0 and count($rooms)>0) {
  $column_widths=100/count($times);
  $row_heights=100/(count($rooms)+1);
}

// This handles all the template information

$commonStyle="* {font-family: verdana,sans-serif;}
body {font-size:10px; vertical-align:middle; text-align:center;}
.HelpInfo {height:50; width:100%;}
.headerbar {font-weight:bold; font-size:30px; text-align:center;}
.numberbar, .ombbar, .webbar, .menubar, .CommandInfo {font-size:15px; text-align:center;}
.mainbody {width:100%;}
table.WholeDay {font-size:10px; width:100%;height:80%;border-collapse:collapse;}
table.WholeDay,th.Time,td.Entry {border: 1px dotted black;}
td.Entry {width:{$column_widths}%;}
tr.entry {height:{$row_heights}%;}
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
";

function renderHelp($class_pointer, $showWeb=FALSE, $marquee='') {
  $class_pointer->doDebug("renderHelp();");
  $display_commands="Identify with this service by sending 
".                  "<b>I &lt;your name&gt; [email:your@email.address] [http://your.web.site]</b>
".                  "(there are more options for identification by going to the website)
".                  "Propose a talk by sending <b>P &lt;Time Slot&gt; &lt;Slots Used&gt; &lt;Talk Title&gt;</b>
".                  "Cancel a talk by sending <b>C &lt;Talk Number&gt; &lt;Time Slot&gt; [Reason]</b>
".                  "Rename a talk by sending <b>E &lt;Talk Number&gt; &lt;Time Slot&gt; &lt;New Talk Title&gt;</b>
".                  "Attend a talk by sending <b>A &lt;Talk Number&gt;</b>
".                  "Decline the attendance of a talk by sending <b>R &lt;Talk Number&gt;</b>
".                  "Note: You can combine multiple A and R commands in one message. 
".                  "Statements surrounded with &lt;&gt; are mandatory options, those statements surrounded with [] are optional.
".                  "These commands should be sent to your preferred ";
  $contact_methods=$class_pointer->getAllConnectionMethods();
  $class_pointer->doDebug("contact_methods: " . var_export($contact_methods, TRUE) . "", 2);
  if($contact_methods['tel']!='') {$display_commands.="mobile service";}
  if($contact_methods['tel']!='' AND $contact_methods['omb']!='') {$display_commands.=" or ";}
  if($contact_methods['omb']!='') {$display_commands.="microblogging service";}
  $display_commands.=" listed above.";

  if($marquee=='') {$marquee='<marquee class="HelpInfo" behaviour="scroll" scrollamount="0.5" direction="up">';}
  if($marquee!='') {$return="$marquee\r\n";} else {$return="<div class=\"HelpInfo\">\r\n";}
  if($contact_methods['tel']!='') {$return.="<div class=\"numberbar\"><span class=\"Label\">Phones:</span> <span class=\"Data\">{$contact_methods['tel']}</span></div>\r\n";}
  if($contact_methods['omb']!='') {$return.="<div class=\"ombbar\"><span class=\"Label\">Microblogging:</span> <span class=\"Data\">{$contact_methods['omb']}</span></div>\r\n";}
  if($contact_methods['web']!='' AND $showWeb=TRUE) {$return.="<div class=\"webbar\"><span class=\"Label\">Website:</span> <span class=\"Data\">{$contact_methods['web']}</span></div>\r\n<div class=\"webbar\"><span class=\"Label\">Mobile site:</span> <span class=\"Data\">{$contact_methods['web']}m/</span></div>\r\n";}
  if($marquee!='') {$return.="<div class=\"CommandInfo\">" . nl2br($display_commands) . "</div>\r\n</marquee>\r\n";} else {$return.="<div class=\"CommandInfo Header\">Command Information: <span class=\"CommandInfo Show\">Click here to expand your options</span><span class=\"CommandInfo Hide\">$display_commands</span></div>\r\n</div>\r\n";}
  return($return);
}
