{* Smarty *}

{include file="error.tpl"}

<h1>Ta strona czeka na design :)</h1>

<h2>{$photo_title|escape}</h2>

<img src="{$photo_url}" alt="{$photo_title|escape}" {$photo_imgwh} />
<br />
{$photo_description|escape|nl2br}
<br />

Kategorie:
{foreach name=categories from=$photo_cid_map item=item key=key}
<a href="{url action="category"}&amp;cid={$key}">{$item|escape}</a>{if !$smarty.foreach.categories.last}, {/if}
{/foreach}
<br />
Kategorie full:
{section name=category_full loop=$categories_full}
{section name=category_tree loop=$categories_full[category_full]}
<a href="{url action="category"}&amp;cid={$categories_full[category_full][category_tree].id}">{$categories_full[category_full][category_tree].name|escape}</a>{if !$smarty.section.category_tree.last} / {/if}
{/section}{if !$smarty.section.category_full.last}<br />{/if}
{/section}

<br /><br />
Kategorie tree:<br />
{foreach from=$categories_tree item=item}
{defun name="catnode" node=$item level="0"}
{foreach from=$node item=item}
<div style="padding-left: {$level*10}px;"><a href="{url action="category"}&amp;cid={$item.id}">{$item.name|escape}</a></div>
{fun name="catnode" node=$item.sub level="`$level+1`"}
{/foreach}
{/defun}
{/foreach}


<br /><br />
Pe³ne drzewo kategori:<br />
{defun name="catnode2" node=$full_category_tree level="0"}
{foreach from=$node item=item}
<div style="padding-left: {$level*10}px;"><a href="{url action="category"}&amp;cid={$item.id}">{$item.name|escape}</a></div>
{fun name="catnode2" node=$item.sub level="`$level+1`"}
{/foreach}
{/defun}


<br /><br />
Komentarze:<br />
{foreach from=$photo_comments item=item}
{$item.title|escape}, napisany {$item.date|escape} przez <a href="{$item.user_url|escape}">{$item.user_login|escape}</a><br />
{$item.text|escape|nl2br}<br /><hr style="height: 1px;"/>
{/foreach}

{if $allow_comments}
<br />Dodaj komentarz:<br />
<form method="post" name="post_comment" action="{$post_comment_action|escape}">
<input type="hidden" name="pid" value="{$photo_id}" />
<input type="hidden" name="ref" value="{$self|escape}" />
Tytu³: <input type="text" name="comment_title" size="60" /><br />
Tre¶æ: <textarea rows="5" cols="70" name="comment_text"></textarea><br />
<input type="submit" value="Wy¶lij" />
</form>
{else}
Musisz siê zalogowaæ aby móc komentowaæ to zdjêcie.
{/if}

<br />
<a href="{$prev_photo.url}">{$prev_photo.thumb_img}&lt;&lt;</a> | <a href="{$next_photo.url}">&gt;&gt;{$next_photo.thumb_img}</a>

