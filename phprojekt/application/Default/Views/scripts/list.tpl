<table align="center" width="100%" class="listView">
<tr align="center">
{foreach name=titles item=showName from=$titles}
    <th>{$showName}</th>
{/foreach}
</tr>
{foreach name=lines item=line key=key from=$lines}
<tr>
    {foreach item=field from=$line}
    <td>
     <a href="{url action="edit" module=$module id=`$key`}">{$field.value}</a>
    </td>
    {/foreach}
</tr>
{/foreach}
</table>