/**
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @category   PHProjekt
 * @package    Application
 * @subpackage Minutes
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <gustavo.solt@mayflower.de>
 */

dojo.provide("phpr.MinutesItem.Grid");

dojo.declare("phpr.MinutesItem.Grid", phpr.Default.SubModule.Grid, {
    _useIdInGrid:function() {
        // Summary:
        //    Draw the ID on the grid.
        return false;
    },

    _customGridLayout:function(gridLayout) {
        // Summary:
        //    Custom functions for the layout.
        var tmp = this.inherited(arguments);

        var deleteFields = new Array('sortOrder', 'minutesId', 'projectId', 'comment');
        tmp = dojo.filter(gridLayout, function(item) {
            return (!phpr.inArray(item['field'], deleteFields));
        });

        for (cell in tmp) {
            if (tmp[cell]['field'] == 'topicId') {
                tmp[cell]['rowSpan'] = 2;
                tmp[cell]['width']   = '9%';
            } else if (tmp[cell]['field'] == 'gridEdit') {
                tmp[cell]['rowSpan'] = 2;
            } else if (tmp[cell]['field'] == 'title') {
                tmp[cell]['width'] = '46%';
            } else if (tmp[cell]['field'] == 'userId') {
                tmp[cell]['width'] = '20%';
            }
        }

        var newGridLayout = [tmp, [
            {
                width:    'auto',
                name:     phpr.nls.get('Comment', this._module),
                field:    'comment',
                type:     phpr.Grid.Cells.Textarea,
                styles:   'text-align: left;',
                editable: false,
                colSpan:  4
            }
        ]];

        return newGridLayout;
    }
});

