<?php
// $Id$

require_once("DB/DataObject.php");
require_once("Mail.php");
require_once("Mail/RFC822.php");
require_once("includes/db.php");
require_once("includes/config.php");
require_once("includes/utils.php");

define('USER_NOT_FOUND', 1);

class User {
	
	var $_uid = 0;
	var $_dbo;
	

	function __construct($uid = 0) {
		global $db;

		$this->_uid = $uid;

		if ($this->_uid > 0) {
			$this->_dbo = DB_DataObject::Factory("phph_users");
			if (PEAR::isError($this->_dbo))
				throw new Exception2("B³±d wewnêtrzny", $this->_dbo->getMessage());
			$r = $this->_dbo->get($uid);
			if (PEAR::isError($r))
				throw new Exception2("B³±d wewnêtrzny", $r->getMessage());
			if ($r == 0)
				throw new Exception2("B³±d", "U¿ytkownik nie istnieje", USER_NOT_FOUND);
		}
	}

	function register(&$data) {
		
		$session = Session::singletone();


		if (empty($data['user_login'])) {
			throw new Exception2("Nie mo¿na zarejestrowaæ konta", "Musisz podaæ login.");
		}
		
		if (empty($data['user_pass1']) || empty($data['user_pass2'])) {
			throw new Exception2("Nie mo¿na zarejestrowaæ konta", "Has³o nie mo¿e byæ puste.");
		}
		
		if (empty($data['user_email'])) {
			throw new Exception2("Nie mo¿na zarejestrowaæ konta", "Musisz podaæ email.");
		}

		$addr = Mail_RFC822::parseAddressList($data['user_email'], "");
		if (empty($addr))
			throw new Exception2("Nie mo¿na zarejestrowaæ konta", "Podany adres email jest nieprawid³owy.");

		if ($data['user_pass1'] != $data['user_pass2']) {
			throw new Exception2("Nie mo¿na zarejestrowaæ konta", "Podane has³a ró¿ni± siê");
		}
		
		$user_login = trim($data['user_login']);
		$user_pass1 = $data['user_pass1'];
		$user_pass2 = $data['user_pass2'];
		$user_email = trim($data['user_email']);

		$this->_dbo = DB_DataObject::Factory('phph_users');
		if (PEAR::isError($this->_dbo)) {
			throw new Exception2(_INTERNAL_ERROR, $this->_dbo->getMessage());
		}

		$r = $this->_dbo->get('user_login', $user_login);
		if (PEAR::isError($r)) {
			throw new Exception2(_INTERNAL_ERROR, $r->getMessage());
		}

		if ($r != 0) {
			throw new Exception2("Nie mo¿na zarejestrowaæ konta", "Podany login jest ju¿ zajêty.");
		}

		$this->_dbo->user_login = $user_login;
		$this->_dbo->user_pass = md5($user_pass1);
		$this->_dbo->user_email = $user_email;
		$this->_dbo->user_registered = time();
		$this->_dbo->user_activation = md5(uniqid($user_login));

		$r = $this->_dbo->insert();
		if (PEAR::isError($r)) {
			throw new Exception2(_INTERNAL_ERROR, $r->getMessage());
		}

		return $r;
	}

	function sendActivation($url) {
		$mail = Mail::factory("mail");

		$site_title = Config::get("site_title");
		
		$headers = array(
			"From" => Config::get("email_user") . " <" . Config::get("email_from") . ">",
			"To" => $this->_dbo->user_name . " <" . $this->_dbo->user_email . ">",
			"Subject" => "Rejestracja w serwisie \"$site_title\""
		);

		$rcpt = $this->_dbo->user_email;

		$link = $url . "&uid=" . $this->_uid . "&r=" . $this->_dbo->user_activation;

		$user_login = $this->_dbo->user_login;
		$body = <<<EOT
Witaj $user_login,

Ten email zosta³ wys³any do Ciebie, poniewa¿ kto¶ u¿ywaj±cy tego
adresu email zarejestrowa³ siê w serwisie "$site_title".
Je¶li uwa¿asz, ¿e ten mail nie powinien dotrzeæ do Ciebie, 
po prostu go zignoruj.

Aby dokonczyæ rejestracjê, kliknij w poni¿szy link:

$link

Dziêkujemy.

-- 
Email wys³any automatycznie. Prosimy nie odpowiadaæ.
EOT;
		$mail->send($rcpt, $headers, $body);
	}

	function activate($r, $login_url) {

		if ($this->_dbo->user_activation != $r)
			throw new Exception2("B³±d aktywacji", "Konto nie istnieje.");

		if ($this->_dbo->user_activated > 0)
			throw new Exception2("B³±d aktywacji", "Konto zosta³o ju¿ aktywowane.");

		$this->_dbo->user_activated = time();
		$this->_dbo->update();

		$mail = Mail::factory("mail");

		$site_title = Config::get("site_title");
		
		$headers = array(
			"From" => Config::get("email_user") . " <" . Config::get("email_from") . ">",
			"To" => $this->_dbo->user_name . " <" . $this->_dbo->user_email . ">",
			"Subject" => "Dziêkujemy za rejestracjê w serwisie \"$site_title\""
		);

		$rcpt = $this->_dbo->user_email;

		$user_login = $this->_dbo->user_login;
		$body = <<<EOT
Witaj $user_login,

Dziêkujemy za rejestracjê w serwisie "$site_title".
Od tej pory mo¿esz zalogowaæ siê na swoje konto pod poni¿szym adresem:
$login_url

Pozdrawiamy.

-- 
Email wys³any automatycznie. Prosimy nie odpowiadaæ.
EOT;
		$mail->send($rcpt, $headers, $body);
	}

	function login($login, $pass) {

		$session = Session::singletone();

		$this->_dbo = DB_DataObject::Factory('phph_users');
		if (PEAR::isError($this->_dbo)) {
			throw new Exception2(_INTERNAL_ERROR, $this->_dbo->getMessage());
		}

		$this->_dbo->user_login = $login;
		$this->_dbo->user_pass = md5($pass);
		$r = $this->_dbo->find();
		if (PEAR::isError($r)) {
			throw new Exception2(_INTERNAL_ERROR, $r->getMessage());
		}

		if ($r == 0) {
			throw new Exception2(_LOGIN_FAILED, "");
		}
		$r = $this->_dbo->fetch();
		if (PEAR::isError($r)) {
			throw new Exception2(_INTERNAL_ERROR, $r->getMessage());
		}

		$this->_dbo->user_lastlogin = time();
		$this->_dbo->update();

		$this->_uid = $this->_dbo->user_id;
		$session->_uid = $this->_dbo->user_id;
		$session->newSession();
	}
}

?>
