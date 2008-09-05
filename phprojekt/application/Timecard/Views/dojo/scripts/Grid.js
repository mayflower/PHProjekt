dojo.provide("phpr.Timecard.Grid");

dojo.declare("phpr.Timecard.Grid", phpr.Default.Grid, {
	
    reloadView:function(/*String*/ view, /*int*/ year, /*int*/ month) {
		phpr.destroySimpleWidget('gridNode');
		this.gridLayout = new Array();
		this.setUrl(year, month, view);
        phpr.DataStore.addStore({url: this.url});
        phpr.DataStore.requestData({url: this.url, processData: dojo.hitch(this, "onLoaded")});
    },
		
    setUrl: function(year, month, view) {
        if (typeof year == "undefined") {
            date = new Date();
            year = date.getFullYear();
        }
        if (typeof month == "undefined") {
            date = new Date();
            month = date.getMonth() + 1;
        }
        if (typeof view == "undefined") {
            view = 'month';
        }
        this.url = phpr.webpath+"index.php/"+phpr.module+"/index/jsonList/year/"+year+"/month/"+month+"/view/"+view;
    },

    showTags: function() {
    },

    canEdit: function(inRowIndex) {
        return false;
    },
	
    useIdInGrid: function () {
        return false;
    },
	
    showForm:function(e) {
        if (e.cellIndex == 0) {
            var item  = this.grid.getItem(e.rowIndex);
			var date = this.grid.store.getValue(item, 'date');
			if (date) {
				var year = date.substr(0, 4);
				var month = date.substr(5, 2);
				var day = date.substr(8, 2);
				var date = new Date(year, (month - 1), day);
				this.main.form.setDate(date);
				this.main.form.reloadDateView();
				this.publish("changeDate", [date]);
			}
        }
    },	
});