{* Smarty *}
{* $Id$ *}

<h1>Użytkownicy i grupy :: Użytkownicy :: {if $user_id}Edycja :: {$frm_user_login|escape}{else}Nowy{/if}</h1>

<form method="post" action="{url action="adm-edit-user"}">

	<input type="hidden" name="uid" value="{$user_id}" />
	<input type="hidden" name="ref" value="{$ref|escape}" />

	<fieldset>
		<legend>Informacje podstawowe</legend>

		<div class="text">
			<label for="user_name">Nazwa</label>
			<input type="text" name="user_name" id="user_name" value="{$frm_user_name|escape}" size="50" />
		</div>

		<div class="text">
			<label for="user_login">Login</label>
			<input type="text" name="user_login" id="user_login" value="{$frm_user_login|escape}" size="20" />
		</div>

		<div class="text">
			<label for="user_pass">Hasło</label>
			<input type="password" name="user_pass" id="user_pass" value="" size="20" />
		</div>

		<div class="text">
			<label for="user_pass2">Powtórz hasło</label>
			<input type="password" name="user_pass2" id="user_pass2" value="" size="20" />
		</div>

	</fieldset>

	<fieldset>
		<legend>Informacje dodatkowe</legend>

		<div class="text">
			<label for="user_title">Tytuł</label>
			<input type="text" name="user_title" id="user_title" value="{$frm_user_title|escape}" size="20" />
		</div>

		<div class="text">
			<label for="user_from">Miejscowość:</label>
			<input type="text" name="user_from" id="user_from" value="{$frm_user_from|escape}" size="20" />
		</div>
	</fieldset>

	<fieldset>
		<legend>Kontakt</legend>

		<div class="text">
			<label for="user_email">Email</label>
			<input type="text" name="user_email" id="user_email" value="{$frm_user_email|escape}" size="20" />
		</div>

		<div class="text">
			<label for="user_jid">Jabber ID:</label>
			<input type="text" name="user_jid" id="user_jid" value="{$frm_user_jid|escape}" size="20" />
		</div>

		<div class="text">
			<label for="user_www">Strona domowa</label>
			<input type="text" name="user_www" id="user_www" value="{$frm_user_www|escape}" size="20" />
		</div>
	</fieldset>

	{if $allow_userlevel || $allow_superuser}
		<fieldset>
			<legend>Uprawnienia</legend>

			{if $allow_userlevel}
				<div class="text">
					<label for="user_level">Poziom użytkownika</label>
					<input type="text" name="user_level" id="user_level" value="{$frm_user_level}" size="10" />
				</div>
			{/if}

			{if $allow_superuser}
				<div class="select">
					<label for="user_admin">Super administrator</label>
					<select id="user_admin" name="user_admin">
						<option value="1" {if $frm_user_admin}selected="selected"{/if}>Tak</option>
						<option value="0" {if !$frm_user_admin}selected="selected"{/if}>Nie</option>
					</select>
				</div>
			{/if}
		</fieldset>
	{/if}

	<div class="buttons">
		<input type="submit" value="Zapisz" />
	</div>

</form>
