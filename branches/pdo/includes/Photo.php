<?php
// $Id$

require_once("includes/Database.php");
require_once("includes/Config.php");
require_once("includes/Utils.php");
require_once("includes/Category.php");
require_once("includes/Language.php");
require_once("includes/User.php");
require_once("includes/pjmt/EXIF.php");
//require_once("includes/Comment.php");

define('PHOTO_NOT_FOUND', 1);
define('PHOTO_USER_NOT_FOUND', 2);

define('PHOTO_OPT_GRAYSCALE', 1);
define('PHOTO_OPT_APPROVED', 2);

class PhotoModeration {
	private $_pid = 0;
	private $_dbdata = array();
	private $_orig_dbdata = array();
	private $_moderator = null;

	function __construct($mid = 0) {
		$this->_mid = $mid;
		$this->updateDBData();
	}

	function mid() {
		return $this->_mid;
	}

	private function updateDBData() {
		if ($this->_mid > 0) {
			$sth = Database::singletone()->db()->prepare("SELECT * FROM phph_photos_moderation WHERE moderation_id = :mid");
			$sth->bindParam(":mid", $this->_mid);
			$sth->execute();
			if (!($this->_dbdata = $sth->fetch()))
				throw new Exception(_T("Rekord nie istnieje."));
			$this->_orig_dbdata = $this->_dbdata;
			$sth = null;
		}
	}

	function dbdata($name, $def = '') {
		if (array_key_exists($name, $this->_dbdata))
			return $this->_dbdata[$name];
		else
			return $def;
	}

	function setDBData($name, $val) {
		$this->_dbdata[$name] = $val;
	}

	function moderator() {
		if (is_object($this->_moderator))
			return $this->_moderator;

		$this->_moderator = new User($this->dbdata('user_id'));
		return $this->_moderator;
	}

	function fullData() {
		$data = $this->_dbdata;
		$data['moderator'] = $this->moderator()->fullData();
		return $data;
	}

	function xml($xml, $name = 'operation') {
		$db = Database::singletone()->db();
		$session = Session::singletone();

		$op = $xml->createElement($name);
		$op->appendChild($xml->createElement('mode', $this->dbdata('moderation_mode')));
		$op->appendChild($xml->createElement('time', Utils::formatTime($this->dbdata('moderation_time'), Config::getUser($session->uid(), 'datetime-format'))));
		$op->appendChild($xml->createElement('note', $this->dbdata('moderation_note')));
		$op->appendChild($this->moderator()->xml($xml, 'moderator'));

		return $op;
	}


}

class Photo {

	private $_pid = 0;
	private $_dbdata = array();
	private $_orig_dbdata = array();
	private $_author = null;
	private $_moderation = null;


	function __construct($pid = 0) {
		$this->_pid = $pid;
		$this->updateDBData();
	}

	function pid() {
		return $this->_pid;
	}

	private function updateDBData() {
		if ($this->_pid > 0) {
			$sth = Database::singletone()->db()->prepare("SELECT * FROM phph_photos WHERE photo_id = :photo_id");
			$sth->bindParam(":photo_id", $this->_pid);
			$sth->execute();
			if (!($this->_dbdata = $sth->fetch()))
				throw new Exception(_T("Zdjęcie nie istnieje"));
			$this->_orig_dbdata = $this->_dbdata;
			$sth = null;
		}
	}

	function dbdata($name, $def = '') {
		if (array_key_exists($name, $this->_dbdata))
			return $this->_dbdata[$name];
		else
			return $def;
	}

	function setDBData($name, $val) {
		$this->_dbdata[$name] = $val;
	}

	function moderation() {
		if (is_array($this->_moderation))
			return $this->_moderation;

		$this->_moderation = array();
		$sth = Database::singletone()->db()->prepare("SELECT moderation_id FROM phph_photos_moderation WHERE photo_id = :photo_id ORDER BY moderation_time DESC");
		$sth->bindParam(":photo_id", $this->_pid);
		$sth->execute();
		while ($row = $sth->fetch())
			$this->_moderation[] = new PhotoModeration($row['moderation_id']);
		return $this->_moderation;
	}

	function moderationFullData() {
		$this->moderation();	// force lod

		$data = array();
		foreach ($this->_moderation as $moder)
			$data[] = $moder->fullData();

		return $data;
	}

