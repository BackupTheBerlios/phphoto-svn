<?php
// $Id$

require_once("includes/config.php");

class HTML {
	static function startHTML($frames = false) {
		ob_start();

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
			header('Content-Type: application/xhtml+xml; charset=iso-8859-2');
		} else {
			header('Content-Type: text/html; charset=iso-8859-2');
		}
		echo '<?xml version="1.0" encoding="iso-8859-2"?>' . "\n";
		if ($frames)
			echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">' . "\n";
		else
			echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' . "\n";
		echo '<html xmlns="http://www.w3.org/1999/xhtml">' . "\n";
	}

	static function endHTML() {
		echo "</html>";
	}

	static function startHEAD($title = "", $description = "", $keywords = "", $css = "") {
		$c_title = Config::getStatic("site_title");
		$url = Config::getStatic("site_url");
		if (!empty($title))
			$c_title .= htmlspecialchars(" :: $title");
		$h_css = "$url/css/style.css";
		if (!empty($css))
			$h_css = $css;
?>
		<head>
			<title><?=$c_title?></title>
			<meta http-equiv="Pragma"  content="no-cache" />
			<meta http-equiv="Cache-Control" content="no-cache" />
			<meta name="description" content="<?=$description?>" />
			<meta name="keywords" content="<?=$keywords?>" />
			<meta name="Generator" content="vim" />
			<meta name="robots" content="index, follow" />
			<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-2" />
			<link href="<?=$h_css?>" rel="stylesheet" type="text/css"/>
			<script type="text/javascript" src='<?=$url?>/js/global.js'></script>
<?php
			echo '<script type="text/javascript" src="' . $url . '/js/overlib/overlib.js"></script>';
			echo '<script type="text/javascript" src="' . $url . '/js/overlib/overlib_crossframe.js"></script>';
	}

	static function endHEAD() {
		echo "</head>";
	}

	static function head($title = "", $description = "", $keywords = "", $css = "", $addhead = "") {
		HTML::startHEAD($title, $description, $keywords, $css);
		echo $addhead;
		HTML::endHEAD();
	}
	
	static function startBODY($s_class = "") {
		$url = Config::getStatic("site_url");
		$h_class = "";
		if (!empty($s_class))
			$h_class = "class=\"$s_class\"";

		echo "<body $h_class>";
		echo '<div id="overDiv" style="position: absolute; visibility: hidden; z-index: 1000;"></div>';
	}

	static function endBODY() {
		echo "</body>";
	}

	static function img($src, $alt, $w = -1, $h = -1, $attrs = "border='0'") {
		echo HTML::getImg($src, $alt, $w, $h, $attrs);
	}
	
	static function getImg($src, $alt, $w = -1, $h = -1, $attrs = "border='0'") {
		$url = Config::getStatic("site_url");
		$url .= "/images/$src";
		$out = "<img src='$url' alt='$alt' ";
		if ($w != -1)
			$out .= "width='$w' ";
		if ($h != -1)
			$out .= "height='$h' ";
		if (!empty($attrs))
			$out .= "$attrs ";
		$out .= "/>";
		return $out;
	}

	static function addRef($url, $param = "ref") {
		$link = "&amp;";
		if (!strstr($url, "?"))
			$link = "?";
		return $url . $link . $param . "=" . urlencode("http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
	}
};

class HTML_Pane {

	var $_id;
	var $_title;
	var $_c_pane = "a_pane";
	var $_c_hdr = "a_pane_hdr";

	function HTML_Pane($id, $title, $c_pane = "a_pane", $c_hdr = "a_pane_hdr") {
		$this->_id = $id;
		$this->_title = $title;
		$this->_c_pane = $c_pane;
		$this->_c_hdr = $c_hdr;
	}

	function startPane() {
?>
		<div class="<?=$this->_c_pane?>" id="<?=$this->_id?>">
<?php		if (!empty($this->_title)) { ?>
			<div class="<?=$this->_c_hdr?>"><?=$this->_title?></div>

<?php
		}
	}

	function endPane() {
		echo "</div>";
	}
	
	function renderContent() {
	}

	function show() {
		$this->startPane();
		$this->renderContent();
		$this->endPane();
	}
}

class HTML_Block {

	var $_expanded = false;
	var $_panes = array();
	var $_id = "";
	var $_title = "";
	var $_c_pane = "a_pane";
	var $_c_block = "a_block";
	var $_c_block_hdr = "a_block_hdr";
	var $_c_block_content = "a_block_content";

	function HTML_Block($id, $title, $c_pane = "a_pane", $c_block = "a_block", $c_block_hdr = "a_block_hdr", $c_block_content = "a_block_content", $exp = false) {
		$this->_expanded = $exp;
		$this->_id = $id;
		$this->_title = $title;
		$this->_c_pane = $c_pane;
		$this->_c_block = $c_block;
		$this->_c_block_hdr = $c_block_hdr;
		$this->_c_block_content = $c_block_content;
	}
	
