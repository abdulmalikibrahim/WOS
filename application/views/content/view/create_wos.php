<style>
    .tableFixHead { overflow: auto; height: 100px; }
    .tableFixHead thead th { position: sticky; top: -1px; z-index: 1; }

    /* Just common table stuff. Really. */
    table  { border-collapse: collapse; width: 100%; }
    th, td { padding: 8px 16px; }
    th     { background:#eee; }
</style>
<div class="row justify-content-center">
    <div class="col-12" align="center">
        <h1 class="m-0 text-white" style="font-size:3rem;"><?=$title?></h1>
    </div>
</div>
<div class="row justify-content-center">
    <div class="col-lg-12 align-items-center mt-3">
        <div class="card card-body">
            <div class="row mb-3">
                <div class="col-12">
                    <span class="text-danger" style="font-size:15px; font-weight:bold;">Silahkan Upload Data Service Part...</i></span>
                </div>
                <div class="col-lg-4">
                    <form action="<?=base_url("import_sp")?>" method="post" id="form_export" enctype="multipart/form-data">
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="upload-excel" name="upload-file" accept=".xls,.xlsx">
                                <label class="custom-file-label" for="customFile" id="customFile">Pilih File Excel</label>
                            </div>
                            <a href="<?=base_url("clear_sp")?>" class="btn btn-sm btn-secondary ml-2" title="Bersihkan Master Service Part" style="height:35px;">Clear</a>
                        </div>
                    </form>
                </div>
		    <div class="col-lg-2"></div>
                <div class="col-lg-6 pl-0" align="right">
				<form action="<?= base_url("create_wos_sp_download"); ?>" target="_blank" method="get">
					<div class="input-group">
						<p class="ml-2 mr-2 mb-0 pt-1">Produksi :</p>
						<input type="date" name="p" class="form-control" value="<?= date("Y-m-d") ?>" style="height:35px;">
						<p class="ml-2 mr-2 mb-0 pt-1">Delivery :</p>
						<input type="date" name="d" class="form-control" value="<?= date("Y-m-d") ?>" style="height:35px;">
						<button class="btn btn-sm btn-success ml-2" title="Download" style="height:35px;">Download</button>
						<a href="<?=base_url("")?>" class="btn btn-sm btn-danger ml-2" title="Main Menu" style="height:35px;">Main Menu</a>
					</div>
				</form>
                </div>
            </div>
			<?php
				$sub = ["Weld","Press"];
				$pro_number_error = [];
				foreach ($sub as $key => $value) {
					?>
					<h3><?= strtoupper($value)." PART" ?></h3>
					<table class="table table-bordered table-hover table-sm w-100 tableFixHead" style="font-size:8pt;" id="datatable">
						<thead class="thead-light">
							<tr align="center">
								<th class="align-middle">No.</th>
								<th class="align-middle">Model</th>
								<th class="align-middle">Job Number</th>
								<th class="align-middle">Part Number</th>
								<th class="align-middle">Part Name</th>
								<th class="align-middle">Routing Part</th>
								<th class="align-middle">Pcs</th>
								<th class="align-middle">PRO</th>	
								<th class="align-middle">Plan PRO</th>
								<th class="align-middle" style="width:250px;">Remark</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$part_number = $this->model->gds_heijunka("create_wos","*,COUNT(No) as row_qty, SUM(Qty) as total","sub = '$value' GROUP BY Part_Number","result");
							$part_number_total = $this->model->gds_heijunka("create_wos","*,SUM(Qty) as total","sub = '$value' GROUP BY sub","row");
							if(!empty($part_number)){
								$no = 1;
								foreach ($part_number as $pn) {
									$detail_part = $this->model->gds_heijunka("breakdown_sp","Breakdown,Part_Number,Part_Name,Route,Original_Part,Qty","Part_Number = '".$pn->Part_Number."' GROUP BY Breakdown ORDER BY (CASE WHEN Part_Number = Breakdown THEN 0 ELSE 1 END), Part_Number, Breakdown","result");
									if(!empty($detail_part)){
										foreach ($detail_part as $detail_part) {
											if($detail_part->Part_Number == $detail_part->Breakdown){
												$row_bg = "bg-dark text-white";
												$row_no = '
												<td class="align-middle '.$row_bg.'">'.$no.'</td>
												<td class="align-middle '.$row_bg.'">'.$pn->type.'</td>';
	
												$get_pro_detail = $this->model->gds_heijunka("pro_number","*","part_number = '".$pn->Part_Number."'","row");
												if(!empty($get_pro_detail)){
													$pro = $get_pro_detail->pro_number;
													$qty = $get_pro_detail->qty;
												}else{
													$pro_number_error[] = [
														"part_number" => $pn->Part_Number,
														"model" => $pn->type,
														"qty" => $pn->Qty
													];
													$pro = "-";
													$qty = "-";
												}
											}else{
												$row_bg = "";
												$row_no = '
												<td></td>
												<td></td>';
												$pro = "";
												$qty = "";
											}
											?>
											<tr align="center">
												<?=$row_no?>
												<td class="align-middle <?=$row_bg?>"><?=$detail_part->Original_Part?></td>
												<td class="align-middle <?=$row_bg?>"><?=$detail_part->Breakdown?></td>
												<td class="align-middle <?=$row_bg?>"><?=$detail_part->Part_Name?></td>
												<td class="align-middle <?=$row_bg?>"><?=$detail_part->Route?></td>
												<td class="align-middle <?=$row_bg?>"><?=($detail_part->Qty*$pn->total)?></td>
												<td class="align-middle <?=$row_bg?>"><?= $pro; ?></td>	
												<td class="align-middle <?=$row_bg?>"><?= $qty; ?></td>
												<td class="align-middle <?=$row_bg?>"></td>
											</tr>
											<?php
										}
										$no++;
									}else{
										$part_number_error[] = [
											"part_number" => $pn->Part_Number,
											"model" => $pn->type,
										];
									}
								}
								?>
								<tr>
									<td colspan="6" style="background:black; text-align:right; color:white; border:solid 1px;">TOTAL&nbsp;&nbsp;</td>
									<td style="background:black; text-align:center; color:white; border:solid 1px;"><?= $part_number_total->total; ?></td>
									<td colspan="3" style="background:black; color:white; border:solid 1px;"></td>
								</tr>
								<?php
							}
							?>
						</tbody>
					</table>
					<?php
				}
			?>
        </div>
    </div>
</div>
<?php
$error_msg = '';
if(!empty($part_number_error)){
	$error_msg .= "<h5 class='mb-2 mt-3'>Part Number Missing :</h5>";
	$copy_data = '';
	$no = 1;
	foreach ($part_number_error as $key => $value) {
		$error_msg .= $no.". ".$value["part_number"]." (".$value["model"].")<br>";
		$copy_data .= $value["part_number"]."\n";
		$no++;
	}
	echo '<div id="data-part-number-error" hidden>'.$copy_data.'</div>';
	$error_msg .= "<button id='btn-copy-part-number-missing' class='btn btn-sm btn-success mt-2' onclick='copy_part_number_missing()'><i class='fas fa-copy pr-1'></i>Copy data</button>";
}

if(!empty($pro_number_error)){
	$error_msg .= "<h5 class='mb-2 mt-3'>PRO Number Missing :</h5>";
	$no = 1;
	$copy_data = '';
	foreach ($pro_number_error as $key => $value) {
		$error_msg .= $no.". ".$value["part_number"]." Qty:".$value["qty"]." (".$value["model"].")<br>";
		$copy_data .= $value["part_number"]."   ".$value["qty"]."\n";
		$no++;
	}
	echo '<div id="data-pro-number-error" hidden>'.$copy_data.'</div>';
	$error_msg .= "<button id='btn-copy-pro-number-missing' class='btn btn-sm btn-success mt-2' onclick='copy_pro_number_missing()'><i class='fas fa-copy pr-1'></i>Copy data</button>";
}
if(!empty($error_msg)){
	?>
	<script>
		function swal_error() {
			swal.fire({
				iconHtml: "<img class='rounded-circle' src='<?= base_url('assets/images/emot-sedih.jpg'); ?>' width='100%'>",
				customClass: {
					icon: "border-0"
				},
				title:"Error Data Syncronize",
				html:"<?= $error_msg; ?>",
				showConfirmButton:true,
				confirmButtonText:"Tutup",
				allowOutsideClick: false
			});
		}
		swal_error();

		function copy_part_number_missing() {
			$("#btn-copy-part-number-missing").html("Copied Data");
			navigator.clipboard.writeText($("#data-part-number-error").html());
		}

		function copy_pro_number_missing() {
			$("#btn-copy-pro-number-missing").html("Copied Data");
			navigator.clipboard.writeText($("#data-pro-number-error").html());
		}
	</script>
	<?php
}
?>
