CREATE TABLE IF NOT EXISTS `kit_auth_message` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `EVENT_NAME` varchar(255) NOT NULL,
  `ID_MESSAGE` int(11) NOT NULL,
  `LID` char(2) NOT NULL,
  `DATE_CREATE` datetime NOT NULL,
  `EMAIL_TO` varchar(255) NOT NULL,
  `ID_USER` int(11) NOT NULL,
  `HASH` char(15) DEFAULT NULL,
  `DATE_ENTRANCE` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`)
);
CREATE TABLE IF NOT EXISTS `kit_auth_statistics` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `OPEN_MESSAGE` varchar(255) NOT NULL,
  `ID_MESSAGE` int(11) NOT NULL,
  `MESSAGE_TRANSITION` varchar(255) NOT NULL,
  `IP` varchar(255) NOT NULL,
  `DEVICE` varchar(255) NOT NULL,
  `EVENT_NAME` varchar(255) NOT NULL,
  `EVENT_TEMPLATE` varchar(255) NOT NULL,
  `LOCATION` varchar(255) NOT NULL,
  `ID_USER` int(11) NOT NULL,
  `DATE_CREATE` datetime NOT NULL,
  `DATE_OPEN` datetime NOT NULL,
  PRIMARY KEY (`ID`)
);
CREATE TABLE IF NOT EXISTS `kit_auth_user_confirm` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_USER` int(11) NULL,
  `LID` char(2) NOT NULL,
  `EMAIL` varchar(255) NOT NULL,
  `FIELDS` text NULL,
  `DATE_CREATE` datetime NOT NULL,
  `DATE_UPDATE` datetime,
  `STATUS` boolean default NULL,
  PRIMARY KEY (`ID`)
);
CREATE TABLE IF NOT EXISTS `kit_auth_buyer_confirm` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_USER` int(11) NULL,
  `LID` char(2) NOT NULL,
  `EMAIL` varchar(255) NOT NULL,
  `INN` varchar(255) NULL,
  `FIELDS` text NULL,
  `DATE_CREATE` datetime NOT NULL,
  `DATE_UPDATE` datetime,
  `STATUS` boolean default NULL,
  PRIMARY KEY (`ID`)
);
CREATE TABLE IF NOT EXISTS `kit_auth_file` (
  `BUYER_ID` int(11) NOT NULL,
  `FILE_ID` int(11) NOT NULL,
  PRIMARY KEY (`BUYER_ID`,`FILE_ID`)
);
CREATE TABLE IF NOT EXISTS `kit_auth_company` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `NAME` varchar(255) NOT NULL,
  `HASH` varchar(255) NULL,
  `BUYER_TYPE` int(11) NOT NULL,
  `DATE_CREATE` datetime NOT NULL,
  `DATE_UPDATE` datetime,
  `ACTIVE` varchar(1) NOT NULL default 'Y',
  `STATUS` varchar(1) NOT NULL default 'M',
  PRIMARY KEY (`ID`)
);
CREATE TABLE IF NOT EXISTS `kit_auth_company_props_value` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `COMPANY_ID` int(11) NOT NULL,
  `PROPERTY_ID` int(11) NOT NULL,
  `NAME` varchar(255) NOT NULL,
  `VALUE` varchar(500) NULL,
  PRIMARY KEY (`ID`)
);
CREATE TABLE IF NOT EXISTS `kit_auth_roles` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `CODE` varchar(255) NOT NULL,
  `NAME` varchar(500) NULL,
  PRIMARY KEY (`ID`)
);
CREATE TABLE IF NOT EXISTS `kit_auth_staff` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `USER_ID` int(11) NOT NULL,
  `COMPANY_ID` int(11) NOT NULL,
  `ROLE` varchar(255) NOT NULL,
  `STATUS` varchar(1) NOT NULL default 'N',
  PRIMARY KEY (`ID`)
);
CREATE TABLE IF NOT EXISTS `kit_auth_company_confirm` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_USER` int(11) NULL,
  `LID` char(2) NOT NULL,
  `FIELDS` text NULL,
  `DATE_CREATE` datetime NOT NULL,
  `STATUS` boolean default NULL,
  `COMPANY_ID` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
);