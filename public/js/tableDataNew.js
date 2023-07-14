class tableData {

    constructor(elementSelector, config) {
        this.element = $(elementSelector);
        this.id = this.element.prop('id');
        this.config = config;
        this.filter = [];
        this.sort = [];
        this.currentPage = 1;
        this.timeoutSearch = null;
        this.initialize();
    }

    initialize() {
        this.setupEventListeners();
        this.loadData();
    }

    setupEventListeners() {
        const $this = this;

        $(document).on('click', `#${this.id}_tableData_container .page_prev_trigger, #${this.id}_tableData_container .page_next_trigger, #${this.id}_tableData_container .page_trigger`, function () {
            $this.paginationNavigateTrigger($(this).data('page'));
        });

        $(document).on('change', `#${this.id}_perPage`, function () {
            sessionStorage.setItem(`${$this.id}_pageLength`, $(this).val());
            $this.getData(1, function (results) {
                $this.loadData(results);
            });
        });

        $(document).on('click', `.${this.id}_tableData_sort_trigger`, function (e) {
            const order = $(this).attr('data-order');
            const col = $(this).data('col');
            const shiftKey = e.shiftKey;
            $this.toggleSort(col, order, shiftKey);
            $this.getData(1, function (results) {
                $this.loadData(results);
            });
        });

        $(document).on('keyup', `.${this.id}_tableData_search_input`, function () {
            const val = $(this).val();
            const col = $(this).data('col');
            clearTimeout($this.timeoutSearch);
            $this.timeoutSearch = setTimeout(function () {
                $this.addFilterAndReload(col, val);
            }, 200);
        });

        $(document).on('click', `.${this.id}_mobile_filter_trigger`, function () {
            $(`#${$this.id}_tableData_container thead tr:last-child`).toggle();
        });
    }

    paginationNavigateTrigger(page) {
        this.currentPage = page;
        this.getData(page, (results) => {
            this.loadData(results);
        });
    }

    getData(page, callback) {
        const data = {
            page,
            len: $(`#${this.id}_perPage`).val(),
            filter: this.filter,
            sort: this.sort
        };

        $.post(this.config.url, { tableData: JSON.stringify(data) })
            .done(function (results) {
                if (typeof callback === 'function') {
                    callback(results);
                } else {
                    console.error('Invalid callback');
                }
            })
            .fail(function (error) {
                console.error('Invalid data request:', error);
            });
    }

    loadData(results) {
        results = JSON.parse(results);
        if (results.total === 0) {
            this.showNoResults();
        } else {
            this.updateShowingOf(results.data.length, results.total);
            this.updatePaginationDisplay(results.page, results.pages);
            this.renderTableData(results.data);
        }
    }

    renderTableData(data) {
        const $tbody = $(`#${this.id}_tableActual tbody`);
        let html = '';

        data.forEach((item) => {
            html += '<tr>';
            this.config.columns.forEach((column) => {
                const style = column.cellStyle ? ` style="${column.cellStyle}"` : '';
                let value = item[column.col] || '';

                if (column.format === 'usd') {
                    value = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value);
                } else if (column.format === 'percentage') {
                    value = `${value}%`;
                }

                html += `<td${style}>${value}</td>`;
            });
            html += '</tr>';
        });

        $tbody.html(html);
    }

    addFilterAndReload(column, value) {
        if (!value) {
            this.removeFilterAndReload(column);
            return;
        }

        const filter = {
            col: column,
            val: value
        };

        this.filter.push(filter);
        this.getData(1, (results) => {
            this.loadData(results);
        });
    }

    removeFilterAndReload(column) {
        this.filter = this.filter.filter((filter) => filter.col !== column);
        this.getData(1, (results) => {
            this.loadData(results);
        });
    }

    toggleSort(column, order, shiftKey) {
        if (!shiftKey) {
            this.sort = [{ col: column, order }];
        } else {
            this.sort.push({ col: column, order });
        }

        this.toggleSortIcons();
    }

    toggleSortIcons() {
        const $sortTriggers = $(`#${this.id}_tableData_container .${this.id}_tableData_sort_trigger`);
        $sortTriggers.removeClass('sorted_asc sorted_desc');

        this.sort.forEach((sort) => {
            const $trigger = $sortTriggers.filter(`[data-col="${sort.col}"]`);
            $trigger.addClass(`sorted_${sort.order}`);
        });
    }

    updateShowingOf(showing, total) {
        const $showingElem = $(`#${this.id}_tableData_showing`);
        $showingElem.text(`${showing}/${total}`);
    }

    updatePaginationDisplay(currentPage, totalPages) {
        const $prevTrigger = $(`#${this.id}_tableData_container .page_prev_trigger`);
        const $nextTrigger = $(`#${this.id}_tableData_container .page_next_trigger`);
        const $pageTriggers = $(`#${this.id}_tableData_container .page_trigger`);

        $pageTriggers.removeClass('current');
        $pageTriggers.filter(`[data-page="${currentPage}"]`).addClass('current');

        if (currentPage === 1) {
            $prevTrigger.addClass('disabled');
        } else {
            $prevTrigger.removeClass('disabled');
        }

        if (currentPage === totalPages) {
            $nextTrigger.addClass('disabled');
        } else {
            $nextTrigger.removeClass('disabled');
        }
    }

    showNoResults() {
        const $tbody = $(`#${this.id}_tableActual tbody`);
        $tbody.html('<tr><td colspan="999">No results found</td></tr>');
    }
}
