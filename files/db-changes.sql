# 14.02.2015
UPDATE `participants` SET `game` = "hsold", `deleted` = 1 WHERE `game` = "hs";
UPDATE `participants` SET `game` = "hs", `server` = "s1" WHERE `game` = "hslan";
ALTER TABLE `tournaments` CHANGE `status` `status` ENUM('Ended','Start') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
UPDATE `tournaments` SET `game` = "hsold" WHERE `game` = "hs";
UPDATE `tournaments` SET `server` = "s1", `game` = "hs" WHERE `game` = "hslan";

# 13.02.2015
UPDATE `tm_modules` SET `name` = 'tournamentSmiteNa' WHERE `tm_modules`.`name` = 'tournamentSmite';

# 11.02.2015
CREATE TABLE IF NOT EXISTS `lol_games` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `match_id` bigint(20) unsigned NOT NULL,
  `game_id` bigint(20) unsigned DEFAULT NULL,
  `participant_id1` int(10) unsigned DEFAULT NULL,
  `participant_id2` int(10) unsigned DEFAULT NULL,
  `message` text,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ended` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

# 10.02.2015
ALTER TABLE `streams` ADD `participant_id` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `user_id`, ADD INDEX (`participant_id`) ;

# 03.02.2015
INSERT INTO `tm_settings` (`setting`, `value`, `field`, `type`, `position`) VALUES
('tournament-start-smite-eu', '0', 'Старт турнира (Smite EU)', 'checkbox', 6),
('tournament-start-smite-na', '0', 'Старт турнира (Smite NA)', 'checkbox', 7);
ALTER TABLE `tournaments` CHANGE `game` `game` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `tournaments` CHANGE `server` `server` VARCHAR(5) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `players` CHANGE `game` `game` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `notifications` CHANGE `game` `game` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
INSERT INTO `tm_modules` (`name`, `level`, `added_timestamp`) VALUES ('tournamentEUNE', '4', CURRENT_TIMESTAMP), ('tournamentSmite', '4', CURRENT_TIMESTAMP), ('tournamentEUW', '4', CURRENT_TIMESTAMP);

# 24.01.2015
INSERT INTO `tm_settings` (`setting`, `value`, `field`, `type`, `position`) VALUES
('smite-current-number-na', '1', 'Название/номер турнира (Smite NA)', 'text', 6),
('tournament-auto-smite-na', '1', 'Авто продвижение (Smite NA)', 'checkbox', 6),
('tournament-checkin-smite-na', '0', 'Процесс checkin (Smite NA)', 'checkbox', 6),
('tournament-reg-smite-na', '1', 'Регистрация на турнир (Smite NA)', 'checkbox', 6);
INSERT INTO `tm_settings` (`setting`, `value`, `field`, `type`, `position`) VALUES
('smite-current-number-eu', '1', 'Название/номер турнира (Smite EU)', 'text', 7),
('tournament-auto-smite-eu', '1', 'Авто продвижение (Smite EU)', 'checkbox', 7),
('tournament-checkin-smite-eu', '0', 'Процесс checkin (Smite EU)', 'checkbox', 7),
('tournament-reg-smite-eu', '1', 'Регистрация на турнир (Smite EU)', 'checkbox', 7);
ALTER TABLE `tournaments` CHANGE `server` `server` ENUM('euw','eune','na','eu','') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `streams` CHANGE `game` `game` ENUM('lol','hs','dota','smite','cs','other','lolcup','smitecup') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

# 22.01.2015
ALTER TABLE `news_comments` ADD `edited` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' ;
ALTER TABLE `news_comments` ADD `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0 - ok, 1 - deleted, 2 - reported' AFTER `added`;

