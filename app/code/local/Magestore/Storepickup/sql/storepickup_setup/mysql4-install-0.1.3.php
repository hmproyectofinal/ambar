<?php

$installer = $this;

$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('storepickup_order')};
DROP TABLE IF EXISTS {$this->getTable('storepickup_location')};
DROP TABLE IF EXISTS {$this->getTable('storepickup_holiday')};
DROP TABLE IF EXISTS {$this->getTable('storepickup_store')};

CREATE TABLE {$this->getTable('storepickup_order')}  (
  `storeorder_id` int(11) NOT NULL auto_increment,
  `order_id` int(11) NOT NULL default '0',
  `store_id` int(11) NOT NULL default '0',
  `shipping_date` date NOT NULL,
  `shipping_time` time NULL,
  PRIMARY KEY  (`storeorder_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

CREATE TABLE {$this->getTable('storepickup_holiday')}  (
  `holiday_id` int(11) NOT NULL auto_increment,
  `store_id` int(11) NOT NULL default '0',
  `date` date NOT NULL,
  `comment` varchar(255) default NULL,
  PRIMARY KEY  (`holiday_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

CREATE TABLE {$this->getTable('storepickup_store')} (
  `store_id` int(11) unsigned NOT NULL auto_increment,
  `store_name` varchar(255) NOT NULL,
  `store_manager` varchar(255) NOT NULL,
  `store_email` varchar(255) NOT NULL,
  `store_phone` varchar(20) NOT NULL,
  `store_fax` varchar(20) NOT NULL,
  `description` text NOT NULL,
  `status` smallint(6) NOT NULL default '0',
  `address` text NOT NULL,
  `address_2` text NOT NULL,
  `state` varchar(255) NOT NULL,
  `suburb` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `region_id` INT( 10 ) UNSIGNED NOT NULL,
  `city_id` INT( 10 ) UNSIGNED NOT NULL,
  `suburb_id` INT( 10 ) UNSIGNED NOT NULL,  
  `zipcode` varchar(255) NOT NULL,
  `state_id` int(11) NOT NULL,
  `country` varchar(255) NOT NULL,
  `store_latitude` varchar(20) NOT NULL,
  `store_longitude` varchar(20) NOT NULL,
  `monday_open` varchar(5) NOT NULL,
  `monday_close` varchar(5) NOT NULL,
  `tuesday_open` varchar(5) NOT NULL,
  `tuesday_close` varchar(5) NOT NULL,
  `wednesday_open` varchar(5) NOT NULL,
  `wednesday_close` varchar(5) NOT NULL,
  `thursday_open` varchar(5) NOT NULL,
  `thursday_close` varchar(5) NOT NULL,
  `friday_open` varchar(5) NOT NULL,
  `friday_close` varchar(5) NOT NULL,
  `saturday_open` varchar(5) NOT NULL,
  `saturday_close` varchar(5) NOT NULL,
  `sunday_open` varchar(5) NOT NULL,
  `sunday_close` varchar(5) NOT NULL,
  `minimum_gap` int(11) NOT NULL default '45',
  PRIMARY KEY  (`store_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

CREATE TABLE {$this->getTable('storepickup_location')} (
`location_id` INT( 10 ) unsigned NOT NULL AUTO_INCREMENT,
`name` VARCHAR( 255 ) NOT NULL ,
`type` VARCHAR( 10 ) NOT NULL ,
`parent_id` VARCHAR( 10 ) NOT NULL ,
`status` TINYINT( 1 ) NOT NULL ,
`description` TEXT NOT NULL,
PRIMARY KEY  (`location_id`)
);

");



$installer->endSetup(); 