define([
    'dojo/_base/declare',
    'dijit/Destroyable',
    'dojo/_base/lang',
    'dojo/_base/array',
    'dojo/topic',
    'phpr/BookingView',
    'phpr/StatisticsView',
    'phpr/TeamStatisticsView'
], function(
    declare,
    destroyable,
    lang,
    array,
    topic,
    BookingView,
    StatisticsView,
    TeamStatisticsView
) {
    return declare(destroyable, {
        baseLayout: null,

        constructor: function(baseLayout) {
            this.baseLayout = baseLayout;
            var eventmap = {
                'phpr/showBookings': 'onBookings',
                'phpr/showStatistics': 'onStatistics',
                'phpr/showTeamStatistics': 'onTeamStatistics'
            };

            for (var top in eventmap) {
                this.own(topic.subscribe(top, lang.hitch(this, eventmap[top])));
            }
        },

        startup: function() {
            this.inherited(arguments);
            this.baseLayout.menubar.onBookingsClick();
        },


        onBookings: function() {
            this.baseLayout.mainContent.set('content', new BookingView());
        },

        onStatistics: function() {
            this.baseLayout.mainContent.set('content', new StatisticsView());
        },

        onTeamStatistics: function() {
            this.baseLayout.mainContent.set('content', new TeamStatisticsView());
        }
    });
});
