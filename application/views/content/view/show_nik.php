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
            <div class="row">
                <div class="col-lg-12 pl-0 mb-4" align="right">
                    <a href="<?=base_url("")?>" class="btn btn-sm btn-danger" title="Main Menu" style="height:35px; padding-top:6px !important;">Main Menu</a>
                    <a href="<?=base_url("upload_vlt")?>" class="btn btn-sm btn-danger" title="Back" style="height:35px; padding-top:6px !important;">Back</a>
                </div>
            </div>
			<div class="row">
				<?php
				$get_model = $this->model->gds("plan_wos_base","model_name","model_name NOT LIKE '%EXP%' AND brand = 'D' GROUP BY model_name","result");
				if(!empty($get_model)){
					$no = 1;
					foreach ($get_model as $model) {
						$get_nik = $this->model->gds("table_vlt","sapnik","lot_code IN((SELECT suffix FROM plan_wos_base WHERE model_name = '".$model->model_name."' AND brand = 'D'))","result");
						?>
						<div class="col text-center">
							<div class="card">
								<div class="card-header pt-1 pb-1">
									<div class="row">
										<div class="col-lg-8 text-left">
											<div class="input-group">
											<img src="<?= base_url("assets/".$model->model_name."_LOGO.png"); ?>" width="64%" class="mr-2"- style="position:absolute; top:-33px; left:50%;">
											<h1><?= $model->model_name; ?></h1>
											</div>
										</div>
										<div class="col-lg-4 text-right mt-2">
											<button class="btn btn-sm btn-info" data-id="<?= $no; ?>" onclick="copy_data(this)"><i class="fas fa-copy mr-1"></i>Copy NIK</button>
										</div>
									</div>
								</div>
								<div class="card-body p-0">
									<div style="max-height:calc(100vh - 260px); overflow:auto;">
										<table class="table table-sm table-bordered table-hover m-0">
											<?php
											$data_nik = [];
											$no_nik = 0;
											if(!empty($get_nik)){
												foreach ($get_nik as $nik) {
													$data_nik[] = $nik->sapnik;
													echo '<tr><td style="font-size:10pt;">'.$no_nik.'</td><td style="font-size:10pt;">'.$nik->sapnik.'</td></tr>';
													$no_nik++;
												}
											}
											$data_nik = implode("\n",$data_nik);
											?>
										</table>
									</div>
								</div>
							</div>
							<div class="bg-info pt-2 pb-2 font-weight-bold text-light" style="font-size:12pt;">TOTAL : <?= ($no_nik); ?></div>
							<textarea hidden id="data-nik-<?= $no; ?>" cols="30" rows="10"><?= $data_nik; ?></textarea>
						</div>
						<?php
						$no++;
					}
				}
				?>
			</div>
        </div>
    </div>
</div>