	function addPane($pane) {
		$this->_panes[] = $pane;
	}

	function startBlock() {
?>
			<div class="<?=$this->_c_pane?>">
			<div class="<?=$this->_c_block?>">

			<div class="<?=$this->_c_block_hdr?>_on" id="b_<?=$this->_id?>_hdr_on" <?php if (!$this->_expanded) echo 'style="display: none;"';?>>
			<a href='#' onclick='block_toggle("<?=$this->_id?>"); return false;'><?php HTML::img("minus.gif", "");?></a>
			<a href='#' onclick='block_toggle("<?=$this->_id?>"); return false;'><?=$this->_title?></a>
			</div>

			<div class="<?=$this->_c_block_hdr?>_off" id="b_<?=$this->_id?>_hdr_off" <?php if ($this->_expanded) echo 'style="display: none;"';?>>
			<a href='#' onclick='block_toggle("<?=$this->_id?>"); return false;'><?php HTML::img("plus.gif", "");?></a>
			<a href='#' onclick='block_toggle("<?=$this->_id?>"); return false;'><?=$this->_title?></a>
			</div>

			<div class='<?=$this->_c_block_content?>' id='b_<?=$this->_id?>_content' <?php if (!$this->_expanded) echo 'style="display: none;"';?>>
<?php
	}

	function endBlock() {
		echo "</div></div></div>";
	}

	function renderPanes() {
		foreach ($this->_panes as $pane) {
			$pane->show();
		}
	}
	
	function renderContent() {
		$this->renderPanes();
	}

	function show() {
		$this->startBlock();
		$this->renderContent();
		$this->endBlock();
	}
		
}

class HTML_MenuCategory extends HTML_Block {

	private $_category = array();

	function HTML_MenuCategory($category, $id, $c_pane = "a_pane", $c_block = "a_block", $c_block_hdr = "a_block_hdr", $c_block_content = "a_block_content", $exp = false) {
		$this->HTML_Block($id, $category['name'], $c_pane, $c_block, $c_block_hdr, $c_block_content, $exp);
		$this->_category = $category;
	}

	function renderContent() {

		foreach ($this->_category['items'] as $i_id => $item) {
?>
			<div class="a_menu_item_off" id="m_itm_<?=$this->_id?>_<?=$i_id?>" onmouseout="change_class('m_itm_<?=$this->_id?>_<?=$i_id?>', 'a_menu_item_off');" onmouseover="change_class('m_itm_<?=$this->_id?>_<?=$i_id?>', 'a_menu_item_on');">
			&nbsp;<?php HTML::img("item.gif", "");?>
			&nbsp;<a href="<?=$item['href']?>" target="main"><?=$item['name']?></a>
			</div>
<?php
		}
	}

}

class HTML_Menu {

	var $_data = array();
	var $_c_pane = "a_menu_pane";

	function HTML_Menu($data, $c_pane = "a_menu_pane") {
		$this->_data = $data;
		$this->_c_pane = $c_pane;
	}

	function show() {
		$pane_class = $this->_c_pane;

		if (empty($pane_class))
			$pane_class = "a_menu_pane";

		foreach ($this->_data as $c_id => $category) {
			$mc = new HTML_MenuCategory($category, "mc$c_id", $pane_class);
			$mc->show();
		}
	}
};

class HTML_MessagePane extends HTML_Pane {
	var $_message = "";
	var $_c_message = "a_message";

	function HTML_MessagePane($id, $title, $message = "", $c_pane = "a_pane", $c_hdr = "a_pane_hdr", $c_message = "a_message") {
		$this->HTML_Pane($id, $title, $c_pane, $c_hdr);
		$this->_message = $message;
		$this->_c_message = $c_message;
	}

	function renderContent() {
		if (!empty($this->_message))
			echo "<div class='$this->_c_message'>$this->_message</div>";
	}
}

class HTML_AdminFormField {

	var $_id;
	var $_title;
	var $_description = "";
	var $_c_table = "a_form_field";
	var $_c_left = "a_form_field_left";
	var $_c_right = "a_form_field_right";
	var $_c_title = "a_form_field_title";
	var $_c_description = "a_form_field_description";
	var $_left_w = "47%";
	var $_right_w = "53%";
	
	function HTML_AdminFormField($id, $title, $description, $c_left = "a_form_field_left", $c_right = "a_form_field_right", $c_table = "a_form_field") {
		$this->_id = $id;
		$this->_title = $title;
		$this->_description = $description;
		$this->_c_left = $c_left;
		$this->_c_right = $c_right;
		$this->_c_table = $c_table;
	}

