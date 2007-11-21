<form method="post" action="{url action='addFilter'}" id=''">
<select name="filterField" id="filterField">
{foreach item=title from=$fields}
  <option value="{$title}">{$title|translate}</option>
{/foreach}
</select>

<select name="filterRule" id="filterRule">
  <option value="like">Like</option>
  <option value="exact">Exact</option>
  <option value="begins">Begins</option>
  <option value="ends">End</option>
  <option value="mayor">></option>
  <option value="mayorequal">>=</option>
  <option value="minor"><</option>
  <option value="minorequal"><=</option>
  <option value="not like">Not Like</option>
</select>

<input type="text" name="filterText" id="filterText" />
<input type="submit" value="{"Search"|translate}" />
</form>
<br />
{foreach name=filters item=filter from=$filters}
<a href="{url action="deleteFilter" module=$module filterId=`$filter.id`}">{filter_element field=`$filter.field` rule=`$filter.rule` text=`$filter.text`}</a>
{if $smarty.foreach.filters.last}
<a href="{url action="deleteFilter" module=$module filterId=-1}">{"Delete all filter"|translate}</a>
{/if}
{/foreach}