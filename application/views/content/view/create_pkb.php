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
                <div class="col-lg-8 pl-0 text-right d-block d-lg-none">
				<a href="<?=base_url("")?>" class="btn btn-sm btn-danger ml-2" title="Main Menu">Main Menu</a>
				<a href="<?=base_url("pkb_service_part")?>" class="btn btn-sm btn-danger ml-2" title="Back">Back</a>
                </div>
                <div class="col-lg-4">
                    <form action="<?=base_url("import_sp")?>" method="post" id="form_export" enctype="multipart/form-data">
				<div class="row mb-2">
					<div class="col">
						<p class="mb-1">Delivery Date</p>
						<input type="date" name="delivery_date" id="delivery_date" class="form-control" value="<?= date("Y-m-d"); ?>">
					</div>
					<div class="col">
						<p class="mb-1">RIT</p>
						<select name="rit" id="rit" class="form-control">
							<option>1</option>
							<option>2</option>
							<option>3</option>
							<option>4</option>
							<option>5</option>
							<option>6</option>
							<option>7</option>
						</select>
					</div>
					<div class="col">
						<p class="mb-1">Tujuan</p>
						<datalist id="data-tujuan">
							<option>TMI</option>
							<option>ASI</option>
						</datalist>
						<input type="text" list="data-tujuan" name="tujuan" id="tujuan" class="form-control">
					</div>
				</div>
                        <div class="input-group">
                              <input type="text" class="form-control" id="scan_qr" name="scan_qr" placeholder="Scan QR Code Disini...">
                            	<button href="<?=base_url("clear_sp")?>" class="btn btn-sm btn-secondary ml-2" title="Bersihkan Master Service Part" style="height:35px;">Clear</button>
                        </div>
                    </form>
                </div>
                <div class="col-lg-8 pl-0 text-right d-none d-lg-block">
				<a href="<?=base_url("")?>" class="btn btn-sm btn-danger ml-2" title="Main Menu" style="height:35px;">Main Menu</a>
				<a href="<?=base_url("pkb_service_part")?>" class="btn btn-sm btn-danger ml-2" title="Back" style="height:35px;">Back</a>
                </div>
            </div>
		<table class="table table-bordered table-hover table-sm w-100 tableFixHead" style="font-size:8pt;" id="datatable">
			<thead class="thead-light">
				<tr align="center">
					<th class="align-middle">No.</th>
					<th class="align-middle">Summary</th>
					<th class="align-middle">Tanggal Delivery</th>
					<th class="align-middle">RIT</th>
					<th class="align-middle">Part Number</th>
					<th class="align-middle">Part Name</th>
					<th class="align-middle">PDD</th>
					<th class="align-middle">Qty</th>
				</tr>
			</thead>
			<tbody>
				<tr align="center">
					<td class="align-middle">No.</td>
					<td class="align-middle">Summary</td>
					<td class="align-middle">Tanggal Delivery</td>
					<td class="align-middle">RIT</td>
					<td class="align-middle">Part Number</td>
					<td class="align-middle">Part Name</td>
					<td class="align-middle">PDD</td>
					<td class="align-middle">Qty</td>
				</tr>
				<tr align="center">
					<td class="align-middle">No.</td>
					<td class="align-middle">Summary</td>
					<td class="align-middle">Tanggal Delivery</td>
					<td class="align-middle">RIT</td>
					<td class="align-middle">Part Number</td>
					<td class="align-middle">Part Name</td>
					<td class="align-middle">PDD</td>
					<td class="align-middle">Qty</td>
				</tr>
				<tr align="center">
					<td class="align-middle">No.</td>
					<td class="align-middle">Summary</td>
					<td class="align-middle">Tanggal Delivery</td>
					<td class="align-middle">RIT</td>
					<td class="align-middle">Part Number</td>
					<td class="align-middle">Part Name</td>
					<td class="align-middle">PDD</td>
					<td class="align-middle">Qty</td>
				</tr>	
			</tbody>
		</table>
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
