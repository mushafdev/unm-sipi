$(function(){
    $(document).on('click', '.upload-btn', function(e){
        e.preventDefault();

        var btn = $(this);
        var formId = btn.data('form');

        if($(formId).parsley().validate()){

            var form = $(formId);
            var urlStore = $('#upload-forms').data('store');
            var fileInput = form.find('input[type="file"]')[0];
            var groupId = $('#group_id').val();

            var progressWrapper = form.find('.progress');
            var progressBar = progressWrapper.find('.progress-bar');
            var result = form.find('.result');
            
            // Debug lebih detail
            console.log('Form ID:', formId);
            console.log('Progress wrapper:', progressWrapper);
            console.log('Progress bar:', progressBar);
            console.log('Progress wrapper length:', progressWrapper.length);
            console.log('Progress bar length:', progressBar.length);

            // Validasi
            if (!fileInput || !fileInput.files.length) {
                Swal.fire("Error", "Silakan pilih file PDF", "error");
                return;
            }

            if (!groupId) {
                Swal.fire("Error", "Group ID wajib diisi", "error");
                return;
            }

            // Cek apakah elemen progress ditemukan
            if (progressWrapper.length === 0) {
                console.error('Progress wrapper tidak ditemukan!');
                return;
            }

            if (progressBar.length === 0) {
                console.error('Progress bar tidak ditemukan!');
                return;
            }

            var formData = new FormData(form[0]);
            formData.append('group_id', groupId);

            // Reset dan tampilkan progress
            progressWrapper.removeClass('d-none').show();
            progressBar.css('width', '0%').attr('aria-valuenow', 0).text('0%');
            result.html('');

            $.ajax({
                type: 'POST',
                url: urlStore,
                data: formData,
                processData: false,
                contentType: false,
                cache: false,
                async: true,
                xhr: function () {
                    var xhr = new window.XMLHttpRequest();

                    // Event listener untuk progress upload
                    xhr.upload.addEventListener("progress", function (e) {
                        console.log('Progress event triggered:', e);
                        
                        if (e.lengthComputable) {
                            var percent = Math.round((e.loaded / e.total) * 100);
                            
                            console.log('Upload progress:', percent + '%');
                            console.log('Loaded:', e.loaded, 'Total:', e.total);
                            
                            // Update progress bar
                            progressBar
                                .css('width', percent + '%')
                                .attr('aria-valuenow', percent)
                                .text(percent + '%');
                        } else {
                            console.log('Progress not computable');
                            // Jika tidak bisa dihitung, tampilkan animasi loading
                            progressBar
                                .addClass('progress-bar-striped progress-bar-animated')
                                .css('width', '100%')
                                .text('Uploading...');
                        }
                    }, false);

                    // Event listener untuk load (selesai)
                    xhr.upload.addEventListener("load", function(e) {
                        console.log('Upload completed');
                        progressBar.removeClass('progress-bar-striped progress-bar-animated');
                    }, false);

                    // Event listener untuk error
                    xhr.upload.addEventListener("error", function(e) {
                        console.log('Upload error:', e);
                        progressBar.removeClass('progress-bar-striped progress-bar-animated');
                    }, false);

                    return xhr;
                },

                beforeSend: function () {
                    console.log('Ajax beforeSend triggered');
                    process(btn); // loading
                    
                    // Tampilkan progress bar dengan animasi awal
                    progressWrapper.removeClass('d-none').show();
                    progressBar
                        .addClass('progress-bar-striped progress-bar-animated')
                        .css('width', '10%')
                        .text('Preparing...');
                },
                
                uploadProgress: function(e) {
                    // Fallback jika xhr.upload tidak bekerja
                    console.log('uploadProgress fallback:', e);
                },
                
                success: function (data) {
                    console.log('Ajax success:', data);
                    
                    // Set progress ke 100%
                    progressBar
                        .removeClass('progress-bar-striped progress-bar-animated')
                        .css('width', '100%')
                        .text('100%');
                    
                    processDone(btn, 'bi bi-cloud-upload', ' Upload');
                    result.text(data.message || 'Berhasil diupload')
                        .removeClass('text-danger')
                        .addClass('text-success');

                    // Sembunyikan progress bar setelah delay
                    setTimeout(function () {
                        progressWrapper.addClass('d-none');
                        location.reload();
                    }, 2000); 
                },
                
                error: function (xhr, status, error) {
                    console.log('Ajax error:', xhr, status, error);
                    
                    progressBar
                        .removeClass('progress-bar-striped progress-bar-animated')
                        .addClass('bg-danger')
                        .text('Error');
                    
                    processDone(btn, 'bi bi-cloud-upload', ' Upload');
                    result.text("Gagal upload").removeClass('text-success').addClass('text-danger');
                    
                    // Sembunyikan progress bar setelah delay
                    setTimeout(function () {
                        progressWrapper.addClass('d-none');
                    }, 3000);
                }
            });
            
        } else {
            return false;
        }
    });

    $("#send").on('click', function(e) {
        e.preventDefault();

        var param = $(this);
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
                    type: 'POST',
                    data: {
                        group_id: $('#group_id').val(),
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    cache: false,
                    async: true,
                    url: $('#upload-forms').data('send'),
                    beforeSend: function() {
                        process(param);
                    },
                    success: function(data) {
                        processDone(param, 'bi bi-send', ' Kirim');
                        Swal.fire({
                            title: "Success!",
                            text: data.text,
                            icon: "success",
                            timer: 2000
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(reject) {
                        processDone(param, 'bi bi-send', ' Kirim');

                        if (reject.status === 422) {
                            var data = reject.responseText;
                            var jsonResponse = JSON.parse(data);
                            $.each(jsonResponse.errors, function(key, value) {
                                if (key.indexOf('.') === -1) {
                                    $('#' + key).parsley().addError('uniqueerror', {
                                        message: value[0],
                                        updateClass: true
                                    });
                                } else {
                                    var arr = key.split('.');
                                    $("." + arr[0]).eq(arr[1]).parsley().addError('uniqueerror', {
                                        message: value[0],
                                        updateClass: true
                                    });
                                }
                            });
                        } else {
                            let err = reject.responseJSON?.message || "Terjadi kesalahan saat mengirim data.";
                            Swal.fire("Gagal!", err, "error");
                        }
                    }
                });
            }
        });
    });


});