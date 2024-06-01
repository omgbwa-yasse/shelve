
/*


    Paramètre pour les Mails


*/


INSERT INTO `mail_priorities`(`id`, `name`, `duration`) VALUES ('','Normale','7');
INSERT INTO `mail_priorities`(`id`, `name`, `duration`) VALUES ('','Urgent','3');

INSERT INTO `mail_typologies`(`id`, `name`, `description`, `class_id`) VALUES ('','Demande','','1');
INSERT INTO `mail_typologies`(`id`, `name`, `description`, `class_id`) VALUES ('','lettre','','1');

INSERT INTO `mail_status`(`id`, `name`) VALUES ('','brouillon');
INSERT INTO `mail_status`(`id`, `name`) VALUES ('','traité');

INSERT INTO `mail_types`(name) VALUES ('send'), ('received');




/*


    Paramètre pour le local


*/

INSERT INTO `buildings`(`id`, `name`, `description`)
VALUES ('','Archives de la BCGF','') ;


INSERT INTO `floors`(`id`, `name`, `description`, `building_id`)
VALUES ('','2e étage','','1') ;


INSERT INTO `rooms`(`id`, `code`, `name`, `description`, `floor_id`)
VALUES ('','Porte 201','Archives financières','','1');


INSERT INTO `shelves`(`id`, `code`, `observation`, `face`, `ear`,  `shelf`, `shelf_length`, `room_id`)
VALUES ('','E201-1','','2','1','6','6','120','1');

