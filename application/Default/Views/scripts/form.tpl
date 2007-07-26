<br />
<a href="{url action="display" controller="form"}">{"Add"|translate}</a>
<a href="{url action="delete" controller="form" id=$currentId}">{"Delete"|translate}</a>
{if $message == ''}
{if $action == 'add'}
    <form method="post" action="{url action='save'} id=''">
{else}
    <form method="post" action="{url action='save' id=$currentId}">
{/if}
    {$errors}
    <table width="100%" align="center">
    {foreach name=fields item=field from=$fields}
    <tr>
        <td>{$field.formLabel|translate}</td>
        <td>{$view->formText("`$field.formLabel`", "`$field.value`")}</td>
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