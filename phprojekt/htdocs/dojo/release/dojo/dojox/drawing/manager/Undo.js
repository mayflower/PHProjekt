/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.drawing.manager.Undo"])dojo._hasResource["dojox.drawing.manager.Undo"]=!0,dojo.provide("dojox.drawing.manager.Undo"),dojox.drawing.manager.Undo=dojox.drawing.util.oo.declare(function(a){this.keys=a.keys;this.undostack=[];this.redostack=[];dojo.connect(this.keys,"onKeyDown",this,"onKeyDown")},{onKeyDown:function(a){a.cmmd&&(a.keyCode==90&&!a.shift?this.undo():(a.keyCode==90&&a.shift||a.keyCode==89)&&this.redo())},add:function(a){a.args=dojo.mixin({},a.args);this.undostack.push(a)},
apply:function(a,b,c){dojo.hitch(a,b)(c)},undo:function(){var a=this.undostack.pop();console.log("undo!",a);a&&(a.before(),this.redostack.push(a))},redo:function(){console.log("redo!");var a=this.redostack.pop();a&&(a.after?a.after():a.before(),this.undostack.push(a))}});