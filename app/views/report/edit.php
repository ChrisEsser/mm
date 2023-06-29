<?php

/** @var Report $report */
$report = $this->getVar('report');
$countReports = $this->getVar('countReports');

?>

<script>

    var preIncluded = <?=json_encode($report->getDetails()->include)?>;

</script>

<h1 class="page_header"><?=($report->report_id) ? 'Edit' : 'Create'?> Report</h1>

<form method="POST" action="/reports/save">

    <input type="hidden" name="report" id="report" value="<?=$report->report_id?>" />

    <div class="row">

        <div class="mb-3 col-sm-12">
            <label for="title" class="form-label">Title</label>
            <input type="text" class="form-control" id="title" name="title" aria-describedby="titleHelp" value="<?=$report->title?>" />
        </div>

    </div>

    <div class="row">

        <div class="mb-3 col-sm-4">
            <label for="reporting_on" class="form-label">Reporting On</label>
            <select class="form-control" id="reporting_on" name="details[reporting_on]" aria-describedby="reporting_onHelp">
                <option value="merchant" <?=($report->getDetails()->reporting_on == 'merchant') ? 'selected' : ''?>>Transaction Merchant</option>
                <option value="title" <?=($report->getDetails()->reporting_on == 'title') ? 'selected' : ''?>>Transaction Title</option>
                <option value="category_primary" <?=($report->getDetails()->reporting_on == 'category_primary') ? 'selected' : ''?>>Category Primary</option>
                <option value="category_detail" <?=($report->getDetails()->reporting_on == 'title') ? 'category_detail' : ''?>>Category Detail</option>
            </select>
        </div>

        <div class="mb-3 col-sm-4">
            <label for="graph_type" class="form-label">Graph Style</label>
            <select class="form-control" id="graph_type" name="details[graph_type]" aria-describedby="rgraph_typeHelp">
                <option value="pie" <?=($report->getDetails()->graph_type == 'pie') ? 'selected' : ''?>>Pie</option>
                <option value="donut" <?=($report->getDetails()->graph_type == 'donut') ? 'selected' : ''?>>Donut</option>
                <option value="line" <?=($report->getDetails()->graph_type == 'line') ? 'selected' : ''?>>Line</option>
                <option value="bar" <?=($report->getDetails()->graph_type == 'bar') ? 'category_detail' : ''?>>Bar</option>
            </select>
        </div>

        <div class="mb-3 col-sm-4">
            <label for="title" class="form-label">Graph Width Size</label>
            <select class="form-control" id="size" name="size">
                <option value="3" <?=($report->size == 3) ? 'selected' : ''?>>1/4th</option>
                <option value="4" <?=($report->size == 4) ? 'selected' : ''?>>1/3rd</option>
                <option value="6" <?=($report->size == 6) ? 'selected' : ''?>>1/2</option>
                <option value="8" <?=($report->size == 8) ? 'selected' : ''?>>2/3rd</option>
                <option value="9" <?=($report->size == 9) ? 'selected' : ''?>>3/4th</option>
                <option value="12" <?=($report->size == 12) ? 'selected' : ''?>>Full</option>
            </select>
            <div id="sizeHelp" class="form-text">This is a fraction of screen width pn larger screens. Small devices will always stack.</div>
        </div>

        <?php if ($countReports) { ?>
            <div class="mb-3 col-sm-4">
                <label for="sort_order" class="form-label">Sort Order</label>
                <select class="form-control" id="sort_order" name="sort_order">
                    <?php for ($i = 0; $i < $countReports; $i++) { ?>
                        <option value="<?=$i?>" <?=($i == $report->sort_order) ? 'selected' : ''?>><?=$i+1?></option>
                    <?php } ?>
                    <option value="<?=$i?>" <?=($report->sort_order == '') ? 'selected' : ''?>><?=$i+1?></option>
                </select>
                <div id="sizeHelp" class="form-text">The order in which the graph will display. New graphs default to last, but you can change that to make it display higher up the screen.</div>
            </div>
        <?php } else { ?>
            <input type="hidden" name="sort_order" value="0" />
        <?php } ?>

        <div class="mb-3 col-sm-4">
            <label for="series" class="form-label">Series</label>
            <select class="form-control" id="series" name="details[series]">
                <option value="year" <?=($report->getDetails()->series == 'year') ? 'selected' : ''?>>Year</option>
                <option value="quarter" <?=($report->getDetails()->series == 'quarter') ? 'selected' : ''?>>Quarter</option>
                <option value="month" <?=($report->getDetails()->series == 'month') ? 'selected' : ''?>>Month</option>
                <option value="day" <?=($report->getDetails()->series == 'day') ? 'selected' : ''?>>Day</option>
            </select>
            <div id="seriesHelp" class="form-text">The units of time transactions are grouped into.</div>
        </div>

        <div class="mb-3 col-sm-4">
            <label for="series" class="form-label">Length</label>
            <select class="form-control" id="length" name="details[length]">
                <option value="7d" <?=($report->getDetails()->length == '7d') ? 'selected' : ''?>>For The Last 7 Days</option>
                <option value="1m" <?=($report->getDetails()->length == '1m') ? 'selected' : ''?>>For The Last Month</option>
                <option value="1q" <?=($report->getDetails()->length == '1q') ? 'selected' : ''?>>For The Last Quarter</option>
                <option value="1y" <?=($report->getDetails()->length == '1y') ? 'selected' : ''?>>For The Last Year</option>
                <option value="2y" <?=($report->getDetails()->length == '2y') ? 'selected' : ''?>>For The Last 2 Years</option>
                <option value="3y" <?=($report->getDetails()->length == '3y') ? 'selected' : ''?>>For The Last 3 Years</option>
                <option value="all" <?=($report->getDetails()->length == 'all') ? 'selected' : ''?>>All Time</option>
            </select>
            <div id="lengthHelp" class="form-text">The length of time to look for transactions.</div>
        </div>

    </div>

    <div class="row">

        <div class="mb-3 col-md-12">
            <label for="include" class="form-label">Report Groups</label>
            <div id="include_container"></div>
            <button type="button" class="btn btn-sm btn-round btn-info" id="add_include_trigger"><i class="bi bi-plus"></i>&nbsp;Add Group</button>
        </div>

    </div>

    <div>
        <button type="submit" class="btn btn-success btn-lg"><i class="bi bi-check-circle"></i>&nbsp;&nbsp;Save</button>
    </div>

