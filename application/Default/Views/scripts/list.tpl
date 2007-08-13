<table width="100%" class="listView">
<tr align="center">
{foreach name=titles item=field from=$titles}
  <th>{$field.formLabel|translate}</th>
{/foreach}
</tr>
{foreach name=lines item=line key=key from=$lines}
<tr>
  {assign var="row" value=$line->getFieldsForList()}
  {foreach item=field key=fieldname from=$row}
  <td>
  <a href="{url action="edit" module=$module id=`$line->id`}">{$list->generateListElement("`$field`","`$line->$fieldname`")}</a>
  </td>
  {/foreach}
</tr>
{/foreach}
</table>