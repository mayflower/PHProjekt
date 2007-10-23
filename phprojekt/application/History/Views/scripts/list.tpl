<table width="100%" class="listView">
<tr align="center">
{foreach item=title from=$records|@titles}
  <th>{$title}</th>
{/foreach}
</tr>
{foreach item=record from=$records|list_ordering}
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

<br />
{if ( ($nextPage < $howManyRows) || ($previousPage >= 0) )}
<table align="center" cellpadding="2" cellspacing="2" border="0">
<tr align="center">
  {if ($previousPage >= 0)}
  <td align="center" valign="middle">
  <a class="page_result" href="{url action="list" module=$module page=0}"> |< </a></td>
  <td align="center" valign="middle">
  <a class="page_result" href="{url action="list" module=$module page=$previousPage}"> << </a></td>
  {else}
  <td align="center" valign="middle">&nbsp;</td>
  {/if}

  <td align="center" valign="middle">
  {foreach name=page item=page from=$pages}
    <a href="{url action="list" module=$module page=$page.position}">{$page.number}</a>&nbsp;
  {/foreach}
  </td>

  {if ($nextPage < $howManyRows)}
  <td align="center" valign="middle">
  <a class="page_result" href="{url action="list" module=$module page=$nextPage}"> >> </a></td>
  <td align="center" valign="middle">
  <a class="page_result" href="{url action="list" module=$module page=$lastPage}"> >| </a></td>
  {else}
  <td align="center" valign="middle">&nbsp;</td>
  {/if}
</tr>
</table>
{/if}