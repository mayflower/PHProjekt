{foreach item=node from=$tree}
 {assign var="depth" value=$node->getDepth()}
 {""|indent:$depth*2:"&nbsp;"}
 <a href="{link_to action="toggleNode" tree=$treeIdentifier treeid=$node->id}">+</a>
 {$node->title}<br />
{/foreach}