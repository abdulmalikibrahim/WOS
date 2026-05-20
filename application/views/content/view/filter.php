<div class="row justify-content-center">
    <div class="col-12" align="center">
        <h1 class="m-0 text-white" style="font-size:3rem;"><?=$title?></h1>
    </div>
	<a href="<?=base_url("")?>" class="btn btn-sm btn-danger" title="Kembali" style="height:35px; position:absolute; top:15px; right:20px;">Main Menu</a>
</div>
<div class="row pl-3 pr-3 mt-5 justify-content-center">
	<?php
	foreach ($plant as $kap) {
		?>
		<div class="col-6">
			<div class="card">
				<div class="card-header text-center"><h3 class="mb-0">KAP <?= $kap ?></h3></div>
				<div class="card-body">
					<form action="<?= base_url("filter/add?p=".$kap) ?>" method="post" class="mb-3">
						<div class="row">
							<div class="pr-0 col-lg">
								<p class="mb-1">Model</p>
								<select name="model" id="model" class="form-control">
									<option value="all">All</option>
									<?php
									if(!empty($model)){
										foreach ($model as $value) {
											?>
											<option value="<?= $value["model"]; ?>"><?= $value["model"]; ?></option>
											<?php
										}
									}
									?>
								</select>
							</div>
							<div class="pr-0 col-lg">
								<p class="mb-1">Suffix</p>
								<select name="suffix" id="suffix" class="form-control">
									<option value="all">All</option>
									<?php
									if(!empty($suffix)){
										foreach ($suffix as $value) {
											?>
											<option value="<?= $value["suffix"]; ?>"><?= $value["suffix"]; ?></option>
											<?php
										}
									}
									?>
								</select>
							</div>
							<div class="pr-0 col-lg">
								<p class="mb-1">Color</p>
								<input type="text" name="color" class="form-control" placeholder="Masukkan color disini">
							</div>
							<div class="pr-0 col-lg-2 d-flex align-items-end">
								<button class="button btn btn-info"><i class="fas fa-save mr-1"></i>Simpan</button>
							</div>
						</div>
					</form>
					<table class="table table-bordered table-hover">
						<thead>
							<tr class="text-center">
								<th>No</th>
								<th>Model</th>
								<th>Suffix</th>
								<th>Color</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$data_filter = $this->model->gds("filter","*","kap = '$kap'","result_array");
							if(!empty($data_filter)){
								$no = 1;
								foreach ($data_filter as $value) {
									?>
									<tr class="text-center">
										<td class="align-middle"><?= $no++ ?></td>
										<td class="align-middle"><?= !empty($value["model"]) ? ($value["model"] == "all" ? '<span class="badge badge-info">'.strtoupper($value["model"]).'</span>' : strtoupper($value["model"])) : '<span class="badge badge-warning">Not Set</span>'; ?></td>
										<td class="align-middle"><?= !empty($value["suffix"]) ? ($value["suffix"] == "all" ? '<span class="badge badge-info">'.strtoupper($value["suffix"]).'</span>' : strtoupper($value["suffix"])) : '<span class="badge badge-warning">Not Set</span>'; ?></td>
										<td class="align-middle"><?= !empty($value["color"]) ? ($value["color"] == "all" ? '<span class="badge badge-info">'.strtoupper($value["color"]).'</span>' : strtoupper($value["color"])) : '<span class="badge badge-warning">Not Set</span>'; ?></td>
										<td class="align-middle">
											<a href="<?= base_url("filter/delete?i=".$value["id"]); ?>" class="btn btn-danger" title="Hapus" >
												<i class="fas fa-trash-alt"></i> Hapus
											</a>
										</td>
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
		<?php
	}
	?>
</div>
