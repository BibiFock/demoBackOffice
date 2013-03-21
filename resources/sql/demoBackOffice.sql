-- phpMyAdmin SQL Dump
-- version 3.5.2.2
-- http://www.phpmyadmin.net
--
-- Client: localhost
-- Généré le: Mer 20 Mars 2013 à 00:53
-- Version du serveur: 5.5.27
-- Version de PHP: 5.4.7

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `demoBackOffice`
--

-- --------------------------------------------------------

--
-- Structure de la table `access`
--

CREATE TABLE IF NOT EXISTS `access` (
  `id_section` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_type_access` int(11) NOT NULL,
  `date_creation_access` datetime NOT NULL,
  `date_modification_access` datetime NOT NULL,
  `id_statut_access` int(11) NOT NULL DEFAULT '-1',
  PRIMARY KEY (`id_section`,`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `section`
--

CREATE TABLE IF NOT EXISTS `section` (
  `id_section` int(11) NOT NULL AUTO_INCREMENT,
  `name_section` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `date_creation_section` datetime NOT NULL,
  `date_modification_section` int(11) NOT NULL,
  `id_statut_section` int(11) NOT NULL DEFAULT '-1',
  `content_section` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_section`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `type_access`
--

CREATE TABLE IF NOT EXISTS `type_access` (
  `id_type_access` int(11) NOT NULL AUTO_INCREMENT,
  `type_access` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `description_access` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_type_access`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `type_user`
--

CREATE TABLE IF NOT EXISTS `type_user` (
  `id_type_user` int(11) NOT NULL AUTO_INCREMENT,
  `type_user` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `description_type_user` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id_type_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `login_user` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `password_user` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `id_type_user` int(11) NOT NULL,
  `date_creation_user` datetime DEFAULT NULL,
  `date_modification_user` datetime DEFAULT NULL,
  `id_status_user` int(11) NOT NULL DEFAULT '-1',
  PRIMARY KEY (`id_user`),
  KEY `login_user` (`login_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;