# 21.01.2015
CREATE TABLE IF NOT EXISTS `boards` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `category` enum('lol','hs','dota','smite','csgo','general') NOT NULL,
  `title` varchar(255) NOT NULL,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 - ok, 1 - deleted, 2 - reported',
  `text` text,
  `ip` varchar(20) NOT NULL,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `comments` int(10) unsigned NOT NULL DEFAULT '0',
  `votes` int(10) NOT NULL DEFAULT '0',
  `activity` int(10) unsigned NOT NULL,
  `edited` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `activity` (`activity`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
CREATE TABLE IF NOT EXISTS `boards_comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `board_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `answer_to_id` int(10) unsigned NOT NULL DEFAULT '0',
  `text` text NOT NULL,
  `ip` varchar(20) NOT NULL,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 - ok, 1 - deleted, 2 - reported',
  `votes` int(10) NOT NULL DEFAULT '0',
  `edited` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `board_id` (`board_id`,`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
CREATE TABLE IF NOT EXISTS `boards_votes` (
  `board_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `comment_id` int(10) unsigned NOT NULL DEFAULT '0',
  `direction` enum('plus','minus') NOT NULL,
  KEY `board_id` (`board_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


# 09.12.2014
INSERT INTO `tm_settings` (`setting`, `value`, `field`, `type`, `position`) VALUES ('dota-current-number', '1', 'Название (Dota)', 'text', '5'), ('tournament-reg-dota', '0', 'Регистрация на турнир (Dota)', 'checkbox', '5');
INSERT INTO `tm_settings` (`setting`, `value`, `field`, `type`, `position`) VALUES ('tournament-auto-dota', '1', 'Авто продвижение (Dota)', 'checkbox', '5'), ('tournament-checkin-dota', '1', 'Процесс checkin (Dota)', 'checkbox', '5');

ALTER TABLE `tournaments` CHANGE `game` `game` ENUM('lol','hs','hslan','dota','smite','cs') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `participants` CHANGE `game` `game` ENUM('lol','hs','hslan','dota','smite','cs') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `notifications` CHANGE `game` `game` ENUM('lol','loleuw','loleune','hs','hslan','dota','smite','cs') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `players` CHANGE `game` `game` ENUM('lol','hs','hslan','dota','smite','cs') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `streams` CHANGE `game` `game` ENUM('lol','hs','dota','smite','cs','other') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `subscribe` CHANGE `theme` `theme` ENUM('all','lol','hs','dota','smite','cs') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'all';

# 08.12.2014
ALTER TABLE `users` ADD `https` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0';
CREATE TABLE IF NOT EXISTS `dota_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` varchar(20) NOT NULL,
  `data` text NOT NULL,
  `response` text,
  `time` varchar(99) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

# 03.12.2014
ALTER TABLE `notifications` CHANGE `game` `game` ENUM('lol','loleuw','loleune','hs','hslan') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

# 26.11.2014
DELIMITER ;;

DROP PROCEDURE IF EXISTS cleanupOldData;;
CREATE PROCEDURE cleanupOldData()
    LANGUAGE SQL
    NOT DETERMINISTIC
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
BEGIN
    DECLARE v_delete_limit INT DEFAULT 1000;
    DECLARE v_row_count INT DEFAULT 0;
    
    SELECT 'Cleaning RiotRequests...';
    REPEAT
        -- SELECT '.';
        START TRANSACTION;
        DELETE FROM riot_requests
            WHERE `timestamp` < DATE_SUB( NOW(), INTERVAL 1 MONTH )
            ORDER BY `id`
            LIMIT v_delete_limit;
        SET v_row_count = ROW_COUNT();
        COMMIT;
        
        SELECT CONCAT( '... deleted ', v_row_count );
    UNTIL v_row_count < v_delete_limit
    END REPEAT;
    
    SELECT 'Cleaning ChallongeRequests...';
    REPEAT
        -- SELECT '.';
        START TRANSACTION;
        DELETE FROM challonge_requests
            WHERE `timestamp` < DATE_SUB( NOW(), INTERVAL 1 MONTH )
            ORDER BY `id`
            LIMIT v_delete_limit;
        SET v_row_count = ROW_COUNT();
        COMMIT;
        
        SELECT CONCAT( '... deleted ', v_row_count );
    UNTIL v_row_count < v_delete_limit
    END REPEAT;
    
    SELECT 'Cleaning TwitchRequest...';
    REPEAT
        -- SELECT '.';
        START TRANSACTION;
        DELETE FROM twitch_requests
            WHERE `timestamp` < DATE_SUB( NOW(), INTERVAL 1 MONTH )
            ORDER BY `id`
            LIMIT v_delete_limit;
        SET v_row_count = ROW_COUNT();
        COMMIT;
        
        SELECT CONCAT( '... deleted ', v_row_count );
    UNTIL v_row_count < v_delete_limit
    END REPEAT;
END;;

DELIMITER ;

# 19.11.2014
ALTER TABLE  `tournaments` ADD  `reg_activated` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '0';
ALTER TABLE  `users` ADD  `battletag` VARCHAR( 30 ) NULL;
ALTER TABLE  `tournaments` ADD  `finalized` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '0'

# 10.11.2014
CREATE TABLE IF NOT EXISTS `users_auth` (
  `user_id` int(10) unsigned NOT NULL,
  `token` varchar(50) NOT NULL,
  `time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE  `summoners` ADD  `approved` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '0';
ALTER TABLE  `summoners` ADD  `masteries` VARCHAR( 15 ) NULL;

# 06.11.2014
INSERT INTO `tm_settings` (`setting`, `value`, `field`, `type`, `position`) VALUES ('tournament-auto-lol-eune', '1', 'Авто продвижение (League of Legends - EUNE)', 'checkbox', '3');
INSERT INTO `tm_settings` (`setting`, `value`, `field`, `type`, `position`) VALUES ('tournament-auto-lol-euw', '1', 'Авто продвижение (League of Legends - EUW)', 'checkbox', '2');
ALTER TABLE  `lol_games` ADD  `ended` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '0';

# 16.10.2014
ALTER TABLE `participants` ADD  `checked_in` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '1';
ALTER TABLE `participants` CHANGE  `checked_in`  `checked_in` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '0';
INSERT INTO `tm_settings` (`setting`, `value`, `field`, `type`, `position`) VALUES ('tournament-checkin-lol-eune', '0', 'Процесс checkin (LoL - EUNE)', 'checkbox', '3'), ('tournament-checkin-lol-euw', '0', 'Процесс checkin (LoL - EUW)', 'checkbox', '1');

# 13.10.2014
CREATE TABLE `summoners` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`user_id` INT UNSIGNED NOT NULL ,
`region` VARCHAR( 10 ) NOT NULL ,
`summoner_id` BIGINT UNSIGNED NOT NULL ,
`name` VARCHAR( 100 ) NOT NULL ,
`added` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
`last_update` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = INNODB;


# 07.10.2014
ALTER TABLE  `tournaments` CHANGE  `dates`  `datesRegistration` VARCHAR( 15 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE  `tournaments` ADD  `datesStart` VARCHAR( 15 ) NOT NULL AFTER  `datesRegistration`;
ALTER TABLE  `tournaments` CHANGE  `datesRegistration`  `dates_registration` VARCHAR( 15 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE  `datesStart`  `dates_start` VARCHAR( 15 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE  `tournaments` ADD  `max_num` SMALLINT( 3 ) UNSIGNED NOT NULL DEFAULT  '0' AFTER  `prize`

# 25.09.2014
ALTER TABLE  `users` ADD  `avatar` TINYINT( 3 ) UNSIGNED NOT NULL DEFAULT  '1';

# 24.09.2014
DROP TABLE  `options`

# 23.09.2014
INSERT INTO `tm_modules` (`name` ,`level` ,`added_timestamp`) VALUES ('leagueParticipants',  '3', CURRENT_TIMESTAMP);

# 17.09.2014
ALTER TABLE  `teams` ADD  `team_id` INT UNSIGNED NOT NULL DEFAULT  '0' AFTER  `user_id` ,
ADD INDEX (  `team_id` );
ALTER TABLE  `teams` ADD  `seed_number` SMALLINT( 4 ) UNSIGNED NOT NULL DEFAULT  '0' AFTER  `place`;
ALTER TABLE  `tournaments` CHANGE  `status`  `status` ENUM(  'Ended',  'Registration',  'Start' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE  `players` CHANGE  `team_id`  `participant_id` INT( 10 ) UNSIGNED NOT NULL DEFAULT  '0';
ALTER TABLE  `lol_games` CHANGE  `teamId1`  `participant_id1` INT( 10 ) UNSIGNED NULL DEFAULT NULL ,
CHANGE  `teamId2`  `participant_id2` INT( 10 ) UNSIGNED NULL DEFAULT NULL;

RENAME TABLE `teams` TO `participants`;

# 16.09.2014
ALTER TABLE  `subscribe` ADD  `removed` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '0';
ALTER TABLE  `subscribe` ADD  `theme` ENUM('all',  'lol',  'hs') NOT NULL DEFAULT  'all';

# 10.09.2014
ALTER TABLE  `streams` CHANGE  `game`  `game` ENUM(  'lol',  'hs',  'other' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL

# 09.09.2014
ALTER TABLE  `tm_admins` ADD  `custom_access` TEXT NULL AFTER  `level`

# 29.08.2014
INSERT INTO `tm_modules` (`name` ,`level` ,`added_timestamp`) VALUES ('streamers', '2', CURRENT_TIMESTAMP);

# 10.08.2014
ALTER TABLE  `teams` CHANGE  `game`  `game` ENUM(  'lol',  'hs',  'hslan' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE  `players` CHANGE  `game`  `game` ENUM(  'lol',  'hs',  'hslan' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE  `tournaments` CHANGE  `game`  `game` ENUM(  'lol',  'hs',  'hslan' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

# 09.08.2014
INSERT INTO `tm_settings` (`setting`, `value`, `field`, `type`, `position`) VALUES ('tournament-reg-hslan', '0', 'Регистрация на турнир (Hearthstone LAN)', 'checkbox', '4'), ('hslan-current-number', '0', 'Номер турнира (Hearthstone LAN)', 'text', '4');
UPDATE `tm_settings` SET  `field` =  'Название/номер турнира (Hearthstone LAN)' WHERE  `tm_settings`.`setting` =  'hslan-current-number';

# 08.08.2014
CREATE TABLE `twitch_requests` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
`ip` VARCHAR( 20 ) NOT NULL ,
`data` TEXT NOT NULL ,
`response` TEXT NULL ,
`time` VARCHAR( 99 ) NULL
) ENGINE = INNODB;
ALTER TABLE  `streams` ADD  `approved` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '0';
ALTER TABLE  `teams` ADD  `user_id` INT UNSIGNED NOT NULL DEFAULT  '0' AFTER  `id` ,
ADD INDEX (  `user_id` );

# 06.08.2014
CREATE TABLE `streams` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`user_id` INT UNSIGNED NOT NULL ,
`name` VARCHAR( 200 ) NOT NULL ,
`featured` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '0',
`online` INT UNSIGNED NOT NULL DEFAULT  '0',
INDEX (  `user_id` )
) ENGINE = INNODB;
ALTER TABLE  `streams` ADD  `game` ENUM(  'lol',  'hs' ) NOT NULL ,
ADD  `languages` ENUM(  'ru',  'en',  'both' ) NOT NULL;
ALTER TABLE  `streams` ADD  `viewers` INT UNSIGNED NOT NULL DEFAULT  '0';
ALTER TABLE  `streams` ADD  `display_name` VARCHAR( 200 ) NULL AFTER  `name`;

# 24.07.2014
CREATE TABLE `news_comments` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`news_id` INT UNSIGNED NOT NULL ,
`user_id` INT UNSIGNED NOT NULL ,
`answer_to_id` INT UNSIGNED NOT NULL DEFAULT  '0',
`text` TEXT NOT NULL ,
`ip` VARCHAR( 20 ) NOT NULL ,
`added` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
INDEX (`news_id`, `user_id`)
) ENGINE = INNODB;

# 04.07.2014
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(250) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `name` varchar(32) DEFAULT NULL,
  `registration_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `timezone` varchar(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `users_social` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `social` char(2) NOT NULL,
  `social_uid` varchar(250) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

# 03.07.2014
DROP TABLE `lol_games`;
CREATE TABLE `lol_games` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`match_id` BIGINT UNSIGNED NOT NULL ,
`game_id` BIGINT UNSIGNED NULL DEFAULT NULL ,
`teamId1` INT UNSIGNED NULL ,
`teamId2` INT UNSIGNED NULL ,
`message` TEXT NULL
) ENGINE = INNODB;
ALTER TABLE `lol_games` ADD `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

# 26.06.2014
ALTER TABLE  `teams` ADD  `server` ENUM(  'euw',  'eune',  '' ) NULL AFTER  `game` , ADD INDEX (  `server` );
UPDATE `teams` SET `server` = "euw" WHERE `game` = "lol";
ALTER TABLE  `tournaments` ADD  `server` ENUM(  'euw',  'eune',  '' ) NULL AFTER  `game` , ADD INDEX (  `server` );
UPDATE `tournaments` SET `server` = "euw" WHERE `game` = "lol";

UPDATE `tm_settings` SET  `setting` =  'lol-current-number-euw' WHERE  `tm_settings`.`setting` =  'lol-current-number';
UPDATE `tm_settings` SET  `setting` =  'tournament-reg-lol-euw' WHERE  `tm_settings`.`setting` =  'tournament-reg-lol';
UPDATE `tm_settings` SET  `setting` =  'tournament-start-lol-euw' WHERE  `tm_settings`.`setting` =  'tournament-start-lol';
UPDATE `tm_settings` SET  `field` =  'Номер турнира (League of Legends - EUW)' WHERE  `tm_settings`.`setting` =  'lol-current-number-euw';
UPDATE `tm_settings` SET  `field` =  'Регистрация на турнир (LoL - EUW)' WHERE  `tm_settings`.`setting` =  'tournament-reg-lol-euw';
UPDATE `tm_settings` SET  `field` =  'Старт турнира (League of Legends - EUW)' WHERE  `tm_settings`.`setting` =  'tournament-start-lol-euw';
INSERT INTO `tm_settings` (`setting`, `value`, `field`, `type`, `position`) VALUES ('lol-current-number-eune', '1', 'Номер турнира (League of Legends - EUNE)', 'text', '3'), ('tournament-reg-lol-eune', '0', 'Регистрация на турнир (LoL - EUNE)', 'checkbox', '3');
INSERT INTO `tm_settings` (`setting`, `value`, `field`, `type`, `position`) VALUES ('tournament-start-lol-eune', '0', 'Старт турнира (League of Legends - EUNE)', 'checkbox', '3');
ALTER TABLE  `notifications` CHANGE  `delivered`  `delivered` TINYINT( 2 ) UNSIGNED NOT NULL;

# 14.06.2014
CREATE TABLE `riot_callback` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`callback` TEXT NOT NULL ,
`timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = INNODB;
ALTER TABLE  `riot_callback` ADD  `game_id` BIGINT UNSIGNED NOT NULL AFTER  `id`;
ALTER TABLE  `riot_callback` ADD  `status` ENUM(  'received',  'processed' ) NOT NULL DEFAULT  'received';

# 10.06.2014 - cms update
ALTER TABLE  `tm_admins` CHANGE  `language`  `language` CHAR( 2 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  'en'

# 04.06.2014
INSERT INTO `tm_modules` (`name` ,`level` ,`added_timestamp`) VALUES ('emails',  '3', CURRENT_TIMESTAMP);

# 03.06.2014
CREATE TABLE `notifications` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`game` ENUM('lol', 'hs') NOT NULL ,
`tournament_id` INT UNSIGNED NOT NULL ,
`delivered` TINYINT( 1 ) UNSIGNED NOT NULL ,
INDEX (  `game` ,  `tournament_id` )
) ENGINE = INNODB;
ALTER TABLE  `notifications` CHANGE  `tournament_id`  `tournament_name` VARCHAR( 50 ) NOT NULL;

# 29.05.2014
ALTER TABLE  `tournaments` ADD  `time` VARCHAR( 20 ) NULL AFTER  `dates`;
ALTER TABLE  `tournaments` CHANGE  `status`  `status` ENUM(  'Ended',  'Registration',  'Live',  'On hold', 'Start' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

# 28.05.2014 - cms update
ALTER TABLE  `tm_settings` ADD  `field` VARCHAR( 99 ) NULL ,
ADD  `type` ENUM(  'text',  'checkbox', 'level' ) NOT NULL DEFAULT  'text',
ADD  `position` TINYINT( 3 ) UNSIGNED NOT NULL DEFAULT  '0';

# 23.05.2014
RENAME TABLE `hs_fights` TO `fights`;
RENAME TABLE `lol_fights` TO `lol_games`;
ALTER TABLE `fights` CHANGE `challonge_tournament_id` `match_id` BIGINT( 20 ) UNSIGNED NOT NULL;
DELETE FROM `options` WHERE `options`.`id` =1;
DELETE FROM `options` WHERE `options`.`id` =4;
DELETE FROM `options` WHERE `options`.`id` =5;
DELETE FROM `options` WHERE `options`.`id` =6;
DELETE FROM `options` WHERE `options`.`id` =7;
DELETE FROM `options` WHERE `options`.`id` =9;
DELETE FROM `options` WHERE `options`.`id` =10;
DELETE FROM `options` WHERE `options`.`id` =11;
DELETE FROM `options` WHERE `options`.`id` =14;
DELETE FROM `options` WHERE `options`.`id` =15;
DELETE FROM `options` WHERE `options`.`id` =16;
DELETE FROM `options` WHERE `options`.`id` =17;
INSERT INTO `tm_modules` (`name` ,`level` ,`added_timestamp`) VALUES ('tournaments',  '1', CURRENT_TIMESTAMP);

# 22.05.2014
DROP TABLE `notifications`;
CREATE TABLE  `subscribe` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`email` VARCHAR( 99 ) NOT NULL ,
`unsublink` VARCHAR( 99 ) NOT NULL ,
INDEX (  `email` )
) ENGINE = INNODB;
ALTER TABLE  `subscribe` CHANGE  `unsublink`  `unsublink` VARCHAR( 200 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

# 19.05.2014
ALTER TABLE `players` DROP `approved`;
ALTER TABLE `players` DROP `deleted`;

# 16.05.2014
ALTER TABLE  `news` ADD  `views` INT UNSIGNED NOT NULL DEFAULT  '0';

# 13.05.2014
ALTER TABLE  `teams` ADD  `ended` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '0';

# 08.05.2014
ALTER TABLE  `teams` ADD  `place` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '0';
ALTER TABLE  `teams` ADD INDEX (  `game` );

--
-- Table structure for table `contact_form_timeout`
--

CREATE TABLE IF NOT EXISTS `contact_form_timeout` (
  `ip` varchar(20) NOT NULL,
  `timestamp` int(10) unsigned NOT NULL,
  KEY `ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `news`
--

CREATE TABLE IF NOT EXISTS `news` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `able` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `short_english` text,
  `english` text,
  `extension` varchar(3) DEFAULT NULL,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `comments` int(10) unsigned NOT NULL DEFAULT '0',
  `likes` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `news_likes`
--

CREATE TABLE IF NOT EXISTS `news_likes` (
  `news_id` int(10) unsigned NOT NULL,
  `ip` varchar(20) NOT NULL,
  KEY `ip` (`ip`),
  KEY `index` (`news_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
