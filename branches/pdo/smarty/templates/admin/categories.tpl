{* Smarty *}
{* $Id$ *}

<h1>Galeria :: Kategorie</h1>

<div class="buttons">
	<a href="{url action="adm-edit-category" ref=$self}" class="button">
		<img src="{full_url path="/images/icons/folder_add.png"}" alt="Dodaj kategorię" width="16" height="16" title="Dodaj grupę" />
	</a>
</div>

<ul>
{foreach from=$categories item=cat}
	<li>{$cat.category_name|escape}</li>
{/foreach}
</ul>

<div class="buttons">
	<a href="{url action="adm-edit-category" ref=$self}" class="button">
		<img src="{full_url path="/images/icons/folder_add.png"}" alt="Dodaj kategorię" width="16" height="16" title="Dodaj kategorię" />
	</a>
</div>

