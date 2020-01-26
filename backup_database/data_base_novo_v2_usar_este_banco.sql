-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: 26-Jan-2020 às 04:01
-- Versão do servidor: 5.7.26
-- versão do PHP: 7.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `data_base_novo_v2`
--

DELIMITER $$
--
-- Procedures
--
DROP PROCEDURE IF EXISTS `sp_addresses_save`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_addresses_save` (`pidaddress` INT(11), `pidperson` INT(11), `pdesaddress` VARCHAR(128), `pdesnumber` VARCHAR(16), `pdescomplement` VARCHAR(32), `pdescity` VARCHAR(32), `pdesstate` VARCHAR(32), `pdescountry` VARCHAR(32), `pdeszipcode` CHAR(8), `pdesdistrict` VARCHAR(32))  BEGIN

	IF pidaddress > 0 THEN
		
		UPDATE tb_addresses
        SET
			idperson = pidperson,
            desaddress = pdesaddress,
            desnumber = pdesnumber,
            descomplement = pdescomplement,
            descity = pdescity,
            desstate = pdesstate,
            descountry = pdescountry,
            deszipcode = pdeszipcode, 
            desdistrict = pdesdistrict
		WHERE idaddress = pidaddress;
        
    ELSE
		
		INSERT INTO tb_addresses (idperson, desaddress, desnumber, descomplement, descity, desstate, descountry, deszipcode, desdistrict)
        VALUES(pidperson, pdesaddress, pdesnumber, pdescomplement, pdescity, pdesstate, pdescountry, pdeszipcode, pdesdistrict);
        
        SET pidaddress = LAST_INSERT_ID();
        
    END IF;
    
    SELECT * FROM tb_addresses WHERE idaddress = pidaddress;

END$$

DROP PROCEDURE IF EXISTS `sp_carts_save`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_carts_save` (`pidcart` INT, `pdessessionid` VARCHAR(64), `piduser` INT, `pdeszipcode` CHAR(8), `pvlfreight` DECIMAL(10,2), `pnrdays` INT)  BEGIN

    IF pidcart > 0 THEN
        
        UPDATE tb_carts
        SET
            dessessionid = pdessessionid,
            iduser = piduser,
            deszipcode = pdeszipcode,
            vlfreight = pvlfreight,
            nrdays = pnrdays
        WHERE idcart = pidcart;
        
    ELSE
        
        INSERT INTO tb_carts (dessessionid, iduser, deszipcode, vlfreight, nrdays)
        VALUES(pdessessionid, piduser, pdeszipcode, pvlfreight, pnrdays);
        
        SET pidcart = LAST_INSERT_ID();
        
    END IF;
    
    SELECT * FROM tb_carts WHERE idcart = pidcart;

END$$

DROP PROCEDURE IF EXISTS `sp_categories_save`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_categories_save` (`pidcategory` INT, `pdescategory` VARCHAR(64))  BEGIN
	
	IF pidcategory > 0 THEN
		
		UPDATE tb_categories
        SET descategory = pdescategory
        WHERE idcategory = pidcategory;
        
    ELSE
		
		INSERT INTO tb_categories (descategory) VALUES(pdescategory);
        
        SET pidcategory = LAST_INSERT_ID();
        
    END IF;
    
    SELECT * FROM tb_categories WHERE idcategory = pidcategory;
    
END$$

DROP PROCEDURE IF EXISTS `sp_orders_save`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_orders_save` (`pidorder` INT, `pidcart` INT(11), `piduser` INT(11), `pidstatus` INT(11), `pidaddress` INT(11), `pvltotal` DECIMAL(10,2))  BEGIN
	
	IF pidorder > 0 THEN
		
		UPDATE tb_orders
        SET
			idcart = pidcart,
            iduser = piduser,
            idstatus = pidstatus,
            idaddress = pidaddress,
            vltotal = pvltotal
		WHERE idorder = pidorder;
        
    ELSE
    
		INSERT INTO tb_orders (idcart, iduser, idstatus, idaddress, vltotal)
        VALUES(pidcart, piduser, pidstatus, pidaddress, pvltotal);
		
		SET pidorder = LAST_INSERT_ID();
        
    END IF;
    
    SELECT * 
    FROM tb_orders a
    INNER JOIN tb_ordersstatus b USING(idstatus)
    INNER JOIN tb_carts c USING(idcart)
    INNER JOIN tb_users d ON d.iduser = a.iduser
    INNER JOIN tb_addresses e USING(idaddress)
    WHERE idorder = pidorder;
    
