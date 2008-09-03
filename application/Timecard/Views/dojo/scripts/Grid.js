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
    }
});