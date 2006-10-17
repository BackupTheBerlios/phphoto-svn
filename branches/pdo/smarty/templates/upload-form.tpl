{* Smarty *}

<form name="upload-form" method="post" enctype="multipart/form-data" action="{$upload_action|escape}">
<fieldset id="upload-form">
<input type="hidden" name="MAX_FILE_SIZE" value="{$max_file_size}" />
<input type="hidden" name="ref" value="{$ref|escape}" />
<input type="hidden" name="submit" value="1" />


Plik: <input type="file" name="file" /><br />
Tytul: <input type="text" name="title" /><br />
Opis: <textarea name="description" rows="4" cols="50"></textarea><br />
Kategoria:
<div id="category-0" class="category-tree"></div>
<br /><br />
<div style="clear: both;">
<input type="submit" id="submit-button" value="Wyślij" />
</div>
</fieldset>
</form>

