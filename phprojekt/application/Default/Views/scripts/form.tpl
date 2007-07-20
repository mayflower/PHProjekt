<br />
{$buttons}
{if $msg == ''}
    <form method="post" action="{$formAction}">
    {$errors}
    <table border="1" width="100%" align="center">
    {foreach name=fields item=field from=$fields}
    {if ($smarty.foreach.fields.iteration % $columns) == 1}
    <tr>
    {/if}
        <td>{$field}</td>
    {if ($smarty.foreach.fields.iteration % $columns) == 0 || $smarty.foreach.fields.last}
    <tr>
    {/if}
    {/foreach}
    </table>
    <br />
    <center><input type="submit" value="Send"></center>
    </form>
{else}
<center>{$msg}</center>
<br />
{/if}