	function author() {
		if (is_object($this->_author))
			return $this->_author;

		$this->_author = new User($this->dbdata('user_id'));
		return $this->_author;
	}

	function fullData() {
		$data = $this->_dbdata;
		$data['author'] = $this->author()->fullData();
		$data['moderation'] = $this->moderationFullData();
		return $data;
	}

	function genName($w, $h, $uid) {
		$name[0] = md5(uniqid($w . "x" . $h . $uid)) . ".jpg";
		$name[1] = $name[0][0] . '/' . $name[0][1];
		$name[2] = $name[1] . '/' . $name[0];
		$name[3] = dirname(__FILE__) . '/../photos/' . $name[1];
		$name[4] = $name[3] . '/' . $name[0];
		return $name;
	}

	static function clearCache() {
		$db = Database::singletone()->db();
		$lft = time() - (Config::get('cache-lifetime', 7) * 3600 * 24);
		$sth = $db->prepare("SELECT file_id, file_name FROM phph_files WHERE file_keep = 0 AND file_accessed < :access");
		$sth->bindParam(':access', $lft);
		$sth->execute();
		$files = array();
		while ($row = $sth->fetch()) {
			$files[] = $row;
		}
		$sth = null;
		$sth = $db->prepare("DELETE FROM phph_files WHERE file_id = :id");
		foreach ($files as $file) {
			unlink(dirname(__FILE__) . "/../photos/" . $file['file_name']);
			$sth->bindValue(':id', $file['file_id']);
			$sth->execute();
		}
	}

	function upload($file, $cids) {

		if ($file["error"] == UPLOAD_ERR_INI_SIZE || $file["error"] == UPLOAD_ERR_FORM_SIZE || $file["size"] > Config::get("max-file-size", 200) * 1024) {
			throw new Exception(_T("Rozmiar pliku przekracza dopuszczalny limit."));
		}

		if (empty($file['name'])) {
			throw new Exception(_T("Brak pliku."));
		}

		$ok = false;

		$mime = $file["type"];
		if ($mime == "image/jpeg" || $mime == "image/pjpeg" || $mime = "image/jpg") {
			$name  = $file["name"];
			$array = explode(".", $name);
			$nr    = count($array);
			$ext  = $array[$nr-1];
			if(strtolower($ext) == "jpg" || strtolower($ext) == "jpeg") {
				$ok = true;
			}
		}
		if (!$ok) {
			throw new Exception(_T("Plik nie jestem plikiem JPEG"));
		}


		if ($ok == true) {

			$db = Database::singletone()->db();

			$session = Session::singletone();
			$tempname = $file['tmp_name'];

			$imgs = getimagesize($tempname);
			if ($imgs['mime'] != "image/jpeg" && $imgs['mime'] != "image/pjpeg" && $imgs['mime'] != "image/jpg") {
				throw new Exception(_T("Plik nie jestem plikiem JPEG"));
			}
			if ($imgs[0] > Config::get("max-width", 640) || $imgs[1] > Config::get("max-height", 600)) {
				throw new Exception(_T("Wymiary zdjęcia przekraczają dopuszczalne limity."));
			}

			$name_a = $this->genName($imgs[0], $imgs[1], $session->_uid);
			if (!file_exists($name_a[3]))
				mkdir($name_a[3], 0755, true);
			$uploadpath = $name_a[4];
			$name = $name_a[2];

			if (is_uploaded_file($tempname)) {
				while (move_uploaded_file($tempname, $uploadpath));
			}

			$sth = $db->prepare(
				'INSERT INTO phph_photos (user_id, photo_title, photo_description, photo_width, photo_height, photo_added) '.
				'VALUES (:user_id, :photo_title, :photo_description, :photo_width, :photo_height, :photo_added)');
			$sth->bindValue(':user_id', $session->uid());
			$sth->bindValue(':photo_title', $this->dbdata('photo_title'));
			$sth->bindValue(':photo_description', $this->dbdata('photo_description'));
			$sth->bindValue(':photo_width', $imgs[0]);
			$sth->bindValue(':photo_height', $imgs[1]);
			$sth->bindValue(':photo_added', time());
			$sth->execute();
			$this->_pid = $db->lastInsertId();
			$sth = null;

			$sth = $db->prepare('INSERT INTO phph_photos_categories (photo_id, category_id) VALUES (:pid, :cid)');
			$cid = 0;
			$sth->bindParam(':cid', $cid);
			$sth->bindParam(':pid', $this->_pid);
			foreach ($cids as $cid) {
				$sth->execute();
			}
			$sth = null;

			$sth = $db->prepare(
				'INSERT INTO phph_files (photo_id, file_name, file_created, file_accessed, file_original, file_keep, file_width, file_height) '.
				'VALUES (:photo_id, :file_name, :file_created, :file_accessed, :file_original, :file_keep, :file_width, :file_height)');
			$sth->bindValue(':photo_id', $this->_pid);
			$sth->bindValue(':file_name', $name);
			$sth->bindValue(':file_created', time());
			$sth->bindValue(':file_accessed', time());
			$sth->bindValue(':file_original', true);
			$sth->bindValue(':file_keep', true);
			$sth->bindValue(':file_width', $imgs[0]);
			$sth->bindValue(':file_height', $imgs[1]);
			$sth->execute();
			$sth = null;

			$this->updateDBData();

			if (Config::get("auto-approve", false))
				$this->approve();
		}

		Photo::clearCache();
	}

