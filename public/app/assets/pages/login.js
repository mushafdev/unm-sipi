
$(".show-pass").on('click', function(e) {
    e.preventDefault();
    var x =document.getElementById("password");
    if (x.type === "password") {
      x.type = "text";
      $('.show-pass i').attr('class', "bi bi-eye" );
    } else {
      $('.show-pass i').attr('class', "bi bi-eye-slash" );
      x.type = "password";
    }
});

let timer;

$('#nim').on('input', function(){
    clearTimeout(timer);

    let nim = $(this).val().trim();
    $('#nama').val('');
    $('#nama').removeClass('is-invalid');
    $('#nama').removeClass('is-valid');

    // Jika kurang dari 3 karakter → hentikan, sembunyikan spinner
    if (nim.length < 3) {
        $('#loading-nama').addClass('d-none');
        return;
    }else {
        $('#loading-nama').removeClass('d-none');
    }

    timer = setTimeout(function(){
        // Tampilkan spinner setelah jeda
        $('#loading-nama').removeClass('d-none');

        $.ajax({
            url: '/registrasi/cari-mahasiswa',
            method: 'GET',
            data: { nim: nim },
            success: function(response) {
                if (response.status && response.data.length > 0) {
                    $('#nama').val(response.data[0].nm_mhs);
                    $('#nama').removeClass('is-invalid');
                    $('#nama').addClass('is-valid');
                } else {
                    $('#nama').val('Data tidak ditemukan');
                    $('#nama').addClass('is-invalid');
                    $('#nama').removeClass('is-valid');
                    // Swal.fire("Info", "Mahasiswa dengan NIM tersebut tidak ditemukan", "info");
                }
            },
            error: function() {
                $('#nama').val('');
                Swal.fire("Error", "Gagal memuat data", "error");
            },
            complete: function(){
                $('#loading-nama').addClass('d-none');
            }
        });
    }, 1000); // hanya dijalankan kalau user berhenti mengetik 0,5 detik
});

$('#kelas').on('input', function() {
    this.value = this.value.toUpperCase();
});

var urlReload=$('#login').data('reload-captcha');
$('.reload-cpt').click(function () {
    $.ajax({
        type: 'GET',
        url: urlReload,
        beforeSend: function() {
          $('.reload-cpt').attr('disabled',true);
          $('.reload-cpt i').attr('class', 'spinner-border spinner-border-sm');
        },
        success: function (data) {
            $(".img-captcha").attr('src', data.captcha);
            $(".img-captcha").on('load', function() {
              $('.reload-cpt').attr('disabled',false);
              $('.reload-cpt i').attr('class', 'bi bi-arrow-repeat');
          });
        },
    });
});

function showPass() {
    var x = document.getElementById("password");
    if (x.type === "password") {
      x.type = "text";
    } else {
      x.type = "password";
    }
  }

 