	function renderLeft() {
?>
		<div class="<?=$this->_c_title?>"><?=$this->_title?></div>
		<div class="<?=$this->_c_description?>"><?=$this->_description?></div>
<?php
		
	}

	function renderRight() {
	}

	function renderContent() {
?>
		<div style="width: 100%">
		<table border="0" class="<?=$this->_c_table?>" cellspacing="0" cellpadding="5">
		<tr>
		<td class="<?=$this->_c_left?>" width="<?=$this->_left_w?>"><?php $this->renderLeft();?></td>
		<td class="<?=$this->_c_right?>" width="<?=$this->_right_w?>"><?php $this->renderRight();?></td>
		</tr>
		</table>
		</div>
<?php
	}
	
	function show() {
		$this->renderContent();
	}
}

class HTML_TextField extends HTML_AdminFormField {

	var $_size;
	var $_value;
	var $_pass = false;
	var $_maxlen;
	var $_c_text = "text_input";
	var $_name = "";

	function HTML_TextField($id, $title, $description, $size = "", $value = "", $pass = false, $maxlen = 0, $name = "") {
		$this->HTML_AdminFormField($id, $title, $description);
		$this->_size = $size;
		$this->_value = $value;
		$this->_pass = $pass;
		$this->_maxlen = $maxlen;
		if (!empty($name))
			$this->_name = $name;
		else
			$this->_name = $id;
	}

	function renderRight() {
		$addattr = "";
		if (!empty($this->_size))
			$addattr .= "size=\"" . $this->_size . "\" ";
		if (!empty($this->_maxlen))
			$addattr .= "maxlength=\"" . $this->_maxlen . "\" ";
?>
		<input id="<?=$this->_id?>" class="<?=$this->_c_text?>_off" type="<?php if ($this->_pass) echo 'password'; else echo 'text';?>" value="<?=htmlspecialchars($this->_value)?>" name="<?=$this->_name?>" onfocus="change_class('<?=$this->_id?>', '<?=$this->_c_text?>_on');" onblur="change_class('<?=$this->_id?>', '<?=$this->_c_text?>_off');" <?=$addattr?>/>
<?php
	}
}

class HTML_StaticField extends HTML_AdminFormField {

	var $_value;
	var $_form_value;
	var $_c_static = "";
	var $_name = "";

	function HTML_StaticField($id, $title, $description, $value = "") {
		$this->HTML_AdminFormField($id, $title, $description);
		$this->_value = $value;
		if (!empty($name))
			$this->_name = $name;
		else
			$this->_name = $id;
	}

	function renderRight() {
		$style = "";
		if (!empty($this->_c_static))
			$style = "class=\"" . $this->_c_static . "\"";
		echo "<span $style>" . htmlspecialchars($this->_value) . "</span>";
	}
}

class HTML_RadioGroup extends HTML_AdminFormField {

	var $_size;
	var $_value;
	var $_c_radio = "radio_input";
	var $_name = "";
	var $_options = array();

	function HTML_RadioGroup($id, $title, $description, $value = "", $name = "") {
		$this->HTML_AdminFormField($id, $title, $description);
		$this->_value = $value;
		if (!empty($name))
			$this->_name = $name;
		else
			$this->_name = $id;
	}

	function addOption($value, $title, $c_radio = "radio_input") {
		$this->_options[] = array($value, $title, $c_radio);
	}

	function renderRight() {

		foreach ($this->_options AS $option) {
			$addattr = "";
			if ($this->_value == $option[0])
				$addattr = "checked=\"checked\"";
?>
<input id="<?=$this->_id?>" class="<?=$option[2]?>_off" type="radio" value="<?=$option[0]?>" name="<?=$this->_name?>" onfocus="change_class('<?=$this->_id?>', '<?=$option[2]?>_on');" onblur="change_class('<?=$this->_id?>', '<?=$option[2]?>_off');" <?=$addattr?> /> <?=$option[1]?><br />
<?php
		}
	}
}


class HTML_MemoField extends HTML_AdminFormField {

	var $_rows;
	var $_cols;
	var $_value;
	var $_c_text = "text_input";
	var $_name = "";

	function HTML_MemoField($id, $title, $description, $value = "", $rows = 20, $cols = 40, $name = "") {
		$this->HTML_AdminFormField($id, $title, $description);
		$this->_rows = $rows;
		$this->_cols = $cols;
		$this->_value = $value;
		if (!empty($name))
			$this->_name = $name;
		else
			$this->_name = $id;
	}

	function renderRight() {
		$addattr = "";
		if (!empty($this->_rows))
			$addattr .= "rows=\"" . $this->_rows . "\" ";
		if (!empty($this->_cols))
			$addattr .= "cols=\"" . $this->_cols . "\" ";
?>
		<textarea id="<?=$this->_id?>" class="<?=$this->_c_text?>_off" name="<?=$this->_name?>" onfocus="change_class('<?=$this->_id?>', '<?=$this->_c_text?>_on');" onblur="change_class('<?=$this->_id?>', '<?=$this->_c_text?>_off');" <?=$addattr?>><?=htmlspecialchars($this->_value)?></textarea>
<?php
	}
}



