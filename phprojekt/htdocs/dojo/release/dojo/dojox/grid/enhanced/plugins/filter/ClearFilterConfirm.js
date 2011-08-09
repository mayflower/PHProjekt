/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.grid.enhanced.plugins.filter.ClearFilterConfirm"]||(dojo._hasResource["dojox.grid.enhanced.plugins.filter.ClearFilterConfirm"]=!0,dojo.provide("dojox.grid.enhanced.plugins.filter.ClearFilterConfirm"),dojo.require("dijit.form.Button"),dojo.declare("dojox.grid.enhanced.plugins.filter.ClearFilterConfirm",[dijit._Widget,dijit._Templated],{templateString:dojo.cache("dojox.grid","enhanced/templates/ClearFilterConfirmPane.html",'<div class="dojoxGridClearFilterConfirm">\n\t<div class="dojoxGridClearFilterMsg">\n\t\t${_clearFilterMsg}\n\t</div>\n\t<div class="dojoxGridClearFilterBtns" dojoAttachPoint="btnsNode">\n\t\t<span dojoType="dijit.form.Button" label="${_cancelBtnLabel}" dojoAttachPoint="cancelBtn" dojoAttachEvent="onClick:_onCancel"></span>\n\t\t<span dojoType="dijit.form.Button" label="${_clearBtnLabel}" dojoAttachPoint="clearBtn" dojoAttachEvent="onClick:_onClear"></span>\n\t</div>\n</div>\n'),
widgetsInTemplate:!0,plugin:null,postMixInProperties:function(){var a=this.plugin.nls;this._clearBtnLabel=a.clearButton;this._cancelBtnLabel=a.cancelButton;this._clearFilterMsg=a.clearFilterMsg},postCreate:function(){this.inherited(arguments);dijit.setWaiState(this.cancelBtn.domNode,"label",this.plugin.nls.waiCancelButton);dijit.setWaiState(this.clearBtn.domNode,"label",this.plugin.nls.waiClearButton)},uninitialize:function(){this.plugin=null},_onCancel:function(){this.plugin.clearFilterDialog.hide()},
_onClear:function(){this.plugin.clearFilterDialog.hide();this.plugin.filterDefDialog.clearFilter(this.plugin.filterDefDialog._clearWithoutRefresh)}}));