define([
    'dojo/_base/declare',
    'dojo/dom-attr',
    'dijit/_Widget',
    'dijit/_TemplatedMixin',
    'dijit/_WidgetsInTemplateMixin',
    'dojo/text!phpr/template/statisticsView.html',
    'd3/d3.v3.js',
    'phpr/Api'
], function(declare, domAttr, Widget, Templated, WidgetsInTemplate, templateString, api) {
    return declare([Widget, Templated, WidgetsInTemplate], {
        templateString: templateString,

        buildRendering: function() {
            this.inherited(arguments);

            var data = [{date: new Date(2013, 1, 1, 0, 0, 0), minutes: 500},
                        {date: new Date(2013, 1, 2, 0, 0, 0), minutes: 270},
                        {date: new Date(2013, 1, 3, 0, 0, 0), minutes: 568},
                        {date: new Date(2013, 1, 4, 0, 0, 0), minutes: 436},
                        {date: new Date(2013, 1, 5, 0, 0, 0), minutes: 830},
                        {date: new Date(2013, 1, 6, 0, 0, 0), minutes: 100},
                        {date: new Date(2013, 1, 7, 0, 0, 0), minutes: 1000},
                        {date: new Date(2013, 1, 8, 0, 0, 0), minutes: 300},
                        {date: new Date(2013, 1, 9, 0, 0, 0), minutes: 0}],
                dataCount = 30,
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
                .data(data)
                .enter().append("svg:rect")
                    .attr("fill", function(d) {
                        return d.minutes < minutesToWork ? "grey" : "lightgrey";
                    })
                    .attr("x", function(d, i) {
                        return i * (barPadding + barWidth);
                    })
                    .attr("y", function(d) {
                        return Math.min(heightForTimebars - 2, heightForTimebars - heightPerMinute * d.minutes);
                    })
                    .attr("width", barWidth)
                    .attr("height", function(d) {
                        return Math.max(2, heightPerMinute * d.minutes);
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
