<script>
    function copy_data(data) {
		id = data.dataset.id;
		data_copy = $("#data-nik-"+id).html();
		data_copy = data_copy.replaceAll("<br>","\n");
		if (navigator.clipboard) {
			navigator.clipboard.writeText(data_copy)
			.then(() => {
				Swal.fire("Copy NIK Sukses","NIK berhasil di salin","success");
			})
			.catch((error) => {
				Swal.fire("Copy NIK Failed","NIK gagal di salin.\nError : "+error,"error");
			});
		} else {
			Swal.fire("Copy NIK Failed","Browser tidak mendukung Clipboard API. Silahkan coba ganti url dari 'http' menjadi 'https'","warning");
		}
    }
</script>
