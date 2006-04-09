{* Smarty *}

<div class="pager">
Strona:
{foreach name=pager from=$pager item=item}
{if !$item.current}<a href="{$item.url}">{$item.page}</a>{else}{$item.page}{/if}{if !$smarty.foreach.pager.last} | {/if}
{/foreach}
</div>
