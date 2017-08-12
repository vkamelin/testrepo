$(function() {
    
    var table = $('#orders-table').DataTable({
        'paging': true,
        'lengthChange': true,
        'searching': true,
        'ordering': true,
        'info': true,
        'autoWidth': false,
        'stateSave': true,
        'processing': true,
        'serverSide': true,
        'ajax': {
            'url': 'orders/json',
            'type': 'POST',
        },
        'language': {
            'url': '/assets/plugins/datatables/locale/' + locale + '.json',
        },
        'columns': [
            { 'data': 'id', 'searchable': true },
            { 'data': 'firstname', 'searchable': true, 'class': 'hidden-sm hidden-xs' },
            { 'data': 'lastname', 'searchable': true, 'class': 'hidden-sm hidden-xs' },
            { 'data': 'email', 'searchable': true },
            { 'data': 'dateCreated', 'searchable': false, 'class': 'hidden-sm hidden-xs' },
            { 'data': 'dateModified', 'searchable': false, 'class': 'hidden-sm hidden-xs' },
            {
                'data': 'status',
                'sortable': true,
                'class': 'hidden-sm hidden-xs',
                'render': function(data, type, full, meta) {
                    var html = '';

                    if (full.status == 0) {
                        html += 'Created';
                    } else if (full.status == 1) {
                        html += 'Paid';
                    } else if (full.status == 2) {
                        html += 'Sended';
                    } else if (full.status == 3) {
                        html += 'Delivered';
                    } else {
                        html += '-----';
                    }

                    return html;
                }
            },
            {
                'data': null,
                'sortable': false,
                'render': function(data, type, full, meta) {
                    var html = '<div class="btn-group pull-right" role="group" aria-label="...">';

                    html += '<a href="orders/invoice/' + full.id + '" class="btn btn-sm btn-default"><i class="fa fa-file-text"></i></a>';
                    html += '<a href="orders/form/' + full.id + '" class="btn btn-sm btn-default"><i class="fa fa-pencil"></i></a>';
                    html += '<a class="btn btn-sm btn-default item-delete"><i class="fa fa-trash"></i></a>';

                    html += '</div>';

                    return html;
                }
            }
        ],
        createdRow: function (row, data, index) {
            /*if (data['status'] != 1) {
                $(row).addClass('danger');
            }*/
            
            if (data['status'] == 1) {
                $(row).addClass('success');
            }
        }

    });
    
    $('#column1_search').on('keyup', function(){
        table
        .columns(1)
        .search(this.value)
        .draw();
    });
    
    $('#column6_search').on('keyup', function(){
        table
        .columns(6)
        .search(this.value)
        .draw();
    });

    $('#users-table tbody').on('click', '.copy1', function() {
        var data = table.row($(this).parents('tr')).data();
        alert(data['id'] + "'s salary is: " + data['id']);
    });
    
    var btns = document.querySelectorAll('copy');
    var clipboard = new Clipboard(btns);
});
