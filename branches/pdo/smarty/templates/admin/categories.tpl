{* Smarty *}
{* $Id$ *}

<h1>Galeria :: Kategorie</h1>

<div class="buttons">
	<a href="{url action="adm-edit-category" ref=$self}" class="button">
		<img src="{full_url path="/images/icons/folder_add.png"}" alt="Dodaj kategorię" width="16" height="16" title="Dodaj grupę" />
	</a>
	<a href="{url action="adm-categories" ref=$self}" class="button" id="refresh-category1">
		<img src="{full_url path="/images/icons/arrow_refresh.png"}" alt="Odśwież" width="16" height="16" title="Odśwież" />
	</a>
</div>

<div class="category-tree" id="category-0"></div>

<div class="tab-sheet" id="right-pane">
<div class="preview" id="preview" style="display: none;"></div>
<div class="preview" id="photo-preview" style="display: none;"></div>
</div>

<div class="buttons">
	<a href="{url action="adm-edit-category" ref=$self}" class="button">
		<img src="{full_url path="/images/icons/folder_add.png"}" alt="Dodaj kategorię" width="16" height="16" title="Dodaj kategorię" />
	</a>
	<a href="{url action="adm-categories" ref=$self}" class="button" id="refresh-category2">
		<img src="{full_url path="/images/icons/arrow_refresh.png"}" alt="Odśwież" width="16" height="16" title="Odśwież" />
	</a>
</div>

