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

            this.own(topic.subscribe('phpr/changedPage', lang.hitch(this, 'onChangedPage')));
        },

        onChangedPage: function(page) {
            switch (page) {
                case 'statistics':
                    clazz.replace(this.domNode, 'menubarOuter statistics');
                    break;
                case 'teamStatistics':
                    clazz.replace(this.domNode, 'menubarOuter teamStatistics');
                    break;
                case 'bookings':
                    clazz.replace(this.domNode, 'menubarOuter bookings');
                    break;
                default:
                    break;
            }
        },

        onBookingsClick: function() {
            topic.publish('phpr/changePage', 'bookings');
        },

        onStatisticsClick: function() {
            topic.publish('phpr/changePage', 'statistics');
        },

        onTeamStatisticsClick: function() {
            topic.publish('phpr/changePage', 'teamStatistics');
        },

        _logout: function() {
            window.location = "index.php/Login/logout";
        }
    });
});
