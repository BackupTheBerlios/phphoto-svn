<?php
// $Id$

require_once("includes/Database.php");
require_once("includes/Config.php");
require_once("includes/Utils.php");

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

		$db = Database::singletone()->db();

		$expire_time = time() - Config::get("session_lifetime", 3600);
		$sq = $db->prepare("DELETE FROM phph_sessions WHERE session_time < ?");
		$sq->bindParam(1, $expire_time);
		$sq->execute();

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

			$sth = $db->prepare("SELECT COUNT(*) AS cnt FROM phph_sessions WHERE session_id = :session_id");
			$sth->bindParam(":session_id", $this->_sid);
			$sth->execute();
			$r = $sth->fetchColumn(0);
			$sth = null;

			if ($r == 1) {
				$sth = $db->prepare("UPDATE phph_sessions SET session_time = :time WHERE session_id = :session_id");
				$sth->bindParam(":session_id", $this->_sid);
				$sth->bindValue(":time", time());
				$sth->execute();
				return;
			}

			$this->_sid = "";

		}

		$this->_uid = 0;
		$this->newSession();
	}

	public function newSession() {

		$db = Database::singletone()->db();

		$sth = $db->prepare("SELECT COUNT(*) AS cnt FROM phph_sessions WHERE session_id = :session_id");
		$sth->bindParam(":session_id", $this->_sid);
		$sth->execute();
		$r = $sth->fetchColumn(0);
		$sth = null;
		if ($r == 0) {

			$this->_sid = md5(uniqid(Utils::getEncodedClientIP()));
			$this->_method = SESSION_METHOD_GET;

			$sth = $db->prepare(
				"INSERT INTO phph_sessions (session_id, user_id, session_time, session_start, session_ip) " .
				"VALUES (:session_id, :user_id, :session_time, :session_start, :session_ip)");
			$sth->bindParam(":session_id", $this->_sid);
			$sth->bindParam(":user_id", $this->_uid);
			$sth->bindValue(":session_time", time());
			$sth->bindValue(":session_start", time());
			$sth->bindValue(":session_ip", Utils::getEncodedClientIP());
			$sth->execute();
			$sth = null;

		} else {

			$sth = $db->prepare(
				"UPDATE phph_sessions SET user_id = :user_id, session_time = :session_time, session_ip = :session_ip " .
				"WHERE session_id = :session_id");
			$sth->bindParam(":session_id", $this->_sid);
			$sth->bindParam(":user_id", $this->_uid);
			$sth->bindValue(":session_time", time());
			$sth->bindValue(":session_ip", Utils::getEncodedClientIP());
			$sth->execute();
			$sth = null;
		}

		$sth = $db->prepare("SELECT COUNT(*) AS cnt FROM phph_session_history WHERE session_id = :session_id");
		$sth->bindParam(":session_id", $this->_sid);
		$sth->execute();
		$r = $sth->fetchColumn(0);
		$sth = null;

		if ($r == 0) {

			$sth = $db->prepare(
				"INSERT INTO phph_session_history (session_id, user_id, session_start, session_ip) " .
				"VALUES (:session_id, :user_id, :session_start, :session_ip)");
			$sth->bindParam(":session_id", $this->_sid);
			$sth->bindParam(":user_id", $this->_uid);
			$sth->bindValue(":session_start", time());
			$sth->bindValue(":session_ip", Utils::getEncodedClientIP());
			$sth->execute();
			$sth = null;

		} else {

			$sth = $db->prepare(
				"UPDATE phph_session_history SET user_id = :user_id, session_ip = :session_ip " .
				"WHERE session_id = :session_id");
			$sth->bindParam(":session_id", $this->_sid);
			$sth->bindParam(":user_id", $this->_uid);
			$sth->bindValue(":session_ip", Utils::getEncodedClientIP());
			$sth->execute();
			$sth = null;
		}

		if ($this->_uid != ANON_USER) {

			$this->getUser()->updateIPRecord();
			$this->getUser()->updateLastLogin();
		}

		$c_domain = Config::get("cookie_domain");
		$c_path = Config::get("cookie_path");
		$sid_name = Session::getSIDCookieName();
		$uid_name = Session::getUIDCookieName();

		setcookie($sid_name, $this->_sid, time() + 31536000, $c_path, $c_domain);
		setcookie($uid_name, $this->_uid, time() + 31536000, $c_path, $c_domain);	// expire in 1 year
	}

	public function logout() {
		$db = Database::singletone()->db();

		if ($this->uid() == ANON_USER)
			return;

		$sth = $db->prepare("DELETE FROM phph_sessions WHERE session_id = :sid");
		$sth->bindParam(":sid", $this->_sid);
		$sth->execute();

		$this->_user = null;
		$this->_method = SESSION_METHOD_GET;
		$this->_sid = '';
		$this->_uid = ANON_USER;

		$this->newSession();
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
				header("Location: $url/index.php?action=login&ref=" . urlencode(Utils::selfURL()));
			}
			return true;
		}
		return false;
	}

	public function getUser() {
		return $this->user();
	}

	public function user() {
		if ($this->_uid == ANON_USER)
			return null;
		if ($this->_user == null) {
			$this->_user = new User($this->_uid);
		}
		return $this->_user;
	}

	public function sid() {
		return $this->_sid;
	}

	public function uid() {
		return $this->_uid;
	}

	public function logged() {
		return $this->_uid != ANON_USER;
	}

	function getUserSetting($name, $def, $glob = true) {
		return Config::getUser($this->_uid, $name, $def, $glob);
	}

	function setUserSetting($name, $val) {
		Config::setUser($this->_uid, $name, $val);
	}

	function isAdmin() {
		if ($this->isAnon())
			return false;
		return $this->getUser()->isAdmin();
	}

	function isAnon() {
		return $this->uid() == ANON_USER;
	}

	function checkLevel($uid) {
		if ($this->isAnon())
			return false;

		return $this->user()->checkLevel($uid);
	}

	function checkLevelVal($level) {
		if ($this->isAnon())
			return false;

		return $this->user()->checkLevelVal($level);
	}

	function checkPerm($perm) {
		if ($this->isAnon())
			return false;

		return $this->user()->checkPerm($perm);
	}

	function checkPermAndLevel($perm, $uid) {
		if ($this->isAnon())
			return false;

		return $this->user()->checkPermAndLevel($perm, $uid);
	}

	function checkPermAndLevelVal($perm, $level) {
		if ($this->isAnon())
			return false;

		return $this->user()->checkPermAndLevelVal($perm, $level);
	}


}

Session::create();

?>
