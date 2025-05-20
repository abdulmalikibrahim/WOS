<style>
    .tableFixHead { overflow: auto; height: 100px; }
    .tableFixHead thead th { position: sticky; top: -1px; z-index: 1; }

    /* Just common table stuff. Really. */
    table  { border-collapse: collapse; width: 100%; }
    th, td { padding: 8px 16px; }
    th     { background:#eee; }
</style>
<?php
if(empty($this->input->get("download"))){
	?>
	<div class="row justify-content-center">
		<div class="col-12" align="center">
			<h1 class="m-0 text-white" style="font-size:3rem;"><?=$title?></h1>
		</div>
	</div>
	<div class="row justify-content-center">
		<div class="col-lg-12 align-items-center mt-3">
			<div class="card card-body">
				<div class="row">
					<div class="col-12">
						<span class="text-danger" style="font-size:15px; font-weight:bold;">Silahkan Upload PRO Number...</i></span>
					</div>
					<div class="col-lg-5">
						<form action="<?=base_url("import_pro_number")?>" method="post" id="form_export" enctype="multipart/form-data">
							<div class="row">
								<div class="col-lg-3 pl-1 pr-0">
									<select name="month" id="month" class="form-control">
										<option value="" disabled>Periode</option>
										<?php
										for ($i=1; $i <= 12; $i++) {
											if(date("m") == sprintf("%02d",$i)){
												$s = "selected";
											}else{
												$s = "";
											}
											echo '<option '.$s.' value="'.sprintf("%02d",$i).'">'.date("M",strtotime("2023-".sprintf("%02d",$i)."-01")).'</option>';
										}
										?>
									</select>
								</div>
								<div class="col-lg-5 pr-0">
									<div class="custom-file w-100">
										<input type="file" class="custom-file-input" id="upload-excel" name="upload-file" accept=".xls">
										<label class="custom-file-label" for="customFile" id="customFile" style="overflow:hidden;">Pilih File Excel</label>
									</div>
								</div>
								<div class="col-lg-4 pl-1">
									<button class="btn btn-info btn-sm" style="height:35px;">Submit</button>
									<a href="<?=base_url("clear_pro_number")?>" class="btn btn-sm btn-secondary ml-1" title="Bersihkan PRO Number" style="height:35px;">Clear</a>
								</div>
							</div>
						</form>
					</div>
					<div class="col-lg-7 pl-0" align="right">
						<a href="<?=base_url("pro_number?download=yes")?>" class="btn btn-sm btn-success ml-2" target="_blank" title="Download Master" style="height:35px;">Download Excell</a>
						<a href="<?=base_url("")?>" class="btn btn-sm btn-danger" title="Main Menu" style="height:35px;">Main Menu</a>
					</div>
					<div class="col-12">
						<span class="text-dark" style="font-size:9pt;">Download template master <a href="<?= base_url("download_pro_number") ?>" target="_blank">disini</a></i></span>
					</div>
				</div>
				<div class="table-responsive mt-3" style="height: calc(100vh - 170px);">
					<table class="table table-bordered table-hover table-sm w-100 tableFixHead" style="font-size:8pt;" id="datatable">
						<thead class="thead-light">
							<tr align="center">
								<th class="align-middle">No.</th>
								<th class="align-middle">Part Number</th>
								<th class="align-middle">Qty</th>
								<th class="align-middle">PRO Number</th>
							</tr>
						</thead>
						<tbody id="data_tabungan">
							<?php
							$data_master = $this->model->gds_heijunka("pro_number","*","id != ''","result");
							if(!empty($data_master)){
								$no = 1;
								foreach ($data_master as $dm) {
									?>
									<tr align="center">
										<td class="align-middle"><?= $no++; ?></td>
										<td class="align-middle"><?= $dm->part_number; ?></td>
										<td class="align-middle"><?= $dm->qty; ?></td>
										<td class="align-middle"><?= $dm->pro_number; ?></td>
									</tr>
									<?php
								}
							}
							?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	<?php
}else{
	?>
	<table class="table table-bordered table-hover table-sm w-100 tableFixHead" border="1" style="font-size:8pt;" id="datatable">
		<thead class="thead-light">
			<tr align="center">
				<td>No.</td>
				<td>Mother Part Number</td>
				<td>Child Part Number</td>
				<td>Part Name</td>
				<td>Job Number</td>
				<td>Route</td>
				<td>Qty</td>
				<td>Keterangan</td>
			</tr>
		</thead>
		<tbody id="data_tabungan">
			<?php
			$data_master = $this->model->gds_heijunka("breakdown_sp","*","No != ''","result");
			if(!empty($data_master)){
				$no = 1;
				foreach ($data_master as $dm) {
					?>
					<tr align="center">
						<td class="align-middle"><?= $no++; ?></td>
						<td class="align-middle"><?= $dm->Part_Number; ?></td>
						<td class="align-middle"><?= $dm->Breakdown; ?></td>
						<td class="align-middle"><?= $dm->Part_Name; ?></td>
						<td class="align-middle"><?= $dm->Original_Part; ?></td>
						<td class="align-middle"><?= $dm->Route; ?></td>
						<td class="align-middle"><?= $dm->Qty; ?></td>
						<td class="align-middle"><?= $dm->Keterangan; ?></td>
					</tr>
					<?php
				}
			}
			?>
		</tbody>
	</table>
	<?php
}
?>
