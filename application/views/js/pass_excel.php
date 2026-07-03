<script>
	$("#toggle-pass").click(function() {
		if($("#pass").attr("type") == "password"){
			$("#pass").attr("type","text");
			$("#toggle-pass-icon").removeClass("fa-eye").addClass("fa-eye-slash");
		}else{
			$("#pass").attr("type","password");
			$("#toggle-pass-icon").removeClass("fa-eye-slash").addClass("fa-eye");
		}
	});

	$("#btn-save-pass").click(function() {
		var pass = $("#pass").val().trim();
		if(pass == ""){
			swal.fire({title:"Gagal", html:"Password tidak boleh kosong", icon:"warning"});
			return;
		}
		swal.fire({
			title: "Simpan Password?",
			html: "Password baru akan dipakai untuk proteksi edit semua download excel Upload PIS berikutnya",
			icon: "question",
			confirmButtonText: "Ya, Simpan",
			showCancelButton: true,
		}).then((result) => {
			if(result.isConfirmed){
				$.ajax({
					url: "<?=base_url("save_pass_excel")?>",
					type: "POST",
					data: {pass: pass},
					dataType: "json",
					success: function(res) {
						swal.fire({title:"Berhasil", html:res.message, icon:"success"});
					},
					error: function(xhr) {
						var msg = "Gagal menyimpan password";
						if(xhr.responseJSON && xhr.responseJSON.message){
							msg = xhr.responseJSON.message;
						}
						swal.fire({title:"Gagal", html:msg, icon:"error"});
					}
				});
			}
		});
	});
</script>
