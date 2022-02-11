SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

CREATE DATABASE IF NOT EXISTS `feed` /*!40100 DEFAULT CHARACTER SET utf8 */;
/*CREATE USER 'feed'@'%' IDENTIFIED BY '1';*/
GRANT ALL PRIVILEGES ON feed.* TO 'feed'@'%' IDENTIFIED BY '1';
FLUSH PRIVILEGES;
USE `feed`;

CREATE TABLE IF NOT EXISTS `preferences` (
  `key` varchar(32) CHARACTER SET ascii NOT NULL,
  `value` varchar(255) CHARACTER SET armscii8 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `preferences` (`key`, `value`) VALUES
('show_anonymous',	'true');

CREATE TABLE IF NOT EXISTS `tokens` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `token` varchar(32) NOT NULL,
  `uid` int(10) unsigned NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  CONSTRAINT `tokens_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `users` (`uid`) ON DELETE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `twits` (
  `tid` int(11) NOT NULL AUTO_INCREMENT,
  `author` char(32) NOT NULL,
  `text` text NOT NULL,
  `sign` char(64) DEFAULT NULL,
  `nonce` char(32) DEFAULT NULL,
  `ptime` int(11) NOT NULL,
  PRIMARY KEY (`tid`),
  KEY `sign` (`sign`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `users` (
  `uid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `login` varchar(32) NOT NULL,
  `pass` varchar(32) NOT NULL,
  `role` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `login` (`login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

