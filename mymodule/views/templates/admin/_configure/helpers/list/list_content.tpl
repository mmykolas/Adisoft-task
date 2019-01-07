{extends file="helpers/list/list_content.tpl"}

{block name="td_content"}
	{if isset($params.type) && $params.type == 'link'}
		<a href="{$tr.$key|escape:'html':'UTF-8'}" target="_blank">{$tr.$key|escape:'html':'UTF-8'}</a>
	{else}
		{$smarty.block.parent}
	{/if}
{/block}
