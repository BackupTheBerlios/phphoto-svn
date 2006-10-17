{* Smarty *}
{* $Id$ *}

<h1>Galeria :: Zdjęcia</h1>

<div class="buttons"></div>

<div id="photos">
{foreach from=$photos item=photo}<div class="photo-block" id="photo-{$photo.photo_id}"><div>
<h1>{$photo.photo_title|escape}&nbsp;</h1>
<h2>{$photo.author.user_login|escape}&nbsp;</h2>
{$photo.file[7]}
</div></div>{/foreach}
</div>

<div class="tab-sheet" id="right-pane">
<div class="preview" id="preview" style="display: none;">preview</div>

<div class="preview" id="stats" style="display: none;">
	<h1>Statystyki</h1>
	<h2>Ogółem</h2>
	<table>
		<tbody>
			<tr class="preview-info"><td class="preview-info-title">Zdjęć:</td><td>{$stats.total.total}</td></tr>
			<tr class="preview-info"><td class="preview-info-title">Zaakceptowanych:</td><td>{$stats.total.approved}</td></tr>
			<tr class="preview-info"><td class="preview-info-title">Odrzuconych:</td><td>{$stats.total.rejected}</td></tr>
			<tr class="preview-info"><td class="preview-info-title">Oczekujących:</td><td>{$stats.total.waiting}</td></tr>
		</tbody>
	</table>
	<h2>Wybrane</h2>
	<table>
		<tbody>
			<tr class="preview-info"><td class="preview-info-title">Zdjęć:</td><td>{$stats.selected.total}</td></tr>
			<tr class="preview-info"><td class="preview-info-title">Zaakceptowanych:</td><td>{$stats.selected.approved}</td></tr>
			<tr class="preview-info"><td class="preview-info-title">Odrzuconych:</td><td>{$stats.selected.rejected}</td></tr>
			<tr class="preview-info"><td class="preview-info-title">Oczekujących:</td><td>{$stats.selected.waiting}</td></tr>
		</tbody>
	</table>
</div>

<div class="preview" id="filter" style="display: none;">
	<h1>Filtr</h1>
	<form method="get" action="{url action="adm-photos"}">

			<input class="hidden" type="hidden" name="action" value="adm-photos" />
			<input class="hidden" type="hidden" name="cid" id="category_id" value="{$_ARGS.cid}" />

			<h2>Autor</h2>

			<div>
				<label for="user_login">Nick</label>
				<input type="text" name="user_login" id="user_login" value="{$_ARGS.user_login}" />
			</div>

			<h2>Kategoria</h2>
			<div class="tree">
				<div id="category-0" />
				<input type="checkbox" {if $_ARGS.scid}checked="checked"{/if} name="scid" id="scid" /><label for="scid">Wraz z podkategoriami</label>
			</div>

			<h2>Status</h2>
			<div>
				<label for="rejected"><input type="checkbox" {if $_ARGS.rejected}checked="checked"{/if} name="rejected" id="rejected" />Odrzucone</label>
				<label for="approved"><input type="checkbox" {if $_ARGS.approved}checked="checked"{/if} name="approved" id="approved" />Zaakceptowane</label><br />
				<label for="waiting"><input type="checkbox" {if $_ARGS.waiting}checked="checked"{/if} name="waiting" id="waiting" />Oczekujące</label>
			</div>

			<div>
				<input type="submit" value="Pokaż" />
			</div>
	</form>
</div>

</div>

{include file="pager.tpl"}
