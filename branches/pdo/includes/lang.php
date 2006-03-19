<?php
// $Id$

require_once("XML/Parser.php");
require_once("includes/config.php");
require_once("includes/session.php");

class XML_LangFile extends XML_Parser {
	var $_langs = array();
	private $_level = 0;
	private $_lang = array();
	private $_cdata = "";

	function startHandler($xp, $name, $attribs) {
		$this->_cdata = "";
		if ($name == "LANG") {
			$this->_lang = array();
			$this->_lang['id'] = $attribs['ID'];
			$this->_lang['name'] = $attribs['NAME'];
			$this->_lang['file'] = $attribs['FILE'];
		}
	}

	function endHandler($xp, $name) {
		if ($name == "LANG") {
			$this->_langs[$this->_lang['id']] = $this->_lang;
			$this->_lang = array();
		}
	}

	function cdataHandler($xp, $cdata) {
	}
}

class Language {

	static function getDefaultLanguage() {
		return Config::get("default_language", "en");
	}
	
	static function getDefaultAdminLanguage() {
		return Config::get("default_admin_language", "en");
	}
	
	static function getCurrentLanguage() {
/*		$session = Session::singletone();
		$user = $session->getUser();
		if ($user != null) {
			if (!empty($user->user_language))
				return $user->user_language;
		}
return Language::getDefaultLanguage();*/
		return "pl";
	}

	static function getCurrentAdminLanguage() {
/*		$session = Session::singletone();
		$user = $session->getUser();
		if ($user != null) {
			if (!empty($user->user_admin_language))
				return $user->user_admin_language;
		}
return Language::getDefaultAdminLanguage();*/
		return "pl";
	}

	static function setDefaultLanguage($lang) {
		return Config::set("default_language", $lang);
	}
	
	static function setDefaultAdminLanguage($lang) {
		return Config::set("default_admin_language", $lang);
	}
	
	static function getAvailableLanguages() {
		$xml = new XML_LangFile();
		$xml->setInputFile(dirname(__FILE__) . "/../language/lang.xml");
		$xml->parse();
		return $xml->_langs;
	}

	static function getAvailableAdminLanguages() {
		$xml = new XML_LangFile();
		$xml->setInputFile(dirname(__FILE__) . "/../language/admin/lang.xml");
		$xml->parse();
		return $xml->_langs;
	}

	static function getLanguageFile($lang) {
		$def_lang = Language::getDefaultLanguage();

		$xml = new XML_LangFile();
		$xml->setInputFile(dirname(__FILE__) . "/../language/lang.xml");
		$xml->parse();
		
		if (!empty($xml->_langs["$lang"]) && !empty($xml->_langs["$lang"]['file']))
			return $xml->_langs["$lang"]['file'];
		if ($lang != $def_lang)
			return Language::getLanguageFile($def_lang);
		return null;
	}

	static function getAdminLanguageFile($lang) {
		$def_lang = Language::getDefaultAdminLanguage();

		$xml = new XML_LangFile();
		$xml->setInputFile(dirname(__FILE__) . "/../language/admin/lang.xml");
		$xml->parse();
		
		if (!empty($xml->_langs["$lang"]) && !empty($xml->_langs["$lang"]['file']))
			return $xml->_langs["$lang"]['file'];
		if ($lang != $def_lang)
			return Language::getLanguageAdminFile($def_lang);
		return null;
	}

	static function getCurrentLanguageFile() {
		return Language::getLanguageFile(Language::getCurrentLanguage());
	}

	static function getCurrentAdminLanguageFile() {
		return Language::getAdminLanguageFile(Language::getCurrentAdminLanguage());
	}
}

$lang = Language::getCurrentLanguageFile();
require_once("language/$lang");

$lang = Language::getCurrentAdminLanguageFile();
require_once("language/admin/$lang");

?>
