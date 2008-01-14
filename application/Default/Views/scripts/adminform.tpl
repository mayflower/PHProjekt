
{if $message == ''}
  {if $action == 'add'}
  <form method="post" action="{url action='save'} id=''">
  {else}
  <form method="post" action="{url action='save' id=$itemid}">
  {/if}
  {foreach name=error item=error from=$errors}
  <div class="error">{$error.field|translate}: {$error.message|translate}</div>
  {/foreach}
  <br />

  <input type="hidden" name="id" value="{$record->id}" />
  <table width="90%">
  {foreach item=field from=$record|form_ordering}
   <tr>
    <td width="25%">{$field.label|translate}</td>
    <td>
     {form_element field=$field}
     {if $field.required}<span class="error">*</span>{/if}
    </td>
   </tr>
  {/foreach}
  <tr>
    <td></td>
    <td><input type="submit" value="{"Save"|translate}" /></td>
  </tr>
  </table>

  {foreach name=history item=history from=$historyData}
  {if $smarty.foreach.history.first}
  <div align="center">
  <a href="#" onclick="displayBlock('historyTable')">{"History"|translate}</a>
  </div>
  <div id="historyTable" style="display:none">
  <table width="100%">
  <tr>
    <td>{"User"|translate}</td>
    <td>{"Module"|translate}</td>
    <td>{"Field"|translate}</td>
    <td>{"OldValue"|translate}</td>
    <td>{"NewValue"|translate}</td>
    <td>{"Action"|translate}</td>
    <td>{"Datetime"|translate}</td>
  </tr>
  {/if}
  <tr>
    <td>{$list->generateListElement("`$userFieldData`","`$history.userId`")}</td>
    <td>{$history.module}</td>
    <td>{$fields[$history.field].formLabel|translate}</td>
    <td>{$list->generateListElement("`$fields[$history.field]`","`$history.oldValue`")}</td>
    <td>{$list->generateListElement("`$fields[$history.field]`","`$history.newValue`")}</td>
    <td>{$history.action|translate}</td>
    <td>{$list->generateListElement("`$dateFieldData`","`$history.datetime`")}</td>
  </tr>
  {/foreach}
  {if $smarty.foreach.history.last}
  </table>
  </div>
  {/if}
{else}
<div style="text-align:center">{$message|translate}</div>
<br />
{/if}
<br />