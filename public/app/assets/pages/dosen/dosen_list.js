

var form=$("#form");
var urlStore=form.data('store');
var urlDetail=form.data('detail');
var urlUpdate=form.data('update');
var urlIndex=form.data('index');
var urlGetData=form.data('get-data');
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
    ajax: urlList,
    columns: [
    {data: 'DT_RowIndex', name: 'DT_RowIndex'},
    {data: 'nama', name: 'nama'},
    {data: 'nip', name: 'nip'},
    {data: 'pangkat', name: 'pangkat'},
    {data: 'golongan', name: 'golongan'},
    {data: 'jabatan', name: 'jabatan'},
    {data: 'prodi', name: 'prodi'},
    {data: 'jurusan', name: 'jurusan'},
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
                        + '<td>' + k.lokasi + '</td>'
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

$('#add').on('click',function(e){
    e.preventDefault();
    $('#form').parsley().reset();
    $("#param").val('add');
    $('#form-title').text('Tambah Dosen');
    processDone($('#save'),'bi bi-save',' Simpan');
    $('#form')[0].reset();
    $('#id').val('');
    $('.modal-load').hide();
    $("#modal").modal('show');
});

$("#save").on('click',function(e) {
    e.preventDefault();
    if($("#form").parsley().validate()){
    var param = $(this);
    var form = $('form')[0];
    var form_data = new FormData(form);
    ax_save = $.ajax({
        type    : 'POST',
        data    : form_data,
        contentType : false,
        processData : false,
        cache: false,
        async:true,
        url     : urlStore,
        beforeSend: function() {
            process(param);
        },
        success : function(data) {
        processDone(param,'bi bi-save',' Simpan');
        Swal.fire({
                title: "Success!",
                text: data.text,
                icon: "success",
                timer: 2000
        }).then((result) => {
            $("#modal").modal('hide');
            table.ajax.reload( null, false );

        });
        },
        error: function (reject) {
            processDone(param,'bi bi-save',' Simpan');
            $('#form').parsley().reset();
            if( reject.status === 422 ) {
                var data=reject.responseText;
                var jsonResponse = JSON.parse(data);
                $.each( jsonResponse.errors, function( key, value) {
                    $('#'+key).parsley().addError('uniqueerror', {message: value[0], updateClass: true});
                });
            }else{
                Swal.fire("Error", "There is an error", "error");
            }
        }
    });
    }else {
    return false();
    }

});

$(document).on( 'click', '.edit', function () {
    $('.modal-load').show();
    $('#form-title').text('Edit Dosen');
    $('#form').parsley().reset();
    var id = $(this).data("id");
    $.ajax({
    type    : 'GET',
    data    : {id:id},
    url     : urlGetData,
    dataType: 'json',
    success : function(data) {
        $('#id').val(data.id);
        $('#param').val('edit');
        $("#nama").val(data.result.nama);
        $("#nip").val(data.result.nip);
        $("#prodi_id").val(data.result.prodi_id);
        $("#pangkat").val(data.result.pangkat);
        $("#golongan").val(data.result.golongan);
        $("#jabatan").val(data.result.jabatan);
        $("#email").val(data.result.email);
        $("#username").val(data.result.username);
        $("#password").val('');
        $('.modal-load').hide();
    }
    });

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

