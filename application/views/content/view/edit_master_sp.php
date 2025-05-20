<style>
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { 
        -webkit-appearance: none; 
        margin: 0; 
    }
</style>
<div class="row justify-content-center">
    <div class="col-12" align="center">
        <h1 class="m-0 text-white" style="font-size:3rem;"><?=$title?></h1>
    </div>
</div>
<form action="<?= base_url("save_edit_master_sp/".$this->params2) ?>" method="post" id="form-input">
	<div class="card card-body">
		<table class="table-sm table-bordered table-hover" style="font-size:10pt;">
			<thead class="thead-light">
				<tr align="center">
					<th class="align-middle">Part Number</th>
					<th class="align-middle">Part Name</th>
					<th class="align-middle">Job Number</th>
					<th class="align-middle">Route</th>
					<th class="align-middle">Qty</th>
					<th class="align-middle">Keterangan</th>
					<th class="align-middle">Action</th>
				</tr>
			</thead>
			<tbody id="body-edit">
				<?php
				$data_master = $this->model->gds_heijunka("breakdown_sp","*","Part_Number = '".$this->params2."'","result");
				if(!empty($data_master)){
					foreach ($data_master as $dm) {
						if($dm->Breakdown == $dm->Part_Number){
							$bg_color = "bg-dark text-white";
						}else{
							$bg_color = "";
						}
						?>
						<tr align="center" id="data-row-<?=$dm->No?>">
							<th class="align-middle">
								<input type="text" class="form-control <?= $bg_color; ?>" name="breakdown[<?=$dm->No;?>]" value="<?=$dm->Breakdown?>" style="width:10rem;" required>
							</th>
							<th class="align-middle">
								<input type="text" class="form-control <?= $bg_color; ?>" name="part_name[<?=$dm->No;?>]" value="<?=$dm->Part_Name?>" style="width:25rem;" required>
							</th>
							<th class="align-middle">
								<input type="text" class="form-control <?= $bg_color; ?>" name="original_part[<?=$dm->No;?>]" value="<?=$dm->Original_Part?>" style="width:7rem;" required>
							</th>
							<th class="align-middle">
								<input type="text" class="form-control <?= $bg_color; ?>" name="route[<?=$dm->No;?>]" value="<?=$dm->Route?>" required>
							</th>
							<th class="align-middle">
								<input type="number" class="form-control text-center <?= $bg_color; ?>" name="qty[<?=$dm->No;?>]" value="<?=$dm->Qty?>" style="width:4rem;" required>
							</th>
							<th class="align-middle">
								<input type="text" class="form-control <?= $bg_color; ?>" id="keterangan-<?=$dm->No;?>" name="keterangan[<?=$dm->No;?>]" value="<?=$dm->Keterangan?>">
							</th>
							<th class="align-middle">
								<button type="reset" class="btn btn-sm btn-danger" title="Delete" onclick="delete_row('<?=$dm->No;?>')"><i class="fas fa-trash-alt m-0"></i></button>
							</th>
						</tr>
						<?php
					}
				}
				?>
				<tr align="center" id="data-row-add-1">
					<th class="align-middle">
						<input type="text" class="form-control" name="breakdown[]" value="" style="width:10rem;" onchange="add_row(1)" required>
					</th>
					<th class="align-middle">
						<input type="text" class="form-control" name="part_name[]" value="" style="width:25rem;" required>
					</th>
					<th class="align-middle">
						<input type="text" class="form-control" name="original_part[]" value="" style="width:7rem;" required>
					</th>
					<th class="align-middle">
						<input type="text" class="form-control" name="route[]" value="" required>
					</th>
					<th class="align-middle">
						<input type="number" class="form-control text-center" name="qty[]" value="" style="width:4rem;" required>
					</th>
					<th class="align-middle">
						<input type="text" class="form-control" name="keterangan[]" value="">
					</th>
					<th class="align-middle">
						<button class="btn btn-sm btn-danger" title="Delete" onclick="delete_row('data-row-add-1')"><i class="fas fa-trash-alt m-0"></i></button>
					</th>
				</tr>
			</tbody>
		</table>
		<div class="w-100 text-right mt-3">
			<a href="<?=base_url("master_service_part")?>" class="btn btn-sm btn-danger">Kembali</a>
			<a href="javascript:void(0)" class="btn btn-sm btn-info" data-id="1" id="btn-save">Simpan</a>
		</div>
	</div>
</form>
