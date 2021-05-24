CREATE TABLE IF NOT EXISTS `sotbit_auth_message` (
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
CREATE TABLE IF NOT EXISTS `sotbit_auth_statistics` (
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
CREATE TABLE IF NOT EXISTS `sotbit_auth_user_confirm` (
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
CREATE TABLE IF NOT EXISTS `sotbit_auth_buyer_confirm` (
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
CREATE TABLE IF NOT EXISTS `sotbit_auth_file` (
  `BUYER_ID` int(11) NOT NULL,
  `FILE_ID` int(11) NOT NULL,
  PRIMARY KEY (`BUYER_ID`,`FILE_ID`)
);