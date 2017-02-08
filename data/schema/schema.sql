-- stub file

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for access_control_list
-- ----------------------------
CREATE TABLE `access_control_list` (
  `access_control_list_pk` int(3) NOT NULL AUTO_INCREMENT,
  `parent_id` int(3) NOT NULL,
  `rule_name` varchar(30) NOT NULL,
  `rule_fail_msg` varchar(255) DEFAULT NULL,
  `rule_desc` varchar(255) DEFAULT NULL,
  `allow` varchar(255) NOT NULL DEFAULT '{"allow":["none"]}',
  `deny` varchar(255) NOT NULL DEFAULT '{"deny":["all"]}',
  PRIMARY KEY (`access_control_list_pk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- ----------------------------
-- Table structure for client
-- ----------------------------
CREATE TABLE `client` (
  `client_pk` int(11) NOT NULL AUTO_INCREMENT,
  `company_name` varchar(100) DEFAULT NULL,
  `api_key` varchar(140) DEFAULT NULL,
  `pass_phrase` varchar(140) DEFAULT NULL,
  PRIMARY KEY (`client_pk`),
  UNIQUE KEY `api_key_idx` (`api_key`),
  KEY `pass_phrase_idx` (`pass_phrase`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for client_user
-- ----------------------------
CREATE TABLE `client_user` (
  `client_user_pk` int(11) NOT NULL AUTO_INCREMENT,
  `client_fk` int(11) NOT NULL,
  `user_role_fk` int(11) NOT NULL DEFAULT '6',
  `alias` varchar(60) NOT NULL,
  `email` varchar(130) NOT NULL,
  `password` varchar(80) NOT NULL,
  `first_name` varchar(60) NOT NULL,
  `last_name` varchar(80) NOT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(60) NOT NULL,
  `state` varchar(4) NOT NULL,
  `country` varchar(4) NOT NULL,
  `postal_code` varchar(7) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `fax` varchar(15) NOT NULL,
  PRIMARY KEY (`client_user_pk`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `alias_idx` (`alias`),
  KEY `client_user_to_client_fk_idx` (`client_fk`),
  KEY `user_role_fk_idx` (`user_role_fk`),
  CONSTRAINT `client_user_to_client_fk_idx` FOREIGN KEY (`client_fk`) REFERENCES `client` (`client_pk`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `user_role_fk_idx` FOREIGN KEY (`user_role_fk`) REFERENCES `user_role` (`user_role_pk`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



-- ----------------------------
-- Table structure for user_role
-- ----------------------------
CREATE TABLE `user_role` (
  `user_role_pk` int(11) NOT NULL AUTO_INCREMENT,
  `role` varchar(60) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`user_role_pk`),
  KEY `user_role_pk` (`user_role_pk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- ----------------------------
-- default data
-- ----------------------------

INSERT INTO `user_role` VALUES ('1', 'admin', '1');
INSERT INTO `user_role` VALUES ('2', 'super', '2');
INSERT INTO `user_role` VALUES ('3', 'client', '3');
INSERT INTO `user_role` VALUES ('4', 'legal_adviser', '2');
INSERT INTO `user_role` VALUES ('5', 'industry_adviser', '2');
INSERT INTO `user_role` VALUES ('6', 'guest', '6');

-- do something with this?
INSERT INTO `access_control_list` VALUES ('1', '1', 'fuck_off', 'Oi! Fuck OFF!', 'block all', '{\"allow\":[\"none\"]}', '{\"deny\":[\"all\"]}');
INSERT INTO `access_control_list` VALUES ('2', '2', 'admin_access', 'Oi! Fuck OFF!', 'allow admin full access', '{\"allow\":[\"admin\"]}', '{\"deny\":[\"all\"]}');
INSERT INTO `access_control_list` VALUES ('3', '3', 'super_user_access', 'user permission failure', 'allow super user full access', '{\"allow\":[\"super_user\"]}', '{\"allow\":[\"all\"]}');
INSERT INTO `access_control_list` VALUES ('4', '3', 'super_userS_access', 'user permission failure', 'allow super userS full access', '{\"allow\":[\"super_user\",\"legal_adviser\",\"industry_adviser\"]}', '{\"deny\":[\"all\"]}');
