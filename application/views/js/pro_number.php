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

	$("#upload-excel").change(() => {
		var upload_excel = document.getElementById("upload-excel");
		console.log(upload_excel.files[0]["name"]);
		$("#customFile").html(upload_excel.files[0]["name"]);
	});
	
	$("#form_export").submit(() => {
		loading("Sedang Upload PRO...","Mohon tunggu sampai proses selesai.");
	});
</script>
