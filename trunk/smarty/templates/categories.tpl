{* Smarty *}

{include file="error.tpl"}

<h1>Ta strona czeka na design :)</h1>

<h2>Kategorie</h2>

<br /><br />
Pe³ne drzewo kategori:<br />
{defun name="catnode2" node=$category_tree level="0"}
{foreach from=$node item=item}
<div style="padding-left: {$level*10}px;"><a href="{url action="category"}&amp;cid={$item.id}">{$item.name|escape}</a></div>
{fun name="catnode2" node=$item.sub level="`$level+1`"}
{/foreach}
{/defun}


