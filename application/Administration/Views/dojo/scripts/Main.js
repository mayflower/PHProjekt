dojo.provide("phpr.Administration.Main");

dojo.declare("phpr.Administration.Main", null, {
    constructor:function() {
        dojo.subscribe("Administration.reload", this, "init");
	},

	init:function() {
        dojo.publish('User.reload');
	},
});