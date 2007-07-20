{if $msg == ''}
    <form method="post" action="save">
    {$errors}
    <table border="1">
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
    <input type="submit">
    </form>
{else}
    {$msg}
{/if}
