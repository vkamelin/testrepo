$(function() {
    var table = $('#users-table').DataTable({
        'paging': true,
        'lengthChange': true,
        'searching': true,
        'ordering': true,
        'info': true,
        'autoWidth': false,
        'processing': true,
        'serverSide': true,
        'ajax': {
            'url': 'users/json',
            'type': 'POST',
        },
        'language': {
            'url': '/assets/plugins/datatables/locale/' + locale + '.json',
        },
        'columns': [
            { 'data': 'id', 'searchable': true },
            { 'data': 'email', 'searchable': true },
            {
                'data': null,
                'sortable': false,
                'render': function(data, type, full, meta) {
                    var html = '<div class="btn-group pull-right" role="group" aria-label="...">';

                    if (full.status == 1) {
                        html += '<a class="btn btn-sm btn-default hidden-sm hidden-xs item-membership"><i class="fa fa-user"></i></a>';
                    } else {
                        html += '<a class="btn btn-sm btn-default hidden-sm hidden-xs item-membership"><i class="fa fa-user-o text-muted"></i></a>';
                    }

                    html += '<a href="users/form/' + full.id + '" class="btn btn-sm btn-default"><i class="fa fa-pencil"></i></a>';
                    html += '<a class="btn btn-sm btn-default item-delete"><i class="fa fa-trash"></i></a>';

                    html += '</div>';

                    return html;
                }
            }
        ],

    });

    $('#users-table tbody').on('click', '.item-membership', function() {
        var data = table.row($(this).parents('tr')).data();
        alert(data['id'] + "'s salary is: " + data['id']);
    });
});