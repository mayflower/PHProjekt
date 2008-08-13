dojo.provide("phpr.Settings.Main");

dojo.require("phpr.Settings.Setting.Main");

dojo.declare("phpr.Settings.Main", null, {
	 constructor:function() {
		this.Setting = new phpr.Settings.Setting.Main();
		dojo.subscribe("Settings.reload", this, "load");
	 },

     load:function() {
         dojo.publish("Setting.reload");
     }
});