<?php

/* Equivalent DDL statement for database table for MWAC plugin
 * consistent for MWAC, version 0.1 and up
CREATE TABLE mwac_wp_post_map (
	ID bigint(20) unsigned NOT NULL auto_increment,
	post_ID bigint(20) unsigned NOT NULL,
	remote_post_ID text NOT NULL,
	PRIMARY KEY (ID)
);
 */

function mwac_install_table() {
    global $wpdb;
    
    if (!mwac_table_exists())
        $wpdb->query("CREATE TABLE mwac_wp_post_map ( ID bigint(20) unsigned NOT NULL auto_increment, post_ID bigint(20) unsigned NOT NULL, remote_post_ID text NOT NULL, PRIMARY KEY (ID))");
}

function mwac_table_exists() {
    global $wpdb;
    $exists = false;
    $q = $wpdb->query("SHOW TABLES LIKE 'mwac_wp_post_map'");
    if ($q == 1) {
        $exists = true;
    }
    return $exists;
}

?>
