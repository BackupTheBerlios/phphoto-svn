{* Smarty *}
{* $Id$ *}

<h1>Użytkownicy i grupy :: Ustawienia</h1>

<form method="post" action="{$self}">

	<fieldset>
		<legend>Rejestracja</legend>

		<div class="combo">
			<label for="require_login">Wymagaj logowania</label>
			<select id="require_login" name="require_login">
				<option value="1" {if $frm_require_login}selected="selected"{/if}>Tak</option>
				<option value="0" {if !$frm_require_login}selected="selected"{/if}>Nie</option>
			</select>
			<img src="{full_url path="/images/help2.gif"}" alt="Pomoc" title="Czy serwis ma być dostępny tylko dla zarejestrowanych użytkowników?" />
		</div>

		<div class="combo">
			<label for="enable_registration">Włącz rejestrację</label>
			<select id="enable_registration" name="enable_registration">
				<option value="1" {if $frm_enable_registration}selected="selected"{/if}>Tak</option>
				<option value="0" {if !$frm_enable_registration}selected="selected"{/if}>Nie</option>
			</select>
			<img src="{full_url path="/images/help2.gif"}" alt="Pomoc" title="Czy zezwolić użytkownikom na zakładanie kont?" />
		</div>

		<div class="select">
			<label for="account_activation">Wymagana aktywacja konta</label>
			<select id="account_activation" name="account_activation">
				<option value="1" {if $frm_account_activation}selected="selected"{/if}>Tak</option>
				<option value="0" {if !$frm_account_activation}selected="selected"{/if}>Nie</option>
			</select>
			<img src="{full_url path="/images/help2.gif"}" alt="Pomoc" title="Czy wymagać aktywacji nowych kont za pomocą linka przesyłanego emailem? (zalecane)" />
		</div>

		<div class="textarea">
			<label for="activation_message">Wiadomość aktywacyjna</label>
			<textarea id="activation_message" name="activation_message" rows="10" cols="70">{$frm_activation_message|escape}</textarea>
		</div>

	</fieldset>

	<div class="buttons">
		<input type="submit" value="Zapisz" />
	</div>

</form>
