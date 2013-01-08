require({cache:{
'url:phpr/template/statisticsView.html':"<div class=\"statisticsView\">\n    This is a statistics page.\n</div>\n"}});
define("phpr/StatisticsView", [
    'dojo/_base/declare',
    'dijit/_Widget',
    'dijit/_TemplatedMixin',
    'dijit/_WidgetsInTemplateMixin',
    'dojo/text!phpr/template/statisticsView.html'
], function(declare, Widget, Templated, WidgetsInTemplate, templateString) {
    return declare([Widget, Templated, WidgetsInTemplate], {
        templateString: templateString
    });
});
