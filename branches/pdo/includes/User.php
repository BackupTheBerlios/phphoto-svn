<?php
// $Id$

require_once("Mail/RFC822.php");
require_once("includes/Database.php");
require_once("includes/Config.php");
require_once("includes/Utils.php");

define('USER_NOT_FOUND', 1);

class User {

	var $_uid = 0;
	var $_dbdata = null;

	private static $_level_cache = array();


	function __construct($uid = 0) {
		$this->_uid = $uid;
		$this->updateDBData();
	}

	function uid() {
		return $this->_uid;
	}

	private function updateDBData() {
		if ($this->_uid > 0) {
			$sth = Database::singletone()->db()->prepare("SELECT * FROM phph_users WHERE user_id = :user_id");
			$sth->bindParam(":user_id", $this->_uid);
			$sth->execute();
			if (!($this->_dbdata = $sth->fetch()))
				throw new Exception("Użytkownik nie istnieje");
		}
	}

	function dbdata($name) {
		return $this->_dbdata[$name];
	}

	function register(&$data) {

		$session = Session::singletone();
		$db = Database::singletone()->db();


		if (empty($data['user_login'])) {
			throw new Exception("Nie można zarejestrować konta. Musisz podać login.");
		}

		if (empty($data['user_pass1']) || empty($data['user_pass2'])) {
			throw new Exception("Nie można zarejestrować konta. Hasło nie może być puste.");
		}

		if (empty($data['user_email'])) {
			throw new Exception("Nie można zarejestrować konta. Musisz podać email.");
		}

		$addr = Mail_RFC822::parseAddressList($data['user_email'], "");
		if (empty($addr))
			throw new Exception("Nie można zarejestrować konta. Podany adres email jest nieprawidłowy.");

		if ($data['user_pass1'] != $data['user_pass2']) {
			throw new Exception("Nie można zarejestrować konta. Podane hasła różnią się");
		}

		$user_login = trim($data['user_login']);
		$user_pass1 = $data['user_pass1'];
		$user_pass2 = $data['user_pass2'];
		$user_email = trim($data['user_email']);

		$sth = $db->prepare("SELECT COUNT(*) AS cnt FROM phph_users WHERE user_login = :user_login");
		$sth->bindParam(":user_login", $user_login);
		$sth->execute();
		$cnt = $sth->fetchColumn(0);
		$sth = null;

		if ($cnt != 0) {
			throw new Exception("Nie można zarejestrować konta. Podany login jest już zajęty.");
		}

		$sth = $db->prepare(
			"INSERT INTO phph_users (user_login, user_pass, user_email, user_registered, user_activation) VALUES ".
			"(:user_login, :user_pass, :user_email, :user_registered, :user_activation)"
		);
		$sth->bindParam(":user_login", $user_login);
		$sth->bindValue(":user_pass", md5($user_pass1));
		$sth->bindParam(":user_email", $user_email);
		$sth->bindValue(":user_registered", time());
		$sth->bindValue(":user_activation", md5(uniqid($user_login)));
		$sth->execute();
		$sth = null;

		$this->_uid = $db->lastInsertId();
		$this->updateDBData();
		return $this->_uid;
	}

	function sendActivation($url) {

		$site_title = Config::get("site_title");

		$link = $url . "&uid=" . $this->_uid . "&r=" . $this->dbdata('user_activation');

		$user_login = $this->dbdata('user_login');
		$body = <<<EOT
Witaj $user_login,

Ten email został wysłany do Ciebie, ponieważ ktoś używajcy tego
adresu email zarejestrował się w serwisie "$site_title".
Jeśli uważasz, że ten mail nie powinien dotrzeć do Ciebie,
po prostu go zignoruj.

Aby dokończyć rejestrację kliknij w poniższy link:

$link

Dziękujemy.

EOT;
		Utils::mail("Rejestracja w serwisie \"$site_title\"", $body, $this->dbdata('user_email'), $this->dbdata('user_name'));
	}

	function activate($r, $login_url) {

		$db = Database::singletone()->db();

		if ($this->dbdata('user_activation') != $r)
			throw new Exception("Błąd aktywacji. Konto nie istnieje.");

		if ($this->dbdata('user_activated') > 0)
			throw new Exception("Błąd aktywacji. Konto zostało już aktywowane.");

		//$this->_dbo->user_activated = time();
		$sth = $db->prepare("UPDATE phph_users SET user_activated = :user_activated WHERE user_id = :uid");
		$sth->bindValue(":user_activated", time());
		$sth->bindParam(":uid", $this->_uid);
		$sth->execute();
		$this->updateDBData();

		$site_title = Config::get("site_title");

		$user_login = $this->dbdata('user_login');
		$body = <<<EOT
Witaj $user_login,

Dziękujemy za rejestrację w serwisie "$site_title".
Od tej pory możesz zalogować się na swoje konto pod poniższym adresem:
$login_url

Pozdrawiamy.

EOT;
		Utils::mail("Dziękujemy za rejestrację w serwisie \"$site_title\"", $body, $this->dbdata('user_email'), $this->dbdata('user_name'));
	}

