/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.grid.enhanced.plugins.Dialog"]||(dojo._hasResource["dojox.grid.enhanced.plugins.Dialog"]=!0,dojo.provide("dojox.grid.enhanced.plugins.Dialog"),dojo.require("dijit.Dialog"),dojo.require("dojo.window"),dojo.declare("dojox.grid.enhanced.plugins.Dialog",dijit.Dialog,{refNode:null,_position:function(){if(this.refNode&&!this._relativePosition){var a=dojo.position(dojo.byId(this.refNode)),c=dojo.position(this.domNode),b=dojo.window.getBox();if(a.x<0)a.x=0;if(a.x+a.w>b.w)a.w=b.w-
a.x;if(a.y<0)a.y=0;if(a.y+a.h>b.h)a.h=b.h-a.y;a.x=a.x+a.w/2-c.w/2;a.y=a.y+a.h/2-c.h/2;if(a.x>=0&&a.x+c.w<=b.w&&a.y>=0&&a.y+c.h<=b.h)this._relativePosition=a}this.inherited(arguments)}}));