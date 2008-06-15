dojo.provide("phpr.Default.field");
dojo.require("phpr.Component");
dojo.require("dijit.form.Textarea");
dojo.require("dojox.widget.MultiComboBox"); 
dojo.require("dijit.form.MultiSelect"); 
dojo.declare("phpr.Default.field", phpr.Component, {
    // summary: 
    //    class for rendering form fields
    // description:
    //    this class renders the different form types which are available in a PHProjekt Detail View
	formdata: '',
	checkRender: function(itemlabel, itemid,itemvalue){
		var itemchecked = null;
		if(itemvalue == "on")itemchecked ="checked";
		return this.render(["phpr.Default.template", "formcheck.html"], null, {
							label: itemlabel,
							labelfor: itemid,
							id: itemid,
							checked:itemchecked
				});
	},
	textFieldRender: function(itemlabel, itemid,itemvalue,itemrequired,itemdisabled){
		return this.render(["phpr.Default.template", "formtext.html"], null, {
							label: itemlabel,
							labelfor: itemid,
							id: itemid,
							value: itemvalue,
							required: itemrequired,
							disabled: itemdisabled
				});
	},
	textAreaRender: function(itemlabel, itemid,itemvalue,itemrequired,itemdisabled){
		return this.render(["phpr.Default.template", "formtextarea.html"], null, {
							label: itemlabel,
							labelfor: itemid,
							id: itemid,
							value: itemvalue,
							required: itemrequired,
							disabled: itemdisabled
				});
	},
	dateRender: function(itemlabel, itemid,itemvalue,itemrequired,itemdisabled){
		return this.render(["phpr.Default.template", "formdate.html"], null, {
						 	label: itemlabel,
							labelfor: itemid,
							id: itemid,	
							value: itemvalue,
							required: itemrequired,
							disabled: itemdisabled
				});
	},
	timeRender: function(itemlabel, itemid,itemvalue,itemrequired,itemdisabled){
		return this.render(["phpr.Default.template", "formtime.html"], null, {
						 	label: itemlabel,
							labelfor: itemid,
							id: itemid,	
							value: itemvalue,
							required: itemrequired,
							disabled: itemdisabled
				});
	},
	selectRender: function(range, itemlabel, itemid,itemvalue,itemrequired,itemdisabled){
		var options=new Array();
		var j=0;
		for (j in range){
			options.push(range[j]);
			j++;
		}
		return this.render(["phpr.Default.template", "formfilterselect.html"], null, {
							label: itemlabel,
							labelfor: itemid,
							id: itemid,
							value: itemvalue,
							required: itemrequired,
							disabled: itemdisabled,
							values: options
				});
	},
	
	multipleSelectBoxRender: function(range, itemlabel, itemid,itemvalue,itemrequired,itemdisabled, itemsize, itemmultiple){
		var options=new Array();
		var j=0;
		for (j in range){
		    if (itemvalue.indexOf("," + range[j].id + ",") >= 0) {
		        range[j].selected = 'selected';
		    }
		    else {
		        range[j].selected = '';
		    }
			options.push(range[j]);
			j++;
		}		
		
		return this.render(["phpr.Default.template", "formselect.html"], null, {
							label: itemlabel,
							labelfor: itemid,
							id: itemid,
							values: itemvalue,
							required: itemrequired,
							disabled: itemdisabled,
							multiple: itemmultiple,
							size: itemsize,
							options: options
				});
	},
	
	MultipleSelectRender: function(range, itemlabel, itemid,itemvalue,itemrequired,itemdisabled){
		var options=new Array();
		var j=0;
		for (j in range){
			options.push(range[j]);
			j++;
		}
		return this.render(["phpr.Default.template", "formmultipleselect.html"], null, {
							label: itemlabel,
							labelfor: itemid,
							id: itemid,
							values: itemvalue,
							required: itemrequired,
							disabled: itemdisabled,
							options: options
				});
	}
});
function dump(arr,level) {
	var dumped_text = "";
	if(!level) level = 0;
	
	//The padding given at the beginning of the line.
	var level_padding = "";
	for(var j=0;j<level+1;j++) level_padding += "    ";
	
	if(typeof(arr) == 'object') { //Array/Hashes/Objects 
		for(var item in arr) {
			var value = arr[item];
			
			if(typeof(value) == 'object') { //If it is an array,
				dumped_text += level_padding + "'" + item + "' ...\n";
				dumped_text += dump(value,level+1);
			} else {
				dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
			}
		}
	} else { //Stings/Chars/Numbers etc.
		dumped_text = "===>"+arr+"<===("+typeof(arr)+")";
	}
	return dumped_text;
}