END$$

DROP PROCEDURE IF EXISTS `sp_products_save`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_products_save` (`pidproduct` INT(11), `pdesproduct` VARCHAR(64), `pvlprice` DECIMAL(10,2), `pvlwidth` DECIMAL(10,2), `pvlheight` DECIMAL(10,2), `pvllength` DECIMAL(10,2), `pvlweight` DECIMAL(10,2), `pdesurl` VARCHAR(128), `pdescricaoprod` VARCHAR(2020))  BEGIN
	
	IF pidproduct > 0 THEN
		
		UPDATE tb_products
        SET 
			desproduct = pdesproduct,
            vlprice = pvlprice,
            vlwidth = pvlwidth,
            vlheight = pvlheight,
            vllength = pvllength,
            vlweight = pvlweight,
            desurl = pdesurl,
            descricaoprod = pdescricaoprod
        WHERE idproduct = pidproduct;
        
    ELSE
		
		INSERT INTO tb_products (desproduct, vlprice, vlwidth, vlheight, vllength, vlweight, desurl, descricaoprod) 
        VALUES(pdesproduct, pvlprice, pvlwidth, pvlheight, pvllength, pvlweight, pdesurl, pdescricaoprod);
        
        SET pidproduct = LAST_INSERT_ID();
        
    END IF;
    
    SELECT * FROM tb_products WHERE idproduct = pidproduct;
    
END$$

DROP PROCEDURE IF EXISTS `sp_userspasswordsrecoveries_create`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_userspasswordsrecoveries_create` (`piduser` INT, `pdesip` VARCHAR(45))  BEGIN
	
	INSERT INTO tb_userspasswordsrecoveries (iduser, desip)
    VALUES(piduser, pdesip);
    
    SELECT * FROM tb_userspasswordsrecoveries
    WHERE idrecovery = LAST_INSERT_ID();
    
END$$

DROP PROCEDURE IF EXISTS `sp_usersupdate_save`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_usersupdate_save` (`piduser` INT, `pdesperson` VARCHAR(64), `pdeslogin` VARCHAR(64), `pdespassword` VARCHAR(256), `pdesemail` VARCHAR(128), `pnrphone` BIGINT, `pinadmin` TINYINT)  BEGIN
	
    DECLARE vidperson INT;
    
	SELECT idperson INTO vidperson
    FROM tb_users
    WHERE iduser = piduser;
    
    UPDATE tb_persons
    SET 
		desperson = pdesperson,
        desemail = pdesemail,
        nrphone = pnrphone
	WHERE idperson = vidperson;
    
    UPDATE tb_users
    SET
		deslogin = pdeslogin,
        despassword = pdespassword,
        inadmin = pinadmin
	WHERE iduser = piduser;
    
    SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = piduser;
    
END$$

DROP PROCEDURE IF EXISTS `sp_users_delete`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_users_delete` (`piduser` INT)  BEGIN
    
    DECLARE vidperson INT;
    
    SET FOREIGN_KEY_CHECKS = 0;
	
	SELECT idperson INTO vidperson
    FROM tb_users
    WHERE iduser = piduser;
	
    DELETE FROM tb_addresses WHERE idperson = vidperson;
    DELETE FROM tb_addresses WHERE idaddress IN(SELECT idaddress FROM tb_orders WHERE iduser = piduser);
	DELETE FROM tb_persons WHERE idperson = vidperson;
    
    DELETE FROM tb_userslogs WHERE iduser = piduser;
    DELETE FROM tb_userspasswordsrecoveries WHERE iduser = piduser;
    DELETE FROM tb_orders WHERE iduser = piduser;
    DELETE FROM tb_cartsproducts WHERE idcart IN(SELECT idcart FROM tb_carts WHERE iduser = piduser);
    DELETE FROM tb_carts WHERE iduser = piduser;
    DELETE FROM tb_users WHERE iduser = piduser;
    
    SET FOREIGN_KEY_CHECKS = 1;
    
