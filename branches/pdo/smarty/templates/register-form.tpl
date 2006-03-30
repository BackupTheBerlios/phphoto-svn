{* Smarty *}
{* $Id$ *}

<form id="register_form" name="register_form" method="post" action="{$register_action}">
<input type="hidden" name="ref" value="{$ref|escape}" />
<input type="hidden" name="submit" value="1" />
<label for="user_login">Login:</label><input id="user_login" type="text" name="user_login" value="{$login|escape}" /><br />
<label for="user_pass1">Hasło:</label><input id="user_pass1" type="password" name="user_pass1" /><br />
<label for="user_pass2">Powtórz hasło:</label><input id="user_pass2" type="password" name="user_pass2" /><br />
<label for="user_email">Email:</label><input id="user_email" type="text" name="user_email" value="{$email|escape}" /><br />
<br /><br />
<input type="submit" value="Zarejestruj" />
</form>

