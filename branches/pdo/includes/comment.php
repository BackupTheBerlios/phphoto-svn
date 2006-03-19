<?php
// $Id$

require_once("DB/DataObject.php");
require_once("includes/db.php");
require_once("includes/config.php");
require_once("includes/utils.php");
require_once("includes/user.php");
require_once("includes/photo.php");

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
				throw new Exception2("B��d wewn�trzny", $this->_dbo->getMessage());
			$r = $this->_dbo->get($cmid);
			if (PEAR::isError($r))
				throw new Exception2("B��d wewn�trzny", $r->getMessage());
			if ($r == 0)
				throw new Exception2("B��d", "Komentarz nie istnieje", COMMENT_NOT_FOUND);

			$this->_user = DB_DataObject::Factory("phph_users");
			if (PEAR::isError($this->_user))
				throw new Exception2("B��d wewn�trzny", $this->_user->getMessage());
			$r = $this->_user->get($this->_dbo->user_id);
			if (PEAR::isError($r))
				throw new Exception2("B��d wewn�trzny", $r->getMessage());
			if ($r == 0)
				throw new Exception2("B��d sp�jno�ci danych", "U�ytkownik do kt�rego nalezy komentarz nie istnieje.<br />Skontakuj si� z administratorem, podaj�c numer komentarza ($cmid).", COMMENT_USER_NOT_FOUND);
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

U�ytkownik $author wyedytowa� komentarz do Twojego zdj�cia "$photo_title".

Obecna tre�� komentarza:

$title

$text

----

Poprzednia tre�� komentarza:

$o_title

$o_text

-- 
Ten email zosta� wys�any automatycznie. Prosimy nie odpowiada�.
EOT;
			Utils::mail("Komentarz do Twojego zdj�cia \"$photo_title\" zosta� zmieniony.", $body, $photo->_user->user_email, $photo->_user->user_name);
		}

		if ($this->_dbo->user_id != $session->_uid) {
			$user = new User($session->_uid);

			$to_name = $this->_user->user_login;
			$photo_title = $photo->_dbo->photo_title;
			$author = $user->_dbo->user_login;

			$body = <<<EOT
Witaj $to_name,

U�ytkownik $author wyedytowa� Tw�j komentarz do zdj�cia "$photo_title".

Obecna tre�� komentarza:

$title

$text

----

Poprzednia tre�� komentarza:

$o_title

$o_text

-- 
Ten email zosta� wys�any automatycznie. Prosimy nie odpowiada�.
EOT;
			Utils::mail("Tw�j komentarz do zdj�cia \"$photo_title\" zosta� zmieniony.", $body, $this->_user->user_email, $this->_user->user_name);
		}

	
	}

	function remove($checkperm = true) {
		global $db;

		if ($checkperm && !Permissions::checkPermAndLevel('delete_comments', $this->_dbo->user_id))
			throw new Exception2("Nie mo�na usun�� komentarza", "Brak uprawnie�.");

		$q = $db->prepare("DELETE FROM phph_comments WHERE comment_id = ?");
		$db->execute($q, array($this->_cmid));

		return true;
	}
}

?>
