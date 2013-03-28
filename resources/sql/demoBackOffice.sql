-- phpMyAdmin SQL Dump
-- version 3.5.2.2
-- http://www.phpmyadmin.net
--
-- Client: localhost
-- Généré le: Ven 22 Mars 2013 à 20:05
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
  `id_type_user` int(11) NOT NULL,
  `id_type_access` int(11) NOT NULL,
  `date_creation_access` datetime NOT NULL,
  `date_modification_access` datetime NOT NULL,
  PRIMARY KEY (`id_section`,`id_type_user`),
  KEY `id_type_user` (`id_type_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `section`
--

CREATE TABLE IF NOT EXISTS `section` (
  `id_section` int(11) NOT NULL AUTO_INCREMENT,
  `name_section` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `date_creation_section` datetime NOT NULL,
  `date_modification_section` datetime NOT NULL,
  `content_section` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `id_status_section` int(11) COLLATE utf8_unicode_ci DEFAULT 0,
  PRIMARY KEY (`id_section`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `type_access`
--

CREATE TABLE IF NOT EXISTS `type_access` (
  `id_type_access` int(11) NOT NULL AUTO_INCREMENT,
  `type_access` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
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
  `date_creation_type_user` datetime NOT NULL,
  `date_modification_type_user` datetime NOT NULL,
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
  PRIMARY KEY (`id_user`),
  KEY `login_user` (`login_user`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- TODO fill table type_access
--
INSERT INTO type_access( `id_type_access`, `type_access`) VALUES('1', 'FORBIDDEN');
INSERT INTO type_access( `id_type_access`, `type_access`) VALUES('2', 'READONLY');
INSERT INTO type_access( `id_type_access`, `type_access`) VALUES('3', 'EDITION');


 INSERT INTO section( `id_section`, `name_section`, `date_creation_section`, `date_modification_section`, `content_section`, `id_status_section`)  VALUES ( 1, 'sections', NOW(), NOW(), '', 2);
 INSERT INTO section( `id_section`, `name_section`, `date_creation_section`, `date_modification_section`, `content_section`, `id_status_section`)  VALUES ( 2, 'users', NOW(), NOW(), '', 2);
 INSERT INTO section( `id_section`, `name_section`, `date_creation_section`, `date_modification_section`, `content_section`, `id_status_section`)  VALUES ( 3, 'rights', NOW(), NOW(), '', 2);
 INSERT INTO section( `id_section`, `name_section`, `date_creation_section`, `date_modification_section`, `content_section`, `id_status_section`)  VALUES ( 4, 'section_user_can_read', NOW(), NOW(), 'Ask to your administrator if you want to change this text ;)', 1);
 INSERT INTO section( `id_section`, `name_section`, `date_creation_section`, `date_modification_section`, `content_section`, `id_status_section`)  VALUES ( 5, 'section_user_can_edit', NOW(), NOW(), 'You can change this content if you want', 1);

INSERT INTO `type_user` (`id_type_user`, `type_user`, `description_type_user`, `date_creation_type_user`, `date_modification_type_user`) VALUES
(1, 'ROLE_ADMIN', 'Super user', NOW(), NOW());
INSERT INTO `type_user` (`id_type_user`, `type_user`, `description_type_user`, `date_creation_type_user`, `date_modification_type_user`) VALUES
(2, 'ROLE_USER', 'simple user', NOW(), NOW());

INSERT INTO `user` (`id_user`, `login_user`, `password_user`, `id_type_user`, `date_creation_user`, `date_modification_user`) VALUES
(1, 'admin', 'admin', 1, NOW(), NOW());
INSERT INTO `user` (`id_user`, `login_user`, `password_user`, `id_type_user`, `date_creation_user`, `date_modification_user`) VALUES
(2, 'user', 'user', 2, NOW(), NOW());

INSERT INTO access ( `id_section`, `id_type_user`, `id_type_access`, `date_creation_access`, `date_modification_access`)
VALUES (4, 2, 2, NOW(), NOW());
INSERT INTO access ( `id_section`, `id_type_user`, `id_type_access`, `date_creation_access`, `date_modification_access`)
VALUES (5, 2, 3, NOW(), NOW());

--
-- Contraintes pour les tables exportées
--

--
-- Contraintes pour la table `access`
--
ALTER TABLE `access`
  ADD CONSTRAINT `access_ibfk_2` FOREIGN KEY (`id_type_user`) REFERENCES `type_user` (`id_type_user`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `access_ibfk_1` FOREIGN KEY (`id_section`) REFERENCES `section` (`id_section`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
