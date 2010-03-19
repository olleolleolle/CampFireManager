CREATE TABLE `account_microblog` (
  `intMbID` int(11) NOT NULL AUTO_INCREMENT,
  `strAccount` varchar(255) NOT NULL,
  `strApiBase` varchar(255) NOT NULL,
  `strPassword` varchar(255) NOT NULL,
  `intLastMessage` bigint(20) NOT NULL,
  PRIMARY KEY (`intMbID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `account_phones` (
  `intPhoneID` int(11) NOT NULL AUTO_INCREMENT,
  `strPhone` varchar(255) NOT NULL,
  `strNumber` varchar(255) NOT NULL,
  `intSignal` int(11) NOT NULL,
  `strGammuRef` varchar(255) NOT NULL,
  PRIMARY KEY (`intPhoneID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `attendees` (
  `intAttendID` int(11) NOT NULL AUTO_INCREMENT,
  `intTalkID` int(11) NOT NULL,
  `intPersonID` int(11) NOT NULL,
  PRIMARY KEY (`intAttendID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `config` (
  `strConfig` varchar(255) NOT NULL,
  `strValue` text NOT NULL,
  PRIMARY KEY (`strConfig`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `people` (
  `intPersonID` int(11) NOT NULL AUTO_INCREMENT,
  `strPhoneNumber` varchar(20) NOT NULL,
  `strName` varchar(255) NOT NULL,
  `strContactInfo` varchar(255) NOT NULL,
  `strDefaultReply` varchar(100) NOT NULL,
  `strOpenID` text NOT NULL,
  `strMicroBlog` varchar(255) NOT NULL,
  `strAuthString` varchar(25) NOT NULL,
  `boolIsAdmin` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`intPersonID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `rooms` (
  `intRoomID` int(11) NOT NULL AUTO_INCREMENT,
  `strRoom` varchar(255) NOT NULL,
  `intCapacity` int(11) NOT NULL,
  PRIMARY KEY (`intRoomID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `room_directions` (
  `intDirectionID` int(11) NOT NULL AUTO_INCREMENT,
  `intScreenID` int(11) NOT NULL,
  `intDestRoomID` int(11) NOT NULL,
  `intDirectionURDL` enum('U','R','D','L','UR','DR','DL','UL') NOT NULL,
  PRIMARY KEY (`intDirectionID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `screens` (
  `intScreenID` int(11) NOT NULL AUTO_INCREMENT,
  `strHostname` varchar(255) NOT NULL,
  PRIMARY KEY (`intScreenID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `sms_screen` (
  `intUpdateID` int(11) NOT NULL AUTO_INCREMENT,
  `intPersonID` int(11) NOT NULL,
  `strMessage` text NOT NULL,
  `datInsert` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`intUpdateID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `talks` (
  `intTalkID` int(11) NOT NULL AUTO_INCREMENT,
  `intTimeID` int(11) NOT NULL,
  `datTalk` date NOT NULL,
  `intRoomID` int(11) NOT NULL,
  `intPersonID` int(11) NOT NULL,
  `strTalkTitle` text NOT NULL,
  `boolFixed` tinyint(1) NOT NULL,
  `intLength` tinyint(1) NOT NULL,
  PRIMARY KEY (`intTalkID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `times` (
  `intTimeID` int(11) NOT NULL AUTO_INCREMENT,
  `strTime` varchar(100) NOT NULL,
  PRIMARY KEY (`intTimeID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
