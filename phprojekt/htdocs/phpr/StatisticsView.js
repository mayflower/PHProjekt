define([
    'dojo/_base/declare',
    'dijit/_Widget',
    'dijit/_TemplatedMixin',
    'dijit/_WidgetsInTemplateMixin',
    'dojo/text!phpr/template/statisticsView.html',
    'phpr/Statistics/WorktimeMonthGraph'
], function(
    declare,
    Widget,
    Templated,
    WidgetsInTemplate,
    templateString
) {
    return declare([Widget, Templated, WidgetsInTemplate], {
        templateString: templateString
    });

});
