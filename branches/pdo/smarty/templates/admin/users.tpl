{* Smarty *}
{* $Id$ *}

<h1>Użytkownicy i grupy :: Użytkownicy</h1>

<div class="buttons">
	<a href="{url action="adm-edit-user" ref=$self}" class="button">
		<img src="{full_url path="/images/icons/user_add.png"}" alt="Dodaj użytkownika" width="16" height="16" title="Dodaj użytkownika" />
	</a>
</div>

<div class="table">
<table>
<thead>
	<th>Id</th>
	<th>Login</th>
	<th>Nazwa<div class="details">Tytuł</div></th>
	<th>Data rejestracji<div class="details">Data akywacji</div></th>
	<th>Ostatnie logowanie<div class="details">Ostatnie IP</div></th>
	<th colspan="6">Działania</th>
</thead>
<tbody>
{foreach from=$users item=user}
<tr class="{cycle values="odd,even"}">

	<td>
		{$user.user_id|escape}
	</td>
	<td>
		{$user.user_login|escape}
	</td>
	<td>
		{$user.user_name|escape}
		{if $user.user_title}<div class="details">{$user.user_title|escape}</div>{/if}
	</td>
	<td>
		{$user.user_registered|date_format:$datetime_format}
		<div class="details">{if $user.user_activated}{$user.user_activated|date_format:$datetime_format}{else}(Konto nieaktywne){/if}</div>
	</td>
	<td>
		{if $user.user_lastlogin}{$user.user_lastlogin|date_format:$datetime_format}{/if}
		{if $user.user_ip}<div class="details">{decode_ip ip=$user.user_ip}</div>{/if}
	</td>
	<td class="icon">
		{if $user.allow_edit}
			<a href="{url action="adm-edit-user" uid=$user.user_id ref=$self}">
				<img src="{full_url path="/images/icons/pencil.png"}" alt="Edycja" title="Edycja użytkownika" width="16" height="16" />
			</a>
		{/if}
	</td>
	<td class="icon">
		{if $user.allow_perms}
			<a href="{url action="adm-edit-perms" uid=$user.user_id ref=$self}">
				<img src="{full_url path="/images/icons/key.png"}" alt="Uprawnienia" title="Edycja uprawnień" width="16" height="16" />
			</a>
		{/if}
	</td>
	<td class="icon">
		{if $user.allow_delete}
			<img src="{full_url path="/images/icons/b_drop.png"}" alt="Usuń" width="16" height="16" />
		{/if}
	</td>
	<td class="icon">
		{if $user.user_email}
			<img src="{full_url path="/images/icons/email.png"}" alt="Email" width="16" height="16" />
		{/if}
	</td>
	<td class="icon">
		{if $user.user_www}
			<a class="external" href="{$user.user_www|escape}">
				<img src="{full_url path="/images/icons/house.png"}" alt="Strona domowa" width="16" height="16" />
			</a>
		{/if}
	</td>
	<td class="icon">
		<a href="{url action="adm-photos" user_login=$user.user_login}">
			<img src="{full_url path="/images/icons/camera.png"}" alt="Zdjęcia" width="16" height="16" />
		</a>
	</td>
</tr>
{/foreach}
</tbody>
</table>
</div>

{include file="pager.tpl"}

<div class="buttons">
	<a href="{url action="adm-edit-user" ref=$self}" class="button">
		<img src="{full_url path="/images/icons/user_add.png"}" alt="Dodaj użytkownika" width="16" height="16" title="Dodaj użytkownika" />
	</a>
</div>

