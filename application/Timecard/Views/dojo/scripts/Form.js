dojo.provide("phpr.Timecard.Form");

dojo.declare("phpr.Timecard.Form", phpr.Component, {
    sendData:    new Array(),
    formdata:    '',
    _hourUrl:    null,
	_bookUrl:    null,
    _formNode:   null,
    _date:       null,
	_dateObject: null,
	
    constructor:function(main, id, module, date) {
        // summary:
        //    render the form on construction
        // description:
        //    this function receives the form data from the server and renders the corresponding form
        //    If the module is a param, is setted
        this.main = main;
        this.id   = id;

        this.render(["phpr.Timecard.template", "form.html"], dojo.byId('detailsBox'));

        this.setDate(date);
        this.loadView();
		this.reloadDateView(); 
    },

    setDate:function(date) {
        // summary:
        //    Set the date for use in the form
        // description:
        //    Set the date for use in the form		
        if (undefined == date) {
            this._dateObject = new Date();
        } else {
            this._dateObject = date; 
        }
        this._date = this.main.getIsoDate(this._dateObject);		
	},
	
	loadView:function() {
        // summary:
        //    Load all the views
        // description:
        //    Load all the views  		
        this.setHourUrl();
        this.setBookUrl();		
        this.getFormData(1,0,1);
	},
	
    setHourUrl:function() {
        // summary:
        //    Set the url for get the data
        // description:
        //    Set the url for get the data
        this._hourUrl = phpr.webpath+"index.php/" + phpr.module + "/index/jsonDetail/date/" + this._date
    },

    setBookUrl:function() {
        // summary:
        //    Set the url for get the data
        // description:
        //    Set the url for get the data
        this._bookUrl = phpr.webpath+"index.php/" + phpr.module + "/index/jsonBookingDetail/date/" + this._date
    },

    getFormData:function(hours, date, books) {
        // summary:
        //    Reload only the views that are needed
        // description:
        //    Reload only the views that are needed		
        if (date) {
            this.reloadDateView();
        }
		
		if (books) {
    		phpr.DataStore.addStore({url: this._bookUrl});
			phpr.DataStore.requestData({url: this._bookUrl, processData: dojo.hitch(this, "reloadBookingView")});
        }
        
		if (hours) {
			// Render the form element on the right bottom
			phpr.DataStore.addStore({url: this._hourUrl});
			phpr.DataStore.requestData({url: this._hourUrl, processData: dojo.hitch(this, "reloadHoursView")});
		}
	},
	
    reloadHoursView:function() {
        // summary:
        //    Reload the Hours view
        // description:
        //    Reload the Hours view with the form and all the hours saved for the current day 	
        var hoursdata = "";

        meta = phpr.DataStore.getMetaData({url: this._hourUrl});
        data = phpr.DataStore.getData({url: this._hourUrl});
       
        totalHours = 0;
        for (var i = 0; i < data.length; i++) {
			totalHours += this.getDiffTime(data[i].endTime, data[i].startTime);
            hoursdata += this.render(["phpr.Timecard.template", "hours.html"], null, {
                hoursDiff: this.convertTime(this.getDiffTime(data[i].endTime, data[i].startTime)),
                start: data[i].startTime,
                end: data[i].endTime,
                id:  data[i].id,
            });
        }   	
		
		this.render(["phpr.Timecard.template", "hoursForm.html"], dojo.byId('TimecardHours'), {
            dateForm: dojo.date.locale.format(this._dateObject, {formatLength:'full', selector:'date', locale: this.lang}),
			date: this._date,
            timecardWorkingTimesText: phpr.nls.timecardWorkingTimes,
            timecardStartText: phpr.nls.timecardStart,
            timecardEndText: phpr.nls.timecardEnd,			
            hoursdata: hoursdata,
			totalHours: this.convertTime(totalHours),
		});
        
        for (var i = 0; i < data.length; i++) {
            dojo.connect(dijit.byId("deleteHourButton_"+data[i].id), "onClick", dojo.hitch(this, "deleteForm", [data[i].id]));
        }   
        dojo.connect(dijit.byId("hoursSaveButton"), "onClick", dojo.hitch(this, "submitForm"));
	},
	
	reloadDateView:function() {
        // summary:
        //    Reload the Date view
        // description:
        //    Reload the HDate picker div with the current date		
        this.render(["phpr.Timecard.template", "date.html"], dojo.byId('TimecardDate') , {
            date: this._date,
		});		
	},
	
    reloadBookingView: function() {
        // summary:
        //    Reload the Booking view
        // description:
        //    Reload the Booking view with the form and all the Bookings saved for the current day		
        phpr.destroyWidgets("TimecardBooking");
		phpr.destroyWidgets("projectId");
		phpr.destroyWidgets("notes");
		phpr.destroyWidgets("amount");
		
		var bookingdata = '';
		
        meta = phpr.DataStore.getMetaData({url: this._bookUrl});
        data = phpr.DataStore.getData({url: this._bookUrl});
        
        totalHours = 0;
        for (var i = 0; i < data.length; i++) {
			var projectName = '';
			for (var j in meta[1]['range']) {
				if (meta[1]['range'][j]['id'] == data[i].projectId) {
					var projectName = meta[1]['range'][j]['name'];
				}
			}
            totalHours += this.getDiffTime(data[i].amount, '00:00:00');
            bookingdata += this.render(["phpr.Timecard.template", "bookings.html"], null, {
                project: projectName,
				notes:   data[i].notes,
                amount:  this.convertTime(this.getDiffTime(data[i].amount, '00:00:00')),
                id:      data[i].id,
            });
        }       
			
        this.render(["phpr.Timecard.template", "bookingForm.html"], dojo.byId('TimecardBooking'), {
            dateForm: dojo.date.locale.format(this._dateObject, {formatLength:'full', selector:'date', locale: this.lang}),
            date: this._date,
			timecardProjectTimesText: phpr.nls.timecardProject,
			projectId: meta[1]['key'],
			projectIdLabel: meta[1]['label'],
			values: meta[1]['range'],
            notes: meta[2]['key'],
            notesLabel: meta[2]['label'],
            amount: meta[3]['key'],
            amountLabel: meta[3]['label'],
			bookingdata: bookingdata,
			totalHours: this.convertTime(totalHours),		
        });

        for (var i = 0; i < data.length; i++) {
            dojo.connect(dijit.byId("deleteBookingButton_"+data[i].id), "onClick", dojo.hitch(this, "deleteBookingForm", [data[i].id]));
        }   
						
		dojo.connect(dijit.byId("bookingSaveButton"), "onClick", dojo.hitch(this, "submitBookingForm"));	
    },
	
    submitForm:function() {
        // summary:
        //    Save the hours form
        // description:
        //    Save the hours form and reload only the grid and the hours form      		
	   this.sendData = dojo.mixin(this.sendData, dijit.byId('hoursForm').getValues());	   
        if (this.sendData.endTime) {
            this.sendData.endTime = this.main.getIsoTime(this.sendData.endTime);
        }
        if (this.sendData.startTime) {
            this.sendData.startTime = this.main.getIsoTime(this.sendData.startTime);
        }
        phpr.send({
            url:       phpr.webpath + 'index.php/Timecard/index/jsonSave/',
            content:   this.sendData,
            onSuccess: dojo.hitch(this, function(data) {
               new phpr.handleResponse('serverFeedback', data);
                if (data.type == 'success') {
                    this.publish("updateCacheData");
					phpr.DataStore.deleteData({url: this._hourUrl}); 
                    this.getFormData(1,0,0);
                    this.main.grid.reloadView(this.main._view, this.main._date.getFullYear(), (this.main._date.getMonth()+1));
               }
            })
        });	   
	},
	
	submitBookingForm:function() {
        // summary:
        //    Save the booking form
        // description:
        //    Save the booking form and reload only the grid and the booking form		
       this.sendData = dojo.mixin(this.sendData, dijit.byId('bookingForm').getValues());     
        if (this.sendData.amount) {
            this.sendData.amount = this.main.getIsoTime(this.sendData.amount);
        }
        phpr.send({
            url:       phpr.webpath + 'index.php/Timecard/index/jsonBookingSave/',
            content:   this.sendData,
            onSuccess: dojo.hitch(this, function(data) {
               new phpr.handleResponse('serverFeedback', data);
                if (data.type == 'success') {
                    this.publish("updateCacheData");
					phpr.DataStore.deleteData({url: this._bookUrl}); 
                    this.getFormData(1,0,1);
                    this.main.grid.reloadView(this.main._view, this.main._date.getFullYear(), (this.main._date.getMonth()+1));
               }
            })
        });		
	},
	
    deleteForm:function(id) {
        // summary:
        //    Delete an hour saved
        // description:
        //    Delete an hour saved and reload only the grid and the hours form       
        phpr.send({
            url:       phpr.webpath + 'index.php/' + phpr.module + '/index/jsonDelete/id/' + id,
            onSuccess: dojo.hitch(this, function(data) {
                new phpr.handleResponse('serverFeedback', data);
                if (data.type == 'success') {
                    this.publish("updateCacheData");
					phpr.DataStore.deleteData({url: this._hourUrl}); 
                    this.getFormData(1,0,0);
					this.main.grid.reloadView(this.main._view, this.main._date.getFullYear(), (this.main._date.getMonth()+1));
               }
            })
        });
    },

    deleteBookingForm:function(id) {
        // summary:
        //    Delete an booking saved
        // description:
        //    Delete an booking saved and reload only the grid and the booking form     
        phpr.send({
            url:       phpr.webpath + 'index.php/' + phpr.module + '/index/jsonBookingDelete/id/' + id,
            onSuccess: dojo.hitch(this, function(data) {
                new phpr.handleResponse('serverFeedback', data);
                if (data.type == 'success') {
                    this.publish("updateCacheData");
					phpr.DataStore.deleteData({url: this._bookUrl});  
                    this.getFormData(0,0,1);
                    this.main.grid.reloadView(this.main._view, this.main._date.getFullYear(), (this.main._date.getMonth()+1));
               }
            })
        });
    },
	
    updateData:function() {
        // summary:
        //    Delete the cache for this form
        // description:
        //    Delete the cache for this form
    },
	
    getDiffTime:function(end, start) {
        // summary:
        //    Ger the diff in minutes between two times
        // description:
        //    Ger the diff in minutes between two times		
        var hoursEnd     = end.substr(0, 2);
        var minutesEnd   = end.substr(3, 2);
        var hoursStart   = start.substr(0, 2);
        var minutesStart = start.substr(3, 2);

        return ((hoursEnd - hoursStart)*60) + (minutesEnd - minutesStart);
    },
    
    convertTime:function(time) {
        // summary:
        //    Convert a number of minutes into HH:mm
        // description:
        //    Convert a number of minutes into HH:mm		
        hoursDiff = Math.floor(time / 60);
        minutesDiff = time - (hoursDiff * 60);
        if (hoursDiff.length == 1) {
            hoursDiff = '0'+hoursDiff;
        }
        if (minutesDiff.length == 1) {
            minutesDiff = '0'+minutesDiff;
        }
        return hoursDiff+':'+minutesDiff;
    }	
});