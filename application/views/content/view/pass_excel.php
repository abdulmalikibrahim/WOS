<style>
	/* reset override .swal2-icon-content dari head.php (untuk icon gambar custom)
	   supaya icon bawaan sweetalert (question/success/error) tidak berantakan */
	.swal2-icon-content {
		min-width: 0 !important;
		height: auto !important;
		padding-bottom: 0 !important;
	}
</style>
<div class="row justify-content-center">
	<div class="col-12" align="center">
		<h1 class="m-0 text-white" style="font-size:3rem;"><?=$title?></h1>
	</div>
</div>
<div class="row justify-content-center">
	<div class="col-lg-5 align-items-center mt-3">
		<div class="card card-body">
			<div class="row">
				<div class="col-12">
					<span class="text-danger" style="font-size:15px; font-weight:bold;">Password ini dipakai untuk edit file excel hasil download Upload PIS (KAP 1 &amp; KAP 2). Tanpa password file tetap bisa dibuka, tapi read-only</span>
				</div>
				<div class="col-12 mt-3">
					<?php
					$pass_row = $this->model->gds("pass_excel","pass","id = 1","row");
					$pass_now = !empty($pass_row->pass) ? $pass_row->pass : "";
					?>
					<label class="font-weight-bold">Password File Excel</label>
					<div class="input-group">
						<input type="password" class="form-control" id="pass" value="<?=htmlspecialchars($pass_now)?>" placeholder="Masukkan password file excel" autocomplete="off">
						<div class="input-group-append">
							<button class="btn btn-outline-secondary" type="button" id="toggle-pass" title="Lihat / sembunyikan password"><i class="fa fa-eye" id="toggle-pass-icon"></i></button>
						</div>
					</div>
					<?php if($pass_now == ""){ ?>
						<span class="text-danger" style="font-size:9pt;">Password belum diset, file excel masih terdownload tanpa proteksi edit</span>
					<?php } ?>
				</div>
				<div class="col-12 mt-3" align="right">
					<button class="btn btn-info btn-sm" id="btn-save-pass" style="height:35px;">Simpan</button>
					<a href="<?=base_url("")?>" class="btn btn-sm btn-danger" title="Main Menu" style="height:35px;">Main Menu</a>
				</div>
			</div>
		</div>
	</div>
</div>
