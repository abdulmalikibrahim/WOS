<script>
	const kapSession = sessionStorage.getItem('kap');
    $(document).ready(function() {
		load_data_kap2();
        load_pis_kap2();
    });

	function checkingURLUpload(change = 'no') {
		let kap = $("#kap").val();
		if(kapSession && change == 'no'){
			kap = kapSession;
		}

		sessionStorage.setItem('kap',kap);

		if(kap){
			$(".form-upload").show();
			const urlPIS = $("#form_upload_pis_kap").attr("data-url");
			const btnDockingUrl = $("#docking").attr("data-url");

			// Update Bank VLT picker link with selected KAP
			$("#btn-pick-bank-vlt").attr("href", `<?=base_url("pick_bank_vlt")?>?kap=${kap}`);

			$("#form_upload_pis_kap").attr("action",`${urlPIS}&t=${kap}`);
			if(kap == "kap1"){
				$("#docking").attr("href",`<?= base_url("adjust_twotone?docking=yes"); ?>`);
			}else{
				$("#docking").attr("href",`${btnDockingUrl}?t=${kap}`);
			}
			$(".plant").html(kap.toUpperCase());
			load_data_kap2();
			load_pis_kap2();
		}else{
			$(".form-upload").hide();
		}

		if(change == 'no'){
			$("#kap").val(kap);
		}
	}
	checkingURLUpload();

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
		const kapSession = sessionStorage.getItem('kap');
		const url = '<?=base_url("load_tabungan")?>';
        <?php
            if(!empty($this->session->userdata("tabungan_actual"))){
            ?>
            $.ajax({
                url:`${url}?t=${kapSession}`,
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
		const kapSession = sessionStorage.getItem('kap');
		const url = '<?=base_url("load_pis_kap2")?>';
        <?php
            if(!empty($this->session->userdata("pis_dummy"))){
            ?>
            $.ajax({
                url:`${url}?t=${kapSession}`,
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

    function clearTabungan() {
        const kap = sessionStorage.getItem('kap') || '';
        if (!kap) {
            Swal.fire('Perhatian', 'Silahkan pilih Plant terlebih dahulu', 'warning');
            return;
        }
        Swal.fire({
            title: 'Kosongkan Tabungan?',
            html: 'Semua data tabungan <b>' + kap.toUpperCase() + '</b> akan dihapus.<br>Tindakan ini tidak bisa dibatalkan.',
            iconHtml: '<i class="fas fa-question" style="font-size:2.5rem;color:#6c757d;"></i>',
            iconColor: 'transparent',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-trash mr-1"></i>Ya, Kosongkan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
        }).then(result => {
            if (!result.isConfirmed) return;
            Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            $.post('<?=base_url("clear_tabungan_json")?>', { kap: kap }, function (res) {
                if (res.status === 'ok') {
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1800, showConfirmButton: false })
                        .then(() => location.reload());
                } else {
                    Swal.fire('Gagal', res.message || 'Terjadi kesalahan', 'error');
                }
            }, 'json').fail(() => Swal.fire('Error', 'Gagal koneksi ke server', 'error'));
        });
    }
    $("#upload-excel-pis-kap2").change(function() {
        loading("Sedang Upload...", "");
        fileupload = document.getElementById("upload-excel-pis-kap2");
        file = fileupload.files[0];
        $("#customFile-pis-kap2").html(file.name);
        $("#form_upload_pis_kap").submit();
    });
</script>
