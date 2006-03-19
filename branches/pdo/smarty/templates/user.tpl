{* Smarty *}

{include file="error.tpl"}

<h1>Ta strona czeka na design :)</h1>

<h2>{$user_name|escape} ({$user_login|escape})</h2>

<br />
Zdjêcia: <br />
{foreach from=$photos item=photo}
<a href="{$photo.url}">{$photo.title|escape}<br />{$photo.thumb_img}</a><br />{$photo.description|escape|nl2br}
<br /><br />
{/foreach}

{include file="pager.tpl"}

