<table align="center" width="100%" class="listView">
<tr align="center">
{foreach name=titles item=showName from=$titles}
    <th>{$showName}</th>
{/foreach}
</tr>
{foreach name=lines item=line from=$lines}
<tr>
    {foreach item=field from=$line}
    <td>&nbsp;&nbsp;{$field}</td>
    {/foreach}
</tr>
{/foreach}
</table>