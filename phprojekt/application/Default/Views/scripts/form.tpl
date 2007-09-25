<br />
<a href="{url action="display"}">{"Add"|translate}</a>
<a href="{url action="delete" id=$itemid}">{"Delete"|translate}</a>
<br />
<br />
{if $message == ''}
  {if $action == 'add'}
  <form method="post" action="{url action='save'} id=''">
  {else}
  <form method="post" action="{url action='save' id=$itemid}">
  {/if}
  {foreach name=errors item=error from=$errors}
  <div class="error">{$error.field|translate}: {$error.message|translate}</div>
  {/foreach}
  <br />
  <table width="100%">
  {assign var="fields" value=$form->generateFormElement("`$model`", "`$params`")}
  {foreach name=field item=field from=$fields}
  <tr>
    <td valign="top">{$field.label}</td>
    <td>{$field.output}
    {if $field.isRequired}
    <span class="error">*</span>
    {/if}
    </td>
  </tr>
  {/foreach}
  </table>
  <br />
  <input type="submit" value="Send">
  </form>

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