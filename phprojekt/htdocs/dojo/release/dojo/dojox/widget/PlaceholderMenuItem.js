/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.widget.PlaceholderMenuItem"]||(dojo._hasResource["dojox.widget.PlaceholderMenuItem"]=!0,dojo.provide("dojox.widget.PlaceholderMenuItem"),dojo.experimental("dojox.widget.PlaceholderMenuItem"),dojo.require("dijit.Menu"),dojo.declare("dojox.widget.PlaceholderMenuItem",dijit.MenuItem,{_replaced:!1,_replacedWith:null,_isPlaceholder:!0,postCreate:function(){this.domNode.style.display="none";this._replacedWith=[];if(!this.label)this.label=this.containerNode.innerHTML;this.inherited(arguments)},
replace:function(c){if(this._replaced)return!1;var b=this.getIndexInParent();if(b<0)return!1;var d=this.getParent();dojo.forEach(c,function(a){d.addChild(a,b++)});this._replacedWith=c;return this._replaced=!0},unReplace:function(c){if(!this._replaced)return[];var b=this.getParent();if(!b)return[];var d=this._replacedWith;dojo.forEach(this._replacedWith,function(a){b.removeChild(a);c&&a.destroyRecursive()});this._replacedWith=[];this._replaced=!1;return d}}),dojo.extend(dijit.Menu,{getPlaceholders:function(c){var b=
[],d=this.getChildren();dojo.forEach(d,function(a){a._isPlaceholder&&(!c||a.label==c)?b.push(a):a._started&&a.popup&&a.popup.getPlaceholders?b=b.concat(a.popup.getPlaceholders(c)):!a._started&&a.dropDownContainer&&(a=dojo.query("[widgetId]",a.dropDownContainer)[0],a=dijit.byNode(a),a.getPlaceholders&&(b=b.concat(a.getPlaceholders(c))))},this);return b}}));