END$$

DROP PROCEDURE IF EXISTS `sp_users_save`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_users_save` (`pdesperson` VARCHAR(64), `pdeslogin` VARCHAR(64), `pdespassword` VARCHAR(256), `pdesemail` VARCHAR(128), `pnrphone` BIGINT, `pinadmin` TINYINT)  BEGIN
	
    DECLARE vidperson INT;
    
	INSERT INTO tb_persons (desperson, desemail, nrphone)
    VALUES(pdesperson, pdesemail, pnrphone);
    
    SET vidperson = LAST_INSERT_ID();
    
    INSERT INTO tb_users (idperson, deslogin, despassword, inadmin)
    VALUES(vidperson, pdeslogin, pdespassword, pinadmin);
    
    SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = LAST_INSERT_ID();
    
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tb_addresses`
--

DROP TABLE IF EXISTS `tb_addresses`;
CREATE TABLE IF NOT EXISTS `tb_addresses` (
  `idaddress` int(11) NOT NULL AUTO_INCREMENT,
  `idperson` int(11) NOT NULL,
  `desaddress` varchar(128) NOT NULL,
  `desnumber` varchar(16) NOT NULL,
  `descomplement` varchar(32) DEFAULT NULL,
  `descity` varchar(32) NOT NULL,
  `desstate` varchar(32) NOT NULL,
  `descountry` varchar(32) NOT NULL,
  `deszipcode` char(8) NOT NULL,
  `desdistrict` varchar(32) NOT NULL,
  `dtregister` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idaddress`),
  KEY `fk_addresses_persons_idx` (`idperson`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `tb_addresses`
--

INSERT INTO `tb_addresses` (`idaddress`, `idperson`, `desaddress`, `desnumber`, `descomplement`, `descity`, `desstate`, `descountry`, `deszipcode`, `desdistrict`, `dtregister`) VALUES
(16, 19, 'Avenida das Américas 1510', '', '', 'Rio de Janeiro', 'RJ', 'Brasil', '22640900', 'Barra da Tijuca', '2020-01-21 20:30:51'),
(17, 19, 'Avenida das Américas 1510', '197', 'casa', 'Rio de Janeiro', 'RJ', 'Brasil', '22640900', 'Barra da Tijuca', '2020-01-21 20:31:48'),
(18, 19, 'Avenida das Américas 1510', '', '', 'Rio de Janeiro', 'RJ', 'Brasil', '22640900', 'Barra da Tijuca', '2020-01-21 20:34:51'),
(19, 20, 'Avenida das Américas 1510', '', '', 'Rio de Janeiro', 'RJ', 'Brasil', '22640900', 'Barra da Tijuca', '2020-01-23 14:26:58'),
(20, 20, 'Avenida das Américas', '197', 'atÃ© 1600 - lado par', 'Rio de Janeiro', 'RJ', 'Brasil', '22640100', 'Barra da Tijuca', '2020-01-26 02:00:51'),
(21, 20, 'Avenida das Américas', '197', 'atÃ© 1600 - lado par', 'Rio de Janeiro', 'RJ', 'Brasil', '22640100', 'Barra da Tijuca', '2020-01-26 02:02:55');

-- --------------------------------------------------------

--
-- Estrutura da tabela `tb_carts`
--

DROP TABLE IF EXISTS `tb_carts`;
CREATE TABLE IF NOT EXISTS `tb_carts` (
  `idcart` int(11) NOT NULL AUTO_INCREMENT,
  `dessessionid` varchar(64) NOT NULL,
  `iduser` int(11) DEFAULT NULL,
  `deszipcode` char(8) DEFAULT NULL,
  `vlfreight` decimal(10,2) DEFAULT NULL,
  `nrdays` int(11) DEFAULT NULL,
  `dtregister` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idcart`),
  KEY `FK_carts_users_idx` (`iduser`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `tb_carts`
--

INSERT INTO `tb_carts` (`idcart`, `dessessionid`, `iduser`, `deszipcode`, `vlfreight`, `nrdays`, `dtregister`) VALUES
(1, '8hcko3j7hmgp8sv7ggnseueupv', NULL, '22041080', NULL, 2, '2017-09-04 18:50:50'),
(2, 'm8iq807es95o2hj1a30772df1d', NULL, '21615338', '72.92', 2, '2017-09-06 13:12:50'),
(4, 'a8frnbabuqu60gguivlmrpagin', NULL, '01310100', '61.12', 1, '2017-09-08 11:39:01'),
(5, '51jglmd9n3cdirc1ah75m31pt1', NULL, NULL, NULL, NULL, '2017-09-14 11:26:39'),
(6, 'tlvjs3tas1bml5uit8b5qgjn9l', NULL, '01310100', '42.79', 1, '2017-09-21 13:18:21'),
(8, 'ovnv32bro6ola9916q31opga5b', NULL, '51190670', '86.62', 2, '2019-12-04 12:30:31'),
(9, 'ru2t9a16cfbduj5ff8dout76vn', NULL, '22640900', '101.14', 6, '2019-12-04 20:58:10'),
(10, 'sc3bf5t7jcbhm2nhl44fsrp40q', NULL, NULL, NULL, NULL, '2019-12-05 10:27:20'),
(11, 'plpimq3k5a38v93dl63j2oskgb', NULL, '22640900', '88.76', 6, '2019-12-05 14:35:55'),
(12, 'nfb2l8ft4ifr2m2m55heq7r2l9', NULL, NULL, NULL, NULL, '2019-12-06 11:29:53'),
(13, 'bou3k9jlgpbct2rhvg6ruek0tu', NULL, NULL, NULL, NULL, '2019-12-06 11:30:01'),
(14, '4ntf8hn37n1s6oaf6nmcu612dr', NULL, '22640900', '101.14', 6, '2019-12-06 11:30:11'),
(15, 'hi1eqtac904hkbj5rc7uoojfsr', NULL, '22640900', '88.76', 6, '2019-12-06 13:21:13'),
(16, 'vpedoa1nh0bq7g6t6pub9efo6m', NULL, '22640900', '129.14', 6, '2019-12-06 13:35:38'),
(17, 'eta7edumns6h14jh2op6chpvgc', NULL, NULL, NULL, NULL, '2019-12-08 12:46:07'),
(18, '8k2uv3qlcipe9ktlqjjlvdmbmr', NULL, '22640900', '88.76', 6, '2019-12-09 20:26:16'),
(19, 'crek4sfp4in2fn69a8nt3idt0s', NULL, '50070075', '46.76', 2, '2019-12-10 20:38:40'),
(20, 'gbtiaeu6oo3nlmjpb9bi5s0lnh', NULL, '22640900', '115.96', 6, '2020-01-21 19:06:01'),
(21, 'jssr6t2botlsjpjf4amlhtq0mq', NULL, NULL, NULL, NULL, '2020-01-21 20:11:42'),
(22, 'nfhb1j3jpkbv19so8ulhnv4bqs', NULL, NULL, NULL, NULL, '2020-01-22 15:42:56'),
(23, '9vn68vtrqocmuhf79gsqfi1d1q', NULL, NULL, NULL, NULL, '2020-01-22 15:42:56'),
(24, '4m8veo5seu21ge44lkmqtuij1h', NULL, '22640900', '0.00', 0, '2020-01-23 14:16:04'),
(25, 'o1ecsciiq21ab282jq2aettai8', NULL, NULL, NULL, NULL, '2020-01-23 14:27:27'),
(26, 'laora8g02orlml99aov451ovph', NULL, NULL, NULL, NULL, '2020-01-23 19:22:19'),
(27, 'hchgcekgdp6d8s0evijff4ogs4', NULL, NULL, NULL, NULL, '2020-01-23 19:30:21'),
(28, '5sek387b4masi780f7s92qah4h', NULL, '54490064', '133.16', 1, '2020-01-26 01:56:40');

-- --------------------------------------------------------

--
-- Estrutura da tabela `tb_cartsproducts`
--

DROP TABLE IF EXISTS `tb_cartsproducts`;
CREATE TABLE IF NOT EXISTS `tb_cartsproducts` (
  `idcartproduct` int(11) NOT NULL AUTO_INCREMENT,
  `idcart` int(11) NOT NULL,
  `idproduct` int(11) NOT NULL,
  `dtremoved` datetime DEFAULT NULL,
  `dtregister` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idcartproduct`),
  KEY `FK_cartsproducts_carts_idx` (`idcart`),
  KEY `fk_cartsproducts_products_idx` (`idproduct`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `tb_cartsproducts`
--

INSERT INTO `tb_cartsproducts` (`idcartproduct`, `idcart`, `idproduct`, `dtremoved`, `dtregister`) VALUES
(1, 1, 2, '2017-09-04 15:51:33', '2017-09-04 18:51:14'),
(2, 1, 2, '2017-09-04 15:52:09', '2017-09-04 18:51:31'),
(3, 1, 4, '2017-09-04 15:53:42', '2017-09-04 18:53:36'),
(4, 1, 4, '2017-09-04 15:54:11', '2017-09-04 18:53:40'),
(5, 1, 2, '2017-09-04 16:32:57', '2017-09-04 18:54:01'),
(6, 1, 2, '2017-09-04 16:33:04', '2017-09-04 19:31:05'),
(7, 1, 2, '2017-09-04 16:41:33', '2017-09-04 19:32:59'),
(8, 1, 2, '2017-09-04 16:41:38', '2017-09-04 19:33:02'),
(9, 1, 2, NULL, '2017-09-04 19:39:39'),
(10, 2, 2, '2017-09-06 10:21:57', '2017-09-06 13:20:44'),
(11, 2, 4, NULL, '2017-09-06 13:42:37'),
(12, 2, 4, NULL, '2017-09-06 15:28:56'),
(13, 4, 4, '2017-09-08 09:39:01', '2017-09-08 11:39:01'),
(14, 4, 4, NULL, '2017-09-08 12:27:38'),
(15, 4, 4, NULL, '2017-09-08 12:38:57'),
(16, 6, 4, NULL, '2017-09-21 13:59:32'),
(19, 8, 3, NULL, '2019-12-04 12:30:54'),
(20, 8, 3, NULL, '2019-12-04 12:31:00'),
(21, 9, 3, NULL, '2019-12-04 20:58:37'),
(22, 11, 3, '2019-12-06 08:23:47', '2019-12-06 10:29:01'),
(23, 11, 4, '2019-12-06 08:24:53', '2019-12-06 11:24:33'),
(24, 11, 5, '2019-12-06 08:29:36', '2019-12-06 11:28:02'),
(25, 13, 3, NULL, '2019-12-06 11:30:01'),
(26, 14, 5, '2019-12-06 08:34:48', '2019-12-06 11:30:18'),
(27, 14, 3, '2019-12-06 10:18:17', '2019-12-06 13:09:36'),
(28, 14, 3, NULL, '2019-12-06 13:20:12'),
(29, 15, 5, NULL, '2019-12-06 13:22:05'),
(30, 16, 3, '2019-12-06 10:53:51', '2019-12-06 13:36:03'),
(31, 16, 3, NULL, '2019-12-06 13:54:00'),
(32, 16, 1, NULL, '2019-12-06 14:03:34'),
(33, 18, 5, NULL, '2019-12-09 20:28:20'),
(34, 19, 5, NULL, '2019-12-10 20:42:05'),
(35, 20, 3, '2020-01-21 17:12:14', '2020-01-21 20:10:08'),
(36, 20, 3, '2020-01-21 17:12:14', '2020-01-21 20:10:12'),
(37, 20, 11, NULL, '2020-01-21 20:14:00'),
(38, 20, 1, '2020-01-21 17:29:22', '2020-01-21 20:23:36'),
(39, 20, 1, '2020-01-21 17:55:33', '2020-01-21 20:29:31'),
(40, 20, 2, '2020-01-21 17:55:40', '2020-01-21 20:34:37'),
(41, 24, 1, NULL, '2020-01-23 14:17:09'),
(42, 24, 1, NULL, '2020-01-23 14:17:47'),
(43, 28, 4, '2020-01-26 00:57:20', '2020-01-26 01:59:22'),
(44, 28, 6, NULL, '2020-01-26 03:59:27');

-- --------------------------------------------------------

--
-- Estrutura da tabela `tb_categories`
--

DROP TABLE IF EXISTS `tb_categories`;
CREATE TABLE IF NOT EXISTS `tb_categories` (
  `idcategory` int(11) NOT NULL AUTO_INCREMENT,
  `descategory` varchar(32) NOT NULL,
  `dtregister` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idcategory`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `tb_categories`
--

INSERT INTO `tb_categories` (`idcategory`, `descategory`, `dtregister`) VALUES
(1, 'Bolsas', '2020-01-21 20:27:07'),
(2, 'Computador e Tecnologia', '2020-01-21 20:27:28');

-- --------------------------------------------------------

--
-- Estrutura da tabela `tb_categoriesproducts`
--

DROP TABLE IF EXISTS `tb_categoriesproducts`;
CREATE TABLE IF NOT EXISTS `tb_categoriesproducts` (
  `idcategory` int(11) NOT NULL,
  `idproduct` int(11) NOT NULL,
  PRIMARY KEY (`idcategory`,`idproduct`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tb_orders`
--

DROP TABLE IF EXISTS `tb_orders`;
CREATE TABLE IF NOT EXISTS `tb_orders` (
  `idorder` int(11) NOT NULL AUTO_INCREMENT,
  `idcart` int(11) NOT NULL,
  `iduser` int(11) NOT NULL,
  `idstatus` int(11) NOT NULL,
  `idaddress` int(11) NOT NULL,
  `vltotal` decimal(10,2) NOT NULL,
  `dtregister` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idorder`),
  KEY `FK_orders_users_idx` (`iduser`),
  KEY `fk_orders_ordersstatus_idx` (`idstatus`),
  KEY `fk_orders_carts_idx` (`idcart`),
  KEY `fk_orders_addresses_idx` (`idaddress`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `tb_orders`
--

INSERT INTO `tb_orders` (`idorder`, `idcart`, `iduser`, `idstatus`, `idaddress`, `vltotal`, `dtregister`) VALUES
(1, 24, 20, 1, 19, '600.00', '2020-01-23 14:26:58'),
(2, 28, 20, 1, 20, '585.16', '2020-01-26 02:00:52'),
(3, 28, 20, 1, 21, '585.16', '2020-01-26 02:02:56');

-- --------------------------------------------------------

--
-- Estrutura da tabela `tb_ordersstatus`
--

DROP TABLE IF EXISTS `tb_ordersstatus`;
CREATE TABLE IF NOT EXISTS `tb_ordersstatus` (
  `idstatus` int(11) NOT NULL AUTO_INCREMENT,
  `desstatus` varchar(32) NOT NULL,
  `dtregister` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idstatus`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `tb_ordersstatus`
--

INSERT INTO `tb_ordersstatus` (`idstatus`, `desstatus`, `dtregister`) VALUES
(1, 'Em Aberto', '2017-03-13 03:00:00'),
(2, 'Aguardando Pagamento', '2017-03-13 03:00:00'),
(3, 'Pago', '2017-03-13 03:00:00'),
(4, 'Entregue', '2017-03-13 03:00:00');

-- --------------------------------------------------------

--
-- Estrutura da tabela `tb_persons`
--

DROP TABLE IF EXISTS `tb_persons`;
CREATE TABLE IF NOT EXISTS `tb_persons` (
  `idperson` int(11) NOT NULL AUTO_INCREMENT,
  `desperson` varchar(64) NOT NULL,
  `desemail` varchar(128) DEFAULT NULL,
  `nrphone` bigint(20) DEFAULT NULL,
  `dtregister` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idperson`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `tb_persons`
--

INSERT INTO `tb_persons` (`idperson`, `desperson`, `desemail`, `nrphone`, `dtregister`) VALUES
(12, 'Rafael Macedo', 'rafaelmacedosilva88@hotmail.com', 81987659536, '2019-12-03 21:39:53'),
(18, 'Cassiano filho', 'cassiano1322@gmail.com', 81971738272, '2020-01-21 20:25:44'),
(19, 'Cassiano Filho', 'cassiano_filho@gmail.com', 81971789345, '2020-01-21 20:30:41'),
(20, 'andreza', 'andreza.j2016@hotmail.com', 81986720424, '2020-01-23 14:18:31');

-- --------------------------------------------------------

--
-- Estrutura da tabela `tb_products`
--

DROP TABLE IF EXISTS `tb_products`;
CREATE TABLE IF NOT EXISTS `tb_products` (
  `idproduct` int(11) NOT NULL AUTO_INCREMENT,
  `desproduct` varchar(64) NOT NULL,
  `vlprice` decimal(10,2) NOT NULL,
  `vlwidth` decimal(10,2) NOT NULL,
  `vlheight` decimal(10,2) NOT NULL,
  `vllength` decimal(10,2) NOT NULL,
  `vlweight` decimal(10,2) NOT NULL,
  `desurl` varchar(128) NOT NULL,
  `dtregister` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `descricaoprod` varchar(2020) DEFAULT NULL,
  PRIMARY KEY (`idproduct`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `tb_products`
--

INSERT INTO `tb_products` (`idproduct`, `desproduct`, `vlprice`, `vlwidth`, `vlheight`, `vllength`, `vlweight`, `desurl`, `dtregister`, `descricaoprod`) VALUES
(1, 'MAC book pro', '300.00', '1.50', '1.30', '0.00', '0.00', 'Para a sua casa', '2020-01-22 15:57:46', '                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus lorem lorem, posuere a tellus eu, hendrerit tempus quam. Praesent sollicitudin luctus molestie. In hac habitasse platea dictumst. Duis viverra, diam sit amet varius condimentum, massa justo facilisis nulla, sed lobortis metus dolor at urna. Nam ac varius ante, sit amet sodales sem. Donec rhoncus turpis vitae est blandit pretium. Curabitur id urna sagittis, feugiat lacus in, semper nulla. Nulla elementum varius lacus, laoreet molestie purus pharetra ac. Donec auctor bibendum fringilla. Ut sit amet enim volutpat, porttitor turpis quis, sodales augue.\r\n              '),
(4, 'Recibo', '500.00', '1.50', '1.30', '1.50', '0.30', 'Para a sua empresa', '2020-01-26 01:58:43', 'Recibo para a sua empresa.                \r\n              '),
(5, 'Ipda 32GB Wi-fi Tela 9,7\" CÃ¢mera 8MP Prata - Apple', '2000.00', '1.50', '1.30', '1.50', '0.30', 'Mac book pro', '2020-01-26 03:45:04', 'Mac book pro               \r\n              '),
(6, 'Tela de televisÃ£o Smart', '5000.00', '4.00', '4.00', '1.90', '0.30', 'EletrÃ´nicos', '2020-01-26 03:58:59', 'TelevisÃ£o, com led e smart.                \r\n              ');

-- --------------------------------------------------------

--
-- Estrutura da tabela `tb_productscategories`
--

DROP TABLE IF EXISTS `tb_productscategories`;
CREATE TABLE IF NOT EXISTS `tb_productscategories` (
  `idcategory` int(11) NOT NULL,
  `idproduct` int(11) NOT NULL,
  PRIMARY KEY (`idcategory`,`idproduct`),
  KEY `fk_productscategories_products_idx` (`idproduct`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `tb_productscategories`
--

INSERT INTO `tb_productscategories` (`idcategory`, `idproduct`) VALUES
(1, 2),
(4, 7),
(5, 10);

-- --------------------------------------------------------

--
-- Estrutura da tabela `tb_users`
--

DROP TABLE IF EXISTS `tb_users`;
CREATE TABLE IF NOT EXISTS `tb_users` (
  `iduser` int(11) NOT NULL AUTO_INCREMENT,
  `idperson` int(11) NOT NULL,
  `deslogin` varchar(64) NOT NULL,
  `despassword` varchar(256) NOT NULL,
  `inadmin` tinyint(4) NOT NULL DEFAULT '0',
  `dtregister` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`iduser`),
  KEY `FK_users_persons_idx` (`idperson`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `tb_users`
--

INSERT INTO `tb_users` (`iduser`, `idperson`, `deslogin`, `despassword`, `inadmin`, `dtregister`) VALUES
(12, 12, 'strael12', '$2y$12$WQ/Gt1DADI3q/ycu4S0douv1raSNz2WbELutlAGGhbZ/oReZZklOm', 1, '2019-12-03 21:39:53'),
(18, 18, 'serrinha', '$2y$12$nfrhv/QXEzhLJpvAV7B6oOIJ9Rm7d9Tv1hWQzTwOVpaTcw.ThaH2C', 1, '2020-01-21 20:25:44'),
(19, 19, 'cassiano_filho@gmail.com', '$2y$12$wxyDFM0kfOL6MgmwNq6o1.joymqIkxuFftVAxeBZ2hxc53H2McJPG', 0, '2020-01-21 20:30:41'),
(20, 20, 'andreza.j2016@hotmail.com', '$2y$12$E.Rzh6a2AFkwyTa1p5Chaenr.G5xc2YrM4piIytqNZ6.zWgwgNi8G', 0, '2020-01-23 14:18:31');

-- --------------------------------------------------------

--
-- Estrutura da tabela `tb_userslogs`
--

DROP TABLE IF EXISTS `tb_userslogs`;
CREATE TABLE IF NOT EXISTS `tb_userslogs` (
  `idlog` int(11) NOT NULL AUTO_INCREMENT,
  `iduser` int(11) NOT NULL,
  `deslog` varchar(128) NOT NULL,
  `desip` varchar(45) NOT NULL,
  `desuseragent` varchar(128) NOT NULL,
  `dessessionid` varchar(64) NOT NULL,
  `desurl` varchar(128) NOT NULL,
  `dtregister` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idlog`),
  KEY `fk_userslogs_users_idx` (`iduser`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tb_userspasswordsrecoveries`
--

DROP TABLE IF EXISTS `tb_userspasswordsrecoveries`;
CREATE TABLE IF NOT EXISTS `tb_userspasswordsrecoveries` (
  `idrecovery` int(11) NOT NULL AUTO_INCREMENT,
  `iduser` int(11) NOT NULL,
  `desip` varchar(45) NOT NULL,
  `dtrecovery` datetime DEFAULT NULL,
  `dtregister` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idrecovery`),
  KEY `fk_userspasswordsrecoveries_users_idx` (`iduser`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Constraints for dumped tables
--

--
-- Limitadores para a tabela `tb_addresses`
--
ALTER TABLE `tb_addresses`
  ADD CONSTRAINT `fk_addresses_persons` FOREIGN KEY (`idperson`) REFERENCES `tb_persons` (`idperson`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Limitadores para a tabela `tb_carts`
--
ALTER TABLE `tb_carts`
  ADD CONSTRAINT `fk_carts_users` FOREIGN KEY (`iduser`) REFERENCES `tb_users` (`iduser`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `tb_orders`
--
ALTER TABLE `tb_orders`
  ADD CONSTRAINT `fk_orders_addresses` FOREIGN KEY (`idaddress`) REFERENCES `tb_addresses` (`idaddress`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_orders_carts` FOREIGN KEY (`idcart`) REFERENCES `tb_carts` (`idcart`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_orders_ordersstatus` FOREIGN KEY (`idstatus`) REFERENCES `tb_ordersstatus` (`idstatus`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_orders_users` FOREIGN KEY (`iduser`) REFERENCES `tb_users` (`iduser`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
