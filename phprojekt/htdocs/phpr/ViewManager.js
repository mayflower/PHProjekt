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
            this.own(topic.subscribe('phpr/showTimecard', lang.hitch(this, 'onShowTimecard')));
            this.own(topic.subscribe('phpr/showContracts', lang.hitch(this, 'onShowContracts')));
        },

        onShowTimecard: function() {

        },

        onShowContracts: function() {

        },

        renderTimecard: function() {
            this.baseLayout.set('content', 'imagine a timecard here');
        },

        renderContracts: function() {
            this.baseLayout.set('content', 'imagine contracts here');
        }
    });
});
