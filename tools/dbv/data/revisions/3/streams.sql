ALTER TABLE `streams` CHANGE `game` `game` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `streams` DROP `languages`;
ALTER TABLE `streams` DROP `participant_id`;
DELETE FROM `streams` WHERE `game` = "lolcup" OR `game` = "smitecup";