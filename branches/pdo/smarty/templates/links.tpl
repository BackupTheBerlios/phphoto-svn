{* Smarty *}
{* $Id$ *}

{foreach from=$_links item=link}
	<link {foreach from=$link item=value key=name}{$name}="{$value|escape}" {/foreach}/>
{/foreach}
