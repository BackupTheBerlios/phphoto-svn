<?php
// $Id$

require_once("DB/DataObject.php");
require_once("includes/db.php");
require_once("includes/config.php");
require_once("includes/utils.php");

define('SESSION_METHOD_GET', 0);
define('SESSION_METHOD_COOKIE', 1);
define('ANON_USER', 0);

class Session {
	
	private static $_session = null;
	var $_user = null;
	var $_method = SESSION_METHOD_GET;
	var $_sid = '';
	var $_uid = ANON_USER;

	private function __construct() {
		global $db;

		$expire_time = time() - Config::get("session_lifetime", 3600);
		$sq = $db->prepare("DELETE FROM phph_sessions WHERE session_time < ?");
		$db->execute($sq, $expire_time);

		$c_domain = Config::get("cookie_domain");
		$c_path = Config::get("cookie_path");
		$sid_name = Session::getSIDCookieName();
		$uid_name = Session::getUIDCookieName();

		$this->_uid = ANON_USER;
		if (isset($_COOKIE[$uid_name]))
			$this->_uid = $_COOKIE[$uid_name];

		if (isset($_COOKIE[$sid_name])) {
			$this->_sid = $_COOKIE[$sid_name];
			$this->_method = SESSION_METHOD_COOKIE;
		} else {
			$this->_sid = Utils::g(Config::get("session_cookie_name", "sid"));
			$this->_method = SESSION_METHOD_GET;
		}

		if (!empty($this->_sid)) {

			// Check if session exists
			$sdbo = DB_DataObject::Factory('phph_sessions');
			if (PEAR::isError($sdbo))
				die($sdbo->getMessage());

			$r = $sdbo->get($this->_sid);
			if (PEAR::isError($r))
				die($sdbo->getMessage());

			if ($r == 1) {

				$sdbo->session_time = time();
				$r = $sdbo->update();
				$r = $sdbo->get($this->_sid);
				if (PEAR::isError($r))
					die($sdbo->getMessage());

				return;
			}

			$this->_sid = "";

		}

		$this->_uid = 0;
		$this->newSession();
	}

	public function newSession() {

		$sdbo = DB_DataObject::Factory('phph_sessions');
		if (PEAR::isError($sdbo))
			die($sdbo->getMessage());

		$r = $sdbo->get($this->_sid);
		if (PEAR::isError($r))
			die($r->getMessage());

		if ($r == 0) {

			$this->_sid = md5(uniqid(Utils::getEncodedClientIP()));
			$this->_method = SESSION_METHOD_GET;

			$sdbo->session_id = $this->_sid;
			$sdbo->user_id = $this->_uid;
			$sdbo->session_time = time();
			$sdbo->session_start = time();
			$sdbo->session_ip = Utils::getEncodedClientIP();
			
			$r = $sdbo->insert();
			if (PEAR::isError($r))
				die($r->getMessage());
	
		} else {
			$sdbo->user_id = $this->_uid;
			$sdbo->session_time = time();
			$sdbo->session_ip = Utils::getEncodedClientIP();
			
			$r = $sdbo->update();
			if (PEAR::isError($r))
				die($r->getMessage());

		}

		$hdbo = DB_DataObject::Factory('phph_session_history');
		if (PEAR::isError($hdbo))
			die($hdbo->getMessage());
		$r = $hdbo->get($this->_sid);
		if (PEAR::isError($r))
			die($r->getMessage());

		if ($r == 0) {
			$hdbo->session_id = $this->_sid;
			$hdbo->user_id = $this->_uid;
			$hdbo->session_start = time();
			$hdbo->session_ip = Utils::getEncodedClientIP();
			$r = $hdbo->insert();
			if (PEAR::isError($r))
				die($r->getMessage());
		} else {
			$hdbo->user_id = $this->_uid;
			$hdbo->session_ip = Utils::getEncodedClientIP();
			
			$r = $hdbo->update();
			if (PEAR::isError($r))
				die($r->getMessage());
		}

		if ($this->_uid != ANON_USER) {

			$idbo = DB_DataObject::Factory('phph_user_ip');
			if (PEAR::isError($idbo))
				die($idbo->getMessage());
			$idbo->keys("user_id", "ip");
			$idbo->ip = Utils::getEncodedClientIP();
			$idbo->user_id = $this->_uid;
			$r = $idbo->find();
			if (PEAR::isError($r))
				die($r->getMessage());

			if ($r == 0) {
				$idbo->user_id = $this->_uid;
				$idbo->last_visit = time();
				$idbo->ip = Utils::getEncodedClientIP();
				$r = $idbo->insert();
				if (PEAR::isError($r))
					die($r->getMessage());
			} else {
				$idbo->last_visit = time();
			
				$r = $idbo->update();
				if (PEAR::isError($r))
					die($r->getMessage());
			}

			$udbo = DB_DataObject::Factory('phph_users');
			if (PEAR::isError($udbo))
				die($udbo->getMessage());
			$r = $udbo->get($this->_uid);
			if (PEAR::isError($r))
				die($r->getMessage());

			if ($r != 0) {
				$udbo->user_lastlogin = time();
			
				$r = $udbo->update();
				if (PEAR::isError($r))
					die($r->getMessage());
			}

		}

		$c_domain = Config::get("cookie_domain");
		$c_path = Config::get("cookie_path");
		$sid_name = Session::getSIDCookieName();
		$uid_name = Session::getUIDCookieName();

		setcookie($sid_name, $this->_sid, 0, $c_path, $c_domain);
		setcookie($uid_name, $this->_uid, time() + 31536000, $c_path, $c_domain);	// expire in 1 year
	}

	public static function getSIDCookieName() {
		return Config::get("cookie_name") . '_' . Config::get("session_cookie_name", "sid");
	}

	public static function getUIDCookieName() {
		return Config::get("cookie_name") . '_uid';
	}

	public static function create() {
		if (self::$_session == null)
			self::$_session = new Session();
	}

	public static function singletone() {
		self::create();
		return self::$_session;
	}

	public function addSID($url) {
		if ($this->_method == SESSION_METHOD_GET && !empty($this->_sid)) {
			$link = "&amp;";
			if (!strstr($url, "?"))
				$link = "?";
			return $url . $link . Config::get("session_cookie_name", "sid") . "=" . $this->_sid;
		}
		return $url;
	}

	public function requireLogin($redirect = true) {
		if ($this->_uid == ANON_USER || empty($this->_sid)) {
			if ($redirect) {
				$url = Config::get("site_url");
				header("Location: $url/index.php?action=login&ref=" . urlencode($url . $_SERVER['REQUEST_URI']));
			}
			return true;
		}
		return false;
	}

	public function getUser() {
		if ($this->_uid == ANON_USER)
			return null;
		if ($this->_user == null) {
			$this->_user = DB_DataObject::Factory('phph_users');
			if (PEAR::isError($this->_user))
				die($this->_user->getMessage());
			$r = $this->_user->get($this->_uid);
			if (PEAR::isError($r))
				die($r->getMessage());
		}
		return $this->_user;
	}
}

Session::create();

?>
