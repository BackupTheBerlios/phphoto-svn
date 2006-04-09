{* Smarty *}
{* $Id$ *}

{if $messages_count > 0 }
<div id="messages">
	{foreach from=$messages item=msg}
		<div class="{$msg.class}">
			{if $msg.title}<div class="title">{$msg.title|escape}</div>{/if}
			{if $msg.body}<div class="body">{$msg.body|escape|nl2br}</div>{/if}
			{if $msg.trace_available}
				<div class="trace">
					<code>
					{$msg.trace_str|escape|nl2br}
					</code>
				</div>
			{/if}
		</div>
	{/foreach}
</div>
{/if}

