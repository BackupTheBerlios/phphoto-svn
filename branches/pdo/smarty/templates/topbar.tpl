{* Smarty *}
{* $Id$ *}

<div id="topbar">
{if $logged_in}
Zalogowany jako <strong>{$logged_user_login|escape}</strong> |
{if !$admin_panel}
<a href="{url action="admin"}">Admin</a> |
{/if}
<a href="{url action="logout"}">Logout</a>
{else}
<a href="{url action="login"}">Login</a><br />
{/if}
</div>

