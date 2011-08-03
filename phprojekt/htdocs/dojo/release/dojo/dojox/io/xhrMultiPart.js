/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.io.xhrMultiPart"]||(dojo._hasResource["dojox.io.xhrMultiPart"]=!0,dojo.provide("dojox.io.xhrMultiPart"),dojo.require("dojox.uuid.generateRandomUuid"),function(){function f(a,e){if(!a.name&&!a.content)throw Error("Each part of a multi-part request requires 'name' and 'content'.");var b=[];b.push("--"+e,'Content-Disposition: form-data; name="'+a.name+'"'+(a.filename?'; filename="'+a.filename+'"':""));if(a.contentType){var c="Content-Type: "+a.contentType;a.charset&&(c+="; Charset="+
a.charset);b.push(c)}a.contentTransferEncoding&&b.push("Content-Transfer-Encoding: "+a.contentTransferEncoding);b.push("",a.content);return b}function g(a,e){var b=dojo.formToObject(a),c=[],d;for(d in b)dojo.isArray(b[d])?dojo.forEach(b[d],function(a){c=c.concat(f({name:d,content:a},e))}):c=c.concat(f({name:d,content:b[d]},e));return c}dojox.io.xhrMultiPart=function(a){if(!a.file&&!a.content&&!a.form)throw Error("content, file or form must be provided to dojox.io.xhrMultiPart's arguments");var e=
dojox.uuid.generateRandomUuid(),b=[],c="";if(a.file||a.content){var d=a.file||a.content;dojo.forEach(dojo.isArray(d)?d:[d],function(a){b=b.concat(f(a,e))})}else if(a.form){if(dojo.query("input[type=file]",a.form).length)throw Error("dojox.io.xhrMultiPart cannot post files that are values of an INPUT TYPE=FILE.  Use dojo.io.iframe.send() instead.");b=g(a.form,e)}b.length&&(b.push("--"+e+"--",""),c=b.join("\r\n"));console.log(c);return dojo.rawXhrPost(dojo.mixin(a,{contentType:"multipart/form-data; boundary="+
e,postData:c}))}}());