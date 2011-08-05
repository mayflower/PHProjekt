/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dijit.form.SimpleTextarea"]||(dojo._hasResource["dijit.form.SimpleTextarea"]=!0,dojo.provide("dijit.form.SimpleTextarea"),dojo.require("dijit.form.TextBox"),dojo.declare("dijit.form.SimpleTextarea",dijit.form.TextBox,{baseClass:"dijitTextBox dijitTextArea",attributeMap:dojo.delegate(dijit.form._FormValueWidget.prototype.attributeMap,{rows:"textbox",cols:"textbox"}),rows:"3",cols:"20",templateString:"<textarea ${!nameAttrSetting} dojoAttachPoint='focusNode,containerNode,textbox' autocomplete='off'></textarea>",
postMixInProperties:function(){if(!this.value&&this.srcNodeRef)this.value=this.srcNodeRef.value;this.inherited(arguments)},buildRendering:function(){this.inherited(arguments);dojo.isIE&&this.cols&&dojo.addClass(this.textbox,"dijitTextAreaCols")},filter:function(c){c&&(c=c.replace(/\r/g,""));return this.inherited(arguments)},_previousValue:"",_onInput:function(c){if(this.maxLength){var a=parseInt(this.maxLength),b=this.textbox.value.replace(/\r/g,""),a=b.length-a;if(a>0){c&&dojo.stopEvent(c);var e=
this.textbox;if(e.selectionStart){var d=e.selectionStart,f=0;if(dojo.isOpera)f=(this.textbox.value.substring(0,d).match(/\r/g)||[]).length;this.textbox.value=b.substring(0,d-a-f)+b.substring(d-f);e.setSelectionRange(d-a,d-a)}else if(dojo.doc.selection)e.focus(),b=dojo.doc.selection.createRange(),b.moveStart("character",-a),b.text="",b.select()}this._previousValue=this.textbox.value}this.inherited(arguments)}}));