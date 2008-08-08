dojo.provide("phpr.grid");
dojo.provide("phpr.grid.cells.Select");

dojo.require("dojox.grid.cells.dijit");

phpr.grid.formatDateTime = function(date) {
    if (!date || !String(date).match(/\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2}/)) {
        return date;
    }
    var iso = String(date).replace(" ", "T"); // Make it a real date ISO string
    var dateObj = dojo.date.stamp.fromISOString(iso);
    return dojo.date.locale.format(dateObj, {formatLength:'short', selector:'dateTime'});
};

phpr.grid.formatDate = function(value) {
    var date = '';
    if (value) {
        if (String(value).match(/\d{4}-\d{2}-\d{2}/)) {
            var iso = String(value).replace(" ", "T"); // Make it a real date ISO string
            var dateObj = dojo.date.stamp.fromISOString(iso);
            date = dojo.date.locale.format(dateObj, this.constraint);
        } else {
            date = dojo.date.locale.format(new Date(value), this.constraint);
        }
        date = String(date).replace(" 00:00", "");
    }
    return date;
};

dojo.declare("phpr.grid.cells.Select", dojox.grid.cells.Select, {
    // summary:
    //    Redefine the function for return the correct value
    // description:
    //    Redefine the function for return the correct value
    format: function(inRowIndex, inItem){
        var f, i=this.grid.edit.info, d=this.get ? this.get(inRowIndex, inItem) : (this.value || this.defaultValue);
        if(this.editable && (this.alwaysEditing || (i.rowIndex==inRowIndex && i.cell==this))){
            return this.formatEditing(d, inRowIndex);
        } else {
            var v = '';
            for (var i=0, o; ((o=this.options[i]) !== undefined); i++){
                if (d == this.values[i]) {
                    v = o;
                }
            }
            return (typeof v == "undefined" ? this.defaultValue : v);
        }
    },
});


dojo.declare("phpr.grid.cells.DateTextBox", dojox.grid.cells.DateTextBox, {
    // summary:
    //    Redefine the function for return the correct value
    // description:
    //    Redefine the function for return the correct value
	format: function(inRowIndex, inItem) {
        var f, i=this.grid.edit.info, d=this.get ? this.get(inRowIndex, inItem) : (this.value || this.defaultValue);
        if (this.editable && (this.alwaysEditing || (i.rowIndex==inRowIndex && i.cell==this))){
            return this.formatEditing(d, inRowIndex);
        } else {
            return phpr.grid.formatDate(d);
        }
	},
});