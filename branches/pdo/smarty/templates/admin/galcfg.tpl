{* Smarty *}
{* $Id$ *}

<h1>Galeria :: Ustawienia</h1>

<form method="post" action="{$self}">

	<fieldset>
		<legend>Zdjęcia</legend>

		<div class="text">
			<label for="max_file_size">Maksymalny rozmiar pliku</label>
			<input type="text" name="max_file_size" id="max_file_size" value="{$frm_max_file_size}" size="10" />
		</div>

		<div class="text">
			<label for="max_width">Maksymalna szerokość zdjęcia</label>
			<input type="text" name="max_width" id="max_width" value="{$frm_max_width}" size="10" />
		</div>

		<div class="text">
			<label for="max_height">Maksymalna wysokość zdjęcia</label>
			<input type="text" name="max_height" id="max_height" value="{$frm_max_height}" size="10" />
		</div>

		<div class="combo">
			<label for="auto_approve">Automatyczna akceptacja</label>
			<select id="auto_approve" name="auto_approve">
				<option value="1" {if $frm_auto_approve}selected="selected"{/if}>Tak</option>
				<option value="0" {if !$frm_auto_approve}selected="selected"{/if}>Nie</option>
			</select>
			<img src="{full_url path="/images/help2.gif"}" alt="Pomoc" title="Czy nowo dodane zdjęcia mają być automatycznie akceptowane?" />
		</div>

		<div class="text">
			<label for="cache_lifetime">Okres przechowywania wygenerowanych plików</label>
			<input type="text" name="cache_lifetime" id="cache_lifetime" value="{$frm_cache_lifetime}" size="10" />
		</div>

	</fieldset>

	<div class="buttons">
		<input type="submit" value="Zapisz" />
	</div>

</form>
