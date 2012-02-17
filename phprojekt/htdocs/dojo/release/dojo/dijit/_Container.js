/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dijit._Container"]||(dojo._hasResource["dijit._Container"]=!0,dojo.provide("dijit._Container"),dojo.declare("dijit._Container",null,{isContainer:!0,buildRendering:function(){this.inherited(arguments);if(!this.containerNode)this.containerNode=this.domNode},addChild:function(a,b){var c=this.containerNode;if(b&&typeof b=="number"){var d=this.getChildren();if(d&&d.length>=b)c=d[b-1].domNode,b="after"}dojo.place(a.domNode,c,b);this._started&&!a._started&&a.startup()},removeChild:function(a){typeof a==
"number"&&(a=this.getChildren()[a]);if(a)(a=a.domNode)&&a.parentNode&&a.parentNode.removeChild(a)},hasChildren:function(){return this.getChildren().length>0},destroyDescendants:function(a){dojo.forEach(this.getChildren(),function(b){b.destroyRecursive(a)})},_getSiblingOfChild:function(a,b){var c=a.domNode,d=b>0?"nextSibling":"previousSibling";do c=c[d];while(c&&(c.nodeType!=1||!dijit.byNode(c)));return c&&dijit.byNode(c)},getIndexOfChild:function(a){return dojo.indexOf(this.getChildren(),a)},startup:function(){this._started||
(dojo.forEach(this.getChildren(),function(a){a.startup()}),this.inherited(arguments))}}));