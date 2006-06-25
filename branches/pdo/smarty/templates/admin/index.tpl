{* Smarty *}
{include file="doctype.tpl"}
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
	{include file="js-config.tpl"}
	<script type="text/javascript" src="{full_url path="/js/functions.js"}"></script>
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
			<li><a href="{url action="adm-groups"}">Grupy</a></li>
			<li>Wiadomość masowa</li>
		</ul>
	</li>
	<li class="menu">Galeria
		<ul>
			<li><a href="{url action="adm-galcfg"}">Ustawienia</a></li>
			<li>Galerie</li>
			<li><a href="{url action="adm-categories"}">Kategorie</a></li>
			<li>Zdjęcia</li>
		</ul>
	</li>
</ul>

<div id="content">

{include file="message.tpl"}
{if $template}{include file=admin/$template}{/if}
{include file="footer.tpl"}

</div>

</div>

</body>

</html>



