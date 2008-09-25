dojo.provide("phpr.Timecard.Form");

dojo.declare("phpr.Timecard.Form", phpr.Component, {
    sendData:    new Array(),
    formdata:    '',
    _hourUrl:    null,
	_bookUrl:    null,
    _formNode:   null,
    _date:       null,
	_dateObject: null,
	contentBar:  null,
	timecardProjectPositions: new Array(),
	
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
                id:  data[i].id			
            });
        }   	
		
		this.render(["phpr.Timecard.template", "hoursForm.html"], dojo.byId('TimecardHours'), {
			date: this._date,
            timecardWorkingTimesText: phpr.nls.get("Working Times"),
            timecardStartText: phpr.nls.get("Start"),
            timecardEndText: phpr.nls.get("End"),
            hoursdata: hoursdata,
			totalHours: this.convertTime(totalHours)
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

        var month = this._dateObject.getMonth();       
        var year  = this._dateObject.getFullYear();
		var dd    = new Date(year, month, 0);
        var lastDay = dd.getDate() + 1;
		var week    = dd.getDay();
		var days = new Array();

        var months = ['January', 'February', 'March', 'April', 'June', 'July', 'Agoust', 'September', 'October', 'November', 'December'];
		var weeks = ['Monday', 'Tuesday', 'Wenesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        var monthString = phpr.nls.get(months[month-1]);
		
		for (var i = 1; i < lastDay; i++) {
			var weekString = phpr.nls.get(weeks[week]);
			days.push({'day': i, 'month': monthString, 'week': weekString});
			week++;
			if (week > 6) {
				week = 0;
			}
		}
        this.render(["phpr.Timecard.template", "date.html"], dojo.byId('TimecardDate') , {
            date: this._date,
			days: days,
			today: this._dateObject.getDate(),
			monthNumber: month,
			yearNumber:  year
		});	
		
		dojo.byId("dateLongText").innerHTML = dojo.date.locale.format(this._dateObject, {formatLength:'full', selector:'date', locale: this.lang});
		dijit.byId("selectDate").attr('value', null);	
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
		range = meta[1]['range'];
		timecardProjectPositions = new Array();
        
		var timeprojData = data['timeproj'];
		var timecardData = data['timecard'];
		
        // Fixed hours 8-20 and 700 px for the bar
        var hours = new Array();		
		for (var i = 8; i < 20; i++) {
			hours.push(i);
		}
		var hourWidth = Math.floor(700 / 12);

        // Bookings forms		 
        for (var i = 0; i < timeprojData.length; i++) {
            var projectName = '';
            for (var j in range) {
                if (range[j]['id'] == timeprojData[i].projectId) {
                    var projectName = range[j]['name'];
                }
            }
            bookingdata += this.render(["phpr.Timecard.template", "bookings.html"], null, {
                projectName:    projectName,
                projectId:      timeprojData[i].projectId,
                projectIdLabel: 'Project',
                date:           this._date,
                notes:          timeprojData[i].notes,
                notesLabel:     'Notes',
                amount:         this.convertTime(this.getDiffTime(timeprojData[i].amount, '00:00:00')),
                amountLabel:    'Amount',
                saveText:       phpr.nls.get('Save'),
                deleteText:     phpr.nls.get('Delete'),
                id:             timeprojData[i].id
            });
        }  
		// New one
        bookingdata += this.render(["phpr.Timecard.template", "bookings.html"], null, {
            projectName:    '',
            projectId:      0,
            projectIdLabel: 'Project',
            date:           this._date,
            notes:          '',
            notesLabel:     'Notes',
            amount:         '00:00',
            amountLabel:    'Amount',
            saveText:       phpr.nls.get('Save'),
            deleteText:     phpr.nls.get('Cancel'),			
            id:             0
        });
	   
	   // Complete view
        this.render(["phpr.Timecard.template", "bookingForm.html"], dojo.byId('TimecardBooking'), {
			hours:                    hours,
			hourWidth:                hourWidth, 
            date:                     this._date,
			timecardProjectTimesText: phpr.nls.get("Project bookings"),
			values:                   range,
			helpText:                 phpr.nls.get('Add working time and drag projects into the bar'),
			bookingdata:              bookingdata
        });
    
        this.contentBar = new phpr.Timecard.ContentBar("projectBookingContainer");
        var surface     = dojox.gfx.createSurface('projectBookingContainer', 700, 22);
        var lastHour    = 0;
        var totalWith   = 0;
        for (var j = 0; j < timeprojData.length; j++) {
            timeprojData[j].displayed = 0;
            timeprojData[j].remaind = 0;
        }
							   
        // Draw hours block							   
        for (var i = 0; i < timecardData.length; i++) {
            var start = this.contentBar.convertHourToPixels(hourWidth, timecardData[i].startTime);
            var end   = this.contentBar.convertHourToPixels(hourWidth, timecardData[i].endTime);
            var left  = start + 'px';
            var width = end - start + 'px';
			var totalbookingWith = 0;
			var finish = 0;
            totalWith += (end - start);
						
            var tmp = dojo.doc.createElement ("div");
			tmp.id = 'targetBooking' + timecardData[i].id;
            tmp.innerHTML = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			dojo.style(tmp, "color", '#292929');
			dojo.style(tmp, "display", 'inline');
			dojo.style(tmp, "border", '1px solid #BABABA');
			dojo.style(tmp, "position", 'absolute');
			dojo.style(tmp, "left", left);
			dojo.style(tmp, "width", width);
			dojo.style(tmp, "height", '20px');
			dojo.style(tmp, "float", 'left');
	        dijit.byId("projectBookingContainer").domNode.appendChild(tmp);
            tgt = new phpr.Timecard.Booking(tmp.id);

			// Draw Bookings
            for (var j = 0; j < timeprojData.length; j++) {
				if (timeprojData[j].displayed == 0) {					
					if (timeprojData[j].remaind > 0) {
						var bookingWith = timeprojData[j].remaind;
					}
					else {
						var bookingWith = this.contentBar.convertAmountToPixels(hourWidth, timeprojData[j].amount);
					}
					
					if (totalbookingWith <= (end - start)) {
						if (lastHour == 0) {
							lastHour = start;
						}
						if (bookingWith > (end - lastHour)) {
							timeprojData[j].remaind = bookingWith - (end - lastHour);
							if (timeprojData[j].remaind > 0) {
								finish = 1;
								bookingWith = end - lastHour;
							}
						}
						var tmpDraw = surface.createRect({x: lastHour -4, y: 0, width: bookingWith - 2, height: 22});
					    tmpDraw.setFill([255, 0, 0, 0.3]);
						tmpDraw.setStroke("red");
                        timecardProjectPositions.push({'start': lastHour, 'end'  : lastHour + bookingWith, 'id'   : timeprojData[j].id});
						
						if (finish) {
							totalbookingWith += bookingWith + end;
							lastHour = 0;
						}
						else {
							totalbookingWith += bookingWith;
							lastHour += bookingWith;
							timeprojData[j].displayed = 1;
						}
					}
				}
            }
        }

        // Event buttons
        for (var i = 0; i < timeprojData.length; i++) {
            dojo.connect(dijit.byId("deleteBookingButton_"+timeprojData[i].id), "onClick", dojo.hitch(this, "deleteBookingForm", [timeprojData[i].id]));
			dojo.connect(dijit.byId("saveBookingButton_"+timeprojData[i].id), "onClick", dojo.hitch(this, "submitBookingForm", [timeprojData[i].id]));
        }
        dojo.connect(dijit.byId("deleteBookingButton_0"), "onClick", function(){
            var node = dojo.byId('projectBookingForm_0');
            if (node) {
                dojo.style(node, "display", "none");
            }
		});
        dojo.connect(dijit.byId("saveBookingButton_0"), "onClick", dojo.hitch(this, "submitBookingForm", [0]));
    },
	
    submitForm:function() {
        // summary:
        //    Save the hours form
        // description:
        //    Save the hours form and reload only the grid and the hours form      		
	   this.sendData = dojo.mixin(this.sendData, dijit.byId('hoursForm').attr('value'));	   
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
					phpr.DataStore.deleteData({url: this._bookUrl});
                    this.getFormData(1,0,1);
                    this.main.grid.reloadView(this.main._view, this.main._date.getFullYear(), (this.main._date.getMonth()+1));
               }
            })
        });	   
	},
	
	submitBookingForm:function(id) {
        // summary:
        //    Save the booking form
        // description:
        //    Save the booking form and reload only the grid and the booking form		
       this.sendData = dojo.mixin(this.sendData, dijit.byId('bookingForm_'+id).attr('value'));     
        if (this.sendData.amount) {
            this.sendData.amount = this.main.getIsoTime(this.sendData.amount);
        }
        phpr.send({
            url:       phpr.webpath + 'index.php/Timecard/index/jsonBookingSave/id/' + id,
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
					phpr.DataStore.deleteData({url: this._bookUrl});
                    this.getFormData(1,0,1);
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
		
		if (hoursDiff == 0 || hoursDiff < 10) {
            hoursDiff = '0'+hoursDiff;
        }
        if (minutesDiff == 0 || minutesDiff < 10) {
            minutesDiff = '0'+minutesDiff;
        }
        return hoursDiff+':'+minutesDiff;
    }	
});
