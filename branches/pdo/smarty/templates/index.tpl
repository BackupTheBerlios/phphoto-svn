{* Smarty *}
<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pl">
{* $Id$ *}

<head>
	<title>{$app_name} - {$page_title}</title>
	<meta http-equiv="Pragma"  content="no-cache" />
	<meta http-equiv="Cache-Control" content="no-cache" />
	<meta name="Generator" content="vim, kwrite" />
	<meta name="robots" content="index, follow" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link href="{full_url path="/css/screen.css"}" rel="stylesheet" media="screen" type="text/css" />
	{include file="js-config.tpl"}
	<script type="text/javascript" src="{full_url path="/js/behaviour.js"}"></script>
	<script type="text/javascript" src="{full_url path="/js/advajax.js"}"></script>
	<script type="text/javascript" src="{full_url path="/js/ajax.js"}"></script>
	<script type="text/javascript" src="{full_url path="/js/service.js"}"></script>
</head>

<body>

<div>

{include file="topbar.tpl"}
{include file="message.tpl"}
{include file=$template}
{include file="footer.tpl"}

</div>

</body>

</html>



