SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


CREATE TABLE IF NOT EXISTS `test_taxonomy` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `parent_id` bigint(20) NOT NULL DEFAULT '0' COMMENT 'refer to this table column id. this column value must be integer. if it is root then this value must be 0, it can not be NULL.',
  `name` varchar(255) DEFAULT NULL COMMENT 'taxonomy name',
  `position` int(9) NOT NULL DEFAULT '0' COMMENT 'position when sort/order tags item.',
  `level` int(10) NOT NULL DEFAULT '1' COMMENT 'deep level of taxonomy hierarchy. begins at 1 (no sub items).',
  `left` int(10) NOT NULL DEFAULT '0' COMMENT 'for nested set model calculation. this will be able to select filtered parent id and all of its children.',
  `right` int(10) NOT NULL DEFAULT '0' COMMENT 'for nested set model calculation. this will be able to select filtered parent id and all of its children.',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='contain taxonomy data such as category.' AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `test_taxonomy2` (
  `tid` bigint(20) NOT NULL AUTO_INCREMENT,
  `parent_id` bigint(20) NOT NULL DEFAULT '0' COMMENT 'refer to this table column id. this column value must be integer. if it is root then this value must be 0, it can not be NULL.',
  `t_type` varchar(100) DEFAULT NULL COMMENT 'taxonomy type. example: category, or product_category',
  `t_name` varchar(255) DEFAULT NULL COMMENT 'taxonomy name',
  `t_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0=unpublished, 1=published',
  `t_position` int(9) NOT NULL DEFAULT '0' COMMENT 'position when sort/order tags item.',
  `t_level` int(10) NOT NULL DEFAULT '1' COMMENT 'deep level of taxonomy hierarchy. begins at 1 (no sub items).',
  `t_left` int(10) NOT NULL DEFAULT '0' COMMENT 'for nested set model calculation. this will be able to select filtered parent id and all of its children.',
  `t_right` int(10) NOT NULL DEFAULT '0' COMMENT 'for nested set model calculation. this will be able to select filtered parent id and all of its children.',
  PRIMARY KEY (`tid`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='contain taxonomy with more complex data/columns.' AUTO_INCREMENT=1 ;