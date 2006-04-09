<?php
// $Id$

require_once("config/config.php");


class PDOEx extends PDO {
	private $_count = 0;

	function inc() {
		$this->_count++;
	}

	function count() {
		return $this->_count;
	}

	function query($stmt) {
		$this->inc();
		return parent::query($stmt);
	}

	function exec($stmt) {
		$this->inc();
		return parent::exec($stmt);
	}
}

class PDOStatementEx extends PDOStatement
{
	private $_dbh;

	protected function __construct($dbh) {
		$this->_dbh = $dbh;
	}

	function execute($params = null) {
		$this->_dbh->inc();
		if (!is_array($params))
			return parent::execute($params);
		else
			return parent::execute();
	}
}



class Database {

	private static $_database = null;
	private $_db = null;

	private function __construct() {

		global $cfg_db_dsn, $cfg_db_user, $cfg_db_pass;

		$this->_db = new PDOEx($cfg_db_dsn, $cfg_db_user, $cfg_db_pass);
		$this->_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->_db->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('PDOStatementEx', array($this->_db)));
	}

	public static function create() {
		if (self::$_database == null)
			self::$_database = new Database();
	}

	public static function singletone() {
		self::create();
		return self::$_database;
	}

	public function db() {
		return $this->_db;
	}
}

?>
