define([
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojo/_base/array',
    'dojo/on',
    'dojo/dom-class',
    'dojo/store/JsonRest',
    'dojo/store/Memory',
    'phpr/BookingList/BookingBlock',
    'phpr/Api',
    'phpr/Timehelper',
    'dojo/text!phpr/template/bookingList/bookingCreator.html'
], function(declare, lang, array, on, clazz, JsonRest, Memory, BookingBlock, api, time, templateString) {
    return declare([BookingBlock], {
        templateString: templateString,

        buildRendering: function() {
            this.inherited(arguments);

            this.date.set('value', new Date());
            this.own(this.form.on('submit', lang.hitch(this, this._submit)));

            api.getData(
                'index.php/Project/Project',
                {query: {projectId: 1, recursive: true}}
            ).then(lang.hitch(this, function(projects) {
                var options = [{id: '1', name: '1 Unassigned', label: '<span class="projectId">1</span> Unassigned'}];
                array.forEach(projects, function(p) {
                    options.push({
                        id: '' + p.id,
                        name: '' + p.id + ' ' + p.title,
                        label: '<span class="projectId">' + p.id + '</span> ' + p.title
                    });
                });

                var store = new Memory({
                    data: options
                });

                this.project.set('store', store);
            }));
        },

        postCreate: function() {
            this.own(on(this.notesIcon, 'click', lang.hitch(this, 'toggleNotes')));
            this.start.set('placeHolder', 'Start');
            this.end.set('placeHolder', 'End');
            this.notes.set('placeHolder', 'Notes');
        },

        toggleNotes: function() {
            clazz.toggle(this.notesContainer, 'open');
        },

        _getStartRegexp: function() {
            return '(\\d{1,2}[:\\. ]?\\d{2})';
        },

        _getEndRegexp: function() {
            return '(\\d{1,2}[:\\. ]?\\d{2})?';
        },

        _submit: function() {
        },

        _setDateAttr: function(date) {
            this.date.set('value', date);
        }
    });
});

