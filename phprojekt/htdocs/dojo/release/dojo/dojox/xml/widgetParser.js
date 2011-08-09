/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.xml.widgetParser"])dojo._hasResource["dojox.xml.widgetParser"]=!0,dojo.provide("dojox.xml.widgetParser"),dojo.require("dojox.xml.parser"),dojo.require("dojo.parser"),dojox.xml.widgetParser=new function(){var d=dojo;this.parseNode=function(a){var b=[];d.query("script[type='text/xml']",a).forEach(function(a){b.push.apply(b,this._processScript(a))},this).orphan();return d.parser.instantiate(b)};this._processScript=function(a){var b=a.src?d._getText(a.src):a.innerHTML||a.firstChild.nodeValue,
b=this.toHTML(dojox.xml.parser.parse(b).firstChild),c=d.query("[dojoType]",b);dojo.query(">",b).place(a,"before");a.parentNode.removeChild(a);return c};this.toHTML=function(a){var b,c=a.nodeName,e=dojo.doc,f=a.nodeType;if(f>=3)return e.createTextNode(f==3||f==4?a.nodeValue:"");var g=a.localName||c.split(":").pop(),c=a.namespaceURI||(a.getNamespaceUri?a.getNamespaceUri():"");c=="html"?b=e.createElement(g):(c=c+"."+g,b=b||e.createElement(c=="dijit.form.ComboBox"?"select":"div"),b.setAttribute("dojoType",
c));d.forEach(a.attributes,function(a){var c=a.name||a.nodeName,a=a.value||a.nodeValue;c.indexOf("xmlns")!=0&&(dojo.isIE&&c=="style"?b.style.setAttribute("cssText",a):b.setAttribute(c,a))});d.forEach(a.childNodes,function(a){a=this.toHTML(a);g=="script"?b.text+=a.nodeValue:b.appendChild(a)},this);return b}};