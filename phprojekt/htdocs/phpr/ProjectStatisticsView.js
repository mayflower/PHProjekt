define([
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojo/_base/array',
    'dijit/_Widget',
    'dijit/_TemplatedMixin',
    'dijit/_WidgetsInTemplateMixin',
    'dojo/text!phpr/template/projectStatisticsView.html'
], function(
    declare,
    lang,
    array,
    Widget,
    Templated,
    WidgetsInTemplate,
    templateString
) {

    return declare([Widget, Templated, WidgetsInTemplate], {
        templateString: templateString
    });
});
