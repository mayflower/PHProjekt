/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.xmpp.widget.ChatSession"]||(dojo._hasResource["dojox.xmpp.widget.ChatSession"]=!0,dojo.provide("dojox.xmpp.widget.ChatSession"),dojo.declare("dojox.xmpp.widget.ChatSession",[dijit.layout.LayoutContainer,dijit._Templated],{templateString:dojo.cache("dojox.xmpp.widget","templates/ChatSession.html",'<div>\n<div dojoAttachPoint="messages" dojoType="dijit.layout.ContentPane" layoutAlign="client" style="overflow:auto">\n</div>\n<div dojoType="dijit.layout.ContentPane" layoutAlign="bottom" style="border-top: 2px solid #333333; height: 35px;"><input dojoAttachPoint="chatInput" dojoAttachEvent="onkeypress: onKeyPress" style="width: 100%;height: 35px;" /></div>\n</div>\n'),
enableSubWidgets:!0,widgetsInTemplate:!0,widgetType:"ChatSession",chatWith:null,instance:null,postCreate:function(){},displayMessage:function(a){a&&(this.messages.domNode.innerHTML+="<b>"+(a.from?this.chatWith:"me")+":</b> "+a.body+"<br/>",this.goToLastMessage())},goToLastMessage:function(){this.messages.domNode.scrollTop=this.messages.domNode.scrollHeight},onKeyPress:function(a){if((a.keyCode||a.charCode)==dojo.keys.ENTER&&this.chatInput.value!="")this.instance.sendMessage({body:this.chatInput.value}),
this.displayMessage({body:this.chatInput.value},"out"),this.chatInput.value=""}}));