define([
    'dojo/_base/lang',
    'dojo/_base/declare',
    'dojo/_base/array',
    'dojo/dom-attr',
    'dojo/date/locale',
    'dojo/promise/all',
    'dijit/_Widget',
    'dijit/_TemplatedMixin',
    'phpr/Api',
    'phpr/Timehelper',
    'phpr/models/Timecard',
    'dojo/text!phpr/template/statistics/WorktimeMonthGraph.html',
    'd3/d3.v3.js'
], function(
    lang,
    declare,
    array,
    domAttr,
    locale,
    all,
    Widget,
    Templated,
    api,
    timehelper,
    timecardModel,
    templateString
) {

    var maxMinutes = 60 * 15,
        barPadding = 2;

    var GeometryHelper = declare(null, {
        _svgNode: null,
        _dayEntries: null,

        constructor: function(svgNode, dayEntries) {
            this._dayEntries = dayEntries;
            this._svgNode = svgNode;
        },

        todayX: function() {
            return (new Date()).getDate() * (this.barWidth() + barPadding) - (barPadding / 2);
        },

        barWidth: function() {
            return (this.displayWidth() / this._dayEntries.length) - barPadding;
        },

        displayWidth: function() {
            return domAttr.get(this._svgNode, 'width') - 40;
        },

        heightPerMinute: function() {
            return this.heightForTimebars() / maxMinutes;
        },

        heightForTimebars: function() {
            return domAttr.get(this._svgNode, 'height');
        }
    });

    var MinutesBookedBlockRenderer = declare(null, {
        _svgNode: null,
        _dayEntries: null,
        _helper: null,

        constructor: function(svgNode, dayEntries) {
            this._dayEntries = dayEntries;
            this._svgNode = svgNode;
            this._helper = new GeometryHelper(svgNode, dayEntries);
        },

        render: function() {
            var svg = d3.select(this._svgNode);
            var svgData = svg.selectAll().data(this._dayEntries);

            svgData.enter()
                .append('svg:rect')
                    .attr('fill', lang.hitch(this, this._fill))
                    .attr('x', lang.hitch(this, this._x))
                    .attr('y', lang.hitch(this, this._y))
                    .attr('width', lang.hitch(this._helper, this._helper.barWidth))
                    .attr('height', lang.hitch(this, this._height))
                    .append('svg:title')
                        .text(lang.hitch(this, this._titleText));
        },

        _x: function(d, i) {
            return i * (barPadding + this._helper.barWidth());
        },

        _height: function(d) {
            return Math.max(2, this._helper.heightPerMinute() * d.minutesBooked);
        },

        _y: function(d) {
            var x = Math.min(
                this._helper.heightForTimebars() - 2,
                this._helper.heightForTimebars() - this._helper.heightPerMinute() * d.minutesBooked
            );
            return x;
        },

        _fill: function(entry) {
            if (!entry.hasOwnProperty('minutesToWork')) {
                return "white";
            }
            return entry.minutesBooked < entry.minutesToWork ? '#b5b5b5' : 'white';
        },

        _titleText: function(d) {
            var date = locale.format(timehelper.dateToJsDate(d.date), {selector: 'date'});
            if (d.minutesBooked !== 0) {
                date += ' (' + timehelper.minutesToHMString(d.minutesBooked) + ')';
            }
            return date;
        }
    });

    var TimeToWorkRenderer = declare(null, {
        _svgNode: null,
        _dayEntries: null,
        _helper: null,

        constructor: function(svgNode, dayEntries) {
            this._svgNode = svgNode;
            this._dayEntries = dayEntries;
            this._helper = new GeometryHelper(svgNode, dayEntries);
        },

        render: function() {
            var svg = d3.select(this._svgNode);
            var svgData = svg.selectAll().data(this._dayEntries);

            this._renderHorizontalLines(svgData);
            this._renderConnectingVerticalLines(svgData);
        },

        _renderHorizontalLines: function(svgData) {
            svgData.enter()
                .append('svg:line')
                    .attr('x1', lang.hitch(this, this._horizontalX1))
                    .attr('x2', lang.hitch(this, this._horizontalX2))
                    .attr('y1', lang.hitch(this, this._horizontalY))
                    .attr('y2', lang.hitch(this, this._horizontalY))
                    .attr('stroke', '#6aa700');
        },

        _horizontalX1: function(d, i) {
            return i * (barPadding + this._helper.barWidth());
        },

        _horizontalX2: function(d, i) {
            return (i + 1) * (barPadding + this._helper.barWidth());
        },

        _horizontalY: function(d, i) {
            var date = timehelper.dateToJsDate(d.date);
            if (locale.isWeekend(date)) {
                return this._helper.heightForTimebars();
            }
            return this._heightForMinutesToWork();
        },

        _renderConnectingVerticalLines: function(svgData) {
            svgData.enter()
                .append('svg:line')
                    .attr('x1', lang.hitch(this, this._verticalX1))
                    .attr('x2', lang.hitch(this, this._verticalX2))
                    .attr('y1', lang.hitch(this, this._verticalY1))
                    .attr('y2', lang.hitch(this, this._horizontalY))
                    .attr('stroke', '#6aa700');
        },

        _verticalX1: function(d, i) {
            return i * (barPadding + this._helper.barWidth());
        },

        _verticalX2: function(d, i) {
            return (i) * (barPadding + this._helper.barWidth());
        },

        _verticalY1: function(d, i) {
            if (i === 0) {
                return this._horizontalY(d, i);
            }
            return this._horizontalY(this._dayEntries[i - 1], i - 1);
        },

        _heightForMinutesToWork: function() {
            return this._helper.heightForTimebars() - this._helper.heightPerMinute() * this._minutesToWork();
        },

        _minutesToWork: function() {
            return 450;
        }
    });

    return declare([Widget, Templated], {
        templateString: templateString,
        baseClass: 'thisMonthDiagram',

        buildRendering: function() {
            this.inherited(arguments);

            this._updateLabels();

            timecardModel.getWorkBalanceByDay().then(
                lang.hitch(this, this._renderUsingWorkBalance),
                lang.hitch(this, function(error) {
                    // fallback rendering, probably no contract
                    api.defaultErrorHandler(error);
                    timecardModel.getMonthList().then(lang.hitch(this, this._renderUsingMonthList));
                })
            );
        },

        _renderUsingWorkBalance: function(data) {
            var entries = [];
            for (var date in data.workBalancePerDay) {
                entries.push({
                    date: date,
                    minutesBooked: data.workBalancePerDay[date].minutesBooked,
                    minutesToWork: data.workBalancePerDay[date].minutesToWork
                });
            }
            new MinutesBookedBlockRenderer(this.bookedTimePerDayGraph, entries).render();
            new TimeToWorkRenderer(this.bookedTimePerDayGraph, entries).render();

            this._fillOvertimeLabel();
            this._renderTodayMarker(new GeometryHelper(this.bookedTimePerDayGraph, entries));
        },

        _renderUsingMonthList: function(data) {
            var entries = [];
            array.forEach(data.days, function(entry) {
                entries.push({
                    date: entry.date,
                    minutesBooked: entry.sumInMinutes
                });
            });
            new MinutesBookedBlockRenderer(this.bookedTimePerDayGraph, entries).render();

            this._renderTodayMarker(new GeometryHelper(this.bookedTimePerDayGraph, entries));
        },

        _fillOvertimeLabel: function() {
            timecardModel.getMonthStatistics().then(lang.hitch(this, function(result) {
                var overtime = result.booked.minutesBooked - result.towork.minutesToWork;
                this.overtimeLabel.innerHTML = timehelper.minutesToHMString(overtime) + ' Overtime';
            }), function(err) {
                api.defaultErrorHandler(err);
            });
        },

        _renderTodayMarker: function(geometryHelper) {
            var svg = d3.select(this.bookedTimePerDayGraph);

            svg.append('rect')
                .attr('x', geometryHelper.todayX() - 1)
                .attr('width', 2)
                .attr('y', 0)
                .attr('height', geometryHelper.heightForTimebars())
                .attr('fill', '#0d639b');
        },

        _updateLabels: function() {
            var thisYear = (new Date()).getFullYear(),
                thisMonth = (new Date()).getMonth(),
                first = new Date(thisYear, thisMonth, 1, 0, 0, 0),
                last = new Date(thisYear, thisMonth + 1, 0, 0, 0, 0);
            this.firstDayLabel.innerHTML = locale.format(first, {selector: 'date', datePattern: 'EEE d'});
            this.lastDayLabel.innerHTML = locale.format(last, {selector: 'date', datePattern: 'EEE d'});
        }
    });
});

