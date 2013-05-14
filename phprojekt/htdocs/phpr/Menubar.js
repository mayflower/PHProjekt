define([
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
                on(this.teamStatisticsButton, 'click', lang.hitch(this, 'onTeamStatisticsClick')),
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

        onTeamStatisticsClick: function() {
            topic.publish('phpr/showTeamStatistics');
            clazz.replace(this.domNode, 'menubarOuter teamStatistics');
        },

        _logout: function() {
            window.location = "index.php/Login/logout";
        }
    });
});
