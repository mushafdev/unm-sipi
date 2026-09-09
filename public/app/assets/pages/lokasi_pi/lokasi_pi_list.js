


var urlIndex=$('#table').data('index');
var urlList=$('#table').data('list');


$('.form-control').on('keyup change', function() {
    $(this).parsley().validate();
})

var table;
table = $('#table').DataTable({
    scrollCollapse: true,
    autoWidth: false,
    responsive: false,
    "scrollX": true,
    "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
    "language": {
        "info": "_START_-_END_ of _TOTAL_ entries",
        searchPlaceholder: "Search"
    },
    "destroy": true,
    "processing": true,
    "serverSide": true,
    "stateSave": true,
    ajax: {
        url: urlList,
        data: function (d) {
            d.filter_kosong = $('#filter_kosong').val();
        }
    },
    columns: [
    {data: 'DT_RowIndex', name: 'DT_RowIndex'},
    {data: 'lokasi_pi', name: 'lokasi_pi'},
    {data: 'kota', name: 'kota'},
    {data: 'alamat', name: 'alamat'},
    {data: 'telp', name: 'telp'},
    {data: 'kompetensi', name: 'kompetensi'},
    {data: 'jumlah_kelompok', name: 'jumlah_kelompok', orderable: false, searchable: false},
    {data: 'action', name: 'action', orderable: false, searchable: false},
    ],
    "columnDefs": [
    {
        "targets": "datatable-nosort",
        "orderable": false,
    },
    ],

});

$('#filter_kosong').on('change', function () {
    table.ajax.reload();
});

var urlKelompok = $('#table').data('kelompok');

$(document).on('click', '.lihat-kelompok', function (e) {
    e.preventDefault();
    var id = $(this).data('id');
    var nama = $(this).data('nama');
    $('#modal-kelompok-nama').text(nama ? '- ' + nama : '');
    $('#modal-kelompok-body').html('<tr><td colspan="4" class="text-center">Memuat...</td></tr>');
    $('#modal-kelompok').modal('show');

    $.ajax({
        type: 'GET',
        url: urlKelompok,
        data: { id: id },
        dataType: 'json',
        success: function (res) {
            var rows = '';
            if (res.data.length === 0) {
                rows = '<tr><td colspan="4" class="text-center text-danger">Tidak ada kelompok</td></tr>';
            } else {
                $.each(res.data, function (i, k) {
                    rows += '<tr class="row-kelompok" style="cursor:pointer" data-url="' + k.url + '">'
                        + '<td>' + (i + 1) + '</td>'
                        + '<td>' + k.anggota + '</td>'
                        + '<td class="text-center">' + k.jumlah + '</td>'
                        + '<td class="text-center">' + k.tahun + '</td>'
                        + '</tr>';
                });
            }
            $('#modal-kelompok-body').html(rows);
        },
        error: function () {
            $('#modal-kelompok-body').html('<tr><td colspan="4" class="text-center text-danger">Gagal memuat data</td></tr>');
        }
    });
});

$(document).on('click', '.row-kelompok', function () {
    var url = $(this).data('url');
    if (url) {
        window.open(url, '_blank');
    }
});

$(document).on( 'click', '.delete', function () {
    var id = $(this).data("id");
    Swal.fire({
    title: 'Are you sure?',
    text: "This record and it`s details will be permanantly deleted!",
    icon: "warning",
    allowOutsideClick: false,
    showCancelButton: true,
    showLoaderOnConfirm: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes, Delete',
    cancelButtonText: 'No'
    }).then((result) => {
    if (result.value) {
        $.ajax({
            type    : 'DELETE',
            url     : urlIndex+'/'+id,
            success : function(data) {
                Swal.fire('Success!',data.text,'success');
                table.ajax.reload( null, false );
            },
            error : function(){
                Swal.fire("Error", "There is an error", "error");
            }
        });
    }
    })
});

