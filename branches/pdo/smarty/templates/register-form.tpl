{* Smarty *}

<form name="register_form" method="post" action="{$register_action}">
<input type="hidden" name="ref" value="{$ref|escape}" />
<input type="hidden" name="submit" value="1" />
Login: <input type="text" name="user_login" value="{$login|escape}" /><br />
Has�o: <input type="password" name="user_pass1" /><br />
Powt�rz has�o: <input type="password" name="user_pass2" /><br />
Email: <input type="text" name="user_email" value="{$email|escape}" /><br />
<br /><br />
<input type="submit" value="Zarejestruj" />
</form>

