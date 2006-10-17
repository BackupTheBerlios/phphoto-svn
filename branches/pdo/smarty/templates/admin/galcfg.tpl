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
		<div class="textarea">
			<label for="moderator_note">Komentarz przy oczekiwaniu na akceptację</label>
			<textarea id="moderator_note" name="moderator_note" rows="2" cols="70">{$frm_moderator_note|escape}</textarea>
		</div>


		<div class="text">
			<label for="cache_lifetime">Okres przechowywania wygenerowanych plików</label>
			<input type="text" name="cache_lifetime" id="cache_lifetime" value="{$frm_cache_lifetime}" size="10" />
		</div>

	</fieldset>

	<fieldset>
		<legend>Powiadomienia</legend>

		<div class="combo">
			<label for="send_approve_notify">Powiadamiaj o akceptacji</label>
			<select id="send_approve_notify" name="send_approve_notify">
				<option value="1" {if $frm_send_approve_notify}selected="selected"{/if}>Tak</option>
				<option value="0" {if !$frm_send_approve_notify}selected="selected"{/if}>Nie</option>
			</select>
		</div>
		<div class="textarea">
			<label for="approve_notify">Treść wiadomości powiadamiającej o zaakceptowaniu zdjęcia</label>
			<textarea id="approve_notify" name="approve_notify" rows="3" cols="70">{$frm_approve_notify|escape}</textarea>
		</div>

		<div class="combo">
			<label for="send_reject_notify">Powiadamiaj o odrzuceniu</label>
			<select id="send_reject_notify" name="send_reject_notify">
				<option value="1" {if $frm_send_reject_notify}selected="selected"{/if}>Tak</option>
				<option value="0" {if !$frm_send_reject_notify}selected="selected"{/if}>Nie</option>
			</select>
		</div>
		<div class="textarea">
			<label for="reject_notify">Treść wiadomości powiadamiającej o odrzuceniu zdjęcia</label>
			<textarea id="reject_notify" name="reject_notify" rows="3" cols="70">{$frm_reject_notify|escape}</textarea>
		</div>

	</fieldset>


	<div class="buttons">
		<input type="submit" value="Zapisz" />
	</div>

</form>
