<table width="100%" class="listView">
<tr align="center">
{foreach item=title from=$records|@titles}
  <th>{$title}</th>
{/foreach}
</tr>

{foreach item=record from=$records}
<tr>
  {foreach item=field key=fieldname from=$record}
  <td>
  <a href="{url action="edit" module=$module id=`$record->id`}">
   {list_element field=`$fieldname` value=`$field`}
  </a>
  </td>
  {/foreach}
</tr>
{/foreach}
</table>
