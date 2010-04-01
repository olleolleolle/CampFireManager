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
  if($contact_methods['web']!='') {$return.="<div class=\"webbar\"><span class=\"Label\">Website:</span> <span class=\"Data\">{$contact_methods['web']}</span></div>\r\n<div class=\"webbar\"><span class=\"Label\">Mobile site:</span> <span class=\"Data\">{$contact_methods['web']}m/</span></div>\r\n";}
  if(isset($class_pointer->config['hashtag']) and $class_pointer->config['hashtag']!='') {$return.="<div class=\"hashtag\"><span class=\"Label\">Event Hashtag:</span> <span class=\"Data\">{$class_pointer->config['hashtag']}</span></div>\r\n";}
  if($marquee!='') {$return.="<div class=\"CommandInfo\">" . nl2br($display_commands) . "</div>\r\n</marquee>\r\n";} else {$return.="<div class=\"CommandInfo Header\">Command Information: <span class=\"CommandInfo Show\">Click here to expand your options</span><span class=\"CommandInfo Hide\">$display_commands</span></div>\r\n</div>\r\n";}
  return($return);
}
