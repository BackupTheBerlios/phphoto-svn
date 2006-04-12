{* Smarty *}
{* $Id$ *}

<h1>Użytkownicy i grupy :: Użytkownicy :: {if $user_id}Edycja{else}Nowy{/if}</h1>

<form method="post" action="{url action="edit-user"}">

	<input type="hidden" name="uid" value="{$user_id}" />

	<fieldset>
		<legend>Nazwy i adresy</legend>

		<div class="text">
			<label for="site_title">Nazwa witryny</label>
			<input type="text" name="site_title" id="site_title" value="{$frm_site_title|escape}" size="50" />
			<img src="{full_url path="/images/help2.gif"}" alt="Pomoc" title="Nazwa witryny wyświetlana jako tytuł strony itd."/>
		</div>

		<div class="text">
			<label for="site_url">Adres strony</label>
			<input type="text" name="site_url" id="site_url" value="{$frm_site_url|escape}" size="50" />
			<img src="{full_url path="/images/help2.gif"}" alt="Pomoc" title="Pełny adres URL serwisu."/>
		</div>

	</fieldset>

	<fieldset>
		<legend>Sesje i ciasteczka</legend>

		<div class="text">
			<label for="cookie_domain">Domena ciasteczka</label>
			<input type="text" name="cookie_domain" id="cookie_url" value="{$frm_cookie_domain|escape}" size="50" />
		</div>

		<div class="text">
			<label for="cookie_name">Nazwa ciasteczka</label>
			<input type="text" name="cookie_name" id="cookie_name" value="{$frm_cookie_name|escape}" size="10" />
		</div>

		<div class="text">
			<label for="cookie_path">Ścieżka ciasteczka</label>
			<input type="text" name="cookie_path" id="cookie_path" value="{$frm_cookie_path|escape}" size="10" />
		</div>

		<div class="text">
			<label for="session_cookie_name">Parametr sesji</label>
			<input type="text" name="session_cookie_name" id="session_cookie_name" value="{$frm_session_cookie_name|escape}" size="10" />
		</div>

		<div class="text">
			<label for="session_lifetime">Czas ważności sesji</label>
			<input type="text" name="session_lifetime" id="session_lifetime" value="{$frm_session_lifetime|escape}" size="10" />
		</div>

	</fieldset>

	<fieldset>
		<legend>Wygląd</legend>

		<div class="text">
			<label for="date_format">Format daty</label>
			<input type="text" name="date_format" id="date_format" value="{$frm_date_format|escape}" size="20" />
		</div>

		<div class="text">
			<label for="time_format">Format czasu</label>
			<input type="text" name="time_format" id="time_format" value="{$frm_time_format|escape}" size="20" />
		</div>

		<div class="text">
			<label for="datetime_format">Format daty i czasu</label>
			<input type="text" name="datetime_format" id="datetime_format" value="{$frm_datetime_format|escape}" size="20" />
		</div>

	</fieldset>

	<fieldset>
		<legend>Email</legend>

		<div class="text">
			<label for="email_from">Adres email</label>
			<input type="text" name="email_from" id="email_from" value="{$frm_email_from|escape}" size="20" />
			<img src="{full_url path="/images/help2.gif"}" alt="Pomoc" title="Adres spod którego będą wysyłane listy do użytkowników" />
		</div>

		<div class="text">
			<label for="email_user">Nazwa</label>
			<input type="text" name="email_user" id="email_user" value="{$frm_email_user|escape}" size="20" />
			<img src="{full_url path="/images/help2.gif"}" alt="Pomoc" title="Nazwa użytkownika pod jaką będą wysyłane listy do użytkowników" />
		</div>

	</fieldset>

	<fieldset>
		<legend>Zaawansowane</legend>

		<div class="select">
			<label for="debug_trace">Pokaż trace</label>
			<select id="debug_trace" name="debug_trace">
				<option value="1" {if $frm_debug_trace}selected="selected"{/if}>Tak</option>
				<option value="0" {if !$frm_debug_trace}selected="selected"{/if}>Nie</option>
			</select>
			<img src="{full_url path="/images/help2.gif"}" alt="Pomoc" title="Włącz pokazywanie stosu wywołań przy wyjątkach (nie zalecane)" />
		</div>
	</fieldset>

	<div class="buttons">
		<input type="submit" value="Zapisz" />
	</div>

</form>
