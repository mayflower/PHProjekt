<table width="100%" id="listViewTab" class="listView">
<tr align="center">
{foreach item=title from=$records|@titles}
  <th>{$title|translate}</th>
{/foreach}
</tr>

{foreach key=id item=record from=$records|@list_ordering}
<tr>
  {foreach item=field from=$record}
  <td>
  <a href="{url action="edit" module=$module id=`$id`}">
   {list_element field=`$field`}
  </a>
  </td>
  {/foreach}
</tr>
{/foreach}
</table>
