/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dijit.MenuBar"]||(dojo._hasResource["dijit.MenuBar"]=!0,dojo.provide("dijit.MenuBar"),dojo.require("dijit.Menu"),dojo.declare("dijit.MenuBar",dijit._MenuBase,{templateString:dojo.cache("dijit","templates/MenuBar.html",'<div class="dijitMenuBar dijitMenuPassive" dojoAttachPoint="containerNode"  role="menubar" tabIndex="${tabIndex}" dojoAttachEvent="onkeypress: _onKeyPress"></div>\n'),baseClass:"dijitMenuBar",_isMenuBar:!0,postCreate:function(){var a=dojo.keys,b=this.isLeftToRight();
this.connectKeyNavHandlers(b?[a.LEFT_ARROW]:[a.RIGHT_ARROW],b?[a.RIGHT_ARROW]:[a.LEFT_ARROW]);this._orient=this.isLeftToRight()?{BL:"TL"}:{BR:"TR"}},focusChild:function(a){var b=this.focusedChild,b=b&&b.popup&&b.popup.isShowingNow;this.inherited(arguments);b&&a.popup&&!a.disabled&&this._openPopup()},_onKeyPress:function(a){if(!a.ctrlKey&&!a.altKey)switch(a.charOrCode){case dojo.keys.DOWN_ARROW:this._moveToPopup(a),dojo.stopEvent(a)}},onItemClick:function(a,b){if(a.popup&&a.popup.isShowingNow)a.popup.onCancel();
else this.inherited(arguments)}}));