{* Smarty *}
{* $Id$ *}

<h1>Galeria :: Kategorie :: {if $category_id}Edycja :: {$frm_category_name|escape}{else}Nowa{/if}</h1>

<form method="post" action="{url action="adm-edit-category"}">

	<fieldset>
		<input type="hidden" id="cid" name="cid" value="{$category_id}" />
		<input type="hidden" id="ref" name="ref" value="{$ref|escape}" />
		<input type="hidden" id="category_parent" name="category_parent" value="" />
		<input type="hidden" id="orig_category_parent" name="orig_category_parent" value="{$frm_category_parent}" />

		<legend>Dane kategorii</legend>

		<div class="text">
			<label for="category_name">Nazwa</label>
			<input type="text" name="category_name" id="category_name" value="{$frm_category_name|escape}" size="50" />
		</div>

		<div class="textarea">
			<label for="category_description">Opis</label>
			<textarea id="category_description" name="category_description" rows="2" cols="70">{$frm_category_description|escape}</textarea>
		</div>

		<div class="tree">
			<label for="category-0">Kategoria nadrzędna</label>
			<div style="margin-left: 21em; " id="category-0">
			</div>
		</div>

	</fieldset>

	<div class="buttons">
		<input type="submit" value="Zapisz" />
	</div>

</form>
