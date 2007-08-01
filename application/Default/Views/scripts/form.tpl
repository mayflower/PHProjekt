<br />
<a href="{url action="display"}">{"Add"|translate}</a>
<a href="{url action="delete" id=$currentId}">{"Delete"|translate}</a>
{if $message == ''}
{if $action == 'add'}
    <form method="post" action="{url action='save'} id=''">
{else}
    <form method="post" action="{url action='save' id=$currentId}">
{/if}
    {foreach name=errors item=error from=$errors}
    <span class="error">{$error.field|translate}: {$error.message|translate}</span><br />
    {/foreach}
    <table width="100%" align="center">
    {foreach name=fields item=field from=$fields}
    <tr>
        <td>{$field.formLabel|translate}</td>
        <td>{$view->formText("`$field.formLabel`", "`$field.value`")}
        {if $field.isRequired}
        <span class="error">*</span>
        {/if}
        </td>
    <tr>
    {/foreach}
    </table>
    <br />
    <input type="submit" value="Send">
    </form>
{else}
<center>{$message|translate}</center>
<br />
{/if}