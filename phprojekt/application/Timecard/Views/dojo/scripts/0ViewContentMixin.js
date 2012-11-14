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
 * @copyright Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license   LGPL v3 (See LICENSE file)
 */
dojo.provide("phpr.Timecard.ViewContentMixin");

dojo.declare("phpr.Timecard.ViewContentMixin", phpr.Default.System.ViewContentMixin, {
    destroyMixin: function() {
        this.view.centerMainContent.destroyDescendants();
        delete this.view.gridBox;
        delete this.view.detailsBox;
    },

    update: function() {
        var content = new phpr.Default.System.TemplateWrapper({
            templateName: "phpr.Timecard.template.mainContent.html"
        });

        this.view.gridBox = content.gridBox;
        this.view.detailsBox = content.detailsBox;

        this.view.centerMainContent.set('content', content);
    }
});
