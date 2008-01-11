<table border='0' class="listView">
{foreach key=id item=module from=$adminModules}
	<tr>
		<td>
  			<a href="{url action="show" controller="admin" module=`$module->module`}">{$module->niceName|translate}</a>
  		</td>
	</tr>
{/foreach}
</table>