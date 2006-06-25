<?php
// $Id$

require_once("Mail.php");
require_once("Mail/mime.php");
require_once("includes/Config.php");
require_once("includes/Session.php");

class Utils {

	static function negotiateContentType($charset = "utf-8") {
		$xhtml = false;
		if (preg_match('/application\/xhtml\+xml(?![+a-z])(;q=(0\.\d{1,3}|[01]))?/i', $_SERVER['HTTP_ACCEPT'], $matches)) {
			$xhtmlQ = isset($matches[2])?($matches[2]+0.2):1;
			if (preg_match('/text\/html(;q=(0\d{1,3}|[01]))s?/i', $_SERVER['HTTP_ACCEPT'], $matches)) {
				$htmlQ = isset($matches[2]) ? $matches[2] : 1;
				$xhtml = ($xhtmlQ >= $htmlQ);
			} else {
				$xhtml = true;
			}
		}
		if ($xhtml) {
			header('Content-Type: application/xhtml+xml; charset=' . $charset);
			return "application/xhtml+xml";
		} else {
			header('Content-Type: text/html; charset=' . $charset);
			return "text/html";
		}
	}

	static function gp($var, $def = null) {
		if (isset($_GET[$var]))
			return urldecode($_GET[$var]);
		elseif (isset($_POST[$var]))
			return $_POST[$var];
		else
			return $def;
	}

	static function pg($var, $def = null) {
		if (isset($_POST[$var]))
			return $_POST[$var];
		elseif (isset($_GET[$var]))
			return urldecode($_GET[$var]);
		else
			return $def;
	}

	static function p($var, $def = null) {
		if (isset($_POST[$var]))
			return $_POST[$var];
		else
			return $def;
	}

	static function g($var, $def = null) {
		if (isset($_GET[$var]))
			return urldecode($_GET[$var]);
		else
			return $def;
	}

	static function selfURL() {
		return "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	}

	static function url($action, $attrs = array(), $script = "index.php") {

		$s = Config::get("site_url") . "/$script?action=$action";
		$s = Session::singletone()->addSID($s);
		//$ref = self::pg("ref");
		//if (!empty($ref))
		//	$s .= "&amp;ref=" . htmlspecialchars(urlencode($ref));

		foreach ($attrs as $id => $val) {
			$s .= htmlspecialchars("&$id=" . urlencode($val));
		}

		return $s;
	}

	static function secureHeaderData($data) {
		$data = str_replace("\n", "", $data);
		$data = str_replace("\r", "", $data);
		return $data;
	}

	static function encodeIP($dotquad_ip) {
		$ip_sep = explode('.', $dotquad_ip);
		return sprintf('%02x%02x%02x%02x', $ip_sep[0], $ip_sep[1], $ip_sep[2], $ip_sep[3]);
	}

	static function decodeIP($int_ip) {
		if (empty($int_ip))
			return "";
		$hexipbang = explode('.', chunk_split($int_ip, 2, '.'));
		return hexdec($hexipbang[0]). '.' . hexdec($hexipbang[1]) . '.' . hexdec($hexipbang[2]) . '.' . hexdec($hexipbang[3]);
	}

	static function getClientIP() {
		return (!empty($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : ((!empty($_ENV['REMOTE_ADDR'])) ? $_ENV['REMOTE_ADDR'] : "");
	}

	static function getEncodedClientIP() {
		return Utils::encodeIP(Utils::getClientIP());
	}

	static function formatTime($time, $format = "%Y-%m-%d %T") {
		return strftime($format, $time);
	}

	static function formatDate($time, $format = "%Y-%m-%d") {
		return Utils::formatTime($time, $format);
	}

	function linewrap($string, $width = 70, $break = "\n", $cut = false) {
		 $array = explode("\n", $string);
		 $string = "";
		 foreach($array as $val) {
			 $string .= wordwrap($val, $width, $break, $cut);
			 $string .= "\n";
		 }
		 return $string;
	}

	function mb_mime_header($string, $encoding = null, $linefeed="\r\n") {
		if(!$encoding) $encoding = mb_internal_encoding();
		$encoded = '';

		while($length = mb_strlen($string)) {
			$encoded .= "=?$encoding?B?"
				. base64_encode(mb_substr($string,0,24,$encoding))
				. "?=$linefeed";

			$string = mb_substr($string,24,$length,$encoding);
		}

		return $encoded;
	}

	static function mail($subject, $txt_body, $to_email, $to_name = "", $html_body = "", $text_charset = "UTF-8", $html_charset = "UTF-8", $head_charset = "UTF-8", $text_encoding = "8bit", $html_encoding = "quoted-printable") {

		$mime = new Mail_mime("\n");

		$mail = Mail::factory("mail");

		if (!empty($to_name))
			$to = "$to_name <$to_email>";
		else
			$to = $to_email;

		//$subject = self::mb_mime_header($subject, $head_charset);
		$headers = array(
			"From" => Config::get("email_user") . " <" . Config::get("email_from") . ">",
			"Reply-To" => Config::get("email_from"),
			"Return-Path" => Config::get("email_from"),
		);

		$txt_body .= "\n\n-- \nEmail wysany automatycznie. Prosimy nie odpowiadać\n";
		$mime->setTXTBody(Utils::linewrap($txt_body));
		$mime->setSubject($subject);
		if (!empty($html_body))
			$mime->setHTMLBody($html_body);

		$body = $mime->get(array(
			'text_charset' => $text_charset,
			'html_charset' => $html_charset,
			'head_charset' => $head_charset,
			'text_encoding' => $text_encoding,
			'html_encoding' => $html_encoding
		));
		$headers = $mime->headers($headers);

		$rcpt = $to;
		$mail->send($rcpt, $headers, $body);
	}
}

?>