	function moderate($mode, $note) {
		$session = Session::singletone();
		$db = Database::singletone()->db();

		$sth = $db->prepare('INSERT INTO phph_photos_moderation (photo_id, user_id, moderation_time, moderation_mode, moderation_note) ' .
				    'VALUES (:photo_id, :user_id, :moderation_time, :moderation_mode, :moderation_note)');
		$sth->bindValue(':photo_id', $this->pid());
		$sth->bindValue(':user_id', $session->uid());
		$sth->bindValue(':moderation_time', time());
		$sth->bindValue(':moderation_mode', $mode);
		$sth->bindValue(':moderation_note', $note);
		$sth->execute();
		$mid = $db->lastInsertId();
		$sth = null;

		$sth = $db->prepare('UPDATE phph_photos SET moderation_id = :mid WHERE photo_id = :pid');
		$sth->bindValue(':pid', $this->pid());
		$sth->bindValue(':mid', $mid);
		$sth->execute();
		$sth = null;

		$this->_moderation = null;	// invalidate old data
		$this->updateDBData();
	}

	function approve() {
		$session = Session::singletone();
		$db = Database::singletone()->db();

		$sth = $db->prepare('UPDATE phph_photos SET photo_approved = 1 WHERE photo_id = :pid');
		$sth->bindValue(':pid', $this->pid());
		$sth->execute();
		$sth = null;
		$this->updateDBData();

		$this->moderate('approve', '');

/*		$subs = array();
		foreach ($this->_category_objs as $category) {
			$category->getSubscribers(&$subs);
		}

		foreach ($subs as $id => $user) {
			if ($id != $this->_dbo->user_id) {
				$name = $user['user_login'];
				$title = $this->_dbo->photo_title;
				$author = $this->_user->user_login;
				$link = Config::get("site_url") . "/index.php?action=view&pid=" . $this->_pid;

				$cata = array();
				foreach ($user['cids'] as $cid => $cat) {
					$cata[] = $cat['category_name'];
				}
				$cats = implode("\n - ", $cata);
				$txt_body = <<<EOT
Witaj $name,

W nast�ujcych kategoriach:
 - $cats
pojawio si�nowe zdj�ie:
Tytu: $title
Autor: $author
$link

--
Ten email zosta wysany automatycznie.
Prosimy nie odpowiada�
EOT;
				Utils::mail("Nowe zdj�ie w obserwowanych kategoriach.", $txt_body, $user['user_email'], $user['user_name']);
			}
		}
*/
		if (Config::get('send-approve-notify', 0)/* && $session->uid() != $this->dbdata('user_id')*/) {
			$search = array('[rcpt_login]', '[photo_title]', '[moderator_login]', '[date]', '[time]', '[datetime]');
			$replace = array($this->author()->dbdata('user_login'), $this->dbdata('photo_title'), $session->user()->dbdata('user_login'),
					Utils::formatDate(time()), Utils::formatTime(time(), '%T'), Utils::formatTime(time()));
			$txt_body = str_replace($search, $replace, Config::get('approve-notify', ''));

			Utils::mail("Twoje zdjęcie zostało zaakceptowane.", $txt_body, $this->author()->dbdata('user_email'), $this->author()->dbdata('user_name'));
		}

	}

