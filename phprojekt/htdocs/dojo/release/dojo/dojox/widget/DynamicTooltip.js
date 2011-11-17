/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.widget.DynamicTooltip"]||(dojo._hasResource["dojox.widget.DynamicTooltip"]=!0,dojo.provide("dojox.widget.DynamicTooltip"),dojo.experimental("dojox.widget.DynamicTooltip"),dojo.require("dijit.Tooltip"),dojo.requireLocalization("dijit","loading",null,"ROOT,ar,ca,cs,da,de,el,es,fi,fr,he,hu,it,ja,kk,ko,nb,nl,pl,pt,pt-pt,ro,ru,sk,sl,sv,th,tr,zh,zh-tw"),dojo.declare("dojox.widget.DynamicTooltip",dijit.Tooltip,{hasLoaded:!1,href:"",label:"",preventCache:!1,postMixInProperties:function(){this.inherited(arguments);
this._setLoadingLabel()},_setLoadingLabel:function(){if(this.href)this.label=dojo.i18n.getLocalization("dijit","loading",this.lang).loadingState},_setHrefAttr:function(a){this.href=a;this.hasLoaded=!1},loadContent:function(a){if(!this.hasLoaded&&this.href)this._setLoadingLabel(),this.hasLoaded=!0,dojo.xhrGet({url:this.href,handleAs:"text",tooltipWidget:this,load:function(b){this.tooltipWidget.label=b;this.tooltipWidget.close();this.tooltipWidget.open(a)},preventCache:this.preventCache})},refresh:function(){this.hasLoaded=
!1},open:function(a){if(a=a||this._connectNodes&&this._connectNodes[0])this.loadContent(a),this.inherited(arguments)}}));