define([
    'dojo/_base/declare',
    'dijit/Destroyable',
    'dojo/_base/lang',
    'dojo/_base/array',
    'dojo/topic',
    'phpr/Api',
    'dojo/Deferred',
    'dojo/DeferredList'
], function(declare, destroyable, lang, array, topic, api, Deferred, DeferredList) {
    return declare(destroyable, {
        baseLayout: null,

        constructor: function(baseLayout) {
            this.baseLayout = baseLayout;
            var eventmap = {
                'phpr/showLiveBooking': 'onLiveBooking',
                'phpr/showBookings': 'onBookings'
            };

            for (var top in eventmap) {
                this.own(topic.subscribe(top, lang.hitch(this, eventmap[top])));
            }
        },

        onLiveBooking: function() {
            this.baseLayout.mainContent.set('content', 'imagine a timecard here');
        },

        onBookings: function() {
            this.baseLayout.mainContent.set('content', 'imagine contracts here');
        }
    });
});
