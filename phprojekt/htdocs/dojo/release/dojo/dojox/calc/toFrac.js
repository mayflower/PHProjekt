/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.calc.toFrac"]||(dojo._hasResource["dojox.calc.toFrac"]=!0,dojo.provide("dojox.calc.toFrac"),function(){function h(c){for(var b,a;e<i.length;){switch(e){case -3:b=1;a="";break;case -2:b=Math.PI;a="pi";break;case -1:b=Math.sqrt(Math.PI);a="\u221a(pi)";break;default:b=Math.sqrt(i[e]),a="\u221a("+i[e]+")"}for(;d<=100;){for(n=1;n<(b==1?d:100);n++){var j=dojox.calc.approx(b*n/d);j in f||(n==d&&(d=n=1),f[j]={n:n,d:d,m:b,mt:a},j==c&&(c=void 0))}d++;if(c==void 0){setTimeout(function(){h()},
1);return}}d=2;e++}g=!0}function k(c){function b(){h(c);return k(c)}var c=Math.abs(c),a=f[dojox.calc.approx(c)];if(!a&&!g)return b();if(!a){var d=Math.floor(c);if(d==0)return g?null:b();var e=c%1;if(e==0)return{m:1,mt:1,n:c,d:1};a=f[dojox.calc.approx(e)];return!a||a.m!=1?(a=dojox.calc.approx(1/e),Math.floor(a)==a?{m:1,mt:1,n:1,d:a}:g?null:b()):{m:1,mt:1,n:d*a.d+a.n,d:a.d}}return a}var f=[],i=[2,3,5,6,7,10,11,13,14,15,17,19,21,22,23,26,29,30,31,33,34,35,37,38,39,41,42,43,46,47,51,53,55,57,58,59,61,
62,65,66,67,69,70,71,73,74,77,78,79,82,83,85,86,87,89,91,93,94,95,97],g=!1,e=-3,d=2;h();dojo.mixin(dojox.calc,{toFrac:function(c){var b=k(c);return b?(c<0?"-":"")+(b.m==1?"":b.n==1?"":b.n+"*")+(b.m==1?b.n:b.mt)+(b.d==1?"":"/"+b.d):c},pow:function(c,b){if(c>0||Math.floor(b)==b)return Math.pow(c,b);else{var a=k(b);return c>=0?a&&a.m==1?Math.pow(Math.pow(c,1/a.d),b<0?-a.n:a.n):Math.pow(c,b):a&&a.d&1?Math.pow(Math.pow(-Math.pow(-c,1/a.d),b<0?-a.n:a.n),a.m):NaN}}})}());