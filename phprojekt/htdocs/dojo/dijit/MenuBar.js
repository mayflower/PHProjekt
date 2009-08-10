/*
	Copyright (c) 2004-2009, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dijit.MenuBar"]){ //_hasResource checks added by build. Do not use _hasResource directly in your code.
dojo._hasResource["dijit.MenuBar"] = true;
dojo.provide("dijit.MenuBar");

dojo.require("dijit.Menu");

dojo.declare("dijit.MenuBar", dijit._MenuBase, {
	// summary:
	//		A menu bar, listing menu choices horizontally, like the "File" menu in most desktop applications

	templateString:"<div class=\"dijitMenuBar dijitMenuPassive\" dojoAttachPoint=\"containerNode\"  waiRole=\"menubar\" tabIndex=\"${tabIndex}\" dojoAttachEvent=\"onkeypress: _onKeyPress\"></div>\r\n",

	// _isMenuBar: [protected] Boolean
	//		This is a MenuBar widget, not a (vertical) Menu widget.
	_isMenuBar: true,

	constructor: function(){
		// summary:
		//		Sets up local variables etc.
		// tags:
		//		private

		// parameter to dijit.popup.open() about where to put popup (relative to this.domNode)
		this._orient = this.isLeftToRight() ? {BL: 'TL'} : {BR: 'TR'};
	},

	postCreate: function(){
		var k = dojo.keys, l = this.isLeftToRight();
		this.connectKeyNavHandlers(
			l ? [k.LEFT_ARROW] : [k.RIGHT_ARROW],
			l ? [k.RIGHT_ARROW] : [k.LEFT_ARROW]
		);
	},

	focusChild: function(item){
		// overload focusChild so that whenever the focus is moved to a new item,
		// check the previous focused whether it has its popup open, if so, after 
		// focusing the new item, open its submenu immediately
		var from_item = this.focusedChild,
			showpopup = from_item && from_item.popup && from_item.popup.isShowingNow;
		this.inherited(arguments);
		if(showpopup && !item.disabled){
			this._openPopup();		// TODO: on down arrow, _openPopup() is called here and in onItemClick()
		}
	},
	
	_onKeyPress: function(/*Event*/ evt){
		// summary:
		//		Handle keyboard based menu navigation.
		// tags:
		//		protected

		if(evt.ctrlKey || evt.altKey){ return; }

		switch(evt.charOrCode){
			case dojo.keys.DOWN_ARROW:
				this._moveToPopup(evt);
				dojo.stopEvent(evt);
		}
	}
});

}