	function reject($note) {
		$session = Session::singletone();
		$db = Database::singletone()->db();

		$sth = $db->prepare('UPDATE phph_photos SET photo_approved = 0 WHERE photo_id = :pid');
		$sth->bindValue(':pid', $this->pid());
		$sth->execute();
		$sth = null;
		$this->updateDBData();

		$this->moderate('reject', $note);

/*		$subs = array();
		foreach ($this->_category_objs as $category) {
			$category->getSubscribers(&$subs);
		}

		foreach ($subs as $id => $user) {
			if ($id != $this->_dbo->user_id) {
				$name = $user['user_login'];
				$title = $this->_dbo->photo_title;
				$author = $this->_user->user_login;
				$link = Config::get("site_url") . "/index.php?action=view&pid=" . $this->_pid;

				$cata = array();
				foreach ($user['cids'] as $cid => $cat) {
					$cata[] = $cat['category_name'];
				}
				$cats = implode("\n - ", $cata);
				$txt_body = <<<EOT
Witaj $name,

W nast�ujcych kategoriach:
 - $cats
pojawio si�nowe zdj�ie:
Tytu: $title
Autor: $author
$link

--
Ten email zosta wysany automatycznie.
Prosimy nie odpowiada�
EOT;
				Utils::mail("Nowe zdj�ie w obserwowanych kategoriach.", $txt_body, $user['user_email'], $user['user_name']);
			}
		}
*/
		if (Config::get('send-reject-notify', 0)/* && $session->uid() != $this->dbdata('user_id')*/) {
			$search = array('[rcpt_login]', '[photo_title]', '[moderator_login]', '[date]', '[time]', '[datetime]', '[moderator_note]');
			$replace = array($this->author()->dbdata('user_login'), $this->dbdata('photo_title'), $session->user()->dbdata('user_login'),
					Utils::formatDate(time()), Utils::formatTime(time(), '%T'), Utils::formatTime(time()), $note);
			$txt_body = str_replace($search, $replace, Config::get('reject-notify', ''));

			Utils::mail("Twoje zdjęcie zostało odrzucone.", $txt_body, $this->author()->dbdata('user_email'), $this->author()->dbdata('user_name'));
		}

	}

	function moderationXML($xml, $name = 'moderation') {
		$db = Database::singletone()->db();
		$session = Session::singletone();

		$this->moderation();

		$status = 'waiting';

		if ($this->dbdata('moderation_id', 0) > 0) {
			$moder = new PhotoModeration($this->dbdata('moderation_id'));
			$status = $moder->dbdata('moderation_mode');
		}

		$el = $xml->createElement($name);
		$el->setAttribute('status', $status);

		foreach ($this->_moderation as $moder) {
			$el->appendChild($moder->xml($xml));
		}
		return $el;
	}

/*	function getApproveTime() {
		$db = Database::singletone()->db();

		$sth = $db->prepare('SELECT moderation_time FROM phph_photos_moderation WHERE photo_id = :photo_id AND moderation_mode =
	}
*/

