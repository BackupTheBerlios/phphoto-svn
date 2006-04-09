{* Smarty *}
{* $Id$ *}

<h1>Użytkownicy i grupy :: Użytkownicy</h1>

<div class="table">
<table>
<thead>
	<th>Id</th>
	<th>Login</th>
	<th>Nazwa</th>
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
			<img src="{full_url path="/images/edit.gif"}" alt="Edycja" />
		{/if}
	</td>
	<td class="icon">
		{if $user.allow_perms}
			<img src="{full_url path="/images/flag.gif"}" alt="Uprawnienia" />
		{/if}
	</td>
	<td class="icon">
		{if $user.allow_delete}
			<img src="{full_url path="/images/trash.gif"}" alt="Usuń" />
		{/if}
	</td>
	<td class="icon">
		{if $user.user_email}
			<img src="{full_url path="/images/email.gif"}" alt="Email" />
		{/if}
	</td>
	<td class="icon">
		{if $user.user_www}
			<a class="external" href="{$user.user_www|escape}"><img src="{full_url path="/images/www.gif"}" alt="Strona domowa" /></a>
		{/if}
	</td>
	<td class="icon">
		<img src="{full_url path="/images/photo.gif"}" alt="Zdjęcia" />
	</td>
</tr>
{/foreach}
</tbody>
</table>
</div>

{include file="pager.tpl"}
