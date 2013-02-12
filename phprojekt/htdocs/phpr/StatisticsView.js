define([
    'dojo/_base/lang',
    'dojo/_base/declare',
    'dojo/dom-attr',
    'dojo/date/locale',
    'dojo/DeferredList',
    'dijit/_Widget',
    'dijit/_TemplatedMixin',
    'dijit/_WidgetsInTemplateMixin',
    'phpr/Api',
    'phpr/Timehelper',
    'dojo/text!phpr/template/statisticsView.html',
    'd3/d3.v3.js'
], function(lang, declare, domAttr, locale, DeferredList, Widget, Templated, WidgetsInTemplate, api, timehelper,
            templateString) {
    return declare([Widget, Templated, WidgetsInTemplate], {
        templateString: templateString,

        year: (new Date()).getFullYear(),
        month: (new Date()).getMonth(),

        buildRendering: function() {
            this.inherited(arguments);

            this._updateLabels();

            api.getData(
                'index.php/Timecard/index/monthList',
                {query: {year: this.year, month: this.month + 1}}
            ).then(lang.hitch(this, this._renderData));

            var theDate = new Date(this.year, this.month, 1, 0, 0, 0);
            (new DeferredList([
                api.getData(
                    'index.php/Timecard/index/minutesBooked',
                    {query: {year: this.year, month: this.month + 1}}
                ),
                api.getData(
                    'index.php/Timecard/index/minutesToWork',
                    {query: {year: this.year, month: this.month + 1}}
                )
            ])).then(lang.hitch(this, function(datas) {
                var netMinutes = datas[0][1].minutesBooked - datas[1][1].minutesToWork,
                    text = (netMinutes < 0) ? '-' : '',
                    difference = Math.abs(netMinutes);
                if (difference >= 60) {
                    text += Math.floor(difference / 60) + 'h';
                }
                if (difference < 60 || difference % 60 !== 0) {
                    text += difference % 60 + 'm';
                }
                text += ' Overtime';

                this.overtimeLabel.innerHTML = text;
            }));
        },

        _renderData: function(data) {
            var days = data.days,
                dataCount = days.length,
                maxMinutes = 1000,
                minutesToWork = 450,
                displayHeight = domAttr.get(this.bookedTimePerDayGraph, "height"),
                heightForTimebars = displayHeight;
                heightPerMinute = heightForTimebars / maxMinutes,
                displayWidth = domAttr.get(this.bookedTimePerDayGraph, "width"),
                barPadding = 2,
                barWidth = (displayWidth - 40) / dataCount - barPadding,
                greenBarY = heightForTimebars - minutesToWork * heightPerMinute,
                todayX = (new Date()).getDate() * (barWidth + barPadding) - (barPadding / 2),
                currentYear = (new Date()).getFullYear(), currentMonth = (new Date()).getMonth(),
                onCurrentMonth = (this.year == currentYear && this.month == currentMonth),
                onPreviousMonth = (this.year < currentYear || this.month < currentMonth);

            if (onCurrentMonth) {
                domAttr.set(this.upperLeftRect, 'height', greenBarY);
                domAttr.set(this.upperLeftRect, 'width', todayX);
            } else if (onPreviousMonth) {
                domAttr.set(this.upperLeftRect, 'height', greenBarY);
                domAttr.set(this.upperLeftRect, 'width', displayWidth);
            } else {
                domAttr.set(this.upperLeftRect, 'width', 0);
            }

            var svg = d3.select(this.bookedTimePerDayGraph);
            var update = svg.selectAll()
                .data(days).enter().append("svg:rect");

            update.attr("fill", function(d) {
                        return d.sumInMinutes < minutesToWork ? "#b5b5b5" : "white";
                    })
                    .attr("x", function(d, i) {
                        return i * (barPadding + barWidth);
                    })
                    .attr("y", function(d) {
                        return Math.min(heightForTimebars - 2, heightForTimebars - heightPerMinute * d.sumInMinutes);
                    })
                    .attr("width", barWidth)
                    .attr("height", function(d) {
                        return Math.max(2, heightPerMinute * d.sumInMinutes);
                    })
                    .append("svg:title")
                        .text(function(d) {
                            var date = locale.format(timehelper.dateToJsDate(d.date), {selector: 'date'});
                            return date + ' (' + d.sumInHours + ')';

                        });

            svg.append("line")
                    .attr("x1", 0)
                    .attr("x2", displayWidth)
                    .attr("y1", greenBarY)
                    .attr("y2", greenBarY)
                    .attr("stroke", "#6aa700");

            if (onCurrentMonth) {
                var currentDate = (new Date()).getDate();
                svg.append("rect")
                    .attr("x", todayX - 1)
                    .attr("width", 2)
                    .attr("y", 0)
                    .attr("height", heightForTimebars)
                    .attr("fill", "#0d639b");
            }

            update.exit().remove();
            svg.exit().remove();
        },

        _updateLabels: function() {
            var first = new Date(this.year, this.month, 1, 0, 0, 0),
                last = new Date(this.year, this.month + 1, 0, 0, 0, 0);
            this.firstDayLabel.innerHTML = locale.format(first, {selector: 'date', datePattern: 'EEE d'});
            this.lastDayLabel.innerHTML = locale.format(last, {selector: 'date', datePattern: 'EEE d'});
        }
    });

});