	/*
	 *	$ret[0] = Utils::fullURL('photos/' . $file_name);
	 *	$ret[1] = $file_width;
	 *	$ret[2] = $file_height;
	 *	$ret[3] = "width=\"" . $file_width . "\"";
	 *	$ret[4] = "height=\"" . $file_height . "\"";
	 *	$ret[5] = $ret[3] . " " . $ret[4];
	 *	$ret[6] = dirname(__FILE__) . "/../photos/" . $file_name;
	 *	$ret[7] = "<img src=\"" . $ret[0] . "\" " . $ret[5] . " alt=\" ";
	 *	if (empty($alt))
	 *		$ret[7] .= htmlspecialchars($this->dbdata('photo_title'));
	 *	else
	 *		$ret[7] .= $alt;
	 *	$ret[7] .= "\" />";
	 */
	function get($w = 0, $h = 0, $opt = 0, $resize_up = true, $alt = "") {

		$db = Database::singletone()->db();

		$file_name = '';
		$file_width = 0;
		$file_height = 0;
		$fid = 0;
		$newopt = $opt & ~PHOTO_OPT_APPROVED;

		if (($opt & PHOTO_OPT_APPROVED) && $this->dbdata('photo_approved') == 0) {
			$newopt = PHOTO_OPT_APPROVED;
		}

		if ($w == 0 || $h == 0 || ($w >= $this->dbdata('photo_width') && $h >= $this->dbdata('photo_height') && !$resize_up)) {

			$sth = $db->prepare('SELECT * FROM phph_files WHERE file_original = 1 AND photo_id = :pid AND file_options = :opt');
			$sth->bindValue(':pid', $this->pid());
			$sth->bindParam(':opt', $newopt);
			$sth->execute();
			$row = $sth->fetch();
			$file_name = $row['file_name'];
			$file_width = $row['file_width'];
			$file_height = $row['file_height'];
			$fid = $row['file_id'];

			$sth = null;

		} else {

			$f_w = $w / floatval($this->dbdata('photo_width'));
			$f_h = $h / floatval($this->dbdata('photo_height'));
			$f = min($f_w, $f_h);
			$nw = round(floatval($this->dbdata('photo_width')) * $f);
			$nh = round(floatval($this->dbdata('photo_height')) * $f);

			$file_width = $nw;
			$file_height = $nh;

			$sth = $db->prepare('SELECT * FROM phph_files WHERE photo_id = :pid AND file_width = :w AND file_height = :h AND file_options = :opt');
			$sth->bindValue(':pid', $this->pid());
			$sth->bindValue(':w', $nw);
			$sth->bindValue(':h', $nh);
			$sth->bindParam(':opt', $newopt);
			$sth->execute();
			$row = $sth->fetch();
			$sth = null;

			if ($row) {
				$file_name = $row['file_name'];
				$file_width = $row['file_width'];
				$file_height = $row['file_height'];
				$fid = $row['file_id'];
			} else {
				$name_a = $this->genName($nw, $nh, $this->dbdata('user_id'));
				$name = $name_a[2];
				if (!file_exists($name_a[3]))
					mkdir($name_a[3], 0755, true);

				$sth = $db->prepare('SELECT file_name FROM phph_files WHERE file_original = 1 AND photo_id = :pid');
				$sth->bindValue(':pid', $this->pid());
				$sth->execute();
				$row = $sth->fetch();
				$orig_file_name = $row['file_name'];
				$sth = null;

				$original = @imagecreatefromjpeg(dirname(__FILE__) . "/../photos/" . $orig_file_name);
				$sized = imagecreatetruecolor($nw, $nh);
				imagecopyresampled($sized, $original, 0, 0, 0, 0, $nw, $nh, $this->dbdata('photo_width'), $this->dbdata('photo_height'));
				//imagefilter($sized, IMG_FILTER_GRAYSCALE);
				imagejpeg($sized, dirname(__FILE__) . "/../photos/" . $name, 100);

				$sth = $db->prepare(
					'INSERT INTO phph_files '.
					'(photo_id, file_original, file_keep, file_width, file_height, file_created, file_accessed, file_name, file_options) VALUES '.
					'(:pid, 0, 0, :w, :h, :created, :accessed, :name, :opt)');
				$sth->bindValue(':pid', $this->pid());
				$sth->bindParam(':opt', $newopt);
				$sth->bindValue(':w', $nw);
				$sth->bindValue(':h', $nh);
				$sth->bindValue(':name', $name);
				$sth->bindValue(':created', time());
				$sth->bindValue(':accessed', time());
				$sth->execute();
				$fid = $db->lastInsertId();
				$sth = null;

				$file_name = $name;
				$file_width = $nw;
				$file_height = $nh;
			}
		}

		$sth = $db->prepare('UPDATE phph_files SET file_accessed = :accessed WHERE file_id = :fid');
		$sth->bindValue(':accessed', time());
		$sth->bindValue(':fid', $fid);
		$sth->execute();

		$ret[0] = Utils::fullURL('photos/' . $file_name);
		$ret[1] = $file_width;
		$ret[2] = $file_height;
		$ret[3] = "width=\"" . $file_width . "\"";
		$ret[4] = "height=\"" . $file_height . "\"";
		$ret[5] = $ret[3] . " " . $ret[4];
		$ret[6] = dirname(__FILE__) . "/../photos/" . $file_name;
		$ret[7] = "<img src=\"" . $ret[0] . "\" " . $ret[5] . " alt=\" ";
		if (empty($alt))
			$ret[7] .= htmlspecialchars($this->dbdata('photo_title'));
		else
			$ret[7] .= $alt;
		$ret[7] .= "\" />";

		self::clearCache();

		return $ret;
	}

