<?php
// $Id$

require_once("includes/Database.php");
require_once("includes/Config.php");
require_once("includes/Utils.php");
require_once("includes/User.php");
require_once("includes/Photo.php");

define('COMMENT_NOT_FOUND', 1);
define('COMMENT_USER_NOT_FOUND', 1);

class Comment {
	
	var $_cmid = 0;
	var $_dbo;
	var $_user;

	function __construct($cmid = 0) {
		global $db;

		$this->_cmid = $cmid;

		if ($this->_cmid != 0) {
		
			$this->_dbo = DB_DataObject::Factory("phph_comments");
			if (PEAR::isError($this->_dbo))
				throw new Exception2("Bd wewn�rzny", $this->_dbo->getMessage());
			$r = $this->_dbo->get($cmid);
			if (PEAR::isError($r))
				throw new Exception2("Bd wewn�rzny", $r->getMessage());
			if ($r == 0)
				throw new Exception2("Bd", "Komentarz nie istnieje", COMMENT_NOT_FOUND);

			$this->_user = DB_DataObject::Factory("phph_users");
			if (PEAR::isError($this->_user))
				throw new Exception2("Bd wewn�rzny", $this->_user->getMessage());
			$r = $this->_user->get($this->_dbo->user_id);
			if (PEAR::isError($r))
				throw new Exception2("Bd wewn�rzny", $r->getMessage());
			if ($r == 0)
				throw new Exception2("Bd sp�noci danych", "Uytkownik do kt�ego nalezy komentarz nie istnieje.<br />Skontakuj si�z administratorem, podajc numer komentarza ($cmid).", COMMENT_USER_NOT_FOUND);
		}

	}

	function update($title, $text) {
		$session = Session::singletone();

		if ($this->_dbo->comment_title == $title && $this->_dbo->comment_text == $text)
			return;

		$o_title = $this->_dbo->comment_title;
		$o_text = $this->_dbo->comment_text;
		$this->_dbo->comment_title = $title;
		$this->_dbo->comment_text = $text;
		$this->_dbo->update();

		$photo = new Photo($this->_dbo->photo_id);
		if ($photo->_dbo->user_id != $session->_uid) {
			$user = new User($session->_uid);

			$to_name = $photo->_user->user_login;
			$photo_title = $photo->_dbo->photo_title;
			$author = $user->_dbo->user_login;

			$body = <<<EOT
Witaj $to_name,

Uytkownik $author wyedytowa komentarz do Twojego zdj�ia "$photo_title".

Obecna tre�komentarza:

$title

$text

----

Poprzednia tre�komentarza:

$o_title

$o_text

-- 
Ten email zosta wysany automatycznie. Prosimy nie odpowiada�
EOT;
			Utils::mail("Komentarz do Twojego zdj�ia \"$photo_title\" zosta zmieniony.", $body, $photo->_user->user_email, $photo->_user->user_name);
		}

		if ($this->_dbo->user_id != $session->_uid) {
			$user = new User($session->_uid);

			$to_name = $this->_user->user_login;
			$photo_title = $photo->_dbo->photo_title;
			$author = $user->_dbo->user_login;

			$body = <<<EOT
Witaj $to_name,

Uytkownik $author wyedytowa Tw� komentarz do zdj�ia "$photo_title".

Obecna tre�komentarza:

$title

$text

----

Poprzednia tre�komentarza:

$o_title

$o_text

-- 
Ten email zosta wysany automatycznie. Prosimy nie odpowiada�
EOT;
			Utils::mail("Tw� komentarz do zdj�ia \"$photo_title\" zosta zmieniony.", $body, $this->_user->user_email, $this->_user->user_name);
		}

	
	}

	function remove($checkperm = true) {
		global $db;

		if ($checkperm && !Permissions::checkPermAndLevel('delete_comments', $this->_dbo->user_id))
			throw new Exception2("Nie mona usun�komentarza", "Brak uprawnie�");

		$q = $db->prepare("DELETE FROM phph_comments WHERE comment_id = ?");
		$db->execute($q, array($this->_cmid));

		return true;
	}
}

?>