	function login($login, $pass) {

		$session = Session::singletone();
		$db = Database::singletone()->db();

		$sth = $db->prepare("SELECT * FROM phph_users WHERE user_login = :login AND user_pass = :pass");
		$sth->bindParam(":login", $login);
		$sth->bindValue(":pass", md5($pass));
		$sth->execute();

		if (!($this->_dbdata = $sth->fetch())) {
			throw new Exception("Nieudane logowanie.");
		}
		$sth = null;

		$this->updateLastLogin();

		$this->_uid = $this->_dbdata['user_id'];
		$session->_uid = $this->_dbdata['user_id'];
		$session->newSession();
	}

	function updateLastLogin() {
		$db = Database::singletone()->db();
		$sth = $db->prepare("UPDATE phph_users SET user_lastlogin = ? WHERE user_id = ?");
		$sth->bindValue(1, time());
		$sth->bindParam(2, $this->_uid);
		$sth->execute();
		$sth = null;
	}

	function updateIPRecord() {
		$db = Database::singletone()->db();

		$sth = $db->prepare("UPDATE phph_user_ip SET last_visit = :last_visit WHERE user_id = :user_id AND ip = :ip");
		$sth->bindValue(":last_visit", time());
		$sth->bindParam(":user_id", $this->_uid);
		$sth->bindValue(":ip", Utils::getEncodedClientIP());
		$sth->execute();
		$cnt = $sth->rowCount();
		$sth = null;
		if ($cnt == 0) {
			$sth = $db->prepare("INSERT INTO phph_user_ip (user_id, ip, last_visit) VALUES (?, ?, ?)");
			$sth->bindParam(1, $this->_uid);
			$sth->bindValue(2, Utils::getEncodedClientIP());
			$sth->bindValue(3, time());
			$sth->execute();
			$sth = null;
		}
	}

	function getSetting($name, $def, $glob = true) {
		return Config::getUser($this->_uid, $name, $def, $glob);
	}

	function setSetting($name, $val) {
		Config::setUser($this->_uid, $name, $val);
	}

	function isAdmin() {
		return $this->dbdata('user_admin');
	}

	static function getUserLevel($uid) {

		if (array_key_exists($uid, self::$_level_cache)) {
			return self::$_level_cache[$uid];
		} else {
			$db = Database::singletone()->db();

			$gl = 0;

			$sth = $db->prepare("SELECT IFNULL(MAX(group_level), 0) AS gl FROM phph_groups WHERE group_id IN (SELECT group_id FROM phph_group_users WHERE user_id = :user_id)");
			$sth->bindParam(":user_id", $uid);
			$sth->execute();
			$gl = $sth->fetchColumn(0);
			$sth = null;

			$sth = $db->prepare("SELECT user_level FROM phph_users WHERE user_id = :user_id");
			$sth->bindParam(":user_id", $uid);
			$sth->execute();
			$ul = $sth->fetchColumn(0);

			$ul = max($gl, $ul);

			self::$_level_cache[$uid] = $ul;

			return $ul;
		}
	}

	function getLevel() {
		return self::getUserLevel($this->uid());
	}

	function checkLevel($uid) {
		if ($this->isAdmin())
			return true;

		return $this->checkLevelVal(self::getUserLevel($uid));
	}

	function checkLevelVal($level) {
		if ($this->isAdmin())
			return true;

		return $this->getUserLevel() > $level;
	}

	function checkPerm($perm) {

		if ($this->isAdmin())
			return true;

		$db = Database::singletone()->db();

		$sth = $db->prepare("SELECT COUNT(*) FROM phph_permissions WHERE permission = :perm AND user_id = :uid");
		$sth->bindParam(":perm", $perm);
		$sth->bindValue(":uid", $this->uid());
		$sth->execute();
		$r = $sth->fetchColumn(0);
		$sth = null;
		if ($r > 0)
			return true;

		$sth = $db->prepare("SELECT COUNT(*) FROM phph_permissions WHERE permission = :perm AND group_id IN (SELECT group_id FROM phph_group_users WHERE user_id = :uid)");
		$sth->bindParam(":perm", $perm);
		$sth->bindValue(":uid", $this->uid());
		$sth->execute();
		$r = $sth->fetchColumn(0);
		$sth = null;
		if ($r > 0)
			return true;

		return false;
	}

	function checkPermAndLevel($perm, $uid) {
		return $this->checkPerm($perm) && $this->checkLevel($uid);
	}

	function checkPermAndLevelVal($perm, $level) {
		return $this->checkPerm($perm) && $this->checkLevelVal($level);
	}
}

?>
