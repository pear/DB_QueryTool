# phpMyAdmin MySQL-Dump
# version 2.3.0
# http://phpwizard.net/phpMyAdmin/
# http://www.phpmyadmin.net/ (download page)
#
# Host: localhost
# Generation Time: Jan 17, 2003 at 01:37 PM
# Server version: 3.23.48
# PHP Version: 4.1.0
# Database : `test`
# --------------------------------------------------------

#
# Table structure for table `time`
#

CREATE TABLE time (
  id int(11) NOT NULL default '0',
  user_id int(11) NOT NULL default '0',
  projectTree_id int(11) NOT NULL default '0',
  task_id int(11) NOT NULL default '0',
  timestamp int(11) NOT NULL default '0',
  durationSec int(11) NOT NULL default '0',
  comment text NOT NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

#
# Dumping data for table `time`
#

INSERT INTO time VALUES (1, 3, 23, 123, 2147483647, 100, 'finishing the DB_QueryTool to be ready for PEAR');
INSERT INTO time VALUES (2, 4, 2, 232, 1112983792, 200, 'quality control :-)');
INSERT INTO time VALUES (3, 3, 0, 234, 2147483647, 21321, 'another entry, just to show the join thingy :-)');
# --------------------------------------------------------

#
# Table structure for table `user`
#

CREATE TABLE user (
  id int(11) NOT NULL default '0',
  login varchar(20) NOT NULL default '',
  name varchar(100) NOT NULL default '',
  surname varchar(100) NOT NULL default '',
  email varchar(100) NOT NULL default '',
  isAdmin tinyint(4) NOT NULL default '0',
  password varchar(32) default NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

#
# Dumping data for table `user`
#

INSERT INTO user VALUES (4, 'pp', 'Paolo', 'Panto', 'pp@visionp.de', 1, NULL);
INSERT INTO user VALUES (3, 'cain', 'Wolfram', 'Kriesing', 'wk@visionp.de', 1, '8a9d62c756bd894451e63e9a511ded0c');

