<table align="center" border="1">
<tr>
{foreach name=titles item=showName from=$titles}
    <td>{$showName}</td>
{/foreach}
</tr>
{foreach name=lines item=line from=$lines}
<tr>
    {foreach item=field from=$line}
    <td>{$field}</td>
    {/foreach}
</tr>
{/foreach}
</table>