class HTML_SelectField extends HTML_AdminFormField {

	var $_size;
	var $_value;
	var $_c_select = "dropdown";
	var $_name = "";
	var $_options = array();
	var $_force_spaces = false;
	var $_multiselect = false;

	function HTML_SelectField($id, $title, $description, $size = "", $value = "", $name = "", $options = array()) {
		$this->HTML_AdminFormField($id, $title, $description);
		$this->_size = $size;
		$this->_value = $value;
		if (!empty($name))
			$this->_name = $name;
		else
			$this->_name = $id;
		$this->_options = $options;
	}

	function setSelected($sel) {
		$this->_value = $sel;
	}

	function addOption($value, $title, $c_option = "") {
		$this->_options[$value] = array(
			'title' => $title,
			'class' => $c_option
		);
	}

	function addYesNo() {
		$this->addOption(1, "Tak");
		$this->addOption(0, "Nie");
	}

	function renderRight() {
		$addattr = "";
		if (!empty($this->_size))
			$addattr .= "size=\"" . $this->_size . "\" ";
		if ($this->_multiselect)
			$addattr .= "multiple=\"multiple\" ";
?>
		<select id="<?=$this->_id?>" class="<?=$this->_c_select?>_off" name="<?=$this->_name?>" onfocus="change_class('<?=$this->_id?>', '<?=$this->_c_select?>_on');" onblur="change_class('<?=$this->_id?>', '<?=$this->_c_select?>_off');" <?=$addattr?>>
<?php
		foreach ($this->_options as $value => $option) {
			$addattrs = "";
			if (!empty($option['class']))
				$addattrs .= "class='" . $option['class'] . "' ";
			if ($this->_multiselect && is_array($this->_value)) {
				if (array_search($value, $this->_value) !== FALSE)
					$addattrs .= "selected='selected' ";
			} else {
				if ($this->_value == $value)
					$addattrs .= "selected='selected' ";
			}

			$title = htmlspecialchars($option['title']);
			if ($this->_force_spaces)
				$title = str_replace(" ", "&nbsp;", $title);
?>
			<option value="<?=$value?>" <?=$addattrs?>><?=htmlspecialchars($option['title'])?></option>
<?php
		}
?>
		</select>
<?php
	}
}



class HTML_AdminFormPane extends HTML_Pane {

	var $_fields = array();

	function HTML_AdminFormPane($id, $title, $c_pane = "a_form_pane", $c_hdr = "a_form_pane_hdr", $fields = array()) {
		$this->HTML_Pane($id, $title, $c_pane, $c_hdr);
		$this->_fields = $fields;
	}

	function addField($field) {
		$this->_fields[] = $field;
	}

	function renderFields() {
		echo "<div>";
		foreach ($this->_fields as $field) {
			$field->show();
		}
		echo "</div>";
	}
	
	function renderContent() {
		$this->renderFields();
	}
}

class HTML_AdminForm extends HTML_Block {

	private $_action;
	private $_method = "post";
	private $_hidden = array();
	var $_submit = _ADMIN_SAVE_SETTINGS;

	function HTML_AdminForm($id, $title, $action, $expanded = true, $method = "post", $c_pane = "a_pane", $c_block = "a_block", $c_block_hdr = "a_block_hdr", $c_block_content = "a_block_content", $panes = array(), $hidden = array()) {

		$this->HTML_Block($id, $title, $c_pane, $c_block, $c_block_hdr, $c_block_content, $expanded);
		$this->_action = $action;
		$this->_method = $method;
		$this->_hidden = $hidden;
		$this->_panes = $panes;

		$this->addHidden("self", "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
		$this->addHidden("submit", "1");
	}

	function addHidden($name, $val) {
		$this->_hidden[$name] = htmlspecialchars($val);
	}

	function renderFormFooter() {
		if (!empty($this->_submit)) {
?>
		<br />
		<div class="a_form_footer"><input class="a_dark_button" type="submit" value="<?=$this->_submit?>" /></div></form>
<?php
		}
	}

	function renderFormContent() {
		$this->renderPanes();
	}

	function renderContent() {
?>
		<form id="<?=$this->_id?>" name="<?=$this->_id?>" action="<?=$this->_action?>" method="<?=$this->_method?>">
<?php
		foreach ($this->_hidden as $name => $value) {
			echo "<input type=\"hidden\" name=\"$name\" value=\"$value\" />\n";
		}
		$this->renderFormContent();
		$this->renderFormFooter();
	}
};

?>
