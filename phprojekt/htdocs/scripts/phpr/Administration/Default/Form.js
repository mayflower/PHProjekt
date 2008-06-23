dojo.provide("phpr.Administration.Default.Form");
dojo.require("phpr.Default.Form");

dojo.declare("phpr.Administration.Default.Form", phpr.Default.Form, {

    constructor: function() {
    },

	submitForm: function() {
        // summary:
        //    This function is responsible for submitting the formdata
        // description:
        //    This function sends the form data as json data to the server and publishes
        //    a form.Submitted topic after the data was send.
		this.sendData = this.formWidget.getValues();
		phpr.send({
			url:       phpr.webpath + 'index.php/' + phpr.module + '/index/jsonSave/id/' + this.id,
			content:   this.sendData,
            onSuccess: dojo.hitch(this, function(data) {
                new phpr.handleResponse('serverFeedback',data);
                if (data.type =='success') {
                    this.publish("reload");
                }
            })
        });
	},

	displayTagInput: function() {
        // summary:
        //    Display nothing for the tags input
        // description:
        //    re-write the function for show an empty template
		return this.render(["phpr.Default.template", "none.html"], null, {});
	},
});