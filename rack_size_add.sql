ALTER TABLE `rack` ADD `size` TINYINT( 3 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `floor` ;
UPDATE `rack` SET `size` = '1' WHERE `size` = ''; 
