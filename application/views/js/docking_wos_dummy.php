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
    $("#upload-excel-pis-kap2").change(function() {
        loading("Sedang Upload...", "");
        fileupload = document.getElementById("upload-excel-pis-kap2");
        file = fileupload.files[0];
        $("#customFile-pis-kap2").html(file.name);
        $("#form_upload_pis_kap").submit();
    });
</script>
