require({cache:{
'url:phpr/template/menubar.html':"<div class=\"menubarOuter\">\n    <div class=\"menubarMiddle\">\n        <table class=\"menubar\"><tr>\n            <td class=\"bookingsButton button left\" data-dojo-attach-point=\"bookingsButton\"\n                ><div class=\"menuItem\">Booking</div></td>\n            <td style='display: none;' class=\"startButton button left\" data-dojo-attach-point=\"startButton\"\n                ><div class=\"menuItem\">Start</div></td>\n            <td style='display: none;' class=\"statisticsButton button left\" data-dojo-attach-point=\"statisticsButton\"\n                ><div class=\"menuItem\">Statistics</div></td>\n            <td style=\"width: auto;\"><div><b></b></div></td>\n            <td class=\"button right\" data-dojo-type=\"phpr/Menubar/WarningIcon\"><div><b></b></div></td>\n            <td class=\"logoutButton button right\" data-dojo-attach-point=\"logoutButton\"\n                ><div class=\"logoutIcon menuItem\"></div></td>\n            </tr></table>\n    </div>\n</div>\n"}});
define("phpr/Menubar", [
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojo/on',
    'dojo/dom-class',
    'dijit/_Widget',
    'dijit/_TemplatedMixin',
    'dijit/_WidgetsInTemplateMixin',
    'dijit/_CssStateMixin',
    'dijit/Tooltip',
    'dojo/topic',
    'dojo/text!phpr/template/menubar.html',
    'phpr/Menubar/WarningIcon'
], function(declare, lang, on, clazz, widget, template, widgetsInTemplate, cssStateMixin, Tooltip, topic, templateString) {
    return declare([widget, template, widgetsInTemplate, cssStateMixin], {
        templateString: templateString,
        baseClass: 'menuBar',
        cssStateNodes: {
            logoutButton: 'dijit'
        },

        buildRendering: function() {
            this.inherited(arguments);
            this.own(
                on(this.startButton, 'click', lang.hitch(this, 'onStartClick')),
                on(this.bookingsButton, 'click', lang.hitch(this, 'onBookingsClick')),
                on(this.statisticsButton, 'click', lang.hitch(this, 'onStatisticsClick')),
                on(this.logoutButton, 'click', lang.hitch(this, '_logout'))
            );
            this.own(
                new Tooltip({
                    connectId: this.logoutButton,
                    label: "Log out"
                })
            );
        },

        onStartClick: function() {
            topic.publish('phpr/showLiveBooking');
            clazz.replace(this.domNode, 'menubarOuter start');
        },

        onBookingsClick: function() {
            topic.publish('phpr/showBookings');
            clazz.replace(this.domNode, 'menubarOuter bookings');
        },

        onStatisticsClick: function() {
            topic.publish('phpr/showStatistics');
            clazz.replace(this.domNode, 'menubarOuter statistics');
        },

        _logout: function() {
            window.location = "index.php/Login/logout";
        }
    });
});
