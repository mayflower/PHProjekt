/**
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 2.1 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    $Id$
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

dojo.provide("phpr.Calendar.Grid");

dojo.declare("phpr.Calendar.Grid", phpr.Default.Grid, {
    onLoaded:function(dataContent) {
        // summary:
        //    This function is called when the grid is loaded
        // description:
        //    It takes care of setting the grid headers to the right format, displays the contextmenu
        //    and renders the filter for the grid
        // Layout of the grid
        var meta = phpr.DataStore.getMetaData({url: this.url});

        // Data of the grid
        this.gridData = {
            items: []
        };
        var content = dojo.clone(phpr.DataStore.getData({url: this.url}));
        for (var i in content) {
            this.gridData.items.push(content[i]);
        }
        store = new dojo.data.ItemFileWriteStore({data: this.gridData});

        // Render save Button
        this.setSaveChangesButton(meta);

        // Render export Button
        this.setExportButton(meta);

        if (meta.length == 0) {
            this._node.attr('content', phpr.drawEmptyMessage('There are no entries on this level'));
        } else {
            this.setGridLayout(meta);
            this.grid = new dojox.grid.DataGrid({
                store: store,
                structure: [{
                            defaultCell: {
                                editable: true,
                                type:     dojox.grid.cells.Input,
                                styles:   'text-align: left;'
                            },
                            rows: [this.gridLayout]
                }]
            }, document.createElement('div'));

            this.setClickEdit();

            this._node.attr('content', this.grid.domNode);
            this.grid.startup();

            dojo.connect(this.grid, "onCellClick", dojo.hitch(this, "showForm"));
            dojo.connect(this.grid, "onApplyCellEdit", dojo.hitch(this, "cellEdited"));
            dojo.connect(this.grid, "onStartEdit", dojo.hitch(this, "checkCanEdit"));
        }

        // Because of existing many views, this avoids not to listing the real contents of the DB
        this.updateData();
    }
});
