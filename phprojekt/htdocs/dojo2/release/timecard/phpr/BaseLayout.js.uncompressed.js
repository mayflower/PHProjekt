require({cache:{
'url:phpr/template/baseLayout.html':"<div class=\"baseLayout\">\n    <div data-dojo-type=\"phpr/Menubar\" data-dojo-attach-point=\"menubar\"></div>\n    <div class=\"mainContent\" data-dojo-type=\"dijit/layout/ContentPane\" data-dojo-attach-point=\"mainContent\">\n    </div>\n</div>\n"}});
define("phpr/BaseLayout", [
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dijit/_Widget',
    'dijit/_TemplatedMixin',
    'dijit/_WidgetsInTemplateMixin',
    'dojo/text!phpr/template/baseLayout.html',
    'dijit/layout/ContentPane',
    'phpr/Menubar',
    'phpr/BookingList'
], function(declare, lang, widget, template, widgetsInTemplate, templateString) {
    return declare([widget, template, widgetsInTemplate], {
        baseClass: 'baseLayout',
        templateString: templateString
    });
});
