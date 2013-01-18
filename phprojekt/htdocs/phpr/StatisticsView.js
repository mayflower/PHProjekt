define([
    'dojo/_base/declare',
    'dojo/dom-attr',
    'dojo/date/locale',
    'dijit/_Widget',
    'dijit/_TemplatedMixin',
    'dijit/_WidgetsInTemplateMixin',
    'phpr/Api',
    'dojo/text!phpr/template/statisticsView.html',
    'd3/d3.v3.js'
], function(declare, domAttr, locale, Widget, Templated, WidgetsInTemplate, api, templateString) {
    return declare([Widget, Templated, WidgetsInTemplate], {
        templateString: templateString,

        year: (new Date()).getFullYear(),
        month: (new Date()).getMonth(),

        buildRendering: function() {
            this.inherited(arguments);

            api.getData(
                'index.php/Timecard/index/monthList',
                {query: {year: this.year, month: this.month + 1}}
            ).then(dojo.hitch(this, this._renderData));
        },

        _renderData: function(data) {
            this._updateLabels();
            var days = data.days,
                dataCount = days.length,
                maxMinutes = 1000,
                minutesToWork = 450,
                displayHeight = domAttr.get(this.bookedTimePerDayGraph, "height"),
                heightForTimebars = displayHeight - 20,
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

            d3.select(this.bookedTimePerDayGraph)
                .selectAll("rect")
                .data(days)
                .enter().append("svg:rect")
                    .attr("fill", function(d) {
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
                    });

            d3.select(this.bookedTimePerDayGraph)
                .append("line")
                    .attr("x1", 0)
                    .attr("x2", displayWidth)
                    .attr("y1", greenBarY)
                    .attr("y2", greenBarY)
                    .attr("stroke", "#6aa700");

            if (onCurrentMonth) {
                var currentDate = (new Date()).getDate();
                d3.select(this.bookedTimePerDayGraph)
                    .append("line")
                    .attr("x1", todayX)
                    .attr("x2", todayX)
                    .attr("y1", 0)
                    .attr("y2", heightForTimebars)
                    .attr("stroke", "#0d639b");
            }

        },

        _updateLabels: function() {
            var first = new Date(this.year, this.month, 1, 0, 0, 0),
                last = new Date(this.year, this.month + 1, 0, 0, 0, 0);
            this.firstDayLabel.innerHTML = locale.format(first, {selector: 'date', datePattern: 'EEE d'});
            this.lastDayLabel.innerHTML = locale.format(last, {selector: 'date', datePattern: 'EEE d'});
        }
    });

});
