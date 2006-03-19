<?php
// $Id$

require_once("DB.php");
require_once("config/config.php");

$db = &DB::connect($db_dsn, $db_options);
if (PEAR::isError($db)) {
	die($db->getMessage());
}

$db->setFetchMode(DB_FETCHMODE_ASSOC);

$options = &PEAR::getStaticProperty('DB_DataObject','options');
$config = parse_ini_file('config/dbo.ini', TRUE);
$options = $config['DB_DataObject'];



?>
