/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dijit._Contained"]||(dojo._hasResource["dijit._Contained"]=!0,dojo.provide("dijit._Contained"),dojo.declare("dijit._Contained",null,{getParent:function(){var a=dijit.getEnclosingWidget(this.domNode.parentNode);return a&&a.isContainer?a:null},_getSibling:function(a){var b=this.domNode;do b=b[a+"Sibling"];while(b&&b.nodeType!=1);return b&&dijit.byNode(b)},getPreviousSibling:function(){return this._getSibling("previous")},getNextSibling:function(){return this._getSibling("next")},
getIndexInParent:function(){var a=this.getParent();return!a||!a.getIndexOfChild?-1:a.getIndexOfChild(this)}}));