var form=$("#form");
var urlStore=form.data('store');
var urlDetail=form.data('detail');
var urlUpdate=form.data('update');
var urlVerifikasi=form.data('verifikasi');
var urlIndex=form.data('index');

var urlPembekalanList=$('#table-mhs').data('list');
var Token=$('#table-mhs').data('token');
var IdPembekalan=$('#table-mhs').data('id');

var tablePembekalan;
tablePembekalan = $('#table-mhs').DataTable({
    scrollCollapse: true,
    autoWidth: false,
    responsive: true,
    "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
    "language": {
        "info": "_START_-_END_ of _TOTAL_ entries",
        searchPlaceholder: "Search"
    },
   
    "destroy": true,
    "processing": true,
    "serverSide": false,
    "stateSave": true,
    ajax: {
        "type": "POST",
        "url": urlPembekalanList,
        "beforeSend" :function(){
        },
        "complete" : function(){
        },
        "data": function(d) {
            d._token = Token;
            d.id = IdPembekalan;
            d.param = $("#param").val();
        }
    },
    columns: [
    {data: 'selectbox', name: 'selectbox'},
    {data: 'nim', name: 'nim'},
    {data: 'nama', name: 'nama'},
    ],
    columnDefs: [
        {
            orderable: false,
            render: DataTable.render.select(),
            targets: 0
        }
    ],
    paging: false,
    scrollCollapse: true,
    scrollX: true,
    scrollY: 400,
    select: {
        'style': 'multi',
    },
    createdRow: function (row, data, dataIndex) {
        if($("#param").val()=='edit'){
            if (data.pembekalan_id !== null) {
                $(row).addClass('selected'); 
                tablePembekalan.row(row).select();
            }
        }
        if($("#param").val()=='verifikasi'){
            if (data.pembekalan_verify == 'Y') {
                $(row).addClass('selected'); 
                tablePembekalan.row(row).select();
            }
        }
        
    }

});

$("#save").on('click', function(e) {
    e.preventDefault();

    if ($("#form").parsley().validate()) {
        var selectedData = tablePembekalan.rows({ selected: true }).data();

        // 🔒 Cek apakah ada mahasiswa dipilih
        if (selectedData.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Pilih Mahasiswa',
                text: 'Silakan pilih minimal satu mahasiswa terlebih dahulu.',
            });
            return;
        }

        var param = $(this);
        var form = $('form')[0];
        var form_data = new FormData(form);

        selectedData.each(function(rowData) {
            form_data.append('mahasiswa_id[]', rowData.mahasiswa_id);
        });

        ax_save = $.ajax({
            type: 'POST',
            data: form_data,
            contentType: false,
            processData: false,
            cache: false,
            async: true,
            url: urlStore,
            beforeSend: function() {
                process(param);
            },
            success: function(data) {
                processDone(param, 'bi bi-save', ' Simpan');
                Swal.fire({
                    title: "Success!",
                    text: data.text,
                    icon: "success",
                    timer: 2000
                }).then(() => {
                    location.replace(urlIndex);
                });
            },
            error: function(reject) {
                processDone(param, 'bi bi-save', ' Simpan');
                $('#form').parsley().reset();

                if (reject.status === 422) {
                    var jsonResponse = JSON.parse(reject.responseText);
                    $.each(jsonResponse.errors, function(key, value) {
                        $('#' + key).parsley().addError('uniqueerror', {
                            message: value[0],
                            updateClass: true
                        });
                    });
                } else {
                    Swal.fire("Error", "There is an error", "error");
                }
            }
        });
    } else {
        return false;
    }
});



$("#update").on('click', function(e) {
    e.preventDefault();

    if ($("#form").parsley().validate()) {
        var selectedData = tablePembekalan.rows({ selected: true }).data();

        // 🔒 Cek apakah ada mahasiswa dipilih
        if (selectedData.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Pilih Mahasiswa',
                text: 'Silakan pilih minimal satu mahasiswa terlebih dahulu.',
            });
            return;
        }

        var param = $(this);
        var form = $('form')[0];
        var form_data = new FormData(form);

        selectedData.each(function(rowData) {
            form_data.append('mahasiswa_id[]', rowData.mahasiswa_id);
        });

        ax_save = $.ajax({
            type: 'POST',
            data: form_data,
            contentType: false,
            processData: false,
            cache: false,
            async: true,
            url: urlUpdate,
            beforeSend: function() {
                process(param);
            },
            success: function(data) {
                processDone(param, 'bi bi-save', ' Simpan');
                Swal.fire({
                    title: "Success!",
                    text: data.text,
                    icon: "success",
                    timer: 2000
                }).then(() => {
                    // location.reload(); // kamu bisa aktifkan ini kalau perlu reload
                });
            },
            error: function(reject) {
                processDone(param, 'bi bi-save', ' Simpan');
                $('#form').parsley().reset();

                if (reject.status === 422) {
                    var jsonResponse = JSON.parse(reject.responseText);
                    $.each(jsonResponse.errors, function(key, value) {
                        $('#' + key).parsley().addError('uniqueerror', {
                            message: value[0],
                            updateClass: true
                        });
                    });
                } else {
                    Swal.fire("Error", "There is an error", "error");
                }
            }
        });
    } else {
        return false;
    }
});



