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

        if (!options.bookingStore) {
            throw Error("no booking store provided");
        }

        this._bookingStore = options.bookingStore;

        this._widget = new phpr.Default.System.TemplateWrapper({
            templateName: "phpr.Timecard.template.startStopButtonRow.html"
        });
        this.garbageCollector.addNode(this._widget);

        options.container.set('content', this._widget);

        this._loadIndicator = new phpr.Default.loadingOverlay(options.container.domNode, null);

        this.garbageCollector.addEvent(
            dojo.connect(this._bookingStore, "onLoadingStart", this, "_updateIndicator"));
        this.garbageCollector.addEvent(
            dojo.connect(this._bookingStore, "onLoadingStop", this, "_updateIndicator"));
        this.garbageCollector.addEvent(
            dojo.connect(this._bookingStore, "onChange", this, "_updateData"));

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

    _updateData: function() {
        this._updateIndicator();
        this._updateButton();
        this._updateProjectSelection();
    },

    _updateIndicator: function() {
        if (this._bookingStore.isLoading()) {
            this._loadIndicator.show();
        } else {
            this._loadIndicator.hide();
        }
    },

    _updateButton: function() {
        if (this._bookingStore.hasRunningBooking()) {
            this._showStopButton();
        } else {
            this._showStartButton();
        }
    },

    _updateProjectSelection: function() {
        if (!this._bookingStore.isLoading()) {
            this._drawProjectSelectBox();
        }
    },

    _drawProjectSelectBox: function(metaData) {
        if (!this._projectSelectionWidget && this._bookingStore.getProjectRange() !== null) {
            if (!this._templateRenderer) {
                this._templateRenderer = new phpr.Default.Field();
            }

            var widget = new phpr.Default.System.TemplateWrapper({
                templateName: "phpr.Timecard.template.projectSelect.html",
                templateData: {
                    values: this._bookingStore.getProjectRange()
                }
            });
            this.garbageCollector.addNode(widget);

            this._widget.selection.appendChild(widget.domNode);
            widget.startup();

            this._projectSelectionWidget = widget.select;
        }

        if (this._bookingStore.hasRunningBooking()) {
            var booking = this._bookingStore.getRunningBooking();
            this._projectSelectionWidget.set('value', booking.projectId);
        }
    },

    _getSelectedProjectId: function() {
        return this._projectSelectionWidget.get('value');
    },

    _showStartButton: function() {
        var button = this._widget.button;
        button.set('label', phpr.nls.get("Start working time", "Timecard"));
        button.onClick = dojo.hitch(this, "_startWorking");
    },

    _startWorking: function() {
        this._bookingStore.startWorking(this._getSelectedProjectId());
    },

    _showStopButton: function() {
        var button = this._widget.button;
        button.set('label', phpr.nls.get("Stop working time", "Timecard"));
        button.onClick = dojo.hitch(this, "_stopWorking");
    },

    _stopWorking: function() {
        this._bookingStore.stopWorking(this._getSelectedProjectId());
    }
});
