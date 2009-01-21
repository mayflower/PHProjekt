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
 * @version    $Id:$
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

dojo.provide("phpr.Gfx");

dojo.declare("phpr.Gfx", null, {
    makeText:function(surface, text, font, fill, stroke) {
        // summary:
        //    Draw a text in the booked zone
        // description:
        //    Draw a text in the booked zone
        var t = surface.createText(text);
        if (font) {
            t.setFont(font);
        }
        if (fill) {
            t.setFill(fill);
        }
        if (stroke) {
            t.setStroke(stroke);
        }
        return t;
    }
});
