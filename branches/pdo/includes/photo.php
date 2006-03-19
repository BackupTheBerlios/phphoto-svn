<?php
// $Id$

require_once("DB/DataObject.php");
require_once("includes/db.php");
require_once("includes/config.php");
require_once("includes/utils.php");
require_once("includes/category.php");
require_once("includes/comment.php");

define('PHOTO_NOT_FOUND', 1);
define('PHOTO_USER_NOT_FOUND', 2);

class Photo {
	
	var $_pid = 0;
	var $_dbo;
	var $_user;
	var $_categories;
	var $_cids;
	var $_cid_map;
	var $_category_objs;
	

	function __construct($pid = 0) {
		$this->_pid = $pid;
		$this->reload();
	}
	
	function reload() {
		global $db;
		$pid = $this->_pid;
		if ($this->_pid != 0) {
			$this->_dbo = DB_DataObject::Factory("phph_photos");
			if (PEAR::isError($this->_dbo))
				throw new Exception2("B³±d wewnêtrzny", $this->_dbo->getMessage());
			$r = $this->_dbo->get($pid);
			if (PEAR::isError($r))
				throw new Exception2("B³±d wewnêtrzny", $r->getMessage());
			if ($r == 0)
				throw new Exception2("B³±d", "Zdjêcie nie istnieje", PHOTO_NOT_FOUND);

			$this->_user = DB_DataObject::Factory("phph_users");
			if (PEAR::isError($this->_user))
				throw new Exception2("B³±d wewnêtrzny", $this->_user->getMessage());
			$r = $this->_user->get($this->_dbo->user_id);
			if (PEAR::isError($r))
				throw new Exception2("B³±d wewnêtrzny", $r->getMessage());
			if ($r == 0)
				throw new Exception2("B³±d spójno¶ci danych", "U¿ytkownik do którego nalezy zdjêcie nie istnieje.<br />Skontakuj siê z administratorem, podaj±c numer zdjêcia ($pid).", PHOTO_USER_NOT_FOUND);

			$q = $db->prepare("SELECT pc.category_id, c.category_name " .
					  "FROM phph_photos_categories pc " .
					  "INNER JOIN phph_categories c ON pc.category_id = c.category_id " .
					  "WHERE pc.photo_id = ?");
			$res = $db->execute($q, array($this->_pid));

			$this->_categories = array();
			$this->_cids = array();
			$this->_cid_map = array();
			$this->_category_objs = array();
			while ($row = $res->fetchRow()) {
				$this->_categories[] = $row['category_name'];
				$this->_cids[] = $row['category_id'];
				$this->_cid_map[$row['category_id']] = $row['category_name'];
				$this->_category_objs[] = new Category($row['category_id']);
			}
		}
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
		global $db;
		$lft = time() - (Config::get("cache_lifetime", 7) * 3600 * 24);
		$q = $db->prepare("SELECT file_id, file_name FROM phph_files WHERE file_original = 0 AND file_accessed < ?");
		$res = $db->execute($q, array($lft));
		$q = $db->prepare("DELETE FROM phph_files WHERE file_id = ?");
		while ($row = $res->fetchRow()) {
			unlink(dirname(__FILE__) . "/../photos/" . $row['file_name']);
			$db->execute($q, array($row['file_id']));
		}
	}

