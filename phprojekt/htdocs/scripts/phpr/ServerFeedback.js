dojo.provide("phpr.ServerFeedback");

dojo.declare("phpr.ServerFeedback",
	[dijit._Widget, dijit._Templated],
	{
		// summary:
		// A class for displaying the ServerFeedback
		// This class receives the Server Feedback and displays it to the User
		
		cssClass:     null,
		output:       null,		
		templatePath: dojo.moduleUrl("phpr.Default", "template/ServerFeedback.html"),
	}
);
