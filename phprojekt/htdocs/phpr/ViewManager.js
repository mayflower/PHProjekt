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
        setterTimeout: null,

        constructor: function(baseLayout) {
            this.baseLayout = baseLayout;
            this.own(topic.subscribe('phpr/changePage', lang.hitch(this, 'onPageChange')));
        },

        startup: function() {
            this.inherited(arguments);
            var validHashes = ['statistics', 'teamStatistics', 'bookings'];
            if (window.location.hash) {
                var hash = window.location.hash.substring(1);
                if (validHashes.indexOf(hash) !== -1) {
                    return this.onPageChange(hash);
                }
            }
            this.onPageChange('bookings');
        },

        onPageChange: function(page) {
            if (this._destroyed) {
                return;
            }

            var changedTo = page;
            switch (page) {
                case 'statistics':
                    this._setContent(new StatisticsView(), changedTo);
                    break;
                case 'teamStatistics':
                    this._setContent(new TeamStatisticsView(), changedTo);
                    break;
                default:
                    /* Fall through */
                case 'bookings':
                    changedTo = 'bookings';
                    this._setContent(new BookingView(), changedTo);
                    break;
            }

            topic.publish('phpr/changedPage', changedTo);
        },

        _setContent: function(obj, name) {
            clearTimeout(this.setterTimeout);
            this.setterTimeout = setTimeout(lang.hitch(this, function() {
                if (this._destroyed) {
                    return;
                }

                this.baseLayout.mainContent.set('content', obj);
                window.location.hash = '#' + name;
            }), 0);
        }
    });
});
