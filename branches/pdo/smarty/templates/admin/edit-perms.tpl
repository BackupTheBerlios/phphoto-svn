{* Smarty *}
{* $Id$ *}

<h1>Użytkownicy i grupy :: {if $user_id}Użytkownicy{else}Grupy{/if} :: Edycja uprawnień :: {$obj_name|escape}</h1>

<form method="post" action="{url action="adm-edit-perms"}">

	{if $user_id}<input type="hidden" name="uid" value="{$user_id}" />{/if}
	{if $group_id}<input type="hidden" name="gid" value="{$group_id}" />{/if}
	<input type="hidden" name="ref" value="{$ref|escape}" />

	<fieldset>
		<legend>Ogólne</legend>

		<div class="select">
			<label for="admin_panel">Dostęp do panelu administracyjnego</label>
			<select id="admin_panel" name="admin_panel">
				<option value="1" {if $frm_admin_panel}selected="selected"{/if}>Tak</option>
				<option value="0" {if !$frm_admin_panel}selected="selected"{/if}>Nie</option>
			</select>
		</div>

		<div class="select">
			<label for="site_config">Konfiguracja witryny</label>
			<select id="site_config" name="site_config">
				<option value="1" {if $frm_site_config}selected="selected"{/if}>Tak</option>
				<option value="0" {if !$frm_site_config}selected="selected"{/if}>Nie</option>
			</select>
		</div>

		<div class="select">
			<label for="mass_message">Wiadomość masowa</label>
			<select id="mass_message" name="mass_message">
				<option value="1" {if $frm_mass_message}selected="selected"{/if}>Tak</option>
				<option value="0" {if !$frm_mass_message}selected="selected"{/if}>Nie</option>
			</select>
		</div>

	</fieldset>

	<fieldset>
		<legend>Użytkownicy i grupy</legend>

		<div class="select">
			<label for="users_and_groups_config">Ustawienia</label>
			<select id="users_and_groups_config" name="users_and_groups_config">
				<option value="1" {if $frm_users_and_groups_config}selected="selected"{/if}>Tak</option>
				<option value="0" {if !$frm_users_and_groups_config}selected="selected"{/if}>Nie</option>
			</select>
		</div>

		<div class="select">
			<label for="users_list">Lista użytkowników</label>
			<select id="users_list" name="users_list">
				<option value="1" {if $frm_users_list}selected="selected"{/if}>Tak</option>
				<option value="0" {if !$frm_users_list}selected="selected"{/if}>Nie</option>
			</select>
		</div>

		<div class="select">
			<label for="create_users">Tworzenie użytkowników</label>
			<select id="create_users" name="create_users">
				<option value="1" {if $frm_create_users}selected="selected"{/if}>Tak</option>
				<option value="0" {if !$frm_create_users}selected="selected"{/if}>Nie</option>
			</select>
		</div>

		<div class="select">
			<label for="edit_users">Edycja użytkowników</label>
			<select id="edit_users" name="edit_users">
				<option value="1" {if $frm_edit_users}selected="selected"{/if}>Tak</option>
				<option value="0" {if !$frm_edit_users}selected="selected"{/if}>Nie</option>
			</select>
		</div>

		<div class="select">
			<label for="remove_users">Usuwanie użytkowników</label>
			<select id="remove_users" name="remove_users">
				<option value="1" {if $frm_remove_users}selected="selected"{/if}>Tak</option>
				<option value="0" {if !$frm_remove_users}selected="selected"{/if}>Nie</option>
			</select>
		</div>

		<div class="select">
			<label for="change_users_permissions">Zmiana uprawnień użytkowników</label>
			<select id="change_users_permissions" name="change_users_permissions">
				<option value="1" {if $frm_change_users_permissions}selected="selected"{/if}>Tak</option>
				<option value="0" {if !$frm_change_users_permissions}selected="selected"{/if}>Nie</option>
			</select>
		</div>

		<div class="select">
			<label for="change_users_level">Zmiana poziomu użytkowników</label>
			<select id="change_users_level" name="change_users_level">
				<option value="1" {if $frm_change_users_level}selected="selected"{/if}>Tak</option>
				<option value="0" {if !$frm_change_users_level}selected="selected"{/if}>Nie</option>
			</select>
		</div>

		<div class="select">
			<label for="groups_list">Lista grup</label>
			<select id="groups_list" name="groups_list">
				<option value="1" {if $frm_groups_list}selected="selected"{/if}>Tak</option>
				<option value="0" {if !$frm_groups_list}selected="selected"{/if}>Nie</option>
			</select>
		</div>

		<div class="select">
			<label for="create_groups">Tworzenie grup</label>
			<select id="create_groups" name="create_groups">
				<option value="1" {if $frm_create_groups}selected="selected"{/if}>Tak</option>
				<option value="0" {if !$frm_create_groups}selected="selected"{/if}>Nie</option>
			</select>
		</div>

		<div class="select">
			<label for="edit_groups">Edycja grup</label>
			<select id="edit_groups" name="edit_groups">
				<option value="1" {if $frm_edit_groups}selected="selected"{/if}>Tak</option>
				<option value="0" {if !$frm_edit_groups}selected="selected"{/if}>Nie</option>
			</select>
		</div>

		<div class="select">
			<label for="remove_groups">Usuwanie grup</label>
			<select id="remove_groups" name="remove_groups">
				<option value="1" {if $frm_remove_groups}selected="selected"{/if}>Tak</option>
				<option value="0" {if !$frm_remove_groups}selected="selected"{/if}>Nie</option>
			</select>
		</div>

		<div class="select">
			<label for="change_groups_permissions">Zmiana uprawnień grup</label>
			<select id="change_groups_permissions" name="change_groups_permissions">
				<option value="1" {if $frm_change_groups_permissions}selected="selected"{/if}>Tak</option>
				<option value="0" {if !$frm_change_groups_permissions}selected="selected"{/if}>Nie</option>
			</select>
		</div>

		<div class="select">
			<label for="change_groups_level">Zmiana poziomu grup</label>
			<select id="change_groups_level" name="change_groups_level">
				<option value="1" {if $frm_change_groups_level}selected="selected"{/if}>Tak</option>
				<option value="0" {if !$frm_change_groups_level}selected="selected"{/if}>Nie</option>
			</select>
		</div>

		<div class="select">
			<label for="view_group_members">Lista członków grup</label>
			<select id="view_group_members" name="view_group_members">
				<option value="1" {if $frm_view_group_members}selected="selected"{/if}>Tak</option>
				<option value="0" {if !$frm_view_group_members}selected="selected"{/if}>Nie</option>
			</select>
		</div>

		<div class="select">
			<label for="add_group_members">Dodawanie członków grup</label>
			<select id="add_group_members" name="add_group_members">
				<option value="1" {if $frm_add_group_members}selected="selected"{/if}>Tak</option>
				<option value="0" {if !$frm_add_group_members}selected="selected"{/if}>Nie</option>
			</select>
		</div>

		<div class="select">
			<label for="remove_group_members">Usuwanie członków grup</label>
			<select id="remove_group_members" name="remove_group_members">
				<option value="1" {if $frm_remove_group_members}selected="selected"{/if}>Tak</option>
				<option value="0" {if !$frm_remove_group_members}selected="selected"{/if}>Nie</option>
			</select>
		</div>

	</fieldset>

	<fieldset>
		<legend>Galeria</legend>

		<div class="select">
			<label for="gallery_config">Ustawienia</label>
			<select id="gallery_config" name="gallery_config">
				<option value="1" {if $frm_gallery_config}selected="selected"{/if}>Tak</option>
				<option value="0" {if !$frm_gallery_config}selected="selected"{/if}>Nie</option>
			</select>
		</div>

		<div class="select">
			<label for="categories_list">Lista kategorii</label>
			<select id="categories_list" name="categories_list">
				<option value="1" {if $frm_categories_list}selected="selected"{/if}>Tak</option>
				<option value="0" {if !$frm_categories_list}selected="selected"{/if}>Nie</option>
			</select>
		</div>

		<div class="select">
			<label for="create_categories">Tworzenie kategorii</label>
			<select id="create_categories" name="create_categories">
				<option value="1" {if $frm_create_categories}selected="selected"{/if}>Tak</option>
				<option value="0" {if !$frm_create_categories}selected="selected"{/if}>Nie</option>
			</select>
		</div>

		<div class="select">
			<label for="edit_categories">Edycja kategorii</label>
			<select id="edit_categories" name="edit_categories">
				<option value="1" {if $frm_edit_categories}selected="selected"{/if}>Tak</option>
				<option value="0" {if !$frm_edit_categories}selected="selected"{/if}>Nie</option>
			</select>
		</div>

		<div class="select">
			<label for="remove_categories">Usuwanie kategorii</label>
			<select id="remove_categories" name="remove_categories">
				<option value="1" {if $frm_remove_categories}selected="selected"{/if}>Tak</option>
				<option value="0" {if !$frm_remove_categories}selected="selected"{/if}>Nie</option>
			</select>
		</div>

	</fieldset>

	<div class="buttons">
		<input type="submit" value="Zapisz" />
	</div>

</form>