	function getImg($w = 0, $h = 0, $opt = 0, $resize_up = true, $alt = "") {
		$data = $this->get($w, $h, $opt, $resize_up, $alt);
		return $data[7];
	}

/*
	function remove() {
		global $db;

		$q = $db->prepare("DELETE FROM phph_photos WHERE photo_id = ?");
		$db->execute($q, array($this->_pid));

		$q = $db->prepare("DELETE FROM phph_photos_categories WHERE photo_id = ?");
		$db->execute($q, array($this->_pid));

		$q = $db->prepare("DELETE FROM phph_comments WHERE photo_id = ?");
		$db->execute($q, array($this->_pid));

		$q = $db->prepare("SELECT file_id, file_name FROM phph_files WHERE photo_id = ?");
		$db->execute($q, array($this->_pid));
		$res = $db->execute($q, array($this->_pid));

		$q = $db->prepare("DELETE FROM phph_files WHERE file_id = ?");
		while ($row = $res->fetchRow()) {
			unlink(dirname(__FILE__) . "/../photos/" . $row['file_name']);
			$db->execute($q, array($row['file_id']));
		}
	}

	function update() {
		global $db;
		$this->_dbo->update();

		$q = $db->prepare("DELETE FROM phph_photos_categories WHERE photo_id = ?");
		$db->execute($q, array($this->_pid));

		$q = $db->prepare("INSERT INTO phph_photos_categories (photo_id, category_id) VALUES (?, ?)");
		foreach ($this->_cids as $cid) {
			if (!empty($cid))
				$db->execute($q, array($this->_pid, $cid));
		}
	}

	function addComment($title, $text) {
		$session = Session::singletone();

		$dbo = DB_DataObject::Factory("phph_comments");
		if (PEAR::isError($dbo)) {
			throw new Exception2(_INTERNAL_ERROR, $dbo->getMessage());
		}
		$dbo->comment_title = $title;
		$dbo->comment_text = $text;
		$dbo->photo_id = $this->_pid;
		$dbo->comment_date = time();
		$dbo->user_id = $session->_uid;
		$dbo->insert();

		if ($this->_dbo->user_id != $session->_uid) {
			$user = new User($session->_uid);

			$to_name = $this->_user->user_login;
			$photo_title = $this->_dbo->photo_title;
			$author = $user->_dbo->user_login;

			$body = <<<EOT
Witaj $to_name,

Uytkownik $author doda do Twojego zdj�ia "$photo_title" komentarz. Poniej znajduje si�tre�komentarza:

$title

$text

--
Ten email zosta wysany automatycznie. Prosimy nie odpowiada�
EOT;
			Utils::mail("Nowy komentarz do Twojego zdj�ia \"$photo_title\".", $body, $this->_user->user_email, $this->_user->user_name);
		}
	}

	function getComments() {
		global $db;

		$q = $db->prepare("SELECT comment_id FROM phph_comments WHERE photo_id = ? ORDER BY comment_date DESC");
		$res = $db->execute($q, array($this->_pid));

		$comments = array();

		while ($row = $res->fetchRow())
			$comments[] = new Comment($row['comment_id']);

		return $comments;

	}
*/
	function exif() {
		$file = $this->get();
		//return exif_read_data(, 0, true);
		return get_EXIF_JPEG($file[6]);
//		$exif_s = "";
//		foreach ($exif as $key => $section) {
//			foreach ($section as $name => $val) {
//				$exif_s .= htmlspecialchars($key) . "." . htmlspecialchars($name) . ": " . htmlspecialchars($val) . "<br />\n";
//			}
//		}
	}

