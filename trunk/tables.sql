-- phpMyAdmin SQL Dump
-- version 2.9.1.1-Debian-3
-- http://www.phpmyadmin.net
-- 
-- Servidor: localhost
-- Tiempo de generación: 15-03-2007 a las 03:37:19
-- Versión del servidor: 5.0.32
-- Versión de PHP: 5.2.0-8
-- 
-- Base de datos: `apf_test`
-- 

-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `vid_categ`
-- 
-- Creación: 08-03-2007 a las 10:47:39
-- Última actualización: 15-03-2007 a las 03:30:05
-- Última revisión: 12-03-2007 a las 14:15:17
-- 

DROP TABLE IF EXISTS `vid_categ`;
CREATE TABLE IF NOT EXISTS `vid_categ` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `parent` int(10) unsigned NOT NULL default '1',
  `name_id` int(10) unsigned NOT NULL default '0',
  `desc_id` int(10) unsigned NOT NULL default '0',
  `count` int(10) unsigned NOT NULL default '0',
  `last` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `created` timestamp NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  KEY `parent` (`parent`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `vid_descs`
-- 
-- Creación: 12-03-2007 a las 14:14:44
-- Última actualización: 15-03-2007 a las 03:30:05
-- 

DROP TABLE IF EXISTS `vid_descs`;
CREATE TABLE IF NOT EXISTS `vid_descs` (
  `id` int(10) NOT NULL auto_increment,
  `lan` enum('en','es','ca') NOT NULL default 'en',
  `desc` varchar(200) NOT NULL,
  PRIMARY KEY  (`id`,`lan`),
  KEY `lan` (`lan`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `vid_mfs`
-- 
-- Creación: 08-03-2007 a las 10:48:04
-- Última actualización: 15-03-2007 a las 03:30:17
-- 

DROP TABLE IF EXISTS `vid_mfs`;
CREATE TABLE IF NOT EXISTS `vid_mfs` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `ctg` int(10) unsigned NOT NULL default '0',
  `name_id` int(10) unsigned NOT NULL default '0',
  `desc_id` int(10) unsigned NOT NULL default '0',
  `prev` varchar(200) NOT NULL,
  `dur` int(10) unsigned NOT NULL default '0',
  `url` varchar(200) NOT NULL,
  `last` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `created` timestamp NOT NULL default '0000-00-00 00:00:00',
  `hits` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `vid_names`
-- 
-- Creación: 12-03-2007 a las 14:14:57
-- Última actualización: 15-03-2007 a las 03:30:05
-- 

DROP TABLE IF EXISTS `vid_names`;
CREATE TABLE IF NOT EXISTS `vid_names` (
  `id` int(10) NOT NULL auto_increment,
  `lan` enum('en','es','ca') NOT NULL default 'en',
  `name` varchar(20) NOT NULL,
  PRIMARY KEY  (`id`,`lan`),
  KEY `lan` (`lan`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

-- 
-- Estructura de tabla para la tabla `vid_users`
-- 
-- Creación: 17-02-2007 a las 00:14:46
-- Última actualización: 15-03-2007 a las 03:09:24
-- 

DROP TABLE IF EXISTS `vid_users`;
CREATE TABLE IF NOT EXISTS `vid_users` (
  `uid` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(30) NOT NULL,
  `password` varchar(32) NOT NULL,
  `admin` tinyint(4) NOT NULL default '0',
  `last` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `hash` varchar(40) NOT NULL,
  PRIMARY KEY  (`uid`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

