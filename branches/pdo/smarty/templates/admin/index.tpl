{* Smarty *}
<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pl">
{* $Id$ *}

<head>
	<title>{$app_name|escape} - {$page_title|escape}</title>
	<meta http-equiv="Pragma"  content="no-cache" />
	<meta http-equiv="Cache-Control" content="no-cache" />
	<meta name="Generator" content="vim, kwrite" />
	<meta name="robots" content="noindex, nofollow" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link href="{full_url path="/css/admin.css"}" rel="stylesheet" media="screen" type="text/css" />
	<script type="text/javascript" charset="utf-8">
		// <![CDATA[
		var _ajax_service_base_url = "{$base_service_url}";
		// ]]>
	</script>
	<script type="text/javascript" src="{full_url path="/js/behaviour.js"}"></script>
	<script type="text/javascript" src="{full_url path="/js/advajax.js"}"></script>
	<script type="text/javascript" src="{full_url path="/js/ajax.js"}"></script>
	<script type="text/javascript" src="{full_url path="/js/admin.js"}"></script>
</head>

<body>

<div id="wrapper">

{include file="topbar.tpl"}

<ul id="menu">
	<li class="menu">Ustawienia główne
		<ul>
			<li><a href="{url action="adm-sitecfg"}">Konfiguracja witryny</a></li>
			<li><a href="{url action="adm-dbcfg"}">Baza danych</a></li>
		</ul>
	</li>
	<li class="menu">Użytkownicy i grupy
		<ul>
			<li><a href="{url action="adm-usercfg"}">Ustawienia</a></li>
			<li><a href="{url action="adm-users"}">Użytkownicy</a></li>
			<li>Grupy</li>
			<li>Wiadomość masowa</li>
		</ul>
	</li>
	<li class="menu">Galeria
		<ul>
			<li>Ustawienia</li>
			<li>Galerie</li>
			<li>Kategorie</li>
			<li>Zdjęcia</li>
		</ul>
	</li>
</ul>

<div id="content">

{include file="message.tpl"}
{include file=admin/$template}
{include file="footer.tpl"}

</div>

</div>

</body>

</html>