$("#verifikasi").on('click', function(e) {
    e.preventDefault();

    if ($("#form").parsley().validate()) {
        var selectedData = tablePembekalan.rows({ selected: true }).data();

        // 🔒 Cek apakah ada mahasiswa dipilih
        if (selectedData.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Pilih Mahasiswa',
                text: 'Silakan pilih minimal satu mahasiswa terlebih dahulu.',
            });
            return;
        }

        var param = $(this);
        var form = $('form')[0];
        var form_data = new FormData(form);

        selectedData.each(function(rowData) {
            form_data.append('mahasiswa_id[]', rowData.mahasiswa_id);
        });

        ax_save = $.ajax({
            type: 'POST',
            data: form_data,
            contentType: false,
            processData: false,
            cache: false,
            async: true,
            url: urlVerifikasi,
            beforeSend: function() {
                process(param);
            },
            success: function(data) {
                processDone(param, 'bi bi-check', ' Verifikasi Pembekalan');
                Swal.fire({
                    title: "Success!",
                    text: data.text,
                    icon: "success",
                    timer: 2000
                }).then(() => {
                    // location.reload(); // bisa diaktifkan jika ingin reload
                });
            },
            error: function(reject) {
                processDone(param, 'bi bi-check', ' Verifikasi Pembekalan');
                $('#form').parsley().reset();

                if (reject.status === 422) {
                    var jsonResponse = JSON.parse(reject.responseText);
                    $.each(jsonResponse.errors, function(key, value) {
                        $('#' + key).parsley().addError('uniqueerror', {
                            message: value[0],
                            updateClass: true
                        });
                    });
                } else {
                    Swal.fire("Error", "There is an error", "error");
                }
            }
        });
    } else {
        return false;
    }
});



$(".permissionTable").on('click', '.selectall', function () {

    if ($(this).is(':checked')) {
        $(this).closest('tr').find('[type=checkbox]').prop('checked', true);

    } else {
        $(this).closest('tr').find('[type=checkbox]').prop('checked', false);

    }

    calcu_allchkbox();

});

$(".permissionTable").on('click', '.grand_selectall', function () {
    if ($(this).is(':checked')) {
        $('.selectall').prop('checked', true);
        $('.permissioncheckbox').prop('checked', true);
    } else {
        $('.selectall').prop('checked', false);
        $('.permissioncheckbox').prop('checked', false);
    }
});

$(function () {

    calcu_allchkbox();
    selectall();

});

function selectall(){

    $('.selectall').each(function (i) {

        var allchecked = new Array();

        $(this).closest('tr').find('.permissioncheckbox').each(function (index) {
            if ($(this).is(":checked")) {
                allchecked.push(1);
            } else {
                allchecked.push(0);
            }
        });

        if ($.inArray(0, allchecked) != -1) {
            $(this).prop('checked', false);
        } else {
            $(this).prop('checked', true);
        }

    });
}

function calcu_allchkbox(){

    var allchecked = new Array();

    $('.selectall').each(function (i) {


        $(this).closest('tr').find('.permissioncheckbox').each(function (index) {
            if ($(this).is(":checked")) {
                allchecked.push(1);
            } else {
                allchecked.push(0);
            }
        });


    });

    if ($.inArray(0, allchecked) != -1) {
        $('.grand_selectall').prop('checked', false);
    } else {
        $('.grand_selectall').prop('checked', true);
    }

}



$('.permissionTable').on('click', '.permissioncheckbox', function () {

    var allchecked = new Array;

    $(this).closest('tr').find('.permissioncheckbox').each(function (index) {
        if ($(this).is(":checked")) {
            allchecked.push(1);
        } else {
            allchecked.push(0);
        }
    });

    if ($.inArray(0, allchecked) != -1) {
        $(this).closest('tr').find('.selectall').prop('checked', false);
    } else {
        $(this).closest('tr').find('.selectall').prop('checked', true);

    }

    calcu_allchkbox();

});
