<?php
// $Id$

require_once("Mail.php");
require_once("Mail/mime.php");

class Exception2 extends Exception {

	private $_desc;

	function __construct($msg, $desc, $code = 0) {
		parent::__construct($msg, $code);
		$this->_desc = $desc;
	}

	final function getDescription() {
		return $this->_desc;
	}
};

class Utils {

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
	
	static function mail($subject, $txt_body, $to_email, $to_name = "", $html_body = "", $text_charset = "iso-8859-2", $html_charset = "iso-8859-2", $head_charset = "iso-8859-2") {

		$mime = new Mail_mime("\n");

		$mail = Mail::factory("mail");

		if (!empty($to_name))
			$to = "$to_name <$to_email>";
		else
			$to = $to_email;
		$headers = array(
			"From" => Config::get("email_user") . " <" . Config::get("email_from") . ">",
			"Subject" => $subject,
			"Reply-To" => Config::get("email_from"),
			"Return-Path" => Config::get("email_from")
		);

		$mime->setTXTBody(Utils::linewrap($txt_body));
		if (!empty($html_body))
			$mime->setHTMLBody($html_body);

		$body = $mime->get(array('text_charset' => $text_charset, 'html_charset' => $html_charset, 'head_charset' => $head_charset));
		$headers = $mime->headers($headers);
	
		$rcpt = $to;
		$mail->send($rcpt, $headers, $body);
	}
}

?>
