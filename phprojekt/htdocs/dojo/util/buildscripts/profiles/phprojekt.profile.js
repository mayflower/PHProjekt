dependencies = {
    version: "1.0.0",
    layers: [{
        //extra layer for the setup system
        name: "setup.js",
        dependencies: [
            "dijit.dijit",
            "dijit.layout.BorderContainer",
            "dijit.layout.ContentPane",
            "dijit.Toolbar",
            "dijit.form.Form",
            "dijit.form.Button",
            "dijit.form.CheckBox",
            "dijit.form.TextBox",
            "dijit.TooltipDialog",
            "dijit.form.FilteringSelect"
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
            "dijit.form.CheckBox"
        ]
    },
    {
        //extra layer for the phprojekt system
        name: "phprojekt.js",
        dependencies: [
            "dijit.CheckedMenuItem",
            "dijit.Dialog",
            "dijit.Menu",
            "dijit.MenuItem",
            "dijit.MenuSeparator",
            "dijit.PopupMenuItem",
            "dijit.ScrollingTabController",
            "dijit.TitlePane",
            "dijit.Tree",
            "dijit._Templated",
            "dijit._Widget",
            "dijit._editor.plugins.FontChoice",
            "dijit._editor.plugins.LinkDialog",
            "dijit._editor.plugins.TextColor",
            "dijit.dijit",
            "dijit.form.CheckBox",
            "dijit.form.DateTextBox",
            "dijit.form.FilteringSelect",
            "dijit.form.Form",
            "dijit.form.HorizontalRule",
            "dijit.form.HorizontalRuleLabels",
            "dijit.form.HorizontalSlider",
            "dijit.form.SimpleTextarea",
            "dijit.form.SliderBarMover",
            "dijit.form.SliderMoverMax",
            "dijit.form.Textarea",
            "dijit.layout.BorderContainer",
            "dijit.layout.StackContainer",
            "dijit.layout.TabContainer",
            "dijit.layout.TabController",
            "dijit.layout._LayoutWidget",
            "dijit.tree.ForestStoreModel",
            "dojo.DeferredList",
            "dojo.NodeList-traverse",
            "dojo.cookie",
            "dojo.data.ItemFileWriteStore",
            "dojo.date.ItemFileWriteStore",
            "dojo.dnd.AutoSource",
            "dojo.dnd.Moveable",
            "dojo.dnd.Source",
            "dojo.hash",
            "dojox.data.QueryReadStore",
            "dojox.dtl.Context",
            "dojox.dtl.Inline",
            "dojox.dtl.Template",
            "dojox.dtl.tag.logic",
            "dojox.form.CheckedMultiSelect",
            "dojox.form.RangeSlider",
            "dojox.form.Rating",
            "dojox.grid.DataGrid",
            "dojox.grid.DataSelection",
            "dojox.grid.Selection",
            "dojox.grid._View",
            "dojox.grid.cells.DateTextBox",
            "dojox.grid.cells.Select",
            "dojox.grid.cells.dijit",
            "dojox.layout.ExpandoPane",
            "dojox.layout.FloatingPane",
            "dojox.layout.ContentPane",
            "dojox.layout.ResizeHandle",
            "dojox.widget.Toaster"
        ]
    }],

    prefixes: [
        [ "dijit", "../dijit" ],
        [ "dojox", "../dojox" ]
    ]
}
