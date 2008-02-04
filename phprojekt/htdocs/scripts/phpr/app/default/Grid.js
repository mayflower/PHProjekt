dojo.provide("phpr.app.default.Grid");

dojo.require("phpr.Component");
// The dijits the template uses
dojo.require("dijit.Menu");
dojo.require("dijit.form.FilteringSelect");
dojo.require("dijit.form.Form");
// Other classes, class specific
dojo.require("phpr.grid");

dojo.declare("phpr.app.default.Grid", phpr.Component, {
    
    _node:null,
    constructor:function() {
        this._node = dojo.byId("gridBox");
        this.render(["phpr.app.default.template", "grid.html"], this._node);
        //var wdgt = dijit.byId("gridNode");
        //dojo.connect(wdgt, "onClick", dojo.hitch(this, "onItemClick"));
        
        this.grid = {
            widget:null,
            model:null,
            layout:null
        };
        this.grid.model = new phpr.grid.Model(null, null, {
            //store:new phpr.grid.QueryReadStore({url:"http://localhost/phprojekt6/htdocs/countries.json"})
            store:new phpr.grid.ReadStore({url:"http://localhost/phprojekt6/htdocs/index.php/Todo/index/jsonList"})
        });
        // I am not 100% sure this is the best way, but it works quite well for now.
        // We trigger the request by hand here, and pass the model to the grid,
        // this way we have received the first set of rows and can render the grid.
        // Ask me (Wolfram) in a while and I know a better way :-).
        // this still has the triggering two request ... for whatever reason :-(
        this.grid.model.requestRows(0, 1, dojo.hitch(this, "onLoaded"));
    },
    
    onLoaded:function() {
        this.grid.widget = dijit.byId("gridNode");
        // Initially we have to update the row count, since we dont know it before we have received the
        // answer from this request, now we know it, so update it, so we also see the
        // numRows available.
        this.grid.widget.updateRowCount(this.grid.model.getRowCount());
        this.grid.widget.setModel(this.grid.model);
        this.grid.widget.setStructure(this._gridLayout);
        //this.inherited("onLoaded", arguments);
        
        this._filterForm = dijit.byId("gridFilterForm");
        dijit.byId("gridFilterSubmitButton").connect("onclick", dojo.hitch(this, "onSubmitFilter"));
    },

    onSubmitFilter:function() {
        var values = this._filterForm.getValues();
        var vals = {};
        for (var i in values) {
            vals["filter["+i+"]"] = values[i];
        }
        this.grid.model.query = vals;
        this.grid.model.requestRows(null, null, dojo.hitch(this, function() {
            this.grid.widget.updateRowCount(this.grid.model.getRowCount());
        }));
    },

    /**Doesnt work
     * 1) i dont know how to connect to onHeaderContextMenu to open the contextmenu
     * 2) using the way below i dont get a ref to the header the context menu opened up on top of
     * 3) connect() must be called AFTER the grid was rendered
     *  tooo much work for now :-(
    */
    connect:function() {
        // Connect the header nodes on double click (like Albrecht wants it) to start filtering
        //dojo.connect(this.grid.widget, "onHeaderContextMenu", dojo.hitch(this, "openHeaderContextMenu"));
        var nodes = dojo.query("th", this.grid.widget.headerNode);
        var wdgt = dijit.byId("gridHeaderContextMenu");
        for (var i=0, l=nodes.length; i<l; i++) {
            wdgt.bindDomNode(nodes[i]);
            //dojo.connect(nodes[i], "oncontextmenu", dojo.hitch(this, "openHeaderContextMenu"));
        }
        dojo.connect(wdgt, "onOpen", dojo.hitch(this, "onOpenContextMenu"));
    },
    
    onOpenContextMenu:function(e) {
        var wdgt = dijit.byId("gridHeaderContextMenu");
        //wdgt.show();
    },
    
    /**
     * I am not sure if this method really belongs here, i doubt it, since this is the grid class, so it should not be responsible for the headerBox!!!!!!!!!!!!!!
     * 
     * @param object i.e. {id:1, name:"My project"}
     */
    setProject:function(project) {
        dojo.byId("headerBox").innerHTML = "project:"+project.name+", id:"+project.id+" TOOOOOOOOODDDDOOOOOOOOOO load the grid with the project data, pass the project id!!!!!!!!";
        // Load the grid with the right data of the project
    },
    
    _gridLayout:[
        {
            cells: [[
                {
                    name: "id",
                    field: "id",
                    width:5
                    //get:function(inRowIndex) { return inRowIndex+1;} // this auto generates a row num
                } 
                ,{
                    name: "Title",
                    field: "title",
                    width:30
                    //formatter: rs.chunk.adminUser.grid.formatUser
                }
                ,{
                    name: "Start",
                    width:10,
                    styles: "text-align:right;",
                    field: "startDate",
                    formatter: phpr.grid.formatDate
                }
                ,{
                    name: "End",
                    width:10,
                    styles: "text-align:right;",
                    field: "endDate",
                    formatter: phpr.grid.formatDate
                }
                ,{
                    name: "Priority",
                    styles: "text-align:right;",
                    width:5,
                    field: "priority"
                    //formatter: phpr.grid.....  Write a function that maps the data retreived and uses the metadata to show the actual text
                }
                ,{
                    name: "Status",
                    width:10,
                    field: "currentStatus"
                    //formatter: phpr.grid.....  Write a function that maps the data retreived and uses the metadata to show the actual text
                }
                
    /*            
                {
                    name: "group",
                    field: "group_admin", 
                    editor: dojox.grid.editors.Bool,
                    styles: 'text-align:center;',
                    //get: rs.chunk.adminUser.grid.boolDefaultToFalse,
                    width: "5%"
                },
                {
                    name: "topic",
                    field: "topic_admin", 
                    editor: dojox.grid.editors.Bool,
                    styles: 'text-align:center;',
                    //get: dojo.hitch(this, function(index) {return rs.chunk.adminUser.grid.boolDefaultToFalse(index, "topic_admin")}),
                    width: "5%"
                },
                {
                    name: "tags",
                    field: "tag_admin", 
                    editor: dojox.grid.editors.Bool,
                    styles: 'text-align:center;',
                    //get: rs.chunk.adminUser.grid.boolDefaultToFalse,
                    width: "5%"
                },
                {
                    name: "ideas",
                    field: "idea_admin", 
                    editor: dojox.grid.editors.Bool,
                    styles: 'text-align:center;',
                    //get: rs.chunk.adminUser.grid.boolDefaultToFalse,
                    width: "5%"
                },
                {
                    name: "users",
                    field: "user_admin",
                    editor: dojox.grid.editors.Bool,
                    styles: 'text-align:center;',
                    //get: rs.chunk.adminUser.grid.boolDefaultToFalse,
                    width: "5%"
                },
                {
                    name: "imi",
                    field: "imi_admin",
                    editor: dojox.grid.editors.Bool,
                    styles: 'text-align:center;',
                    //get: rs.chunk.adminUser.grid.boolDefaultToFalse,
                    width: "5%"
                },
                
                {
                    name: "Member since",
                    width:"auto",
                    styles: "text-align:right;",
                    field: "date_joined",
                    formatter: rs.grid.formatDate
                },
                {
                    name: "(De)activate",
                    width:"auto",
                    styles: 'text-align:center;',
                    field: "is_active",
                    formatter: rs.chunk.adminUser.formatIsActive
                },
                {
                    name: "Reset password",
                    width:"auto",
                    styles: 'text-align:center;',
                    field: "is_active",
                    formatter: rs.chunk.adminUser.formatResetPassword
                },
                {
                    name: "Invite codes",
                    width:"auto",
                    styles: 'text-align:center;',
                    formatter: function(val, index) {return '<input type="button" class="button1" onclick="rs.chunk.adminUser.openInviteDialog('+index+')" value="New invites..." />'}
                },
                {
                    name: "Edit data",
                    width:"auto",
                    styles: 'text-align:center;',
                    formatter: function(val, index) {return '<input type="button" class="button1" onclick="rs.chunk.adminUser.openEditDialog('+index+')" value="Edit..." />'}
                }
    */
              ]]
        }
    ]
});
