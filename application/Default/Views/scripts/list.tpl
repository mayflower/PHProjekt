<table align="center" width="100%" class="listView">
<tr align="center">
{foreach name=titles item=field from=$titles}
    <th>{$field.formLabel|translate}</th>
{/foreach}
</tr>
{foreach name=lines item=line key=key from=$lines}
<tr>
    {foreach item=field from=$line}
    <td>
     <a href="{url action="edit" module=$module id=`$key`}">{$list->generateListElement("`$field`")}</a>
    </td>
    {/foreach}
</tr>
{/foreach}
</table>