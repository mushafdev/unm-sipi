var form=$("#form");
var urlStore=form.data('store');
var urlCheck=form.data('check');
var urlSend=form.data('send');

$("#add").on('click',function(e) {
    e.preventDefault();
    if($("#form").parsley().validate()){
    var param = $(this);
    var form = $('form')[0];
    var form_data = new FormData(form);
    $.ajax({
        type: 'POST',
        url: urlCheck,
        data: form_data,
        contentType : false,
        processData : false,
        cache: false,
        async:true,
        success: function (response) {
            if (response.exists) {
                    Swal.fire({
                        title: 'Data ini sudah ada',
                        text: "Apakah anda ingin update data ini?",
                        icon: "warning",
                        allowOutsideClick: false,
                        showCancelButton: true,
                        showLoaderOnConfirm: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, Update',
                        cancelButtonText: 'No'
                    }).then((result) => {
                        if (result.value) {
                            store(form_data,param);
                        }
                    })
            } else {
                store(form_data, param);
            }
        },
    });
    }else {
    return false();
    }

});

$("#send").on('click',function(e) {
    e.preventDefault();
    if($('#check-list').val()==0){
        Swal.fire("Error", "Bukti Masih Kosong", "error");
    }else{
        var param = $(this);
        var form = $('form')[0];
        var form_data = new FormData(form);
        Swal.fire({
            title: 'Apakah anda yakin?',
            text: "Data yang telah dikirim tidak bisa diubah lagi?",
            icon: "warning",
            allowOutsideClick: false,
            showCancelButton: true,
            showLoaderOnConfirm: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Kirim',
            cancelButtonText: 'No'
        }).then((result) => {
            if (result.value) {
                $.ajax({
                    type    : 'POST',
                    data    : form_data,
                    contentType : false,
                    processData : false,
                    cache: false,
                    async:true,
                    url     : urlSend,
                    beforeSend: function() {
                        process(param);
                    },
                    success : function(data) {
                    processDone(param,'bi bi-send',' Kirim');
                    Swal.fire({
                            title: "Success!",
                            text: data.text,
                            icon: "success",
                            timer: 2000
                    }).then((result) => {
                        location.reload();
            
                    });
                    },
                    error: function (reject) {
                        processDone(param,'bi bi-send',' Kirim');
                        $('#form').parsley().reset();
                        if( reject.status === 422 ) {
                            var data=reject.responseText;
                            var jsonResponse = JSON.parse(data);
                            $.each( jsonResponse.errors, function( key, value) {
                                if (key.indexOf('.') == -1) {
                                    $('#'+key).parsley().addError('uniqueerror', {message: value[0], updateClass: true});
                                }else{
                                    arr=key.split('.');
                                    $("."+arr[0]).eq(arr[1]).parsley().addError('uniqueerror', {message: value[0], updateClass: true});    
                                }
                            });
                        }else{
                            Swal.fire("Error", "There is an error", "error");
                        }
                    }
                });
            }
        })
    }

});

function store(form_data,param){ 
    $.ajax({
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
        processDone(param,'bi bi-plus',' Tambahkan');
        Swal.fire({
                title: "Success!",
                text: data.text,
                icon: "success",
                timer: 2000
        }).then((result) => {
            location.reload();

        });
        },
        error: function (reject) {
            processDone(param,'bi bi-plus',' Tambahkan');
            $('#form').parsley().reset();
            if( reject.status === 422 ) {
                var data=reject.responseText;
                var jsonResponse = JSON.parse(data);
                $.each( jsonResponse.errors, function( key, value) {
                    if (key.indexOf('.') == -1) {
                        $('#'+key).parsley().addError('uniqueerror', {message: value[0], updateClass: true});
                    }else{
                        arr=key.split('.');
                        $("."+arr[0]).eq(arr[1]).parsley().addError('uniqueerror', {message: value[0], updateClass: true});    
                    }
                });
            }else{
                Swal.fire("Error", "There is an error", "error");
            }
        }
    });
}