</form>

<div class="modal fade" id="lookupModal" tabindex="-1" aria-labelledby="lookupModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="lookupModalLabel"><span id="lookup_type"></span> Group</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <p>Select one and add it or select multiple to group them. For example, say you want to group Uber and Lift  and name it taxis as one bar or line in a graph, select those two merchants then hit "Add Selected".</p>

                <div class="mb-2">
                    <label class="form-label" for="include_alias">Group Alias</label>
                    <input type="text" class="form-control" id="include_alias" placeholder="Alias" />
                </div>

                <div class="mb-2">
                    <div class="input-group" style="position: relative">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" id="lookup_search" placeholder="Search" />
                    </div>
                    <div id="searchResults" style="position: absolute; width: calc(100% - 30px); z-index: 1; display: none">
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center">Result 1<button class="btn btn-sm" role="button"><i class="bi bi-plus"></i></button></li>
                        </ul>
                    </div>
                </div>

                <div class="">
                    <label for="currently_selected" class="form-label">Currently Selected For this Group</label>
                    <div id="currently_selected_container"></div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" id="add_selected_trigger" class="btn btn-primary">Add Selected</button>
            </div>
        </div>
    </div>
</div>

<script>

    $(document).ready(function() {

        $('#reporting_on').change(function() {
            $('#include_container').html('');
        });

       $('#add_include_trigger').click(function() {
           showLookupModal();
       });

       var timeout;
       $('#lookup_search').keyup(function() {

           const val = $(this).val();
           if (val === '') {
               $('#searchResults .list-group').html('');
               $('#searchResults').hide();
               return;
           }
           clearTimeout(timeout);
           timeout = setTimeout(function() {

               const reportingOn = $('#reporting_on').val();
               let url = '/app-data/'
               let filterKey = '';
               if (reportingOn === 'merchant') {
                   url += 'merchants';
                   filterKey = 'merchant';
               } else if (reportingOn === 'title') {
                   url += 'titles';
                   filterKey = 'title';
               } else if (reportingOn === 'category_primary') {
                   url += 'categories';
                   filterKey = 'primary_desc';
               } else if (reportingOn === 'category_detail') {
                   url += 'categories';
                   filterKey = 'detail_desc';
               }

               let tmpFilterObj = {}
               tmpFilterObj[filterKey] = val;
               let tmpSortObj = {};
               tmpSortObj[filterKey] = 'ASC';

               let config = {};
               config.filter = [tmpFilterObj];
               config.sort = [tmpSortObj];
               var data = {
                   page: 1,
                   len: 5,
                   filter: config.filter,
                   sort: config.sort
               };
               data = 'tableData=' + JSON.stringify(data);

               $.post(url, data).done(function (results) {
                   loadLookUpResults(results);
               }).fail(function(result) {
                   console.log(result);
                   alert('Invalid data request');
               });
           }, 200);
       });

       $(document).on('click', '.add_listitem_trigger', function() {
           let html = '<div class="currently_selected_item">';
           html += '<div style="display: none" class="istitem_value">' + $(this).data('item') + '</div>';
           html += '<button role="button" class="btn btn-sm remove_listitem_trigger"><i class="bi bi-x"></i></button>&nbsp;';
           html += '<span class="istitem_display">' + $(this).data('display') + '</span></div>';
           $('#currently_selected_container').append(html);
           $('#searchResults .list-group').html('');
           $('#searchResults').hide();
       });

       $(document).on('click', '.remove_listitem_trigger', function() {
           $(this).closest('.currently_selected_item').remove();
       });

       $('#add_selected_trigger').click(function() {
           var v = [];
           var d = '';
           var a = $('#include_alias').val();
            $('.istitem_value').each(function() {
                v.push($(this).text());
                d += $(this).closest('.currently_selected_item').find('.istitem_display').text() + ', ';
            });
           $('#lookupModal').modal('hide');
           if (v.length) {
               addGroupRow(d, v, a);
           }
       });

       $(document).on('click', '.remove_include_trigger', function() {
           var value = $(this).closest('.include_row_container').find('.include_value').val();
           removeFromIncludeData(value);
           $(this).closest('.include_row_container').remove();
       });

        $(document).on('click', '.edit_include_trigger', function() {
            showLookupModal();
        });

        for(k = 0; k < preIncluded.length; k++) {
            addGroupRow(
                preIncluded[k].list.join(', '),
                preIncluded[k].list,
                preIncluded[k].alias
            );
        }

    });

    function showLookupModal(data) {
        $('#currently_selected_container').html('');
        $('#include_alias').val('');
        const reportingOn = $('#reporting_on').val();
        if (reportingOn === 'merchant') {
            $('#lookup_type').text('Merchant');
        } else if (reportingOn === 'title') {
            $('#lookup_type').text('Title');
        } else if (reportingOn === 'category_primary') {
            $('#lookup_type').text('Category Primary');
        } else if (reportingOn === 'category_detail') {
            $('#lookup_type').text('Category Detail');
        }
        $('#lookupModal').modal('show');
    }


    function addGroupRow(display, value, alias) {

        const newIndex = $('.include_row_container').length;
        let html = '';
        html = '<div class="include_row_container mb-1 p-2 border d-flex justify-content-between align-items-center">';
        for(i = 0; i < value.length; i++) {
            html += '<input type="hidden" name="details[include][' + newIndex + '][list][]" value="' + value[i] + '" />';
        }
        html += '<input class="include_alias" type="hidden" name="details[include][' + newIndex + '][alias]" value="' + ((alias) ? alias : display) + '" />';
        html += '<div class="flex-grow-1 include_display">';
        if (typeof alias == 'string' && alias !== '') {
            html += '<strong>' + alias + '</strong><br />';
            html += display;
        } else {
            html += '<strong>' + display + '</strong><br />';
        }
        html += '</div>';
        html += '<div class="d-flex">';
        html += '<div class="col-auto"><button type="button" role="button" class="btn edit_include_trigger"><i class="bi bi-pencil"></i></button></div>';
        html += '<div class="col-auto"><button type="button" role="button" class="btn remove_include_trigger"><i class="bi bi-x"></i></button></div>';
        html += '</div>';
        html += '</div>';
        $('#include_container').append(html)
    }

    function loadLookUpResults(results) {

        $('#searchResults .list-group').html('');
        $('#searchResults').hide();

        const reportingOn = $('#reporting_on').val();
        let filterKey = '';
        if (reportingOn === 'merchant') {
            filterKey = 'merchant';
        } else if (reportingOn === 'title') {
            filterKey = 'title';
        } else if (reportingOn === 'category_primary') {
            filterKey = 'primary_desc';
        } else if (reportingOn === 'category_detail') {
            filterKey = 'detail_desc';
        }

        try {
            results = JSON.parse(results);
            if (typeof results.total == 'undefined' || typeof results.data == 'undefined') throw "malformed";
        } catch (e) {
            console.log(results);
            alert('Invalid response data:');
            return;
        }
        if (results.total > 0) {
            let html = '';
            for (i = 0; i < results.data.length; i++) {
                html += '<li class="list-group-item d-flex justify-content-between align-items-center">' + results.data[i][filterKey] + '<button class="btn btn-sm add_listitem_trigger" data-item="' + results.data[i][filterKey] + '" data-display="' + results.data[i][filterKey] + '" role="button"><i class="bi bi-plus"></i></button></li>';
            }
            if (html !== '') {
                $('#searchResults .list-group').html(html);
                $('#searchResults').show();
            }
        }
    }

</script>