	function upload($file, $title, $description, $cid) {
		global $db;

		if ($file["error"] == UPLOAD_ERR_INI_SIZE || $file["error"] == UPLOAD_ERR_FORM_SIZE || $file["size"] > Config::get("max_file_size", 200) * 1024) {
			throw new Exception2(_ERROR_UPLOADING_FILE, _UPLOADED_FILE_TOO_LARGE);
		}

		if (empty($file['name'])) {
			throw new Exception2(_ERROR_UPLOADING_FILE, _NO_FILE_SELECTED);
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
			throw new Exception2(_ERROR_UPLOADING_FILE, _BAD_FILE_TYPE);
		}


		if ($ok == true) {
			$session = Session::singletone();
			$tempname = $file['tmp_name'];

			$imgs = getimagesize($tempname);
			if ($imgs['mime'] != "image/jpeg" && $imgs['mime'] != "image/pjpeg" && $imgs['mime'] != "image/jpg") {
				throw new Exception2(_ERROR_UPLOADING_FILE, _BAD_FILE_TYPE);
			}
			if ($imgs[0] > Config::get("max_width", 640) || $imgs[1] > Config::get("max_height", 600)) {
				throw new Exception2(_ERROR_UPLOADING_FILE, _UPLOADED_FILE_TOO_LARGE);
			}

			$name_a = $this->genName($imgs[0], $imgs[1], $session->_uid);
			if (!file_exists($name_a[3]))
				mkdir($name_a[3], 0755, true);
			$uploadpath = $name_a[4];
			$name = $name_a[2];

			if (is_uploaded_file($tempname)) {  
				while (move_uploaded_file($tempname, $uploadpath));
			}


			$dbo = DB_DataObject::Factory("phph_photos");
			if (PEAR::isError($dbo)) {
				unlink($uploadpath);
				throw new Exception2(_INTERNAL_ERROR, $dbo->getMessage());
			}
			$dbo->user_id = $session->_uid;
			$dbo->photo_title = $title;
			$dbo->photo_description = $description;
			$dbo->photo_width = $imgs[0];
			$dbo->photo_height = $imgs[1];
			$dbo->photo_added = time();
			$this->_pid = $id = $this->_pid = $dbo->insert();
			if (PEAR::isError($id)) {
				unlink($uploadpath);
				throw new Exception2(_INTERNAL_ERROR, $id->getMessage());
			}


			$q = $db->prepare("INSERT INTO phph_photos_categories (photo_id, category_id) VALUES (?, ?)");
			foreach ($cid as $c) {
				$db->execute($q, array($id, $c));
			}

			$dbo = DB_DataObject::Factory("phph_files");
			if (PEAR::isError($dbo)) {
				throw new Exception2(_INTERNAL_ERROR, $dbo->getMessage());
			}
			$dbo->photo_id = $id;
			$dbo->file_name = $name;
			$dbo->file_created = time();
			$dbo->file_accessed = time();
			$dbo->file_original = true;
			$dbo->file_width = $imgs[0];
			$dbo->file_height = $imgs[1];
			$dbo->insert();

			$this->reload();

			if (Config::get("auto_approve", false))
				$this->approve();
		}
	}

