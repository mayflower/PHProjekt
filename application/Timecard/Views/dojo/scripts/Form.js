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

        if (undefined == date) {
            this._dateObject = new Date();
        } else {
			this._dateObject = date; 
		}
        this._date = this.main.getIsoDate(this._dateObject);

        this.setHourUrl();
		this.setBookUrl();

        this.render(["phpr.Timecard.template", "form.html"], dojo.byId('detailsBox'), {
            date: this._date,
            values: new Array(),//this.projectStore.getList(),
            tcProjecthValue: 0,
            tcProjectmValue:0,
            timecardDateText: phpr.nls.timecardDate,
            timecardProjectTimesText: phpr.nls.timecardProjectTimes,
            timecardProjectText: phpr.nls.timecardProject,
            timecardNotesText: phpr.nls.timecardNotes,
            timecardTimesText: phpr.nls.timecardTimes,
            timecardHText: phpr.nls.timecardH,
            timecardMText: phpr.nls.timecardM,
            timecardSavedTimesText: phpr.nls.timecardSavedTimes,
            saveText: phpr.nls.save,            
        });
    
		this.getFormData(1,1,1);
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
        if (date) {
            this.reloadDateView();
        }
		
		if (books) {
    		//phpr.DataStore.addStore({url: this._bookUrl});
            //phpr.DataStore.requestData({url: this._bookUrl});
        }
        
		if (hours) {
			// Render the form element on the right bottom
			phpr.DataStore.addStore({
				url: this._hourUrl
			});
			phpr.DataStore.requestData({
				url: this._hourUrl,
				processData: dojo.hitch(this, "reloadHoursView")
			});
		}
	},
	
    reloadHoursView:function() {
        this.hoursdata = "";

        meta = phpr.DataStore.getMetaData({url: this._hourUrl});
        data = phpr.DataStore.getData({url: this._hourUrl});
       
        for (var i = 0; i < data.length; i++) {
            this.hoursdata += this.render(["phpr.Timecard.template", "hours.html"], null, {
                hoursDiff: this.convertTime(this.getDiffTime(data[i].endTime, data[i].startTime)),
                start: data[i].startTime,
                end: data[i].endTime,
                id:  data[i].id,
            });
        }   	
		
		this.render(["phpr.Timecard.template", "hoursForm.html"], dojo.byId('TimecardHours'), {
			date: this._date,
            timecardWorkingTimesText: phpr.nls.timecardWorkingTimes,
            timecardStartText: phpr.nls.timecardStart,
            timecardEndText: phpr.nls.timecardEnd,			
            hoursdata: this.hoursdata,
		});
        
        for (var i = 0; i < data.length; i++) {
            dojo.connect(dijit.byId("deleteHourButton_"+data[i].id), "onClick", dojo.hitch(this, "deleteForm", [data[i].id]));
        }   
        dojo.connect(dijit.byId("hoursSaveButton"), "onClick", dojo.hitch(this, "submitForm"));
	},
	
	reloadDateView:function() {
        this.render(["phpr.Timecard.template", "date.html"], dojo.byId('TimecardDate') , {
            date: this._date,
            timecardtimeRecordingForText: phpr.nls.timecardtimeRecordingFor,
            dateForm: dojo.date.locale.format(this._dateObject, {formatLength:'full', selector:'date', locale: this.lang}),			
		});		
	},
	
    submitForm:function() {
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
                    this.getFormData(1,0,0);
                    this.main.grid.reloadView(this.main._view, this.main._date.getFullYear(), (this.main._date.getMonth()+1));
               }
            })
        });	   
	},
	
    deleteForm: function(id) {
        // summary:
        //    This function is responsible for deleting a dojo element
        // description:
        //    This function calls jsonDeleteAction
        phpr.send({
            url:       phpr.webpath + 'index.php/' + phpr.module + '/index/jsonDelete/id/' + id,
            onSuccess: dojo.hitch(this, function(data) {
                new phpr.handleResponse('serverFeedback', data);
                if (data.type == 'success') {
                    this.publish("updateCacheData");  
                    this.getFormData(1,0,0);
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
        phpr.DataStore.deleteData({url: this._hourUrl});
    },
	
    getDiffTime:function(end, start) {
        var hoursEnd     = end.substr(0, 2);
        var minutesEnd   = end.substr(3, 2);
        var hoursStart   = start.substr(0, 2);
        var minutesStart = start.substr(3, 2);

        return ((hoursEnd - hoursStart)*60) + (minutesEnd - minutesStart);
    },
    
    convertTime:function(time) {
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