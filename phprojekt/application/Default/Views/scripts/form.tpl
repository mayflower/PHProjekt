<br />
<a href="{url action="display"}">{"Add"|translate}</a>
<a href="{url action="delete" id=$currentId}">{"Delete"|translate}</a>
<br /><br />
{if $message == ''}
{if $action == 'add'}
    <form method="post" action="{url action='save'} id=''">
{else}
    <form method="post" action="{url action='save' id=$currentId}">
{/if}
    {foreach name=errors item=error from=$errors}
    <div class="error">{$error.field|translate}: {$error.message|translate}</div>
    {/foreach}
    <br />
    <table width="100%" style="align:center">
    {foreach name=fields item=field from=$fields}
    <tr>
        <td valign="top">{$field.formLabel|translate}</td>
        <td>{$form->generateFormElement("`$field`")}
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
{else}
<div style="text-align:center">{$message|translate}</div>
<br />
{/if}
<br />