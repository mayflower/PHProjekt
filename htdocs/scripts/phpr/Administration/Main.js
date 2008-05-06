dojo.provide("phpr.Administration.Main");

dojo.require("phpr.Component");
dojo.require("phpr.Administration.User.Main");
dojo.require("phpr.Administration.Role.Main");
dojo.require("phpr.Administration.Groups.Main");
dojo.require("phpr.Administration.Modules.Main");
dojo.require("phpr.Administration.Default.Main");

dojo.declare("phpr.Administration.Main", null, {
	 constructor: function(){
		this.User    = new phpr.Administration.User.Main();
		this.Role    = new phpr.Administration.Role.Main();
		this.Groups  = new phpr.Administration.Groups.Main();
        this.Modules = new phpr.Administration.Modules.Main();
		dojo.subscribe("Administration.reload", this, "load");
	 },
     load:function(){
         dojo.publish("Administration.User.load");
     }
});
