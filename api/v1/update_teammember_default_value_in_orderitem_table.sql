ALTER TABLE `orderitem` CHANGE `teammember_id` `teammember_id` INT(11) NULL DEFAULT NULL;
UPDATE `orderitem` SET`teammember_id`=null WHERE`teammember_id`=0;