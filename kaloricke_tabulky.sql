-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Počítač: 127.0.0.1
-- Vytvořeno: Čtv 18. dub 2024, 00:20
-- Verze serveru: 10.4.32-MariaDB
-- Verze PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Databáze: `kaloricke_tabulky`
--

DELIMITER $$
--
-- Procedury
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `PridejAktivitu` (IN `nazevAktivity` VARCHAR(255), IN `casHodiny` DECIMAL(10,2), IN `spaleneKalorieNaHodinu` DECIMAL(10,2), IN `datum` DATE, IN `idUzivatele` INT)   BEGIN
    DECLARE spaleneKalorie DECIMAL(10,2);

    -- Výpočet celkových spálených kalorií
    SET spaleneKalorie = casHodiny * spaleneKalorieNaHodinu;

    -- Vložení aktivity do tabulky cviceni
    INSERT INTO cviceni (id_aktivity, doba_cviceni, spalene_kalorie, datum, Id_Uzivatele)
    VALUES (
        (SELECT Id_Aktivity FROM aktivity WHERE Nazev_Aktivity = nazevAktivity),
        casHodiny,
        spaleneKalorie,
        datum,
        idUzivatele
    );
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SpocitejCelkoveMakroJidla` (IN `idUzivatele` INT, IN `datumJidla` DATE, OUT `celkoveBilkoviny` DECIMAL(10,2), OUT `celkoveSacharidy` DECIMAL(10,2), OUT `celkoveTuky` DECIMAL(10,2), OUT `celkovaVlaknina` DECIMAL(10,2))   BEGIN
    SELECT 
        SUM(potraviny.Bílkoviny * jidlo.gramaz / 100) AS celkoveBilkoviny,
        SUM(potraviny.Sacharidy * jidlo.gramaz / 100) AS celkoveSacharidy,
        SUM(potraviny.Tuky * jidlo.gramaz / 100) AS celkoveTuky,
        SUM(potraviny.Vláknina * jidlo.gramaz / 100) AS celkovaVlaknina
    INTO
        celkoveBilkoviny, celkoveSacharidy, celkoveTuky, celkovaVlaknina
    FROM jidlo
    LEFT JOIN potraviny ON jidlo.potravina = potraviny.Id_Potraviny
    WHERE DATE(jidlo.datum) = datumJidla AND jidlo.id_uzivatele = idUzivatele;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SpocitejCelkoveMakroNapoje` (IN `idUzivatele` INT, IN `datumJidla` DATE, OUT `celkoveBilkoviny` DECIMAL(10,2), OUT `celkoveSacharidy` DECIMAL(10,2), OUT `celkoveTuky` DECIMAL(10,2), OUT `celkovaVlaknina` DECIMAL(10,2))   BEGIN
    SELECT 
        SUM(piti.Bílkoviny * napoje.gramaz / 100) AS celkoveBilkoviny,
        SUM(piti.Sacharidy * napoje.gramaz / 100) AS celkoveSacharidy,
        SUM(piti.Tuky * napoje.gramaz / 100) AS celkoveTuky,
        SUM(piti.Vláknina * napoje.gramaz / 100) AS celkovaVlaknina
    INTO
        celkoveBilkoviny, celkoveSacharidy, celkoveTuky, celkovaVlaknina
    FROM napoje
    LEFT JOIN piti ON napoje.piti = piti.id_Piti
    WHERE DATE(napoje.datum) = datumJidla AND napoje.id_uzivatele = idUzivatele;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SpocitejCelkovouEnergetickouHodnotuJidla` (IN `idUzivatele` INT, IN `datumJidla` DATE, OUT `celkovaEnergetickaHodnota` DECIMAL(10,2))   BEGIN
    SELECT 
        SUM(potraviny.Energeticka_hodnota * jidlo.gramaz / 100) AS celkovaEnergetickaHodnota
    INTO
        celkovaEnergetickaHodnota
    FROM jidlo
    LEFT JOIN potraviny ON jidlo.potravina = potraviny.Id_Potraviny
    WHERE DATE(jidlo.datum) = datumJidla AND jidlo.id_uzivatele = idUzivatele;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SpocitejCelkovouEnergetickouHodnotuPiti` (IN `idUzivatele` INT, IN `datumPiti` DATE)   BEGIN
    DECLARE celkovaEnergetickaHodnota DECIMAL(10,2);
    DECLARE celkovaGramaz DECIMAL(10,2);

    SELECT 
        IFNULL(SUM(piti.Energeticka_hodnota * napoje.gramaz / 100), 0) AS celkovaEnergetickaHodnota,
        IFNULL(SUM(napoje.gramaz), 0) AS celkovaGramaz
    INTO
        celkovaEnergetickaHodnota, celkovaGramaz
    FROM napoje
    LEFT JOIN piti ON napoje.piti = piti.id_Piti
    WHERE DATE(napoje.datum) = datumPiti AND napoje.id_uzivatele = idUzivatele;
    
    SELECT celkovaEnergetickaHodnota, celkovaGramaz;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SpocitejSpaleneKalorieDne` (IN `datumCviceni` DATE, IN `idUzivatele` INT, OUT `SpaleneKalorie` DECIMAL(6,2))   BEGIN
    SELECT 
        SUM(aktivity.Spalene_kalorie * cviceni.doba_cviceni) INTO SpaleneKalorie
    FROM cviceni
    INNER JOIN aktivity ON cviceni.id_aktivity = aktivity.Id_Aktivity
    WHERE DATE(cviceni.datum) = datumCviceni AND cviceni.Id_Uzivatele = idUzivatele;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `update_nastaveni` ()   BEGIN
  DECLARE last_makra_id INT;

  -- Get the latest ID_Makra from the makra table
  SELECT ID_Makra INTO last_makra_id
  FROM makra
  ORDER BY ID_Makra DESC
  LIMIT 1;

  -- Update the corresponding record in the nastaveni table
  UPDATE nastaveni
  SET id_makra = last_makra_id
  WHERE Id_Nastaveni = (SELECT Id_Nastaveni FROM nastaveni ORDER BY Id_Nastaveni DESC LIMIT 1);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `vyberjidlauzivatele` (IN `userId` INT, IN `date` DATE)   BEGIN
    SELECT potraviny.Nazev_Potraviny, jidlo.cast_dne
    FROM jidlo
    INNER JOIN potraviny ON jidlo.potravina = potraviny.Id_Potraviny
    WHERE jidlo.Id_Uzivatele = userId 
    AND jidlo.datum = date;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Struktura tabulky `aktivity`
--

CREATE TABLE `aktivity` (
  `Id_Aktivity` int(11) NOT NULL,
  `Nazev_Aktivity` varchar(128) DEFAULT NULL,
  `Spalene_kalorie` decimal(6,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vypisuji data pro tabulku `aktivity`
--

INSERT INTO `aktivity` (`Id_Aktivity`, `Nazev_Aktivity`, `Spalene_kalorie`) VALUES
(1, 'Běh', 15.00),
(2, 'Jízda na kole', 12.00),
(3, 'Plavání rekreační', 14.00),
(4, 'Posilování s Vlastní vahou', 6.00),
(5, 'Yoga', 3.00),
(13, 'Lezení po skalách', 5.00),
(17, 'Posilování', 4.00),
(18, 'Jóga', 3.00),
(19, 'Kardio cvičení', 7.00),
(20, 'Tanec', 6.00),
(21, 'Chůze', 4.00),
(22, 'Fotbal', 8.00),
(23, 'Basketbal', 9.00),
(24, 'Volejbal', 7.00),
(25, 'Tenis', 6.00),
(26, 'Golf', 5.00),
(27, 'Jízda na koni', 5.00),
(28, 'Stolní tenis', 5.00),
(29, 'Box', 8.00),
(30, 'Zumba', 6.00),
(31, 'Krasobruslení', 7.00),
(32, 'Lyžování', 7.00),
(33, 'Snowboarding', 8.00),
(34, 'Surfování', 6.00),
(35, 'Jízda na skateboardu', 6.00),
(36, 'Kanoistika', 7.00),
(37, 'Horská turistika', 7.00),
(42, 'nevim', 150.00),
(43, 'nevim', 500.00);

-- --------------------------------------------------------

--
-- Struktura tabulky `cast_dne`
--

CREATE TABLE `cast_dne` (
  `ID_Casti` int(11) NOT NULL,
  `Nazev` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Vypisuji data pro tabulku `cast_dne`
--

INSERT INTO `cast_dne` (`ID_Casti`, `Nazev`) VALUES
(1, 'Snídaně'),
(2, 'Dopolední svačina'),
(3, 'Oběd'),
(4, 'Odpolední svačina'),
(5, 'Večeře'),
(6, 'Druhá večeře');

-- --------------------------------------------------------

--
-- Struktura tabulky `cviceni`
--

CREATE TABLE `cviceni` (
  `id_cviceni` int(11) NOT NULL,
  `datum` date DEFAULT NULL,
  `id_aktivity` int(11) DEFAULT NULL,
  `doba_cviceni` int(3) DEFAULT NULL,
  `Id_Uzivatele` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vypisuji data pro tabulku `cviceni`
--

INSERT INTO `cviceni` (`id_cviceni`, `datum`, `id_aktivity`, `doba_cviceni`, `Id_Uzivatele`) VALUES
(2, '2024-02-28', 1, 60, 51),
(3, '2024-02-28', 1, 60, 51),
(4, '2024-02-28', 1, 60, 51),
(5, '2024-02-28', 1, 60, 51),
(6, '2024-02-28', 1, 60, 51),
(7, '2024-02-28', 1, 60, 51),
(8, '2024-02-28', 1, 60, 51),
(9, '2024-02-28', 1, 60, 51),
(10, '2024-02-28', 1, 60, 51),
(11, '2024-02-28', 1, 60, 51),
(12, '2024-02-28', 1, 60, 51),
(13, '2024-02-28', 1, 60, 51),
(14, '2024-02-28', 1, 50, 51),
(15, '2024-02-28', 1, 50, 51),
(16, '2024-02-28', 1, 50, 51),
(17, '2024-02-28', 1, 50, 51),
(18, '2024-02-28', 1, 50, 51),
(19, '2024-02-28', 1, 50, 51),
(20, '2024-02-28', 1, 49, 51),
(21, '2024-02-28', 1, 49, 51),
(22, '2024-03-01', 1, 200, 51),
(49, '2024-04-17', 42, 1, 51),
(50, '2024-04-17', 43, 1, 52);

-- --------------------------------------------------------

--
-- Struktura tabulky `jidlo`
--

CREATE TABLE `jidlo` (
  `id_jidla` int(11) NOT NULL,
  `datum` date DEFAULT NULL,
  `potravina` int(11) DEFAULT NULL,
  `gramaz` int(11) DEFAULT NULL,
  `cast_dne` int(11) DEFAULT NULL,
  `Id_Uzivatele` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vypisuji data pro tabulku `jidlo`
--

INSERT INTO `jidlo` (`id_jidla`, `datum`, `potravina`, `gramaz`, `cast_dne`, `Id_Uzivatele`) VALUES
(146, '2024-02-29', 1, 50, 1, 51),
(147, '2024-02-29', 1, 50, 1, 51),
(149, '2024-02-29', 1, 60, 1, 51),
(150, '2024-02-29', 1, 60, 1, 51),
(151, '2024-02-29', 1, 60, 1, 51),
(152, '2024-05-29', 1, 600, 1, 51),
(153, '2024-03-01', 1, 200, 1, 51),
(154, '2024-03-27', 1, 520, 1, 51),
(155, '2024-04-15', 1, 150, 1, 51),
(156, '2024-04-15', 3, 80, 1, 51),
(157, '2024-04-15', 30, 50, 1, 51),
(158, '2024-04-17', 56, 150, 1, 51),
(159, '2024-04-17', 3, 150, 1, 51);

-- --------------------------------------------------------

--
-- Struktura tabulky `makra`
--

CREATE TABLE `makra` (
  `ID_Makra` int(11) NOT NULL,
  `Pitny_rezim` int(11) DEFAULT NULL,
  `Cil_Snezenych_Kalorii` int(11) DEFAULT NULL,
  `Bilkoviny` decimal(6,2) DEFAULT NULL,
  `Sacharidy` decimal(6,2) DEFAULT NULL,
  `Tuky` decimal(6,2) DEFAULT NULL,
  `Vlaknina` decimal(6,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Vypisuji data pro tabulku `makra`
--

INSERT INTO `makra` (`ID_Makra`, `Pitny_rezim`, `Cil_Snezenych_Kalorii`, `Bilkoviny`, `Sacharidy`, `Tuky`, `Vlaknina`) VALUES
(135, 3480, 1610, 445.33, 683.77, 450.96, 32.68),
(136, 2800, 1307, 323.22, 603.57, 351.19, 29.02),
(137, 3480, 1525, 421.82, 647.67, 427.15, 30.96),
(138, 3480, 1610, 445.33, 683.77, 450.96, 32.68),
(139, 3480, 2042, 501.52, 984.04, 516.83, 32.06),
(140, 3480, 1894, 464.22, 877.87, 517.06, 38.45),
(141, 3480, 1218, 336.17, 517.16, 341.77, 27.04),
(142, 3480, 1576, 387.85, 773.50, 399.83, 26.79),
(143, 3480, 1894, 464.22, 877.87, 517.06, 38.45),
(144, 3480, 1644, 454.73, 698.21, 460.48, 33.37),
(145, 3480, 2133, 523.86, 1027.89, 581.24, 36.90),
(146, 3480, 1610, 50.00, 60.00, 80.00, 10.00);

--
-- Triggery `makra`
--
DELIMITER $$
CREATE TRIGGER `after_insert_makra` AFTER INSERT ON `makra` FOR EACH ROW BEGIN
  UPDATE `uzivatel`
  SET `Makra_Uzivatele` = NEW.`ID_Makra`
  WHERE `Makra_Uzivatele` IS NULL;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktura tabulky `napoje`
--

CREATE TABLE `napoje` (
  `id_napoje` int(11) NOT NULL,
  `datum` date DEFAULT NULL,
  `piti` int(11) DEFAULT NULL,
  `gramaz` int(11) DEFAULT NULL,
  `cast_dne` int(11) DEFAULT NULL,
  `Id_Uzivatele` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vypisuji data pro tabulku `napoje`
--

INSERT INTO `napoje` (`id_napoje`, `datum`, `piti`, `gramaz`, `cast_dne`, `Id_Uzivatele`) VALUES
(23, '2024-02-29', 1, 100, 1, 51),
(24, '2024-02-29', 1, 100, 1, 51),
(25, '2024-04-15', 1, 500, 1, 51),
(26, '2024-04-15', 3, 150, 1, 51),
(27, '2024-04-16', 1, 1000, 1, 51),
(28, '2024-04-16', 2, 1500, 1, 51),
(29, '2024-04-17', 1, 100, 1, 51),
(30, '2024-04-17', 1, 500, 1, 52);

-- --------------------------------------------------------

--
-- Struktura tabulky `nastaveni`
--

CREATE TABLE `nastaveni` (
  `Id_Nastaveni` int(11) NOT NULL,
  `Vyska` int(3) NOT NULL,
  `Vaha` decimal(4,1) DEFAULT NULL,
  `Datum_Narozeni` date DEFAULT NULL,
  `Cile` enum('Zhubout','Nabrat svalovou hmotu','Zůstat fit') DEFAULT NULL,
  `Pohlavi` enum('Muž','Žena') NOT NULL,
  `pitny_rezim` int(11) DEFAULT NULL,
  `Cil_Snezenych_Kalorii` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vypisuji data pro tabulku `nastaveni`
--

INSERT INTO `nastaveni` (`Id_Nastaveni`, `Vyska`, `Vaha`, `Datum_Narozeni`, `Cile`, `Pohlavi`, `pitny_rezim`, `Cil_Snezenych_Kalorii`) VALUES
(135, 175, 90.0, '2004-07-28', 'Zhubout', 'Muž', 3480, 1610),
(136, 158, 60.0, '2000-03-10', 'Zůstat fit', 'Žena', 2800, 1307),
(137, 175, 80.0, '2004-07-28', 'Zhubout', 'Muž', 3480, 1525),
(138, 175, 90.0, '2004-07-28', 'Zhubout', 'Muž', 3480, 1610),
(139, 175, 90.0, '2004-07-28', 'Nabrat svalovou hmotu', 'Muž', 3480, 2042),
(140, 175, 90.0, '2004-07-28', 'Zůstat fit', 'Muž', 3480, 1894),
(141, 175, 60.0, '2004-07-28', 'Zhubout', 'Žena', 3480, 1218),
(142, 175, 60.0, '2004-07-28', 'Nabrat svalovou hmotu', 'Žena', 3480, 1576),
(143, 175, 90.0, '2004-07-28', 'Zůstat fit', 'Muž', 3480, 1894),
(144, 175, 94.0, '2004-07-28', 'Zhubout', 'Muž', 3480, 1644),
(145, 175, 94.0, '2004-08-27', 'Nabrat svalovou hmotu', 'Muž', 3480, 2133),
(146, 175, 80.0, '2024-04-18', 'Zhubout', 'Muž', 3480, 1610);

--
-- Triggery `nastaveni`
--
DELIMITER $$
CREATE TRIGGER `INSERT_Počítej_Makro_žena_Fit` AFTER INSERT ON `nastaveni` FOR EACH ROW BEGIN
    DECLARE protein DECIMAL(6,2);
    DECLARE carbs DECIMAL(6,2);
    DECLARE fats DECIMAL(6,2);
    DECLARE fiber DECIMAL(6,2);

    IF NEW.Cile = 'Zůstat fit' AND NEW.Pohlavi = 'Žena' THEN
        SET protein = 0.2473 * NEW.Cil_Snezenych_Kalorii;
        SET carbs = 0.4618 * NEW.Cil_Snezenych_Kalorii;
        SET fats = 0.2687 * NEW.Cil_Snezenych_Kalorii;
        SET fiber = 0.0222 * NEW.Cil_Snezenych_Kalorii;

        UPDATE makra
        SET Bilkoviny = protein,
            Sacharidy = carbs,
            Tuky = fats,
            Vlaknina = fiber
        WHERE ID_Makra = NEW.Id_Nastaveni;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `INSERT_Počítej_Makro_žena_Svaly` AFTER INSERT ON `nastaveni` FOR EACH ROW BEGIN
    DECLARE protein DECIMAL(6,2);
    DECLARE carbs DECIMAL(6,2);
    DECLARE fats DECIMAL(6,2);
    DECLARE fiber DECIMAL(6,2);

    IF NEW.Cile = 'Nabrat svalovou hmotu' AND NEW.Pohlavi = 'Žena' THEN
        SET protein = 0.2461 * NEW.Cil_Snezenych_Kalorii;
        SET carbs = 0.4908 * NEW.Cil_Snezenych_Kalorii;
        SET fats = 0.2537 * NEW.Cil_Snezenych_Kalorii;
        SET fiber = 0.017 * NEW.Cil_Snezenych_Kalorii;

        UPDATE makra
        SET Bilkoviny = protein,
            Sacharidy = carbs,
            Tuky = fats,
            Vlaknina = fiber
        WHERE ID_Makra = NEW.Id_Nastaveni;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `Insert_Počítej_Makroživiny_Muž_Fit` AFTER INSERT ON `nastaveni` FOR EACH ROW BEGIN
    DECLARE protein DECIMAL(6,2);
    DECLARE carbs DECIMAL(6,2);
    DECLARE fats DECIMAL(6,2);
    DECLARE fiber DECIMAL(6,2);

    IF NEW.Cile = 'Zůstat fit' AND NEW.Pohlavi = 'Muž' THEN
        SET protein = 0.2451 * NEW.Cil_Snezenych_Kalorii;
        SET carbs = 0.4635 * NEW.Cil_Snezenych_Kalorii;
        SET fats = 0.2730 * NEW.Cil_Snezenych_Kalorii;
        SET fiber = 0.0203 * NEW.Cil_Snezenych_Kalorii;

        UPDATE makra
        SET Bilkoviny = protein,
            Sacharidy = carbs,
            Tuky = fats,
            Vlaknina = fiber
        WHERE ID_Makra = NEW.Id_Nastaveni;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `Insert_Počítej_Makroživiny_Muž_Svaly` AFTER INSERT ON `nastaveni` FOR EACH ROW BEGIN
    DECLARE protein DECIMAL(6,2);
    DECLARE carbs DECIMAL(6,2);
    DECLARE fats DECIMAL(6,2);
    DECLARE fiber DECIMAL(6,2);

    IF NEW.Cile = 'Nabrat svalovou hmotu' AND NEW.Pohlavi = 'Muž' THEN
        SET protein = 0.2456 * NEW.Cil_Snezenych_Kalorii;
        SET carbs = 0.4819 * NEW.Cil_Snezenych_Kalorii;
        SET fats = 0.2531 * NEW.Cil_Snezenych_Kalorii;
        SET fiber = 0.0157 * NEW.Cil_Snezenych_Kalorii;

        UPDATE makra
        SET Bilkoviny = protein,
            Sacharidy = carbs,
            Tuky = fats,
            Vlaknina = fiber
        WHERE ID_Makra = NEW.Id_Nastaveni;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `Insert_Počítej_Makroživiny_Muž_hubne` AFTER INSERT ON `nastaveni` FOR EACH ROW BEGIN
    DECLARE protein DECIMAL(6,2);
    DECLARE carbs DECIMAL(6,2);
    DECLARE fats DECIMAL(6,2);
    DECLARE fiber DECIMAL(6,2);

    IF NEW.Cile = 'Zhubout' AND NEW.Pohlavi = 'Muž' THEN
        SET protein = 0.2766 * NEW.Cil_Snezenych_Kalorii;
        SET carbs = 0.4247 * NEW.Cil_Snezenych_Kalorii;
        SET fats = 0.2801 * NEW.Cil_Snezenych_Kalorii;
        SET fiber = 0.0203 * NEW.Cil_Snezenych_Kalorii;

        UPDATE makra
        SET Bilkoviny = protein,
            Sacharidy = carbs,
            Tuky = fats,
            Vlaknina = fiber
        WHERE ID_Makra = NEW.Id_Nastaveni;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `Insert_Vypočitej_Makro_žena_hubne` AFTER INSERT ON `nastaveni` FOR EACH ROW BEGIN
    DECLARE protein DECIMAL(6,2);
    DECLARE carbs DECIMAL(6,2);
    DECLARE fats DECIMAL(6,2);
    DECLARE fiber DECIMAL(6,2);

    IF NEW.Cile = 'Zhubout' AND NEW.Pohlavi = 'Žena' THEN
        SET protein = 0.2760 * NEW.Cil_Snezenych_Kalorii;
        SET carbs = 0.4246 * NEW.Cil_Snezenych_Kalorii;
        SET fats = 0.2806 * NEW.Cil_Snezenych_Kalorii;
        SET fiber = 0.0222 * NEW.Cil_Snezenych_Kalorii;

        UPDATE makra
        SET Bilkoviny = protein,
            Sacharidy = carbs,
            Tuky = fats,
            Vlaknina = fiber
        WHERE ID_Makra = NEW.Id_Nastaveni;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `Nastaveni_Insert_Uzivatel` AFTER INSERT ON `nastaveni` FOR EACH ROW BEGIN
    -- Aktualizace sloupce Nastaveni_Uzivatele v tabulce uzivatel
    UPDATE uzivatel SET Nastaveni_Uzivatele = NEW.Id_Nastaveni ORDER BY Id_Uzivatele DESC LIMIT 1;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `Update_Počítej_Makro_žena_Fit` AFTER UPDATE ON `nastaveni` FOR EACH ROW BEGIN
    DECLARE protein DECIMAL(6,2);
    DECLARE carbs DECIMAL(6,2);
    DECLARE fats DECIMAL(6,2);
    DECLARE fiber DECIMAL(6,2);

    IF OLD.Cile != NEW.Cile OR OLD.Vaha != NEW.Vaha THEN
        IF NEW.Cile = 'Zůstat fit' AND NEW.Pohlavi = 'Žena' THEN
            SET protein = 0.2473 * NEW.Cil_Snezenych_Kalorii;
            SET carbs = 0.4618 * NEW.Cil_Snezenych_Kalorii;
            SET fats = 0.2687 * NEW.Cil_Snezenych_Kalorii;
            SET fiber = 0.0222 * NEW.Cil_Snezenych_Kalorii;

            UPDATE makra
            SET Bilkoviny = protein,
                Sacharidy = carbs,
                Tuky = fats,
                Vlaknina = fiber
            WHERE ID_Makra = NEW.Id_Nastaveni;
        END IF;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `Update_Počítej_Makro_žena_Svaly` AFTER UPDATE ON `nastaveni` FOR EACH ROW BEGIN
    DECLARE protein DECIMAL(6,2);
    DECLARE carbs DECIMAL(6,2);
    DECLARE fats DECIMAL(6,2);
    DECLARE fiber DECIMAL(6,2);

    IF OLD.Cile != NEW.Cile OR OLD.Vaha != NEW.Vaha THEN
        IF NEW.Cile = 'Nabrat svalovou hmotu' AND NEW.Pohlavi = 'Žena' THEN
            SET protein = 0.2461 * NEW.Cil_Snezenych_Kalorii;
            SET carbs = 0.4908 * NEW.Cil_Snezenych_Kalorii;
            SET fats = 0.2537 * NEW.Cil_Snezenych_Kalorii;
            SET fiber = 0.017 * NEW.Cil_Snezenych_Kalorii;

            UPDATE makra
            SET Bilkoviny = protein,
                Sacharidy = carbs,
                Tuky = fats,
                Vlaknina = fiber
            WHERE ID_Makra = NEW.Id_Nastaveni;
        END IF;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `Update_Počítej_Makro_žena_hubne` AFTER UPDATE ON `nastaveni` FOR EACH ROW BEGIN
    DECLARE protein DECIMAL(6,2);
    DECLARE carbs DECIMAL(6,2);
    DECLARE fats DECIMAL(6,2);
    DECLARE fiber DECIMAL(6,2);

    IF OLD.Cile != NEW.Cile OR OLD.Vaha != NEW.Vaha THEN
        IF NEW.Cile = 'Zhubout' AND NEW.Pohlavi = 'Žena' THEN
            SET protein = 0.2760 * NEW.Cil_Snezenych_Kalorii;
            SET carbs = 0.4246 * NEW.Cil_Snezenych_Kalorii;
            SET fats = 0.2806 * NEW.Cil_Snezenych_Kalorii;
            SET fiber = 0.0222 * NEW.Cil_Snezenych_Kalorii;

            UPDATE makra
            SET Bilkoviny = protein,
                Sacharidy = carbs,
                Tuky = fats,
                Vlaknina = fiber
            WHERE ID_Makra = NEW.Id_Nastaveni;
        END IF;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `Update_Počítej_Makroživiny_Muž_Fit` AFTER UPDATE ON `nastaveni` FOR EACH ROW BEGIN
    DECLARE protein DECIMAL(6,2);
    DECLARE carbs DECIMAL(6,2);
    DECLARE fats DECIMAL(6,2);
    DECLARE fiber DECIMAL(6,2);

    IF OLD.Cile != NEW.Cile OR OLD.Vaha != NEW.Vaha THEN
        IF NEW.Cile = 'Zůstat fit' AND NEW.Pohlavi = 'Muž' THEN
            SET protein = 0.2451 * NEW.Cil_Snezenych_Kalorii;
            SET carbs = 0.4635 * NEW.Cil_Snezenych_Kalorii;
            SET fats = 0.2730 * NEW.Cil_Snezenych_Kalorii;
            SET fiber = 0.0203 * NEW.Cil_Snezenych_Kalorii;

            UPDATE makra
            SET Bilkoviny = protein,
                Sacharidy = carbs,
                Tuky = fats,
                Vlaknina = fiber
            WHERE ID_Makra = NEW.Id_Nastaveni;
        END IF;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `Update_Počítej_Makroživiny_Muž_Hubne` AFTER UPDATE ON `nastaveni` FOR EACH ROW BEGIN
    DECLARE protein DECIMAL(6,2);
    DECLARE carbs DECIMAL(6,2);
    DECLARE fats DECIMAL(6,2);
    DECLARE fiber DECIMAL(6,2);

    IF OLD.Cile != NEW.Cile OR OLD.Vaha != NEW.Vaha THEN
        IF NEW.Cile = 'Zhubout' AND NEW.Pohlavi = 'Muž' THEN
            SET protein = 0.2766 * NEW.Cil_Snezenych_Kalorii;
            SET carbs = 0.4247 * NEW.Cil_Snezenych_Kalorii;
            SET fats = 0.2801 * NEW.Cil_Snezenych_Kalorii;
            SET fiber = 0.0203 * NEW.Cil_Snezenych_Kalorii;

            UPDATE makra
            SET Bilkoviny = protein,
                Sacharidy = carbs,
                Tuky = fats,
                Vlaknina = fiber
            WHERE ID_Makra = NEW.Id_Nastaveni;
        END IF;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `Update_Počítej_Makroživiny_Muž_Svaly` AFTER UPDATE ON `nastaveni` FOR EACH ROW BEGIN
    DECLARE protein DECIMAL(6,2);
    DECLARE carbs DECIMAL(6,2);
    DECLARE fats DECIMAL(6,2);
    DECLARE fiber DECIMAL(6,2);

    IF OLD.Cile != NEW.Cile OR OLD.Vaha != NEW.Vaha THEN
        IF NEW.Cile = 'Nabrat svalovou hmotu' AND NEW.Pohlavi = 'Muž' THEN
            SET protein = 0.2456 * NEW.Cil_Snezenych_Kalorii;
            SET carbs = 0.4819 * NEW.Cil_Snezenych_Kalorii;
            SET fats = 0.2725 * NEW.Cil_Snezenych_Kalorii;
            SET fiber = 0.0173 * NEW.Cil_Snezenych_Kalorii;

            UPDATE makra
            SET Bilkoviny = protein,
                Sacharidy = carbs,
                Tuky = fats,
                Vlaknina = fiber
            WHERE ID_Makra = NEW.Id_Nastaveni;
        END IF;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_nastaveni_insert` AFTER INSERT ON `nastaveni` FOR EACH ROW BEGIN
    INSERT INTO makra (ID_Makra, Pitny_rezim, Cil_Snezenych_Kalorii)
    VALUES (NEW.Id_Nastaveni, NEW.pitny_rezim, NEW.Cil_Snezenych_Kalorii);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `nastaveni_before_insert_Fit_Muž` BEFORE INSERT ON `nastaveni` FOR EACH ROW BEGIN
    IF NEW.`Pohlavi` = 'Muž' AND NEW.`Cile` = 'Zůstat fit' THEN
            SET NEW.`Cil_Snezenych_Kalorii` = ROUND((10 * NEW.`Vaha`) + (6.25 * NEW.`Vyska`) - (5 * (YEAR(CURDATE()) - YEAR(NEW.`Datum_Narozeni`))));
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `nastaveni_before_insert_Fit_žena` BEFORE INSERT ON `nastaveni` FOR EACH ROW BEGIN
    IF NEW.`Pohlavi` = 'Žena' AND NEW.`Cile` = 'Zůstat fit' THEN
        SET NEW.`Cil_Snezenych_Kalorii` = ROUND((10 * NEW.`Vaha`) + (6.25 * NEW.`Vyska`) - (5 * (YEAR(CURDATE()) - YEAR(NEW.`Datum_Narozeni`))) - 161);
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `nastaveni_before_insert_Hubne_muž` BEFORE INSERT ON `nastaveni` FOR EACH ROW BEGIN
    IF NEW.`Pohlavi` = 'Muž' AND NEW.`Cile` = 'Zhubout' THEN
        SET NEW.`Cil_Snezenych_Kalorii` = ROUND(((10 * NEW.`Vaha`) + (6.25 * NEW.`Vyska`) - (5 * (YEAR(CURDATE()) - YEAR(NEW.`Datum_Narozeni`)))) * 0.85);
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `nastaveni_before_insert_Hubne_žena` BEFORE INSERT ON `nastaveni` FOR EACH ROW BEGIN
  IF NEW.`Pohlavi` = 'Žena' AND NEW.`Cile` = 'Zhubout' THEN
        SET NEW.`Cil_Snezenych_Kalorii` = ROUND(((10 * NEW.`Vaha`) + (6.25 * NEW.`Vyska`) - (5 * (YEAR(CURDATE()) - YEAR(NEW.`Datum_Narozeni`))) - 161) * 0.85);
    END IF;  
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `nastaveni_before_insert_Svaly_Muž` BEFORE INSERT ON `nastaveni` FOR EACH ROW BEGIN
    IF NEW.`Pohlavi` = 'Muž' AND NEW.`Cile` = 'Nabrat svalovou hmotu' THEN
        SET NEW.`Cil_Snezenych_Kalorii` = ROUND(NEW.`Vaha` * 22.69148936);
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `nastaveni_before_insert_Svaly_Žena` BEFORE INSERT ON `nastaveni` FOR EACH ROW BEGIN
    IF NEW.`Pohlavi` = 'Žena' AND NEW.`Cile` = 'Nabrat svalovou hmotu' THEN
        SET NEW.`Cil_Snezenych_Kalorii` = ROUND(NEW.`Vaha` * 26.26);
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `nastaveni_before_update_Fit_Muž` BEFORE UPDATE ON `nastaveni` FOR EACH ROW BEGIN
    IF NEW.`Pohlavi` = 'Muž' AND NEW.`Cile` = 'Zůstat fit' THEN
        SET NEW.`Cil_Snezenych_Kalorii` = ROUND((10 * NEW.`Vaha`) + (6.25 * NEW.`Vyska`) - (5 * (YEAR(CURDATE()) - YEAR(NEW.`Datum_Narozeni`))));
    END IF;

    IF OLD.`Vaha` != NEW.`Vaha` THEN
        IF NEW.`Pohlavi` = 'Muž' AND NEW.`Cile` = 'Zůstat fit' THEN
            SET NEW.`Cil_Snezenych_Kalorii` = ROUND((10 * NEW.`Vaha`) + (6.25 * NEW.`Vyska`) - (5 * (YEAR(CURDATE()) - YEAR(NEW.`Datum_Narozeni`))));
        END IF;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `nastaveni_before_update_Fit_žena` BEFORE UPDATE ON `nastaveni` FOR EACH ROW BEGIN
    IF NEW.`Pohlavi` = 'Žena' AND NEW.`Cile` = 'Zůstat fit' THEN
        SET NEW.`Cil_Snezenych_Kalorii` = ROUND((10 * NEW.`Vaha`) + (6.25 * NEW.`Vyska`) - (5 * (YEAR(CURDATE()) - YEAR(NEW.`Datum_Narozeni`))) - 161);
    END IF;

    IF OLD.`Vaha` != NEW.`Vaha` THEN
        IF NEW.`Pohlavi` = 'Žena' AND NEW.`Cile` = 'Zůstat fit' THEN
            SET NEW.`Cil_Snezenych_Kalorii` = ROUND((10 * NEW.`Vaha`) + (6.25 * NEW.`Vyska`) - (5 * (YEAR(CURDATE()) - YEAR(NEW.`Datum_Narozeni`))) - 161);
        END IF;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `nastaveni_before_update_Hubne_muž` BEFORE UPDATE ON `nastaveni` FOR EACH ROW BEGIN
    IF NEW.Pohlavi = 'Muž' AND NEW.Cile = 'Zhubout' THEN
        SET NEW.Cil_Snezenych_Kalorii = ROUND((10 * NEW.Vaha) + (6.25 * NEW.Vyska) - (5 * (YEAR(CURDATE()) - YEAR(NEW.Datum_Narozeni)))) * 0.85;
    END IF;

    IF OLD.Vaha != NEW.Vaha THEN
        SET NEW.Cil_Snezenych_Kalorii = ROUND((10 * NEW.Vaha) + (6.25 * NEW.Vyska) - (5 * (YEAR(CURDATE()) - YEAR(NEW.Datum_Narozeni)))) * 0.85;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `nastaveni_before_update_Hubne_zena` BEFORE UPDATE ON `nastaveni` FOR EACH ROW BEGIN
    IF NEW.`Pohlavi` = 'Žena' AND NEW.`Cile` = 'Zhubout' THEN
        SET NEW.`Cil_Snezenych_Kalorii` = ROUND(((10 * NEW.`Vaha`) + (6.25 * NEW.`Vyska`) - (5 * (YEAR(CURDATE()) - YEAR(NEW.`Datum_Narozeni`))) - 161) * 0.85);
    END IF;

    IF OLD.`Vaha` != NEW.`Vaha` THEN
        IF NEW.`Pohlavi` = 'Žena' THEN
            SET NEW.`Cil_Snezenych_Kalorii` = ROUND(((10 * NEW.`Vaha`) + (6.25 * NEW.`Vyska`) - (5 * (YEAR(CURDATE()) - YEAR(NEW.`Datum_Narozeni`))) - 161) * 0.85);
        END IF;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `nastaveni_before_update_Svaly_muž` BEFORE UPDATE ON `nastaveni` FOR EACH ROW BEGIN
    IF NEW.`Pohlavi` = 'Muž' AND NEW.`Cile` = 'Nabrat svalovou hmotu' THEN
        SET NEW.`Cil_Snezenych_Kalorii` = ROUND(NEW.`Vaha` * 22.69148936);
    END IF;
    
    IF NEW.`Cile` = 'Nabrat svalovou hmotu' AND NEW.`Cile` <> OLD.`Cile` THEN
        SET NEW.`Cil_Snezenych_Kalorii` = ROUND(NEW.`Vaha` * 22.69148936);
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `nastaveni_before_update_Svaly_Žena` BEFORE UPDATE ON `nastaveni` FOR EACH ROW BEGIN
    IF NEW.`Pohlavi` = 'Žena' AND NEW.`Cile` = 'Nabrat svalovou hmotu' THEN
        SET NEW.`Cil_Snezenych_Kalorii` = ROUND(NEW.`Vaha` * 26.26);
    END IF;

    IF NEW.`Cile` = 'Nabrat svalovou hmotu' AND NEW.`Cile` <> OLD.`Cile` AND NEW.`Pohlavi` = 'Žena' THEN
        SET NEW.`Cil_Snezenych_Kalorii` = ROUND(NEW.`Vaha` * 26.26);
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `spocti_pitny_rezim` BEFORE INSERT ON `nastaveni` FOR EACH ROW BEGIN
    DECLARE pitny_rezim int(11);
    SET pitny_rezim = (NEW.vyska - 88) * 40;

    SET NEW.pitny_rezim = pitny_rezim;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_jidelnicek_after_update` AFTER UPDATE ON `nastaveni` FOR EACH ROW BEGIN
    IF OLD.Vaha != NEW.Vaha OR OLD.Cile != NEW.Cile THEN
        UPDATE makra
        SET Pitny_rezim = NEW.pitny_rezim,
            Cil_Snezenych_Kalorii = NEW.Cil_Snezenych_Kalorii
        WHERE ID_Makra  = NEW.Id_Nastaveni;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktura tabulky `piti`
--

CREATE TABLE `piti` (
  `id_Piti` int(11) NOT NULL,
  `Nazev_Piti` varchar(128) DEFAULT NULL,
  `Energeticka_Hodnota` decimal(6,2) NOT NULL,
  `Bílkoviny` decimal(6,2) DEFAULT NULL,
  `Sacharidy` decimal(6,2) DEFAULT NULL,
  `Tuky` decimal(6,2) DEFAULT NULL,
  `Vláknina` decimal(6,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vypisuji data pro tabulku `piti`
--

INSERT INTO `piti` (`id_Piti`, `Nazev_Piti`, `Energeticka_Hodnota`, `Bílkoviny`, `Sacharidy`, `Tuky`, `Vláknina`) VALUES
(1, 'Voda', 0.00, 0.00, 0.00, 0.00, 0.00),
(2, 'Coca-Cola', 42.00, 0.00, 10.60, 0.00, 0.00),
(3, 'Pomerančový džus', 45.00, 1.00, 9.00, 0.20, 0.50),
(4, 'Jablčný džus', 48.00, 0.10, 11.80, 0.10, 0.20);

-- --------------------------------------------------------

--
-- Struktura tabulky `potraviny`
--

CREATE TABLE `potraviny` (
  `Id_Potraviny` int(11) NOT NULL,
  `Nazev_Potraviny` varchar(128) DEFAULT NULL,
  `Energeticka_Hodnota` decimal(6,2) NOT NULL,
  `Bílkoviny` decimal(6,2) DEFAULT NULL,
  `Sacharidy` decimal(6,2) DEFAULT NULL,
  `Tuky` decimal(6,2) DEFAULT NULL,
  `Vláknina` decimal(6,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vypisuji data pro tabulku `potraviny`
--

INSERT INTO `potraviny` (`Id_Potraviny`, `Nazev_Potraviny`, `Energeticka_Hodnota`, `Bílkoviny`, `Sacharidy`, `Tuky`, `Vláknina`) VALUES
(3, 'Vejce', 151.00, 12.38, 0.94, 10.87, 0.00),
(4, 'Banán', 94.00, 0.50, 22.00, 0.20, 2.00),
(5, 'Řecký jogurt bílý', 58.00, 10.00, 3.50, 0.30, 0.00),
(6, 'Okurka Salátová', 16.00, 0.82, 2.00, 0.18, 0.93),
(7, 'brambory syrové', 88.00, 2.00, 19.00, 0.20, 1.50),
(8, 'Eidam 30%', 254.00, 27.00, 0.50, 16.00, 0.00),
(9, 'šunka kuřecí prsní', 98.00, 14.00, 1.50, 4.00, 0.00),
(10, 'Rye Bread žitný celozrnný chléb', 212.00, 5.80, 38.00, 1.80, 9.30),
(11, 'ledový salát', 16.00, 0.70, 2.00, 0.14, 1.20),
(12, 'paprika červená', 35.00, 1.00, 6.00, 0.30, 1.70),
(13, 'paprika žlutá', 30.00, 1.10, 4.60, 0.39, 2.00),
(14, 'paprika zelená', 30.00, 1.13, 4.59, 0.39, 1.97),
(15, 'Mrkev', 35.00, 1.00, 7.34, 0.22, 3.51),
(16, 'kuřecí prsa syrová', 106.00, 23.10, 0.00, 1.50, 0.00),
(17, 'hovězí maso mleté', 221.00, 17.00, 0.00, 17.00, 0.00),
(18, 'hovězí maso libové syrové', 158.00, 20.70, 0.50, 8.00, 0.00),
(19, 'vepřová kotleta libová bez kosti', 128.00, 23.00, 0.00, 4.00, 0.00),
(20, 'vepřová krkovice bez kosti libová syrová', 173.00, 19.90, 0.00, 10.40, 0.00),
(21, 'Losos', 196.00, 22.00, 0.00, 12.00, 0.12),
(22, 'Rybí Filé', 70.00, 16.20, 0.10, 0.50, 0.00),
(23, 'hovězí přední bez kosti syrové', 176.00, 20.70, 0.00, 9.80, 0.00),
(24, 'hovězí zadní bez kosti', 116.00, 20.90, 0.30, 6.00, 0.00),
(25, 'kuřecí stehenní řízek', 107.00, 20.00, 0.00, 3.00, 0.00),
(26, 'Kaiserka cereální', 262.00, 10.00, 43.00, 5.00, 3.00),
(27, 'Kaiserka', 270.00, 10.00, 50.00, 3.00, 2.00),
(28, 'rohlík bílý', 310.00, 9.78, 57.47, 3.68, 4.45),
(29, 'rohlík tukový', 286.00, 9.00, 55.00, 3.00, 2.00),
(30, 'chléb konzumní kmínový', 244.00, 8.00, 45.00, 1.10, 4.00),
(31, 'chléb žitný', 7.00, 5.00, 48.30, 1.00, 6.00),
(32, 'rýže jasmínová', 346.00, 7.00, 79.00, 0.00, 1.50),
(33, 'jasmínová rýže vařená', 113.00, 2.70, 25.00, 0.20, 0.50),
(34, 'brambory syrové', 88.00, 2.00, 19.00, 0.20, 1.50),
(35, 'Brambory Vařené', 85.00, 1.70, 18.60, 0.10, 1.40),
(36, 'batáty sladké brambory', 83.00, 1.00, 17.00, 0.14, 3.00),
(37, 'černá čočka', 307.00, 23.00, 41.00, 1.60, 0.00),
(38, 'čočka vařená', 110.00, 7.80, 17.17, 0.40, 3.70),
(39, 'Špagety syrové', 366.00, 12.00, 72.00, 2.00, 3.60),
(40, 'Jablko', 63.00, 0.37, 12.95, 0.40, 3.14),
(41, 'Hruška', 58.00, 0.44, 13.42, 0.33, 3.34),
(42, 'hroznové víno', 77.00, 0.68, 16.93, 0.35, 2.14),
(43, 'hroznové víno bílé bezsemenné', 76.00, 0.60, 17.00, 0.40, 0.90),
(44, 'Máslo', 748.00, 0.70, 0.52, 82.58, 0.00),
(45, 'Perla máslová příchuť', 708.00, 2.50, 0.50, 80.00, 0.31),
(46, 'Mozzarella Galbani', 236.00, 17.00, 2.00, 18.00, 0.00),
(47, 'Mozzarella light Galbani', 165.00, 19.00, 2.00, 9.00, 0.00),
(48, 'Hermelín Originál Sedlčanský', 309.00, 19.00, 0.50, 26.00, 0.00),
(49, 'šunka vepřová dušená nejvyšší jakosti', 118.00, 19.00, 0.50, 4.50, 0.00),
(50, 'šunka od kosti nejvyšší jakosti 95% masa shaved LE & CO', 155.00, 19.10, 1.40, 8.00, 0.00),
(51, 'Meloun vodní', 29.00, 0.65, 6.00, 0.17, 0.69),
(73, 'Vejce', 226.67, 18.57, 1.41, 16.31, 0.00),
(75, 'vejce', 226.67, 18.57, 1.41, 16.31, 0.00),
(76, 'dadada', 226.67, 18.57, 1.41, 16.31, 0.00);

-- --------------------------------------------------------

--
-- Struktura tabulky `recepty`
--

CREATE TABLE `recepty` (
  `ID` int(11) NOT NULL,
  `Nazev` varchar(255) NOT NULL,
  `Postup` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vypisuji data pro tabulku `recepty`
--

INSERT INTO `recepty` (`ID`, `Nazev`, `Postup`) VALUES
(34, 'dadada', 'dadada');

-- --------------------------------------------------------

--
-- Struktura tabulky `recepty_potraviny`
--

CREATE TABLE `recepty_potraviny` (
  `ID` int(11) NOT NULL,
  `Recept_ID` int(11) DEFAULT NULL,
  `Potravina_ID` int(11) DEFAULT NULL,
  `Mnozstvi_g` decimal(8,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vypisuji data pro tabulku `recepty_potraviny`
--

INSERT INTO `recepty_potraviny` (`ID`, `Recept_ID`, `Potravina_ID`, `Mnozstvi_g`) VALUES
(42, 34, 3, 150.00);

-- --------------------------------------------------------

--
-- Struktura tabulky `uzivatel`
--

CREATE TABLE `uzivatel` (
  `Id_Uzivatele` int(11) NOT NULL,
  `Email_Uzivatele` varchar(128) DEFAULT NULL,
  `Heslo_Uzivatele` varchar(64) DEFAULT NULL,
  `Nastaveni_Uzivatele` int(6) DEFAULT NULL,
  `Makra_Uzivatele` int(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vypisuji data pro tabulku `uzivatel`
--

INSERT INTO `uzivatel` (`Id_Uzivatele`, `Email_Uzivatele`, `Heslo_Uzivatele`, `Nastaveni_Uzivatele`, `Makra_Uzivatele`) VALUES
(41, 'kajik2004@seznam.cz', 'f900d30794b50f92e6400e66d2963232', 135, 135),
(42, 'tereza.z@seznam.cz', '44448f65f1126a30f11b0b7c9809fdb9', 136, 136),
(43, 'frantisek@seznam.cz', 'b1c1d81fff45dda531d870a06a63cd6e', 137, 137),
(44, '123@seznam.cz', '202cb962ac59075b964b07152d234b70', 138, 138),
(45, '123@seznam.cz', '202cb962ac59075b964b07152d234b70', 139, 139),
(46, '123@seznam.cz', '202cb962ac59075b964b07152d234b70', 140, 140),
(47, '123@seznam.cz', '202cb962ac59075b964b07152d234b70', 141, 141),
(48, '123@seznam.cz', '202cb962ac59075b964b07152d234b70', 142, 142),
(49, '123@seznam.cz', '202cb962ac59075b964b07152d234b70', 143, 143),
(50, 'nevim@seznam.cz', 'f900d30794b50f92e6400e66d2963232', 144, 144),
(51, '123@123', '202cb962ac59075b964b07152d234b70', 145, 145),
(52, 'zak@se.cz', '202cb962ac59075b964b07152d234b70', 146, 146);

--
-- Indexy pro exportované tabulky
--

--
-- Indexy pro tabulku `aktivity`
--
ALTER TABLE `aktivity`
  ADD PRIMARY KEY (`Id_Aktivity`);

--
-- Indexy pro tabulku `cast_dne`
--
ALTER TABLE `cast_dne`
  ADD PRIMARY KEY (`ID_Casti`);

--
-- Indexy pro tabulku `cviceni`
--
ALTER TABLE `cviceni`
  ADD PRIMARY KEY (`id_cviceni`),
  ADD KEY `id_aktivity` (`id_aktivity`),
  ADD KEY `Id_Uzivatele` (`Id_Uzivatele`);

--
-- Indexy pro tabulku `jidlo`
--
ALTER TABLE `jidlo`
  ADD PRIMARY KEY (`id_jidla`),
  ADD KEY `cast_dne` (`cast_dne`),
  ADD KEY `fk_Jidlo_Uzivatele` (`Id_Uzivatele`),
  ADD KEY `fk_Jidlo_Potraviny` (`potravina`);

--
-- Indexy pro tabulku `makra`
--
ALTER TABLE `makra`
  ADD PRIMARY KEY (`ID_Makra`);

--
-- Indexy pro tabulku `napoje`
--
ALTER TABLE `napoje`
  ADD PRIMARY KEY (`id_napoje`),
  ADD KEY `fk_Napoje_Uzivatele` (`Id_Uzivatele`),
  ADD KEY `fk_napoje_piti` (`piti`),
  ADD KEY `cast_dne` (`cast_dne`);

--
-- Indexy pro tabulku `nastaveni`
--
ALTER TABLE `nastaveni`
  ADD PRIMARY KEY (`Id_Nastaveni`);

--
-- Indexy pro tabulku `piti`
--
ALTER TABLE `piti`
  ADD PRIMARY KEY (`id_Piti`);

--
-- Indexy pro tabulku `potraviny`
--
ALTER TABLE `potraviny`
  ADD PRIMARY KEY (`Id_Potraviny`);

--
-- Indexy pro tabulku `recepty`
--
ALTER TABLE `recepty`
  ADD PRIMARY KEY (`ID`);

--
-- Indexy pro tabulku `recepty_potraviny`
--
ALTER TABLE `recepty_potraviny`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `Recept_ID` (`Recept_ID`),
  ADD KEY `Potravina_ID` (`Potravina_ID`);

--
-- Indexy pro tabulku `uzivatel`
--
ALTER TABLE `uzivatel`
  ADD PRIMARY KEY (`Id_Uzivatele`),
  ADD KEY `FK_Nastaveni_Uzivatele` (`Nastaveni_Uzivatele`),
  ADD KEY `fk_uzivatel_makra` (`Makra_Uzivatele`);

--
-- AUTO_INCREMENT pro tabulky
--

--
-- AUTO_INCREMENT pro tabulku `aktivity`
--
ALTER TABLE `aktivity`
  MODIFY `Id_Aktivity` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT pro tabulku `cviceni`
--
ALTER TABLE `cviceni`
  MODIFY `id_cviceni` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT pro tabulku `jidlo`
--
ALTER TABLE `jidlo`
  MODIFY `id_jidla` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=160;

--
-- AUTO_INCREMENT pro tabulku `napoje`
--
ALTER TABLE `napoje`
  MODIFY `id_napoje` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT pro tabulku `nastaveni`
--
ALTER TABLE `nastaveni`
  MODIFY `Id_Nastaveni` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=147;

--
-- AUTO_INCREMENT pro tabulku `piti`
--
ALTER TABLE `piti`
  MODIFY `id_Piti` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pro tabulku `potraviny`
--
ALTER TABLE `potraviny`
  MODIFY `Id_Potraviny` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT pro tabulku `recepty`
--
ALTER TABLE `recepty`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT pro tabulku `recepty_potraviny`
--
ALTER TABLE `recepty_potraviny`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT pro tabulku `uzivatel`
--
ALTER TABLE `uzivatel`
  MODIFY `Id_Uzivatele` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- Omezení pro exportované tabulky
--

--
-- Omezení pro tabulku `cviceni`
--
ALTER TABLE `cviceni`
  ADD CONSTRAINT `cviceni_ibfk_1` FOREIGN KEY (`id_aktivity`) REFERENCES `aktivity` (`Id_Aktivity`),
  ADD CONSTRAINT `cviceni_ibfk_2` FOREIGN KEY (`Id_Uzivatele`) REFERENCES `uzivatel` (`Id_Uzivatele`);

--
-- Omezení pro tabulku `jidlo`
--
ALTER TABLE `jidlo`
  ADD CONSTRAINT `fk_Jidlo_Potraviny` FOREIGN KEY (`potravina`) REFERENCES `potraviny` (`Id_Potraviny`),
  ADD CONSTRAINT `fk_Jidlo_Uzivatele` FOREIGN KEY (`Id_Uzivatele`) REFERENCES `uzivatel` (`Id_Uzivatele`),
  ADD CONSTRAINT `jidlo_ibfk_1` FOREIGN KEY (`potravina`) REFERENCES `potraviny` (`Id_Potraviny`),
  ADD CONSTRAINT `jidlo_ibfk_2` FOREIGN KEY (`cast_dne`) REFERENCES `cast_dne` (`ID_Casti`);

--
-- Omezení pro tabulku `napoje`
--
ALTER TABLE `napoje`
  ADD CONSTRAINT `cast_dne` FOREIGN KEY (`cast_dne`) REFERENCES `cast_dne` (`ID_Casti`),
  ADD CONSTRAINT `fk_Napoje_Uzivatele` FOREIGN KEY (`Id_Uzivatele`) REFERENCES `uzivatel` (`Id_Uzivatele`),
  ADD CONSTRAINT `fk_napoje_cast_dne` FOREIGN KEY (`cast_dne`) REFERENCES `cast_dne` (`ID_Casti`),
  ADD CONSTRAINT `fk_napoje_piti` FOREIGN KEY (`piti`) REFERENCES `piti` (`id_Piti`);

--
-- Omezení pro tabulku `recepty_potraviny`
--
ALTER TABLE `recepty_potraviny`
  ADD CONSTRAINT `recepty_potraviny_ibfk_1` FOREIGN KEY (`Recept_ID`) REFERENCES `recepty` (`ID`),
  ADD CONSTRAINT `recepty_potraviny_ibfk_2` FOREIGN KEY (`Potravina_ID`) REFERENCES `potraviny` (`Id_Potraviny`);

--
-- Omezení pro tabulku `uzivatel`
--
ALTER TABLE `uzivatel`
  ADD CONSTRAINT `FK_Nastaveni_Uzivatele` FOREIGN KEY (`Nastaveni_Uzivatele`) REFERENCES `nastaveni` (`Id_Nastaveni`),
  ADD CONSTRAINT `fk_uzivatel_makra` FOREIGN KEY (`Makra_Uzivatele`) REFERENCES `makra` (`ID_Makra`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
