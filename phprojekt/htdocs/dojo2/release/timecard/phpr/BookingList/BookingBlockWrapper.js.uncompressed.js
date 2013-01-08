define("phpr/BookingList/BookingBlockWrapper", [
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dijit/_WidgetBase',
    'dojo/dom-style',
    'phpr/BookingList/BookingBlock',
    'phpr/BookingList/BookingEditor'
], function(declare, lang, _WidgetBase, style, BookingBlock, BookingEditor) {
    return declare([_WidgetBase], {
        bookingBlock : null,
        editBlock: null,
        store: null,

        postCreate: function() {
            this.inherited(arguments);
            this.bookingBlock = new BookingBlock(this.params);
            this.bookingBlock.placeAt(this.domNode);
            this.bookingBlock.on('editClick', lang.hitch(this, '_editClick'));
            this.own(this.bookingBlock);
            this.bookingBlock.startup();
        },

        _editClick: function() {
            var b = this.bookingBlock.get('booking');

            style.set(this.bookingBlock.domNode, 'display', 'none');

            if (this.editBlock) {
                this.editBlock.destroyRecursive();
            }

            this.editBlock = new BookingEditor({ booking: b, store: this.store });
            this.editBlock.placeAt(this.domNode);
            this.editBlock.on('editCancel', lang.hitch(this, '_editCancel'));
            this.editBlock.startup();
            this.own(this.editBlock);
        },

        _editCancel: function() {
            if (this.editBlock) {
                this.editBlock.destroyRecursive();
                this.editBlock = null;
            }

            style.set(this.bookingBlock.domNode, 'display', '');
        }
    });
});
