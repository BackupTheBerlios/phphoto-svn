{* Smarty *}

{include file="error.tpl"}

<form name="upload_form" method="post" enctype="multipart/form-data" action="upload.php">
-<input type="hidden" name="MAX_FILE_SIZE" value="{$max_file_size}" />
<input type="hidden" name="ref" value="{$ref|escape}" />
<input type="hidden" name="submit" value="1" />
{$l_file} <input type="file" name="file" /><br />
{$l_title} <input type="text" name="title" /><br />
{$l_description} <textarea name="description" rows="4" cols="50"></textarea><br />
{$l_category}
<select name="cid[]" size="10" multiple="multiple">
{foreach from=$categories item=item key=key}
<option value="{$key}">{$item|escape}</option>
{/foreach}
</select>
<br /><br />
<input type="submit" value="{$l_upload}" />
</form>

