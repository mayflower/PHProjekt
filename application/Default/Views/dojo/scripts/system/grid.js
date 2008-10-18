dojo.provide("phpr.grid");
dojo.provide("phpr.grid.cells.Select");

phpr.grid.formatDateTime = function(date) {
    if (!date || !String(date).match(/\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2}/)) {
        return date;
    }
    var iso = String(date).replace(" ", "T"); // Make it a real date ISO string
    var dateObj = dojo.date.stamp.fromISOString(iso);
    return dojo.date.locale.format(dateObj, {formatLength:'short', selector:'dateTime'});
};

phpr.grid.formatTime = function(value) {

    var isoRegExp = /^(?:(\d{2})(\d{2})?)$/;
    var match = isoRegExp.exec(value);
    if(match) {
        match.shift();
        return match[0] + ':' + match[1];
    } else {
        return value;
    }
},
phpr.grid.formatPercentage = function(value) {
    value = dojo.number.round(value, 2);
    return value;
},

phpr.grid.formatUpload = function(value) {
    if (value.indexOf('|') > 0) {
        value = value.substring(value.indexOf('|') + 1, value.length);
    }
    return value;
},

phpr.grid.formatDate = function(value) {
    var date = '';
    if (value) {
        if (String(value).match(/\d{4}-\d{2}-\d{2}/)) {
            //var iso = String(value).replace(" ", "T"); // Make it a real date ISO string
            //var dateObj = dojo.date.stamp.fromISOString(iso);
            //date = dojo.date.locale.format(dateObj, this.constraint);
            date = value;
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
    }
});

dojo.declare("phpr.grid.cells.DateTextBox", dojox.grid.cells.DateTextBox, {
    // summary:
    //    Redefine the function for work with iso format
    // description:
    //    Redefine the function for work with iso format    
    widgetClass: "dijit.form.DateTextBox",
        
    getValue: function(inRowIndex){
        var date = this.widget.attr('value');
        var day = date.getDate();       
        if (day < 10) {
            day = '0'+day; 
        }
        var month = (date.getMonth()+1);       
        if (month < 10) {
            month = '0'+month 
        }
        return date.getFullYear() + '-' + month + '-' + day;
    },
            
    setValue:function(inRowIndex, inValue) {
        if (this.widget) {
            var parts = inValue.split("-"); 
            var year  = parts[0];
            var month = parts[1]-1;
            var day   = parts[2];
            this.widget.attr('value',new Date(year, month, day));
        } else {
            this.inherited(arguments);
        }
    },
        
    getWidgetProps: function(inDatum){
        var parts = inDatum.split("-"); 
        var year  = parts[0];
        var month = parts[1]-1;
        var day   = parts[2];
        return dojo.mixin(this.inherited(arguments), {          
            value: new Date(year, month, day)
        });
    }
});
var dgc = dojox.grid.cells;
dgc.DateTextBox.markupFactory = function(node, cell){
    dgc._Widget.markupFactory(node, cell);
};