	function approve() {
		$session = Session::singletone();
		$this->_dbo->photo_approved = time();
		$this->_dbo->photo_approved_by = $session->_uid;
		$this->_dbo->update();

		$subs = array();
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

W nastêpuj±cych kategoriach:
 - $cats
pojawi³o siê nowe zdjêcie:
Tytu³: $title
Autor: $author
$link

-- 
Ten email zosta³ wys³any automatycznie.
Prosimy nie odpowiadaæ.
EOT;
				Utils::mail("Nowe zdjêcie w obserwowanych kategoriach.", $txt_body, $user['user_email'], $user['user_name']);
			}
		}

		if ($session->_uid != $this->_dbo->user_id) {
			$name = $this->_user->user_login;
			$title = $this->_dbo->photo_title;
			$moderator = $session->_user->user_login;
			$txt_body = <<<EOT
Witaj $name,

Twoje zdjêcie "$title" zosta³o w³a¶nie zaakceptowane przez moderatora ($moderator).

-- 
Ten email zosta³ wys³any automatycznie.
Prosimy nie odpowiadaæ.
EOT;
			Utils::mail("Twoje zdjêcie zosta³o zaakceptowane.", $txt_body, $this->_user->user_email, $this->_user->user_name);
		}
	
	}

	function get($w = 0, $h = 0, $resize_up = true, $alt = "") {
		global $db;

		$dbo = DB_DataObject::Factory("phph_files");
		if (PEAR::isError($dbo))
			die($dbo->getMessage());

		if ($w == 0 || $h == 0 || ($w <= $this->_dbo->photo_width && $h <= $this->_dbo->photo_height && !$resize_up)) {
			$dbo->file_original = true;
			$dbo->photo_id = $this->_pid;
			$dbo->keys("file_original", "photo_id");
			$r = $dbo->find();
			if (PEAR::isError($r))
				die($r->getMessage());

			if ($r == 0)
				return false;

			$dbo->fetch();
		} else {
			$f_w = $w / $this->_dbo->photo_width;
			$f_h = $h / $this->_dbo->photo_height;
			$f = min($f_w, $f_h);
			$nw = round($this->_dbo->photo_width * $f);
			$nh = round($this->_dbo->photo_height * $f);
			
			$dbo->file_width = $nw;
			$dbo->file_height = $nh;
			$dbo->photo_id = $this->_pid;
			$dbo->keys("file_width", "file_height", "photo_id");
			$r = $dbo->find();
			if (PEAR::isError($r))
				die($r->getMessage());

			if ($r != 0) {
				$dbo->fetch();
			} else {
				$name_a = $this->genName($nw, $nh, $this->_dbo->user_id);
				$name = $name_a[2];
				if (!file_exists($name_a[3]))
					mkdir($name_a[3], 0755, true);
		
				$dbo = DB_DataObject::Factory("phph_files");
				if (PEAR::isError($dbo))
					die($dbo->getMessage());
				$dbo->file_original = true;
				$dbo->photo_id = $this->_pid;
				$dbo->keys("file_original", "photo_id");
				$r = $dbo->find();
				if (PEAR::isError($r))
					die($r->getMessage());

				if ($r == 0)
					return false;

				$dbo->fetch();

				$original = @imagecreatefromjpeg(dirname(__FILE__) . "/../photos/" . $dbo->file_name);
				$sized = imagecreatetruecolor($nw, $nh);
				imagecopyresampled($sized, $original, 0, 0, 0, 0, $nw, $nh, $this->_dbo->photo_width, $this->_dbo->photo_height);
				imagejpeg($sized, dirname(__FILE__) . "/../photos/" . $name, 100);

				$dbo = DB_DataObject::Factory("phph_files");
				if (PEAR::isError($dbo))
					die($dbo->getMessage());

				$dbo->file_name = $name;
				$dbo->file_width = $nw;
				$dbo->file_height = $nh;
				$dbo->file_created = time();
				$dbo->file_accessed = time();
				$dbo->photo_id = $this->_pid;
				$id = $dbo->insert();
				$dbo->file_id = $id;
			}
		}

		$q = $db->prepare("UPDATE phph_files SET file_accessed = ? WHERE file_id = ?");
		$db->execute($q, array(time(), $dbo->file_id));

		$ret[0] = Config::get("site_url") . "/photos/" . $dbo->file_name;
		$ret[1] = $dbo->file_width;
		$ret[2] = $dbo->file_height;
		$ret[3] = "width=\"" . $dbo->file_width . "\"";
		$ret[4] = "height=\"" . $dbo->file_height . "\"";
		$ret[5] = $ret[3] . " " . $ret[4];
		$ret[6] = dirname(__FILE__) . "/../photos/" . $dbo->file_name;
		$ret[7] = "<img src=\"" . $ret[0] . "\" " . $ret[5] . " alt=\" ";
		if (empty($alt))
			$ret[7] .= htmlspecialchars($this->_dbo->photo_title);
		else
			$ret[7] .= $alt;
		$ret[7] .= "\" />";

		Photo::clearCache();

		return $ret;
	}

	function getImg($w = 0, $h = 0, $resize_up = true, $alt = "") {
		$data = $this->get($w, $h, $resize_up, $alt);
		return $data[7];
	}

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

U¿ytkownik $author doda³ do Twojego zdjêcia "$photo_title" komentarz. Poni¿ej znajduje siê tre¶æ komentarza:

$title

$text

-- 
Ten email zosta³ wys³any automatycznie. Prosimy nie odpowiadaæ.
EOT;
			Utils::mail("Nowy komentarz do Twojego zdjêcia \"$photo_title\".", $body, $this->_user->user_email, $this->_user->user_name);
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

	function exif() {
//		$file = $this->get();
///		$exif = exif_read_data($file[6], '', true, false);
//		$exif_s = "";
//		foreach ($exif as $key => $section) {
//			foreach ($section as $name => $val) {
//				$exif_s .= htmlspecialchars($key) . "." . htmlspecialchars($name) . ": " . htmlspecialchars($val) . "<br />\n";
//			}
//		}
		return "";
	}
}

?>
