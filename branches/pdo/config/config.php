<?php
// $Id$

/*
 * Create file dbcfg.inc with the following content:
 *
 * $cfg_db_driver = "mysql";
 * $cfg_db_host = "hostname";
 * $cfg_db_user = "user";
 * $cfg_db_pass = "password";
 * $cfg_db_name = "database name";
 *
 * Another way to setup database connection is to edit this file,
 * comment out dbcfg.inc inclusion, and set $cfg_db_dsn directly.
 */

require_once("dbcfg.php");

$cfg_db_dsn = "$cfg_db_driver:dbname=$cfg_db_name;host=$cfg_db_host";

?>
