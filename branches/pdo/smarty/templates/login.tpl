{* Smarty *}

<form id="login_form" name="login_form" method="post" action="{$login_action}">
<input type="hidden" name="ref" value="{$ref|escape}" />
<input type="hidden" name="submit" value="1" />
<label for="user_login">Login:</label><input id="user_login" type="text" name="user_login" /><br />
<label for="user_pass">Hasło:</label><input id="user_pass" type="password" name="user_pass" /><br />
<br /><br />
<input type="submit" value="Zaloguj" />
</form>

