-- 
-- Table structure for table `legal_accepted`
-- 

CREATE TABLE `legal_accepted` (
  `legal_id` int(10) NOT NULL auto_increment,
  `uid` int(10) NOT NULL default '0',
  `tc_id` int(10) NOT NULL default '0',
  `accepted` int(11) NOT NULL default '0',
  PRIMARY KEY  (`legal_id`),
  KEY `uid` (`uid`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `legal_conditions`
-- 

CREATE TABLE `legal_conditions` (
  `tc_id` int(10) NOT NULL auto_increment,
  `conditions` longtext NOT NULL,
  `date` int(11) NOT NULL default '0',
  KEY `tc_id` (`tc_id`)
) TYPE=MyISAM;
