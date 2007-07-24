<br />
{$buttons}
{if $msg == ''}
    <form method="post" action="{$formAction}">
    {$errors}
    <table width="100%" align="center">
    {foreach name=fields item=field from=$fields}
    <tr>
        <td>{$field.description}</td>
        <td>{$field.field}</td>
    <tr>
    {/foreach}
    </table>
    <br />
    <input type="submit" value="Send">
    </form>
{else}
<center>{$msg}</center>
<br />
{/if}