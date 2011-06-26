dependencies = {
        version: "1.0.0",
	layers: [
		{
                        //extra layer for the setup system
			name: "setup.js",
			dependencies: [
                            "dijit.dijit",
                            "dijit.layout.BorderContainer",
                            "dijit.layout.ContentPane",
                            "dijit.layout.BorderContainer",
                            "dijit.layout.ContentPane",
                            "dijit.Toolbar",
                            "dijit.form.Form",
                            "dijit.form.Button",
                            "dijit.form.CheckBox",
                            "dijit.form.TextBox",
                            "dijit.TooltipDialog",
                            "dijit.form.FilteringSelect",
			]
		},
		{
                        //extra layer for the setup system
			name: "login.js",
			dependencies: [
                            "dojo.parser",
                            "dijit.layout.BorderContainer",
                            "dijit.layout.ContentPane",
                            "dijit.Toolbar",
                            "dijit.form.Form",
                            "dijit.form.TextBox",
                            "dijit.form.CheckBox",
			]
		},
		{
                        //extra layer for the phprojekt system
			name: "phprojekt.js",
			dependencies: [
                            "dijit.dijit",
                            "dojox.data.QueryReadStore",
                            "dijit.form.DateTextBox",
                            "dijit._Widget",
                            "dijit.Dialog",
                            "dijit.layout._LayoutWidget",
                            "dijit._Templated",
                            "dijit.form.FilteringSelect",
                            "dijit.form.CheckBox",
                            "dijit.form.HorizontalSlider",
                            "dojox.form.Rating",
                            "dojox.grid.cells.dijit",
                            "dojox.grid.cells.Select",
                            "dojox.grid.cells.DateTextBox",
                            "dojox.grid._View",
                            "dojox.layout.ExpandoPane",
                            "dojo.dnd.Moveable",
                            "dojox.layout.ResizeHandle",
                            "dojo.dnd.AutoSource",
                            "dojox.form.RangeSlider",
                            "dijit.form.SliderMoverMax",
                            "dijit.form.SliderBarMover",
                            "dojo.dnd.Source",
                            "dojox.dtl.Context",
                            "dojox.dtl.Template",
                            "dojo.data.ItemFileWriteStore",
                            "dijit.tree.ForestStoreModel",
                            "dijit.form.Form",
                            "dijit.form.Textarea",
                            "dijit.form.SimpleTextarea",
                            "dijit.form.HorizontalRuleLabels",
                            "dijit.form.HorizontalRule",
                            "dojox.widget.Toaster",
                            "dojox.dtl.Inline",
                            "dojo.date.ItemFileWriteStore",
                            "dijit.Tree",
                            "dojo.DeferredList",
                            "dojo.cookie",
                            "dijit.layout.TabContainer",
                            "dijit.layout.TabController",
                            "dijit.layout.StackContainer",
                            "dijit.Menu",
                            "dijit.MenuItem",
                            "dijit.PopupMenuItem",
                            "dijit.CheckedMenuItem",
                            "dijit.MenuSeparator",
                            "dijit.ScrollingTabController",
                            "dojox.grid.DataGrid",
                            "dojox.grid.Selection",
                            "dojox.grid.DataSelection",
                            "dijit.layout.BorderContainer",
                            "dijit._editor.plugins.LinkDialog",
                            "dijit._editor.plugins.TextColor",
                            "dijit._editor.plugins.FontChoice"
			]
		},
	],

	prefixes: [
		[ "dijit", "../dijit" ],
		[ "dojox", "../dojox" ]
	]
}
