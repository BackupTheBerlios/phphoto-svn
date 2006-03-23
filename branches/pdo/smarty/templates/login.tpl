{* Smarty *}

<form name="login_form" method="post" action="{$login_action}">
<input type="hidden" name="ref" value="{$ref|escape}" />
<input type="hidden" name="submit" value="1" />
Login: <input type="text" name="user_login" /><br />
Hasło: <input type="password" name="user_pass" /><br />
<br /><br />
<input type="submit" value="Zaloguj" />
</form>

