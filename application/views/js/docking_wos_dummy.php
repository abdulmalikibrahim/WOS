<script>
    $(document).ready(function() {
		load_data_kap2();
        load_pis_kap2();
    });
    function loading(title, html) {
        Swal.fire({
            title: title,
            html: html,
            timerProgressBar: true,
            didOpen: () => {
                Swal.showLoading()
                const b = Swal.getHtmlContainer().querySelector('b')
            },
            allowOutsideClick: false
        })
    }
	function load_data_kap2() {
        <?php
            if(!empty($this->session->userdata("tabungan_actual"))){
            ?>
            $.ajax({
                url:"<?=base_url("load_tabungan?t=kap2")?>",
                beforeSend:function() {
                    $("#data_tabungan_kap2").html('<tr><td align="center" colspan="25" class="align-middle"><i>Sedang Memuat...</i></td></tr>');
                },
                success:function(r) {
                    $("#data_tabungan_kap2").html(r);
                },
                error:function(xhr,status,error) {
                    voice("gagal.mp3");
                    swal.fire("Error",xhr.responseText,"error");
                }
            })
            <?php
            }
        ?>
	}
	function load_pis_kap2() {
        <?php
            if(!empty($this->session->userdata("pis_dummy"))){
            ?>
            $.ajax({
                url:"<?=base_url("load_pis_kap2?t=kap2")?>",
                beforeSend:function() {
                    $("#data_pis_kap2").html('<tr><td align="center" colspan="25" class="align-middle"><i>Sedang Memuat...</i></td></tr>');
                },
                success:function(r) {
                    $("#data_pis_kap2").html(r);
                },
                error:function(xhr,status,error) {
                    voice("gagal.mp3");
                    swal.fire("Error",xhr.responseText,"error");
                }
            })
            <?php
            }
        ?>
	}
	function docking() {
        loading("Sedang docking...", "");
	}
    $("#upload-excel-kap2").change(function() {
        loading("Sedang Upload...", "");
        fileupload = document.getElementById("upload-excel-kap2");
        file = fileupload.files[0];
        $("#customFile-kap2").html(file.name);
        $("#form_upload_kap2").submit();
    });
    $("#upload-excel-pis-kap2").change(function() {
        loading("Sedang Upload...", "");
        fileupload = document.getElementById("upload-excel-pis-kap2");
        file = fileupload.files[0];
        $("#customFile-pis-kap2").html(file.name);
        $("#form_upload_pis_kap2").submit();
    });
</script>