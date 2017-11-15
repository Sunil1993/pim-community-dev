/* global define */
define(
    [
        'jquery',
        'underscore',
        'backgrid',
        'oro/datagrid/row',
        'pim/template/datagrid/row/product'
    ],
    function(
        $,
        _,
        Backgrid,
        BaseRow,
        template
    ) {
    'use strict';

    /**
     * Grid row.
     *
     * Triggers events:
     *  - "clicked" when row is clicked
     *
     * @export  oro/datagrid/row
     * @class   oro.datagrid.Row
     * @extends Backgrid.Row
     */
    return BaseRow.extend({
        tagName: 'div',
        // versionTemplate: _.template(versionTemplate),
        // changesTemplate: _.template(changesTemplate),
        template: _.template(template),

        render() {
            console.trace();

            this.$el.empty();
            const row = $(this.template({}));

            this.$el.append(row);

            for (let i = 0; i < this.cells.length; i++) {
                const cell = this.cells[i];
                // console.log(cell.column)
                const display = cell.column.get('display_thumbnail');
                const renderable = cell.column.get('renderable');

                if (display && renderable) {
                    this.$('.AknGrid-bodyRow').append(cell.render().el);
                }
            }

            this.delegateEvents();

            return this;
        },

        renderCells() {

        },

        /**
         Renders a row of cells for this row's model.
        */
        // render: function () {
        //     this.$el.empty();

        //     const mainLine = $(this.versionTemplate({
        //         version: this.model.get('version'),
        //         id: this.model.get('id')
        //     }));
        //     const changesLine = $(this.changesTemplate({
        //         version: this.model.get('version'),
        //         id: this.model.get('id')
        //     }));

        //     mainLine.on('click', function () {
        //         changesLine.toggle();
        //         mainLine.toggleClass('AknGrid-bodyRow--expanded')
        //         mainLine.find('.AknGrid-expand').toggleClass('AknGrid-expand--expanded')
        //     });

        //     for (let i = 0; i < this.cells.length; i++) {
        //         const cell = this.cells[i];
        //         const line = 'changes' === cell.column.get('name') ? changesLine : mainLine;
        //         line.append(cell.render().el);
        //         if (!cell.column.get('renderable') && 'changes' !== cell.column.get('name')) cell.$el.hide();
        //     }

        //     this.$el.append(mainLine);
        //     this.$el.append(changesLine);
        //     changesLine.hide();

        //     this.delegateEvents();

        //     return this;
        // }
    });
});
