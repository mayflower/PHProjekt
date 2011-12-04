dojo.provide("phpr.Timecard.StartStopBar");
dojo.require("dojo.DeferredList");

dojo.declare("phpr.Timecard.StartStopBar", phpr.Default.System.Component, {
    _widget: null,
    _onStartClickCb: null,
    _onStopClickCb: null,
    _button: null,
    _date: null,
    _templateRenderer: null,
    _projectSelectionWidget: null,
    _runningBooking: null,
    _loadIndicator: null,

    constructor: function(options) {
        if (!options) {
            throw Error("no options provided");
        }

        if (!options.container) {
            throw Error("no container provided");
        }

        if (!options.date) {
            throw Error("no date provided");
        }

        this._date = options.date;

        this._onStartClickCb = options.onStartClick || function() {};
        this._onStopClickCb = options.onStopClick || function() {};


        this._widget = new phpr.Default.System.TemplateWrapper({
            templateName: "phpr.Timecard.template.startStopButtonRow.html"
        });
        this.garbageCollector.addNode(this._widget);

        options.container.set('content', this._widget);

        this._loadIndicator = new phpr.Default.loadingOverlay(options.container.domNode, null);
        this._loadIndicator.show();

        this._updateData();
    },

    destroy: function() {
        if (this._templateRenderer && dojo.isFunction(this._templateRenderer.destroy)) {
            this._templateRenderer.destroy();
        }
        this._templateRenderer = null;
        this._projectSelectionWidget = null;

        if (this._loadIndicator && dojo.isFunction(this._loadIndicator.hide)) {
            this._loadIndicator.hide();
        }
        this._loadIndicator = null;

        this.inherited(arguments);
    },

    dateChanged: function(date) {
        this._date = date;
        this.dataChanged();
    },

    dataChanged: function() {
        phpr.DataStore.deleteData({url: this._url});
        phpr.DataStore.deleteData({url: this._detailsUrl});
        this._updateData();
    },

    _updateData: function() {
        this._loadIndicator.show();
        this._setUrl();

        phpr.DataStore.addStore({url: this._url});
        phpr.DataStore.addStore({url: this._detailsUrl});

        var dlist = new dojo.DeferredList([
            phpr.DataStore.requestData({url: this._url}),
            phpr.DataStore.requestData({url: this._detailsUrl})
        ]);

        dlist.addCallback(dojo.hitch(this, "_onDataLoaded"));
    },

    _setUrl: function() {
        this._url = phpr.webpath + 'index.php/Timecard/index/jsonDayList/date/' + this._date;
        this._detailsUrl = phpr.webpath + 'index.php/Timecard/index/jsonDetail/nodeId/1/id/0';
    },

    _onDataLoaded: function() {
        var data = this._latestData = phpr.DataStore.getData({url: this._url});
        var metaData = phpr.DataStore.getMetaData({url: this._detailsUrl});
        var runningBooking = null;
        var l = this._latestData.length;

        this._drawProjectSelectBox(metaData);

        for (var i = 0; i < l; i++) {
            if (data[i].endTime === null) {
                runningBooking = data[i];
                break;
            }
        }

        this._runningBooking = runningBooking;

        if (!runningBooking) {
            this._showStartButton();
        } else {
            this._showStopButton();
        }
        this._loadIndicator.hide();
    },

    _drawProjectSelectBox: function(metaData) {
        if (!this._projectSelectionWidget) {
            if (!this._templateRenderer) {
                this._templateRenderer = new phpr.Default.Field();
            }

            var widget = new phpr.Default.System.TemplateWrapper({
                templateName: "phpr.Timecard.template.projectSelect.html",
                templateData: {
                    values: this._getProjectRange(metaData)
                }
            });
            this.garbageCollector.addNode(widget);

            this._widget.selection.appendChild(widget.domNode);
            widget.startup();

            this._projectSelectionWidget = widget.select;
        }
    },

    _getProjectRange: function(metaData) {
        var range = dojo.clone(metaData[3].range);

        var l = range.length;
        for (var i = 0; i < l; i++) {
            if (range[i].id == 1) {
                range[i].name = "unassigned";
                break;
            }
        }

        return range;
    },

    _getSelectedProjectId: function() {
        return this._projectSelectionWidget.get('value');
    },

    _showStartButton: function() {
        var button = this._widget.button;
        button.set('label', 'Start working time');
        button.onClick = dojo.hitch(this, "_startWorking");
    },

    _startWorking: function() {
        var data = {
            startDatetime: phpr.date.getIsoDate(this._date) + " " + phpr.date.getIsoTime(new Date()),
            projectId: this._getSelectedProjectId(),
            notes: ""
        };

        phpr.send({
            url: phpr.webpath + 'index.php/Timecard/index/jsonSave/nodeId/1/id/0',
            content: data
        }).then(dojo.hitch(this, function(data) {
            if (data) {
                new phpr.handleResponse('serverFeedback', data);
                if (data.type == 'success') {
                    this.dataChanged();
                    this._onStartClickCb();
                    this._showStopButton();
                }
            }
        }));
    },

    _showStopButton: function() {
        var button = this._widget.button;
        button.set('label', 'Stop working time');
        button.onClick = dojo.hitch(this, "_stopWorking");
    },

    _stopWorking: function() {
        var data = {
            startDatetime: phpr.date.getIsoDate(this._date) + " " + phpr.date.getIsoTime(this._runningBooking.startTime),
            endTime: phpr.date.getIsoTime(new Date()),
            projectId: this._getSelectedProjectId(),
            timecardId: this._runningBooking.id,
            notes: ""
        };

        phpr.send({
            url: phpr.webpath + 'index.php/Timecard/index/jsonSave/nodeId/1/id/' + this._runningBooking.id,
            content: data
        }).then(dojo.hitch(this, function(data) {
            if (data) {
                new phpr.handleResponse('serverFeedback', data);
                if (data.type == 'success') {
                    this.dataChanged();
                    this._onStartClickCb();
                    this._showStopButton();
                }
            }
        }));
    }
});
