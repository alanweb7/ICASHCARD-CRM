<?php

defined('BASEPATH') || exit('No direct script access allowed');

update_option('advanced_api_enabled', 1);
add_option('allow_register_api', 1);

$CI = &get_instance();

// Create table `icash_sign_events`
if (!$CI->db->table_exists(db_prefix() . 'contract_sign_events')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'contract_sign_events` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `document` VARCHAR(255) NOT NULL,
        `name` VARCHAR(255) NOT NULL,
        `event_type` VARCHAR(100) NOT NULL,
        `viewed` DATETIME DEFAULT NULL,
        `signed` DATETIME DEFAULT NULL,
        `rejected` DATETIME DEFAULT NULL,
        `status` VARCHAR(50) DEFAULT NULL,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
}


if (table_exists('contacts')) {
	if (!get_instance()->db->field_exists('customer_api_key', db_prefix() . 'contacts')) {
	    get_instance()->db->query('ALTER TABLE `' . db_prefix() . 'contacts` ADD `customer_api_key` TEXT NULL DEFAULT NULL AFTER `ticket_emails`');
	}
}

if (table_exists('staff')) {
	if (!get_instance()->db->field_exists('customer_api_key', db_prefix() . 'staff')) {
	    get_instance()->db->query('ALTER TABLE `' . db_prefix() . 'staff` ADD `customer_api_key` TEXT NULL DEFAULT NULL');
	}
}
