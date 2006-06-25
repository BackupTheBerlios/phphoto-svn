{* Smarty *}
{* $Id$ *}

<h1>Użytkownicy i grupy :: Grupy :: {if $group_id}Edycja :: {$frm_group_name|escape}{else}Nowa{/if}</h1>

<form method="post" action="{url action="adm-edit-group"}">

	<input type="hidden" name="gid" value="{$group_id}" />
	<input type="hidden" name="ref" value="{$ref|escape}" />

	<fieldset>
		<legend>Dane grupy</legend>

		<div class="text">
			<label for="group_name">Nazwa</label>
			<input type="text" name="group_name" id="group_name" value="{$frm_group_name|escape}" size="50" />
		</div>

		<div class="textarea">
			<label for="group_description">Opis</label>
			<textarea id="group_description" name="group_description" rows="2" cols="70">{$frm_group_description|escape}</textarea>
		</div>

	</fieldset>

	{if $allow_grouplevel}
		<fieldset>
			<legend>Uprawnienia</legend>

			{if $allow_grouplevel}
				<div class="text">
					<label for="group_level">Poziom grupy</label>
					<input type="text" name="group_level" id="group_level" value="{$frm_group_level}" size="10" />
				</div>
			{/if}
		</fieldset>
	{/if}

	<div class="buttons">
		<input type="submit" value="Zapisz" />
	</div>

</form>
