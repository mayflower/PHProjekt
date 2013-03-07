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

    var MinutesBookedBlockRenderer = declare(null, {

        constructor: function(svgNode, dayEntries) {
            this._dayEntries = dayEntries;
            this._svgNode = svgNode;
        },

        render: function() {
            var svg = d3.select(this._svgNode);
            var svgData = svg.selectAll().data(this._dayEntries);

            svgData.enter()
                .append('svg:rect')
                    .attr('fill', this._helpers.fill)
                    .attr('x', lang.hitch(this, this._x))
                    .attr('y', lang.hitch(this, this._y))
                    .attr('width', lang.hitch(this, this._barWidth))
                    .attr('height', lang.hitch(this, this._height))
                    .append('svg:title').text(this._helpers.titleText);
        },

        _x: function(d, i) {
            return i * (barPadding + this._barWidth());
        },

        _height: function(d) {
            return Math.max(2, this._heightPerMinute() * d.minutesBooked);
        },

        _displayWidth: function() {
            return domAttr.get(this._svgNode, 'width') - 40;
        },

        _y: function(d) {
            var x = Math.min(
                this._heightForTimebars() - 2,
                this._heightForTimebars() - this._heightPerMinute() * d.minutesBooked
            );
            return x;
        },

        _heightForTimebars: function() {
            return domAttr.get(this._svgNode, 'height');
        },

        _heightPerMinute: function() {
            return this._heightForTimebars() / maxMinutes;
        },

        _barWidth: function() {
            return (this._displayWidth() / this._dayEntries.length) - barPadding;
        },

        _helpers: {
            fill: function(entry) {
                if (!entry.hasOwnProperty('minutesToWork')) {
                    return "white";
                }
                return entry.minutesBooked < entry.minutesToWork ? '#b5b5b5' : 'white';
            },

            titleText: function(d) {
                var date = locale.format(timehelper.dateToJsDate(d.date), {selector: 'date'});
                if (d.minutesBooked !== 0) {
                    date += ' (' + timehelper.minutesToHMString(d.minutesBooked) + ')';
                }
                return date;
            }
        }
    });

    return declare([Widget, Templated], {
        templateString: templateString,
        baseClass: 'thisMonthDiagram',

        buildRendering: function() {
            this.inherited(arguments);

            this._updateLabels();

            timecardModel.getMonthList().then(lang.hitch(this, function(data) {
                var minutesBookedByDay = [];
                array.forEach(data.days, function(entry) {
                    minutesBookedByDay.push({
                        date: entry.date,
                        minutesBooked: entry.sumInMinutes
                    });
                });
                new MinutesBookedBlockRenderer(this.bookedTimePerDayGraph, minutesBookedByDay).render()

                // Still needed for the toWork-line
                this._renderDays(data.days);

                this._updateUpperLeftRect();
            }));

            timecardModel.getMonthStatistics().then(lang.hitch(this, function(result) {
                var overtime = result.booked.minutesBooked - result.towork.minutesToWork;
                this.overtimeLabel.innerHTML = timehelper.minutesToHMString(overtime) + ' Overtime';
            }), function(err) {
                api.defaultErrorHandler(err);
            });
        },

        _updateLabels: function() {
            var thisYear = (new Date()).getFullYear(),
                thisMonth = (new Date()).getMonth(),
                first = new Date(thisYear, thisMonth, 1, 0, 0, 0),
                last = new Date(thisYear, thisMonth + 1, 0, 0, 0, 0);
            this.firstDayLabel.innerHTML = locale.format(first, {selector: 'date', datePattern: 'EEE d'});
            this.lastDayLabel.innerHTML = locale.format(last, {selector: 'date', datePattern: 'EEE d'});
        },

        // Stores the data by day we got from the server for helper functions.
        _days: null,

        _renderDays: function(days) {
            this.days = days;

            var svg = d3.select(this.bookedTimePerDayGraph);
            var svgData = svg.selectAll().data(days);

            var greenBarY = lang.hitch(this, function(d, i) {
                var date = timehelper.dateToJsDate(d.date);
                if (locale.isWeekend(date)) {
                    return this._heightForTimebars();
                }
                return this._heightForMinutesToWork();
            });

            // horizontal lines
            svgData.enter()
                .append('svg:line')
                    .attr('x1', lang.hitch(this, function(d, i) {
                        return i * (barPadding + this._barWidth());
                    }))
                    .attr('x2', lang.hitch(this, function(d, i) {
                        return (i + 1) * (barPadding + this._barWidth());
                    }))
                    .attr('y1', greenBarY)
                    .attr('y2', greenBarY)
                    .attr('stroke', '#6aa700');

            // vertical lines
            svgData.enter()
                .append('svg:line')
                    .attr('x1', lang.hitch(this, function(d, i) {
                        return i * (barPadding + this._barWidth());
                    }))
                    .attr('x2', lang.hitch(this, function(d, i) {
                        return (i) * (barPadding + this._barWidth());
                    }))
                    .attr('y1', function(d, i) {
                        if (i === 0) {
                            return greenBarY(d, i);
                        }
                        return greenBarY(days[i - 1], i - 1);
                    })
                    .attr('y2', greenBarY)
                    .attr('stroke', '#6aa700');

            svg.append('rect')
                .attr('x', this._todayX() - 1)
                .attr('width', 2)
                .attr('y', 0)
                .attr('height', this._heightForTimebars())
                .attr('fill', '#0d639b');
        },

        // Functions below here assume _days is set
        _heightPerMinute: function() {
            return this._heightForTimebars() / maxMinutes;
        },

        _heightForMinutesToWork: function() {
            return this._heightForTimebars() - this._heightPerMinute() * this._minutesToWork();
        },

        _heightForTimebars: function() {
            return domAttr.get(this.bookedTimePerDayGraph, 'height');
        },

        _minutesToWork: function() {
            return 450;
        },

        _displayWidth: function() {
            return domAttr.get(this.bookedTimePerDayGraph, 'width') - 40;
        },

        _barWidth: function() {
            return (this._displayWidth() / this.days.length) - barPadding;
        },

        _todayX: function() {
            return (new Date()).getDate() * (this._barWidth() + barPadding) - (barPadding / 2);
        },

        _updateUpperLeftRect: function() {
            domAttr.set(this.upperLeftRect, 'height', this._heightForMinutesToWork());
            domAttr.set(this.upperLeftRect, 'width', this._todayX());
        }
    });
});

