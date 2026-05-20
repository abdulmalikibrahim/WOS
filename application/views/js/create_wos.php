<script>
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

    $("#upload-excel").change(function() {
        loading("Sedang Upload...", "");
        fileupload = document.getElementById("upload-excel");
        file = fileupload.files[0];
        $("#customFile").html(file.name);
        $("#form_export").submit();
    });

    <?php
    $p = $this->input->get("p");
    if(!empty($p)){
        if($p == "docking"){
            echo 'docking();';
        }
    }
    ?>
</script>
<script>
	document.getElementById("btnDownload").addEventListener("click", function(e) {
		e.preventDefault(); // jangan submit dulu

		let p = document.getElementById("p").value;
		let d = document.getElementById("d").value;

		// ============ TAB 1 (kap=1) ============
		let url1 = "<?= base_url('create_wos_sp_download'); ?>?kap=1&p=" + p + "&d=" + d;
		window.open(url1, "_blank");

		// ============ TAB 2 (kap=2) ============
		let url2 = "<?= base_url('create_wos_sp_download'); ?>?kap=2&p=" + p + "&d=" + d;
		window.open(url2, "_blank");
	});
</script>
