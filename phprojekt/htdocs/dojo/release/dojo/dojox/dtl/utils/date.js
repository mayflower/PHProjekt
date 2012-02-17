/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.dtl.utils.date"])dojo._hasResource["dojox.dtl.utils.date"]=!0,dojo.provide("dojox.dtl.utils.date"),dojo.require("dojox.date.php"),dojox.dtl.utils.date.DateFormat=function(a){dojox.date.php.DateFormat.call(this,a)},dojo.extend(dojox.dtl.utils.date.DateFormat,dojox.date.php.DateFormat.prototype,{f:function(){return!this.date.getMinutes()?this.g():this.g()+":"+this.i()},N:function(){return dojox.dtl.utils.date._months_ap[this.date.getMonth()]},P:function(){return!this.date.getMinutes()&&
!this.date.getHours()?"midnight":!this.date.getMinutes()&&this.date.getHours()==12?"noon":this.f()+" "+this.a()}}),dojo.mixin(dojox.dtl.utils.date,{format:function(a,b){return(new dojox.dtl.utils.date.DateFormat(b)).format(a)},timesince:function(a,b){a instanceof Date||(a=new Date(a.year,a.month,a.day));b||(b=new Date);for(var f=Math.abs(b.getTime()-a.getTime()),e=0,c;c=dojox.dtl.utils.date._chunks[e];e++){var d=Math.floor(f/c[0]);if(d)break}return d+" "+c[1](d)},_chunks:[[31536E6,function(a){return a==
1?"year":"years"}],[2592E6,function(a){return a==1?"month":"months"}],[6048E5,function(a){return a==1?"week":"weeks"}],[864E5,function(a){return a==1?"day":"days"}],[36E5,function(a){return a==1?"hour":"hours"}],[6E4,function(a){return a==1?"minute":"minutes"}]],_months_ap:["Jan.","Feb.","March","April","May","June","July","Aug.","Sept.","Oct.","Nov.","Dec."]});