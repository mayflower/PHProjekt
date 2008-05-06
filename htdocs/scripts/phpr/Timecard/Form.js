dojo.provide("phpr.Timecard.Form");

dojo.require("phpr.Default.Form");
dojo.require("phpr.roundedContentPane");

dojo.declare("phpr.Timecard.Form", phpr.Default.Form, {
    // summary: 
    //    This class is responsible for rendering the Form of a Timecard module
    // description: 
    //    The Detail View of the timecard is rendered
	constructor: function(){
         // summary:    
        //    render the form on construction
        // description: 
        //    this function receives the form data from the server and renders the corresponding form
        this.render(["phpr.Timecard.template", "form.html"], dojo.byId("tcBookings"),{});
	
    }
});
