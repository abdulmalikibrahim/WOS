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
                <div class="col-lg-4">
                    <form action="<?=base_url("import_sp")?>" method="post" id="form_export" enctype="multipart/form-data">
				<p class="ml-2 mr-2 mb-2 pt-1">Filter By Delivery Date</p>
				<div class="input-group">
					<p class="ml-2 mr-2 mb-0 pt-1">From :</p>
					<input type="date" name="p" class="form-control" value="<?= date("Y-m-d") ?>" style="height:35px;">
					<p class="ml-2 mr-2 mb-0 pt-1">To :</p>
					<input type="date" name="d" class="form-control" value="<?= date("Y-m-d") ?>" style="height:35px;">
					<button class="btn btn-sm btn-info ml-2" title="Filter" style="height:35px;"><i class="fas fa-search m-0"></i></button>
				</div>
                    </form>
                </div>
                <div class="col-lg-8 pl-0 text-lg-right" style="overflow:auto;">
				<a href="<?= base_url("create_pkb"); ?>" class="btn btn-sm btn-success ml-1 mt-2" title="Buat PKB" style="height:35px;"><i class="fas fa-plus mr-1"></i>Buat PKB</a>
				<button class="btn btn-sm btn-success ml-1 mt-2" title="Monitoring Status Service Part" style="height:35px;"><i class="fas fa-tv mr-1"></i>Monitoring</button>
				<button class="btn btn-sm btn-success ml-1 mt-2" title="Searching History By Part Number" style="height:35px;"><i class="fas fa-search mr-1"></i>Search Part Number</button>
				<button class="btn btn-sm btn-success ml-1 mt-2" title="Download" style="height:35px;"><i class="fas fa-file-excel mr-1"></i>Download</button>
				<a href="<?=base_url("")?>" class="btn btn-sm btn-danger ml-1 mt-2" title="Main Menu" style="height:35px;">Main Menu</a>
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
