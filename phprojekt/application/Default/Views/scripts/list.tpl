<table align="center" border="1" width="100%">
<tr align="center">
{foreach name=titles item=showName from=$titles}
    <td>{$showName}</td>
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