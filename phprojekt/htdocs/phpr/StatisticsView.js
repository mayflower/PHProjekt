define([
    'dojo/_base/declare',
    'dojo/dom-attr',
    'dijit/_Widget',
    'dijit/_TemplatedMixin',
    'dijit/_WidgetsInTemplateMixin',
    'phpr/Api',
    'dojo/text!phpr/template/statisticsView.html',
    'd3/d3.v3.js'
], function(declare, domAttr, Widget, Templated, WidgetsInTemplate, api, templateString) {
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
            var days = data.days,
                dataCount = days.length,
                maxMinutes = 1000,
                minutesToWork = 450,
                displayHeight = domAttr.get(this.bookedTimePerDayGraph, "height"),
                heightForTimebars = displayHeight - 20,
                heightPerMinute = heightForTimebars / maxMinutes,
                displayWidth = domAttr.get(this.bookedTimePerDayGraph, "width"),
                barPadding = 2,
                barWidth = (displayWidth - 40) / dataCount - barPadding;

            d3.select(this.bookedTimePerDayGraph)
                .selectAll("rect")
                .data(days)
                .enter().append("svg:rect")
                    .attr("fill", function(d) {
                        return d.sumInMinutes < minutesToWork ? "grey" : "lightgrey";
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
                    .attr("y1", heightForTimebars - minutesToWork * heightPerMinute)
                    .attr("y2", heightForTimebars - minutesToWork * heightPerMinute)
                    .attr("stroke", "green");
        }
    });

});
