{* Smarty *}

{include file="error.tpl"}

<h1>Ta strona czeka na design :)</h1>

<h2>{$category_name}</h2>

{if $subscribe_url}
<a href="{$subscribe_url}">Powiadamiaj o nowych zdjêciach</a>
{/if}
{if $unsubscribe_url}
<a href="{$unsubscribe_url}">Zrezygnuj z subskrypcji</a>
{/if}

<br /><br />
Pe³ne drzewo kategori:<br />
{defun name="catnode2" node=$category_tree level="0"}
{foreach from=$node item=item}
<div style="padding-left: {$level*10}px;"><a href="{url action="category"}&amp;cid={$item.id}">{$item.name|escape}</a></div>
{fun name="catnode2" node=$item.sub level="`$level+1`"}
{/foreach}
{/defun}

<br />
Zdjêcia: <br />
{foreach from=$photos item=photo}
<a href="{$photo.url}">{$photo.title|escape}<br />{$photo.thumb_img}</a><br /><a href="{$photo.user_url}">{$photo.user_login}</a><br />{$photo.description|escape|nl2br}
<br /><br />
{/foreach}

{include file="pager.tpl"}

