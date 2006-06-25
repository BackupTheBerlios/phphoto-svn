{* Smarty *}
{* $Id$ *}

<h1>Użytkownicy i grupy :: Grupy</h1>

<div class="buttons">
	<a href="{url action="adm-edit-group" ref=$self}" class="button">
		<img src="{full_url path="/images/icons/group_add.png"}" alt="Dodaj grupę" width="16" height="16" title="Dodaj grupę" />
	</a>
</div>

<div class="table">
<table id="groups-table">
<thead>
	<td class="icon" />
	<th>Id</th>
	<th>Nazwa</th>
	<th>Opis</th>
	<th>Użytkowników</th>
	<th>Data utworzenia<div class="details">Przez</div></th>
	<th colspan="4">Działania</th>
</thead>
<tbody>
{foreach from=$groups item=group}
<tr class="{cycle values="odd,even"}" id="{$group.group_id}">

	<td class="icon">
		{if $group.allow_members}
			<a class="members-icon" href="{url action="adm-group-members" gid=$group.group_id ref=$self}">
				<img src="{full_url path="/images/icons/group.png"}" alt="Członkowie" title="Członkowie grupy" width="16" height="16" />
			</a>
		{/if}
	</td>
	<td>
		{$group.group_id|escape}
	</td>
	<td>
		{$group.group_name|escape}
	</td>
	<td>
		{$group.group_description|escape|nl2br}
	</td>
	<td id="members-count-{$group.group_id}">
		{$group.user_count|escape}
	</td>
	<td>
		{$group.group_created|date_format:$datetime_format}
		{if $group.creator_login}<div class="details">{$group.creator_login}</div>{/if}
	</td>
	<td class="icon">
		{if $group.allow_edit}
			<a href="{url action="adm-edit-group" gid=$group.group_id ref=$self}">
				<img src="{full_url path="/images/icons/pencil.png"}" alt="Edycja" title="Edycja grupy" width="16" height="16" />
			</a>
		{/if}
	</td>
	<td class="icon">
		{if $group.allow_members}
			<a href="{url action="adm-group-members" gid=$group.group_id ref=$self}">
				<img src="{full_url path="/images/icons/group.png"}" alt="Członkowie" title="Członkowie grupy" width="16" height="16" />
			</a>
		{/if}
	</td>
	<td class="icon">
		{if $group.allow_perms}
			<a href="{url action="adm-edit-perms" gid=$group.group_id ref=$self}">
				<img src="{full_url path="/images/icons/key.png"}" alt="Uprawnienia" title="Edycja uprawnień" width="16" height="16" />
			</a>
		{/if}
	</td>
	<td class="icon">
		{if $group.allow_delete}
			<img src="{full_url path="/images/icons/b_drop.png"}" alt="Usuń" width="16" height="16" />
		{/if}
	</td>
</tr>
{/foreach}
</tbody>
</table>
</div>

{include file="pager.tpl"}

<div class="buttons">
	<a href="{url action="adm-edit-group" ref=$self}" class="button">
		<img src="{full_url path="/images/icons/group_add.png"}" alt="Dodaj grupę" width="16" height="16" title="Dodaj grupę" />
	</a>
</div>