	function ifdXML($ifd, $xml, $ifd_id, $name = 'ifd') {
		$el = $xml->createElement($name);
		$el->setAttribute('id', $ifd_id);

		foreach ($ifd as $id => $tag) {
			if (is_numeric($id)) {

				if ($tag['Decoded'] == true) {
					if ($tag['Type'] == "SubIFD") {
						foreach ($tag['Data'] as $subifd) {
							$el->appendChild($this->ifdXML($subifd, $xml, $id));
						}
					} else {
						$tag_el = $xml->createElement('tag', $tag['Text Value']);
						$tag_el->setAttribute('name', $tag['Tag Name']);
						$tag_el->setAttribute('type', $tag['Type']);
						$el->appendChild($tag_el);
					}
				}

/*

					// Check for SubIFD
						$extra_IFD_str .= "<h3 class=\"EXIF_Secondary_Heading\">" . $Exif_Tag['Tag Name'] . " contents</h3>";

                                // Cycle through each sub-IFD in the chain
                                foreach ( $Exif_Tag['Data'] as $subIFD )
                                {
                                        // Interpret this sub-IFD and add the html to the secondary output
                                        $extra_IFD_str .= interpret_IFD( $subIFD, $filename );
                                }
                        }
                                // Check if the tag is a makernote
                        else if ( $Exif_Tag['Type'] == "Maker Note" )
                        {
                                // This is a Makernote Tag
                                // Add a sub-heading for the Makernote
                                $extra_IFD_str .= "<h3 class=\"EXIF_Secondary_Heading\">Maker Note Contents</h3>";

                                // Interpret the Makernote and add the html to the secondary output
                                $extra_IFD_str .= Interpret_Makernote_to_HTML( $Exif_Tag, $filename );
                        }
                                // Check if this is a IPTC/NAA Record within the EXIF IFD
                        else if ( $Exif_Tag['Type'] == "IPTC" )
                        {
                                // This is a IPTC/NAA Record, interpret it and output to the secondary html
                                $extra_IFD_str .= "<h3 class=\"EXIF_Secondary_Heading\">Contains IPTC/NAA Embedded in EXIF</h3>";
                                $extra_IFD_str .=Interpret_IPTC_to_HTML( $Exif_Tag['Data'] );
                        }
                                // Change: Check for embedded XMP as of version 1.11
                                // Check if this is a XMP Record within the EXIF IFD
                        else if ( $Exif_Tag['Type'] == "XMP" )
                        {
                                // This is a XMP Record, interpret it and output to the secondary html
                                $extra_IFD_str .= "<h3 class=\"EXIF_Secondary_Heading\">Contains XMP Embedded in EXIF</h3>";
                                $extra_IFD_str .= Interpret_XMP_to_HTML( $Exif_Tag['Data'] );
                        }
                                // Change: Check for embedded IRB as of version 1.11
                                // Check if this is a Photoshop IRB Record within the EXIF IFD
                        else if ( $Exif_Tag['Type'] == "IRB" )
                        {
                                // This is a Photoshop IRB Record, interpret it and output to the secondary html
                                $extra_IFD_str .= "<h3 class=\"EXIF_Secondary_Heading\">Contains Photoshop IRB Embedded in EXIF</h3>";
                                $extra_IFD_str .= Interpret_IRB_to_HTML( $Exif_Tag['Data'], $filename );
                        }
                                // Check if the tag is Numeric
                        else if ( $Exif_Tag['Type'] == "Numeric" )
                        {
                                // Numeric Tag - Output text value as is.
                                $output_str .= "<tr class=\"EXIF_Table_Row\"><td class=\"EXIF_Caption_Cell\">" . $Exif_Tag['Tag Name'] . "</td><td class=\"EXIF_Value_Cell\">" . $Exif_Tag['Text Value'] . "</td></tr>\n";
                        }
                        else
                        {
                                // Other tag - Output text as preformatted
                                $output_str .= "<tr class=\"EXIF_Table_Row\"><td class=\"EXIF_Caption_Cell\">" . $Exif_Tag['Tag Name'] . "</td><td class=\"EXIF_Value_Cell\"><pre>" . trim( $Exif_Tag['Text Value']) . "</pre></td></tr>\n";
                        }

                }
                else
                {
                        // Tag has NOT been decoded successfully
                        // Hence it is either an unknown tag, or one which
                        // requires processing at the time of html construction

                        // Table cells won't get drawn with nothing in them -
                        // Ensure that at least a non breaking space exists in them

                        if ( trim($Exif_Tag['Text Value']) == "" )
                        {
                                $Exif_Tag['Text Value'] = "&nbsp;";
                        }

                        // Check if this tag is the first IFD Thumbnail
                        if ( ( $IFD_array['Tags Name'] == "TIFF" ) &&
                             ( $Tag_ID == 513 ) )
                        {
                                // This is the first IFD thumbnail - Add html to the output

                                // Change: as of version 1.11 - Changed to make thumbnail link portable across directories
                                // Build the path of the thumbnail script and its filename parameter to put in a url
                                $link_str = get_relative_path( dirname(__FILE__) . "/get_exif_thumb.php" , getcwd ( ) );
                                $link_str .= "?filename=";
                                $link_str .= get_relative_path( $filename, dirname(__FILE__) );

                                // Add thumbnail link to html
                                $output_str .= "<tr class=\"EXIF_Table_Row\"><td class=\"EXIF_Caption_Cell\">" . $Exif_Tag['Tag Name'] . "</td><td class=\"EXIF_Value_Cell\"><a class=\"EXIF_First_IFD_Thumb_Link\" href=\"$link_str\"><img class=\"EXIF_First_IFD_Thumb\" src=\"$link_str\"></a></td></tr>\n";
                        }
                                // Check if this is the Makernote
                        else if ( $Exif_Tag['Type'] == "Maker Note" )
                        {
                                // This is the makernote, but has not been decoded
                                // Add a message to the secondary output
                                $extra_IFD_str .= "<h3 class=\"EXIF_Secondary_Heading\">Makernote Coding Unknown</h3>\n";
                        }
                        else
                        {
                                // This is an Unknown Tag

                                // Check if the user wants to hide unknown tags
                                if ( $GLOBALS['HIDE_UNKNOWN_TAGS'] === FALSE )
                                {
                                        // User wants to display unknown tags

                                        // Check if the Data is an ascii string
                                        if ( $Exif_Tag['Data Type'] == 2 )
                                        {
                                                // This is a Ascii String field - add it preformatted to the output
                                                $output_str .= "<tr class=\"EXIF_Table_Row\"><td class=\"EXIF_Caption_Cell\">" . $Exif_Tag['Tag Name'] . "</td><td class=\"EXIF_Value_Cell\"><pre>" . trim( $Exif_Tag['Text Value'] ) . "</pre></td></tr>\n";
                                        }
                                        else
                                        {
                                                // Not an ASCII string - add it as is to the output
                                                $output_str .= "<tr class=\"EXIF_Table_Row\"><td class=\"EXIF_Caption_Cell\">" . $Exif_Tag['Tag Name'] . "</td><td class=\"EXIF_Value_Cell\">" . trim( $Exif_Tag['Text Value'] ) . "</td></tr>\n";
                                        }
                                }
                        }
                }
*/


			}
		}

		return $el;
	}

	function exifXML($xml, $name = 'meta') {
		$el = $xml->createElement($name);
		$el->setAttribute('type', 'exif');
		$exif = $this->exif();

		$i = 0;
		while (array_key_exists($i, $exif)) {
			$el->appendChild($this->ifdXML($exif[$i], $xml, $i));
			$i++;
		}
		return $el;
	}

	function metaXML($xml, $name = 'meta-data', $subname = 'meta') {
		$el = $xml->createElement($name);
		$el->appendChild($this->exifXML($xml, $subname));
		return $el;

/*		$meta = $this->_dom->createElement('meta');
		foreach ($exif[0] as $id => $tag) {
			if (is_numeric($id)) {
				$tag_el = $this->_dom->createElement('tag', $tag['Text Value']);
				$tag_el->setAttribute('name', $tag['Tag Name']);
				$meta->appendChild($tag_el);
//				$exif_el->appendChild($this->_dom->createElement('val', $val));
			}
		}
		//	}
//       echo "$key.$name: $val<br />\n";
		//	$exif->appendChild($exif_section);
   		//}
   		$this->_response->appendChild($meta);
*/

	}
}

?>
