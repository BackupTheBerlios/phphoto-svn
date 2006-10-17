{* Smarty *}
{* $Id$ *}

<script type="text/javascript" charset="utf-8">
	// <![CDATA[
	var _ajax_service_base_url = "{$base_service_url}";
	var _base_url = "{$base_url}";
	var _ajax_http_method = "{$ajax_http_method}";
	var _ref_url = "{$ref}";
	var _self_url = "{$self}";
	// ]]>
</script>

{foreach from=$_scripts item=script}
	<script type="{$script.type}" src="{$script.src}"></script>
{/foreach}
