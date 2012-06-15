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
 * @subpackage Timecard
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */

dojo.provide("phpr.Timecard.Favorites");

dojo.declare("phpr.Timecard.Favorites", dojo.dnd.Source, {
    MIN_HEIGHT: 18,

    onDrop: function(source, nodes, copy) {
        if (this != source) {
            this.onDropExternal(source, nodes, copy);
            if (source.node.id == 'projectFavoritesSource') {
                // If there are no projects in the box, don't let it reduce its height so much
                if (projectFavoritesSource && projectFavoritesSource.getAllNodes().length === 0) {
                    dojo.style('projectFavoritesSource', 'height', this.MIN_HEIGHT + 'px');
                }
                dojo.style('projectFavoritesTarget', 'height', '');
            } else if (source.node.id == 'projectFavoritesTarget') {
                // If there are no projects in the box, don't let it reduce its height so much
                if (projectFavoritesTarget && projectFavoritesTarget.getAllNodes().length === 0) {
                    dojo.style('projectFavoritesTarget', 'height', this.MIN_HEIGHT + 'px');
                }
                dojo.style('projectFavoritesSource', 'height', '');
            }
        } else {
            this.onDropInternal(nodes, copy);
        }
    },

    markupFactory: function(params, node) {
        params._skipStartup = true;
        return new phpr.Timecard.Favorites(node, params);
    }
});
