# ************************************************************
# Sequel Pro SQL dump
# Version 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: localhost (MySQL 5.7.27)
# Database: fg10
# Generation Time: 2019-12-20 17:34:24 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table addresses
# ------------------------------------------------------------

DROP TABLE IF EXISTS `addresses`;

CREATE TABLE `addresses` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `created` datetime DEFAULT NULL,
  `modified` datetime NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(128) DEFAULT '',
  `company` varchar(128) DEFAULT NULL,
  `address` varchar(128) DEFAULT '',
  `address2` varchar(128) DEFAULT '',
  `address3` varchar(128) DEFAULT '',
  `city` varchar(128) DEFAULT '',
  `state` varchar(8) DEFAULT NULL,
  `zip` varchar(20) DEFAULT '',
  `country` varchar(20) DEFAULT '',
  `sequence` float DEFAULT '0',
  `active` int(1) DEFAULT '1',
  `type` varchar(100) DEFAULT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `email` varchar(500) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `epms_vendor_id` int(11) DEFAULT NULL COMMENT 'the actual EPMS vendor ID',
  `tax_rate_id` int(11) DEFAULT NULL,
  `fedex_acct` varchar(25) DEFAULT NULL,
  `ups_acct` varchar(25) DEFAULT NULL,
  `residence` tinyint(1) DEFAULT '0' COMMENT '1=home delivery 0=business',
  `customer_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table budgets
# ------------------------------------------------------------

DROP TABLE IF EXISTS `budgets`;

CREATE TABLE `budgets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `use_budget` tinyint(1) DEFAULT '0' COMMENT 'whether a $ budget applies',
  `budget` float DEFAULT NULL COMMENT 'monthly $ budget',
  `remaining_budget` float DEFAULT NULL COMMENT 'remaining budget',
  `use_item_budget` tinyint(1) DEFAULT '0' COMMENT 'whether an item budget applies',
  `item_budget` smallint(6) DEFAULT NULL COMMENT 'monthly item count limit',
  `remaining_item_budget` smallint(6) DEFAULT NULL COMMENT 'remaining item count',
  `budget_month` varchar(7) DEFAULT NULL COMMENT '''0x/20xx'' month the budget is good for',
  `current` tinyint(1) DEFAULT '1' COMMENT 'indicates the current active budget',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table carts
# ------------------------------------------------------------

DROP TABLE IF EXISTS `carts`;

CREATE TABLE `carts` (
  `id` char(36) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `sessionid` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `item_id` char(36) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `weight` decimal(6,2) DEFAULT NULL,
  `price` decimal(6,2) DEFAULT NULL,
  `quantity` float DEFAULT NULL,
  `sell_quantity` float DEFAULT NULL,
  `each_quantity` float DEFAULT NULL,
  `sell_unit` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `weight_total` decimal(6,2) DEFAULT NULL,
  `subtotal` decimal(6,2) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `catalog_id` int(11) DEFAULT NULL,
  `note` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table catalogs
# ------------------------------------------------------------

DROP TABLE IF EXISTS `catalogs`;

CREATE TABLE `catalogs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created` datetime DEFAULT NULL,
  `modified` datetime NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `name` varchar(128) DEFAULT '' COMMENT 'HTML image alt attribute override',
  `parent_id` int(11) DEFAULT '0',
  `ancestor_list` varchar(256) DEFAULT '',
  `item_count` int(11) DEFAULT NULL,
  `lock` int(11) DEFAULT '0',
  `sequence` float DEFAULT '0',
  `active` int(1) DEFAULT '1',
  `customer_id` int(11) DEFAULT NULL,
  `customer_user_id` int(11) DEFAULT NULL,
  `sell_quantity` float DEFAULT NULL,
  `sell_unit` varchar(128) DEFAULT NULL,
  `max_quantity` int(11) DEFAULT NULL,
  `price` decimal(8,2) DEFAULT '0.00',
  `description` text,
  `type` tinyint(4) unsigned DEFAULT NULL,
  `item_code` varchar(50) NOT NULL DEFAULT '',
  `customer_item_code` varchar(50) DEFAULT NULL,
  `comment` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table catalogs_users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `catalogs_users`;

CREATE TABLE `catalogs_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created` datetime DEFAULT NULL,
  `modified` datetime NOT NULL,
  `catalog_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table customers
# ------------------------------------------------------------

DROP TABLE IF EXISTS `customers`;

CREATE TABLE `customers` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime NOT NULL,
  `customer_code` varchar(24) DEFAULT NULL,
  `order_contact` varchar(50) DEFAULT NULL,
  `billing_contact` varchar(50) DEFAULT NULL,
  `allow_backorder` tinyint(1) DEFAULT '0',
  `allow_direct_pay` tinyint(1) DEFAULT '0',
  `address_id` int(11) DEFAULT NULL,
  `release_hold` int(1) DEFAULT '0' COMMENT 'indicates if approved orders should hold for staff release',
  `taxable` tinyint(1) DEFAULT '1',
  `rent_qty` int(10) DEFAULT '0',
  `rent_unit` varchar(50) DEFAULT '0',
  `rent_price` decimal(8,2) DEFAULT '0.00',
  `item_pull_charge` decimal(8,2) DEFAULT '0.00',
  `order_pull_charge` decimal(8,2) DEFAULT '0.00',
  `token` varchar(40) DEFAULT NULL,
  `customer_type` varchar(50) DEFAULT NULL COMMENT 'AMP or GM customers',
  `image_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table documents
# ------------------------------------------------------------

DROP TABLE IF EXISTS `documents`;

CREATE TABLE `documents` (
  `id` char(36) NOT NULL DEFAULT '',
  `created` datetime DEFAULT NULL,
  `modified` datetime NOT NULL,
  `img_file` varchar(255) DEFAULT NULL COMMENT 'File name',
  `dir` varchar(255) DEFAULT NULL COMMENT 'File name',
  `title` varchar(255) DEFAULT NULL COMMENT 'Human readible name of the document',
  `instructions` varchar(255) DEFAULT NULL COMMENT 'instructions for printing this document',
  `customer_id` int(11) DEFAULT NULL,
  `order_id` char(36) NOT NULL DEFAULT '',
  `printed` tinyint(1) DEFAULT '0' COMMENT 'Has the file been printed yet',
  `count` int(1) DEFAULT '0' COMMENT 'How many times has the file been printed',
  `user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table gateways
# ------------------------------------------------------------

DROP TABLE IF EXISTS `gateways`;

CREATE TABLE `gateways` (
  `id` char(36) NOT NULL DEFAULT '',
  `model_id` varchar(36) DEFAULT NULL COMMENT 'The id of the object being acted upon',
  `model_alias` varchar(50) DEFAULT NULL COMMENT 'The alias of the model of the object being acted upon',
  `user_id` int(11) DEFAULT NULL COMMENT 'The id of the user taking the action',
  `complete` tinyint(4) DEFAULT NULL COMMENT 'Flag for completion of the action',
  `action` varchar(50) DEFAULT NULL COMMENT 'The action to be taken',
  `controller` varchar(50) DEFAULT NULL COMMENT 'The controller to use',
  `created` datetime DEFAULT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table helps
# ------------------------------------------------------------

DROP TABLE IF EXISTS `helps`;

CREATE TABLE `helps` (
  `id` char(36) NOT NULL DEFAULT '',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL COMMENT 'The heading for the help item',
  `help` text COMMENT 'The help text in markdown',
  `tag` varchar(125) DEFAULT NULL COMMENT 'This is the string used in the help attribute to link this record',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table images
# ------------------------------------------------------------

DROP TABLE IF EXISTS `images`;

CREATE TABLE `images` (
  `img_file` varchar(35) DEFAULT '' COMMENT 'The image file name',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `modified` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `mimetype` varchar(40) DEFAULT NULL COMMENT 'From EXIF data',
  `filesize` bigint(20) DEFAULT NULL COMMENT 'From EXIF data',
  `width` mediumint(9) DEFAULT NULL COMMENT 'From EXIF data',
  `height` mediumint(9) DEFAULT NULL COMMENT 'From EXIF data',
  `title` varchar(255) DEFAULT '' COMMENT 'HTML image title attribute',
  `date` bigint(20) DEFAULT NULL COMMENT 'From EXIF data',
  `alt` varchar(255) DEFAULT NULL COMMENT 'HTML image alt attribute',
  `item_id` int(11) DEFAULT NULL,
  `dir` varchar(255) DEFAULT NULL COMMENT 'directory path of the image',
  UNIQUE KEY `serial_num` (`id`),
  KEY `images_title_idx` (`title`),
  KEY `images_alt_idx` (`alt`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='collection of images and supporting text';



# Dump of table invoice_items
# ------------------------------------------------------------

DROP TABLE IF EXISTS `invoice_items`;

CREATE TABLE `invoice_items` (
  `id` char(36) NOT NULL DEFAULT '',
  `invoice_id` char(36) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `order_id` char(36) DEFAULT NULL,
  `order_item_id` char(36) DEFAULT NULL,
  `quantity` decimal(8,2) DEFAULT NULL,
  `price` decimal(8,2) DEFAULT NULL,
  `unit` varchar(128) DEFAULT NULL,
  `description` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table invoices
# ------------------------------------------------------------

DROP TABLE IF EXISTS `invoices`;

CREATE TABLE `invoices` (
  `id` char(36) NOT NULL DEFAULT '',
  `created` datetime DEFAULT NULL,
  `modified` datetime NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `status` varchar(128) DEFAULT NULL,
  `job_number` varchar(36) DEFAULT NULL COMMENT 'EPMS Job Number',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table items
# ------------------------------------------------------------

DROP TABLE IF EXISTS `items`;

CREATE TABLE `items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `item_code` varchar(50) DEFAULT NULL,
  `customer_item_code` varchar(50) DEFAULT NULL,
  `name` varchar(128) DEFAULT NULL,
  `description` text CHARACTER SET utf8,
  `description_2` text,
  `color` varchar(25) DEFAULT NULL,
  `price` decimal(8,2) DEFAULT '0.00',
  `weight` decimal(8,2) DEFAULT '0.00',
  `quantity` float(8,1) DEFAULT NULL,
  `reorder_qty` int(11) DEFAULT NULL,
  `available_qty` float(8,1) DEFAULT NULL COMMENT 'quantity - orders',
  `pending_qty` float(8,1) DEFAULT NULL COMMENT 'quantity + replenishments on order',
  `reorder_level` int(11) DEFAULT NULL,
  `minimum` int(11) DEFAULT NULL,
  `non_stock` tinyint(1) DEFAULT '0',
  `customer_owned` tinyint(1) DEFAULT '0',
  `catalog_count` int(11) DEFAULT NULL,
  `active` int(1) DEFAULT '1',
  `vendor_id` int(11) DEFAULT NULL,
  `cost` decimal(8,2) DEFAULT NULL COMMENT 'price to order from the vendor',
  `po_item_code` varchar(75) DEFAULT NULL,
  `po_description` text,
  `po_unit` varchar(10) DEFAULT 'ea' COMMENT 'the english unit name for po line items (default)',
  `po_quantity` int(11) DEFAULT '1' COMMENT 'the number of individuals in the po_unit (default)',
  `location_id` int(11) DEFAULT NULL,
  `max_quantity` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table labels
# ------------------------------------------------------------

DROP TABLE IF EXISTS `labels`;

CREATE TABLE `labels` (
  `id` char(36) NOT NULL DEFAULT '',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL COMMENT 'The name for the label',
  `order_id` char(36) DEFAULT NULL COMMENT 'order the labels belong to',
  `items` text COMMENT 'serialized info indicating the box contents',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table locations
# ------------------------------------------------------------

DROP TABLE IF EXISTS `locations`;

CREATE TABLE `locations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT NULL,
  `name` varchar(25) DEFAULT NULL COMMENT 'the brief location name, nomally encoded',
  `quantity` int(8) DEFAULT NULL COMMENT 'the quantity of the item at this location',
  `building` varchar(100) DEFAULT NULL,
  `row` varchar(25) DEFAULT NULL,
  `bin` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table menus
# ------------------------------------------------------------

DROP TABLE IF EXISTS `menus`;

CREATE TABLE `menus` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) DEFAULT '0',
  `lft` int(10) DEFAULT NULL,
  `rght` int(10) DEFAULT NULL,
  `name` varchar(128) DEFAULT NULL,
  `group` varchar(128) DEFAULT NULL,
  `access` varchar(128) DEFAULT NULL,
  `controller` varchar(128) DEFAULT NULL,
  `action` varchar(128) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table observers
# ------------------------------------------------------------

DROP TABLE IF EXISTS `observers`;

CREATE TABLE `observers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_observer_id` int(11) DEFAULT NULL,
  `observer_name` varchar(150) DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL,
  `user_name` varchar(150) DEFAULT NULL,
  `location` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table order_items
# ------------------------------------------------------------

DROP TABLE IF EXISTS `order_items`;

CREATE TABLE `order_items` (
  `id` char(36) COLLATE utf8_unicode_ci NOT NULL,
  `order_id` char(36) COLLATE utf8_unicode_ci DEFAULT '',
  `item_id` char(36) COLLATE utf8_unicode_ci DEFAULT '',
  `name` varchar(255) CHARACTER SET utf8 DEFAULT '',
  `quantity` float DEFAULT NULL,
  `sell_quantity` float DEFAULT NULL,
  `each_quantity` float DEFAULT NULL,
  `sell_unit` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `weight` decimal(8,2) unsigned DEFAULT '0.00' COMMENT 'weight per item',
  `price` decimal(8,2) unsigned DEFAULT '0.00' COMMENT 'price per item',
  `subtotal` decimal(8,2) unsigned DEFAULT NULL COMMENT 'price * qty',
  `created` datetime DEFAULT NULL,
  `modified` datetime NOT NULL,
  `weight_total` decimal(8,2) DEFAULT NULL COMMENT 'weight * qty',
  `pulled` tinyint(1) DEFAULT '0' COMMENT 'after pull item doesn''t count in uncommitted',
  `catalog_id` int(11) DEFAULT NULL COMMENT 'the id of the ordered catalog item',
  `type` tinyint(4) unsigned DEFAULT NULL,
  `catalog_type` tinyint(4) unsigned DEFAULT NULL,
  `sequence` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT;



# Dump of table orders
# ------------------------------------------------------------

DROP TABLE IF EXISTS `orders`;

CREATE TABLE `orders` (
  `id` char(36) COLLATE utf8_unicode_ci NOT NULL,
  `order_seed` int(10) NOT NULL AUTO_INCREMENT,
  `order_number` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `budget_id` int(11) DEFAULT NULL COMMENT 'link to the dollar budget of the user that placed this order',
  `first_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `last_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `phone` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `billing_company` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `billing_address` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `billing_address2` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `billing_city` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `billing_zip` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `billing_state` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `billing_country` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `weight` decimal(8,2) unsigned DEFAULT '0.00',
  `order_item_count` int(11) DEFAULT NULL,
  `subtotal` decimal(8,2) DEFAULT NULL,
  `tax` decimal(8,2) DEFAULT '0.00',
  `shipping` decimal(8,2) DEFAULT '0.00',
  `total` decimal(8,2) unsigned DEFAULT NULL,
  `order_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `authorization` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `transaction` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip_address` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `created` datetime DEFAULT NULL,
  `modified` datetime NOT NULL,
  `user_customer_id` int(11) DEFAULT NULL COMMENT 'link to billing user/customer',
  `backorder_id` char(36) COLLATE utf8_unicode_ci DEFAULT '',
  `taxable` tinyint(1) DEFAULT '1',
  `handling` decimal(8,2) DEFAULT '0.00',
  `note` text COLLATE utf8_unicode_ci,
  `order_reference` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'ordering user''s purchase order',
  `ship_date` datetime DEFAULT NULL COMMENT 'date order shipped',
  `exclude` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_seed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT;



# Dump of table preferences
# ------------------------------------------------------------

DROP TABLE IF EXISTS `preferences`;

CREATE TABLE `preferences` (
  `id` char(36) NOT NULL DEFAULT '',
  `created` datetime DEFAULT NULL,
  `modified` datetime NOT NULL,
  `prefs` text COMMENT 'A serialized array of user preferences',
  `user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table prices
# ------------------------------------------------------------

DROP TABLE IF EXISTS `prices`;

CREATE TABLE `prices` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) DEFAULT NULL,
  `min_qty` int(11) DEFAULT NULL,
  `max_qty` int(11) DEFAULT NULL,
  `price` decimal(8,2) DEFAULT '0.00',
  `test_max_qty` int(11) DEFAULT NULL,
  `catalog_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table replenishment_items
# ------------------------------------------------------------

DROP TABLE IF EXISTS `replenishment_items`;

CREATE TABLE `replenishment_items` (
  `id` char(36) COLLATE utf8_unicode_ci NOT NULL,
  `replenishment_id` char(36) COLLATE utf8_unicode_ci DEFAULT '',
  `item_id` char(36) COLLATE utf8_unicode_ci DEFAULT '',
  `name` varchar(255) CHARACTER SET utf8 DEFAULT '',
  `vendor_product_code` varchar(75) COLLATE utf8_unicode_ci DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `weight` decimal(8,2) unsigned DEFAULT '0.00',
  `price` decimal(8,2) unsigned DEFAULT NULL,
  `subtotal` decimal(8,2) unsigned DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `note` text COLLATE utf8_unicode_ci,
  `pulled` tinyint(1) DEFAULT '0' COMMENT 'When items are received and shelve set to true',
  `po_quantity` int(11) DEFAULT NULL,
  `po_unit` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT;



# Dump of table replenishments
# ------------------------------------------------------------

DROP TABLE IF EXISTS `replenishments`;

CREATE TABLE `replenishments` (
  `id` char(36) COLLATE utf8_unicode_ci NOT NULL,
  `first_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `last_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `status` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vendor_company` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vendor_address` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `vendor_address2` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `vendor_city` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `vendor_zip` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `vendor_state` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `vendor_country` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `company` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `address2` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `city` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `zip` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `state` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `country` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `weight` decimal(8,2) unsigned DEFAULT '0.00',
  `order_item_count` int(11) DEFAULT NULL,
  `subtotal` decimal(8,2) DEFAULT NULL,
  `tax` decimal(8,2) DEFAULT NULL,
  `shipping` decimal(8,2) DEFAULT NULL,
  `total` decimal(8,2) unsigned DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `vendor_id` int(11) DEFAULT NULL COMMENT 'link to vendor address',
  `active` tinyint(1) DEFAULT '1',
  `order_seed` int(10) NOT NULL AUTO_INCREMENT,
  `order_number` varchar(12) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_seed` (`order_seed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT;



# Dump of table shipments
# ------------------------------------------------------------

DROP TABLE IF EXISTS `shipments`;

CREATE TABLE `shipments` (
  `id` char(36) NOT NULL DEFAULT '',
  `order_id` char(36) DEFAULT NULL,
  `status` varchar(128) DEFAULT NULL,
  `carrier` varchar(128) DEFAULT NULL,
  `method` varchar(128) DEFAULT NULL,
  `first_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT '',
  `last_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT '',
  `phone` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT '',
  `company` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `address` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT '',
  `address2` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT '',
  `city` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT '',
  `zip` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT '',
  `state` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT '',
  `country` varchar(2) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT '',
  `weight` decimal(8,2) unsigned DEFAULT '0.00',
  `length` decimal(8,2) DEFAULT NULL,
  `width` decimal(8,2) DEFAULT NULL,
  `height` decimal(8,2) DEFAULT NULL,
  `ship_ref1` varchar(255) DEFAULT NULL,
  `ship_ref2` varchar(255) DEFAULT NULL,
  `ship_ref3` varchar(255) DEFAULT NULL,
  `packaging` varchar(10) DEFAULT NULL,
  `billing_account` varchar(50) DEFAULT NULL,
  `carrier_notes` varchar(128) DEFAULT NULL,
  `tracking` varchar(128) DEFAULT NULL,
  `shipment_cost` decimal(8,2) unsigned DEFAULT '0.00',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `tax_jurisdiction` varchar(50) DEFAULT 'EX',
  `tax_rate_id` int(11) DEFAULT NULL,
  `tax_percent` double DEFAULT NULL,
  `tpb_company` varchar(255) DEFAULT NULL COMMENT 'third party billing company''s company name',
  `tpb_address` varchar(255) DEFAULT NULL COMMENT 'third party billing company''s address',
  `tpb_city` varchar(255) DEFAULT NULL COMMENT 'third party billing company''s city',
  `tpb_state` varchar(20) DEFAULT NULL COMMENT 'third party billing company''s state',
  `tpb_zip` varchar(20) DEFAULT NULL COMMENT 'third party billing company''s zip',
  `tpb_phone` varchar(255) DEFAULT NULL COMMENT 'third party billing company''s phone',
  `billing` varchar(25) DEFAULT NULL COMMENT 'shipment billing type',
  `shipment_code` varchar(11) DEFAULT NULL COMMENT 'unique ID for shipment transfer',
  `residence` tinyint(1) DEFAULT '0' COMMENT '1=home delivery 0=business',
  `ups_email1` varchar(50) DEFAULT NULL COMMENT 'UPS Notification Recipient 1 Email',
  `ups_email2` varchar(50) DEFAULT NULL COMMENT 'UPS Notification Recipient 2 Email',
  `ups_email3` varchar(50) DEFAULT NULL COMMENT 'UPS Notification Recipient 3 Email',
  `ups_flag1` varchar(1) DEFAULT 'n' COMMENT 'UPS Notification Flag 1',
  `ups_flag2` varchar(1) DEFAULT 'n' COMMENT 'UPS Notification Flag 2',
  `ups_flag3` varchar(1) DEFAULT 'n' COMMENT 'UPS Notification Flag 3',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table tax_rates
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tax_rates`;

CREATE TABLE `tax_rates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `tax_jurisdiction` varchar(50) DEFAULT '',
  `tax_rate` decimal(5,4) DEFAULT '0.0000',
  `name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table times
# ------------------------------------------------------------

DROP TABLE IF EXISTS `times`;

CREATE TABLE `times` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created` timestamp NULL DEFAULT NULL,
  `modified` timestamp NULL DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL,
  `time_in` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `time_out` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `activity` text,
  `user` varchar(50) NOT NULL DEFAULT 'anonymous',
  `project` varchar(100) NOT NULL DEFAULT 'am-fg',
  `duration` float DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table ups
# ------------------------------------------------------------

DROP TABLE IF EXISTS `ups`;

CREATE TABLE `ups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_number` varchar(36) DEFAULT NULL,
  `status` varchar(36) DEFAULT NULL,
  `tracking_number` varchar(128) DEFAULT NULL,
  `cost` decimal(8,2) DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DELIMITER ;;
/*!50003 SET SESSION SQL_MODE="" */;;
/*!50003 CREATE */ /*!50017 DEFINER=`root`@`localhost` */ /*!50003 TRIGGER `before_ups_insert` BEFORE INSERT ON `ups` FOR EACH ROW BEGIN
UPDATE shipments
SET shipments.shipment_cost = NEW.cost,
shipments.tracking = NEW.tracking_number
WHERE shipments.shipment_code = NEW.order_number;
IF(NEW.status = 'N') THEN
UPDATE orders
SET orders.status = 'Shipping'
WHERE orders.order_number = NEW.order_number;
ELSE
UPDATE orders
SET orders.status = 'Pulled'
WHERE orders.order_number = NEW.order_number;
END IF;
END */;;
/*!50003 SET SESSION SQL_MODE="" */;;
/*!50003 CREATE */ /*!50017 DEFINER=`root`@`localhost` */ /*!50003 TRIGGER `StatusInsurance` AFTER INSERT ON `ups` FOR EACH ROW BEGIN
IF(NEW.status = 'N') THEN
UPDATE orders
SET orders.status = 'Shipping'
WHERE orders.order_number = NEW.order_number;
ELSE
UPDATE orders
SET orders.status = 'Pulled'
WHERE orders.order_number = NEW.order_number;
END IF;
END */;;
DELIMITER ;
/*!50003 SET SESSION SQL_MODE=@OLD_SQL_MODE */;


# Dump of table user_registries
# ------------------------------------------------------------

DROP TABLE IF EXISTS `user_registries`;

CREATE TABLE `user_registries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `node_id` int(11) DEFAULT NULL,
  `model` varchar(75) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(500) DEFAULT NULL,
  `password` char(40) DEFAULT NULL COMMENT 'password hash',
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `active` int(1) DEFAULT '1' COMMENT 'activeate or deactivate this record',
  `username` varchar(255) DEFAULT '',
  `role` char(50) DEFAULT NULL COMMENT 'The users access control setting',
  `created` datetime DEFAULT NULL,
  `modified` datetime NOT NULL,
  `parent_id` float DEFAULT '0' COMMENT 'the id of this users parent',
  `ancestor_list` varchar(255) DEFAULT ',' COMMENT ',aa,bb,cc, chain of ancestors back up the tree prfixed and suffixed with comma',
  `lock` int(11) DEFAULT '0' COMMENT 'preven self and all decendents from access by other users. Someone is editing',
  `sequence` float DEFAULT '0' COMMENT 'the sequence of the related siblings',
  `folder` tinyint(1) DEFAULT '0' COMMENT 'Indicates this is a node that can be a parent or one that is only considered an endpoint (grain)',
  `session_change` tinyint(1) DEFAULT '0' COMMENT 'If any data changes that is stored in Auth for this user, flag them to refresh session on next server hit',
  `verified` tinyint(1) DEFAULT '0' COMMENT 'indicates if an invited user has activated their account',
  `logged_in` bigint(16) DEFAULT '0' COMMENT 'indicates if the user is logged in',
  `cart_session` varchar(36) DEFAULT NULL COMMENT 'UUID of the session',
  `use_budget` tinyint(1) DEFAULT '0' COMMENT 'whether a $ budget applies',
  `budget` float DEFAULT NULL COMMENT 'monthly $ budget',
  `use_item_budget` tinyint(1) DEFAULT '0' COMMENT 'whether an item budget applies',
  `item_budget` smallint(6) DEFAULT NULL COMMENT 'monthly item count limit',
  `rollover_item_budget` tinyint(1) DEFAULT '0' COMMENT 'should remaining item budget rollover to next month',
  `rollover_budget` tinyint(1) DEFAULT '0' COMMENT 'should remaining budget rollover to next month',
  `use_item_limit_budget` tinyint(1) DEFAULT '0' COMMENT 'should item limits budgets be used',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table users_users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `users_users`;

CREATE TABLE `users_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `user_managed_id` int(11) DEFAULT NULL,
  `user_manager_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
