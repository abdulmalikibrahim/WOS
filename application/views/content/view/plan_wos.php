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
	<a href="<?=base_url("")?>" class="btn btn-sm btn-danger" title="Kembali" style="height:35px; position:absolute; top:15px; right:20px;">Main Menu</a>
</div>
<div class="row pl-3 pr-3 justify-content-center">
    <div class="col-lg-6 pr-1 align-items-center mt-0">
        <div class="card card-body">
            <div class="row">
                <div class="col-12">
                    <span class="text-danger" style="font-size:15px; font-weight:bold;">Silahkan Upload Template Plan WOS KAP 1</i></span>
                </div>
                <div class="col-lg-8">
                    <form action="<?=base_url("import_plan_wos")?>" method="post" id="form_export" enctype="multipart/form-data">
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="upload-excel" name="upload-file" accept=".xls">
                                <label class="custom-file-label" for="customFile" id="customFile">Pilih File Excel</label>
                            </div>
                            <a href="<?=base_url("clear_plan_wos")?>" class="btn btn-sm btn-secondary ml-2" title="Bersihkan Plan WOS" style="height:37px;">Clear</a>
                        </div>
                    </form>
                </div>
            </div>
            <div style="overflow: auto;">
                <?php
                $data_batch = $this->model->gds("plan_wos","batch","suffix != '' GROUP BY batch","result");
                if(!empty($data_batch)){
                    foreach ($data_batch as $db) {
                        $batch = $db->batch;
                        ?>
                        <div class="table-responsive" style="height: calc(100vh - 180px);">
                            <center><h5 class="mb-2 mt-2"><?= "PLAN WOS BATCH-".$batch; ?></h5></center>
                            <table class="table table-bordered table-hover table-sm mt-3 tableFixHead w-100" style="font-size:8pt;">
                                <thead class="thead-light">
                                    <tr align="center">
                                        <th class="align-middle" width="20%">Katashiki</th>
                                        <th class="align-middle" width="14%">Suffix</th>
                                        <th class="align-middle" width="14%">Plan</th>
                                        <th class="align-middle" width="14%">Stock<br>Tab</th>
                                        <th class="align-middle" width="10%">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $load_model_code = '';
                                    $load_model_name = '';
                                    $load_plan = '';
                                    $load = '';
                                    $data_model_code = $this->model->gds("plan_wos","model_code","suffix != '' AND batch = '$batch' GROUP BY model_code ORDER BY CASE model_code WHEN 'D30H' THEN 1 WHEN 'D06A' THEN 2 WHEN 'D55L' THEN 3 ELSE 4 END","result");
                                    if(!empty($data_model_code)){
                                        foreach ($data_model_code as $dmc) {
                                            $get_suffix = $this->model->gds("plan_wos","suffix","model_code = '".$dmc->model_code."' AND batch = '$batch'","result");
                                            if(!empty($get_suffix)){
                                                $data_suffix_mc = '';
                                                foreach ($get_suffix as $get_suffix) {
                                                    $data_suffix_mc .= "'".$get_suffix->suffix."',";
                                                }
                                                $ds = rtrim($data_suffix_mc,",");
                                            }else{
                                                $ds = "'0'";
                                            }
                                            $sum_by_model_code = $this->model->gds("plan_wos","SUM(plan) as plan","model_code = '".$dmc->model_code."' AND batch = '$batch'","row");
                                            $sum_by_model_name_mc = $this->model->gds("tabungan_vlt","COUNT(wos_material) as count","katashiki_suffix IN(".$ds.")","row");
                                            $load_model_code = '
                                            <tr class="bg-warning text-dark">
                                                <td colspan="2" align="center">TOTAL '.$dmc->model_code.'</td>
                                                <td align="center">'.$sum_by_model_code->plan.'</td>
                                                <td align="center">'.$sum_by_model_name_mc->count.'</td>
                                                <td align="center"></td>
                                            </tr>';
                                            $data_model_name = $this->model->gds("plan_wos","model_name","model_code = '".$dmc->model_code."' AND batch = '$batch' GROUP BY model_name ORDER BY CASE brand WHEN 'D' THEN 1 WHEN 'T' THEN 2 WHEN 'TE' THEN 3 ELSE 4 END","result");
                                            foreach ($data_model_name as $dmn) {
                                                $get_suffix = $this->model->gds("plan_wos","suffix","model_name = '".$dmn->model_name."' AND batch = '$batch'","result");
                                                $data_suffix_mn = '';
                                                if(!empty($get_suffix)){
                                                    foreach ($get_suffix as $get_suffix) {
                                                        $data_suffix_mn .= "'".$get_suffix->suffix."',";
                                                    }
                                                    $ds = rtrim($data_suffix_mn,",");
                                                }else{
                                                    $ds = "'0'";
                                                }
                                                $sum_by_model_name = $this->model->gds("plan_wos","SUM(plan) as plan","model_name = '".$dmn->model_name."' AND batch = '$batch'","row");
                                                $sum_by_model_name_mn = $this->model->gds("tabungan_vlt","COUNT(wos_material) as count","katashiki_suffix IN(".$ds.")","row");
                                                $load_model_name = '
                                                <tr class="bg-secondary text-white">
                                                    <td colspan="2" align="center">'.$dmn->model_name.'</td>
                                                    <td align="center">'.$sum_by_model_name->plan.'</td>
                                                    <td align="center">'.$sum_by_model_name_mn->count.'</td>
                                                    <td align="center"></td>
                                                </tr>';
                                                $data_plan = $this->model->gds("plan_wos","*","model_code = '".$dmc->model_code."' AND model_name = '".$dmn->model_name."' AND batch = '$batch'","result");
                                                foreach ($data_plan as $dp) {
                                                    $tabungan = $this->model->gds("tabungan_vlt","COUNT(sapnik) AS tabungan","katashiki_suffix = '".$dp->suffix."'","row");
                                                    if(!empty($tabungan->tabungan)){
                                                        $tabungan = $tabungan->tabungan;
                                                    }else{
                                                        $tabungan = "";
                                                    }
                                                    if($dp->plan <= $tabungan){
                                                        $status = "OK";
                                                        $bg = "bg-success text-white";
                                                    }else{
                                                        $status = "NOK";
                                                        $bg = "bg-danger text-white";
                                                    }
                                                    $load_plan .= '
                                                    <tr>
                                                        <td>'.$dp->katashiki.'</td>
                                                        <td align="center">'.$dp->suffix.'</td>
                                                        <td align="center" id="row_plan_'.$dp->suffix.'">'.$dp->plan.'</td>
                                                        <td align="center">'.$tabungan.'</td>
                                                        <td align="center" class="'.$bg.'">'.$status.'</td>
                                                    </tr>';
                                                    $arr_suffix[$dp->suffix] = $dp->plan;
                                                }
                                                $load_plan .= $load_model_name;
                                            }
                                            $load_plan .= $load_model_code;
                                        }
                                        $load = $load_plan;
                                        $data_table = "isi";
                                    }else{
                                        $data_table = "kosong";
                                        $load = '
                                        <tr class="bg-warning text-dark">
                                            <td colspan="8" align="center" class="align-middle"><i>Data kosong</i></td>
                                        </tr>';
                                    }
                                    echo $load;
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <?php
                        if($data_table == "isi"){
                            $get_suffix_gt = $this->model->gds("plan_wos","suffix","model_name != '' AND batch = '$batch'","result");
                            $data_suffix_gt = '';
                            if(!empty($get_suffix_gt)){
                                foreach ($get_suffix_gt as $get_suffix_gt) {
                                    $data_suffix_gt .= "'".$get_suffix_gt->suffix."',";
                                }
                                $ds = rtrim($data_suffix_gt,",");
                            }else{
                                $ds = "'0'";
                            }
                            $grand_total = $this->model->gds("plan_wos","SUM(plan) as plan","suffix !='' AND batch = '$batch'","row");
                            $sum_by_model_name_gt = $this->model->gds("tabungan_vlt","COUNT(wos_material) as count","katashiki_suffix IN(".$ds.")","row");
                            echo '
                            <table class="table table-sm table-warning" style="font-size:12px; width:97%;">
                                <tr class="bg-warning font-weight-bold">
                                    <td colspan="2" align="center" width="34%">GRAND TOTAL</td>
                                    <td align="center" width="14%">'.$grand_total->plan.'</td>
                                    <td align="center" width="14%">'.$sum_by_model_name_gt->count.'</td>
                                    <td align="center" width="10%"></td>
                                <tr>
                            </table>';
                        }
                    }
                }
                ?>
            </div>
        </div>
    </div>
    <div class="col-lg-6 pl-1 align-items-center mt-0">
        <div class="card card-body">
            <div class="row">
                <div class="col-12">
                    <span class="text-danger" style="font-size:15px; font-weight:bold;">Silahkan Upload Template Plan WOS KAP 2</i></span>
                </div>
                <div class="col-lg-8">
                    <form action="<?=base_url("import_plan_wos?t=kap2")?>" method="post" id="form_export_kap2" enctype="multipart/form-data">
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="upload-excel-kap2" name="upload-file" accept=".xls">
                                <label class="custom-file-label" for="customFile-kap2" id="customFile-kap2">Pilih File Excel</label>
                            </div>
                            <a href="<?=base_url("clear_plan_wos?t=kap2")?>" class="btn btn-sm btn-secondary ml-2" title="Bersihkan Plan WOS" style="height:37px;">Clear</a>
                        </div>
                    </form>
                </div>
            </div>
            <div style="overflow: auto;">
                <?php
                $data_batch = $this->model->gds("plan_wos_kap2","batch","suffix != '' GROUP BY batch","result");
                if(!empty($data_batch)){
                    foreach ($data_batch as $db) {
                        $batch = $db->batch;
                        ?>
                        <div class="table-responsive" style="height: calc(100vh - 180px);">
                            <center><h5 class="mb-2 mt-2"><?= "PLAN WOS BATCH-".$batch; ?></h5></center>
                            <table class="table table-bordered table-hover table-sm mt-3 tableFixHead w-100" style="font-size:8pt;">
                                <thead class="thead-light">
                                    <tr align="center">
                                        <th class="align-middle" width="20%">Katashiki</th>
                                        <th class="align-middle" width="14%">Suffix</th>
                                        <th class="align-middle" width="14%">Plan</th>
                                        <th class="align-middle" width="14%">Stock<br>Tab</th>
                                        <th class="align-middle" width="10%">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $load_model_code = '';
                                    $load_model_name = '';
                                    $load_plan = '';
                                    $load = '';
                                    $data_model_code = $this->model->gds("plan_wos_kap2","model_code","suffix != '' AND batch = '$batch' GROUP BY model_code ORDER BY CASE model_code WHEN 'D30H' THEN 1 WHEN 'D06A' THEN 2 WHEN 'D55L' THEN 3 ELSE 4 END","result");
                                    if(!empty($data_model_code)){
                                        foreach ($data_model_code as $dmc) {
                                            $get_suffix = $this->model->gds("plan_wos_kap2","suffix","model_code = '".$dmc->model_code."' AND batch = '$batch'","result");
                                            if(!empty($get_suffix)){
                                                $data_suffix_mc = '';
                                                foreach ($get_suffix as $get_suffix) {
                                                    $data_suffix_mc .= "'".$get_suffix->suffix."',";
                                                }
                                                $ds = rtrim($data_suffix_mc,",");
                                            }else{
                                                $ds = "'0'";
                                            }
                                            $sum_by_model_code = $this->model->gds("plan_wos_kap2","SUM(plan) as plan","model_code = '".$dmc->model_code."' AND batch = '$batch'","row");
                                            $sum_by_model_name_mc = $this->model->gds("tabungan_vlt_kap2","COUNT(wos_material) as count","katashiki_suffix IN(".$ds.")","row");
                                            $load_model_code = '
                                            <tr class="bg-warning text-dark">
                                                <td colspan="2" align="center">TOTAL '.$dmc->model_code.'</td>
                                                <td align="center">'.$sum_by_model_code->plan.'</td>
                                                <td align="center">'.$sum_by_model_name_mc->count.'</td>
                                                <td align="center"></td>
                                            </tr>';
                                            $data_model_name = $this->model->gds("plan_wos_kap2","model_name","model_code = '".$dmc->model_code."' AND batch = '$batch' GROUP BY model_name ORDER BY CASE brand WHEN 'D' THEN 1 WHEN 'T' THEN 2 WHEN 'TE' THEN 3 ELSE 4 END","result");
                                            foreach ($data_model_name as $dmn) {
                                                $get_suffix = $this->model->gds("plan_wos_kap2","suffix","model_name = '".$dmn->model_name."' AND batch = '$batch'","result");
                                                $data_suffix_mn = '';
                                                if(!empty($get_suffix)){
                                                    foreach ($get_suffix as $get_suffix) {
                                                        $data_suffix_mn .= "'".$get_suffix->suffix."',";
                                                    }
                                                    $ds = rtrim($data_suffix_mn,",");
                                                }else{
                                                    $ds = "'0'";
                                                }
                                                $sum_by_model_name = $this->model->gds("plan_wos_kap2","SUM(plan) as plan","model_name = '".$dmn->model_name."' AND batch = '$batch'","row");
                                                $sum_by_model_name_mn = $this->model->gds("tabungan_vlt_kap2","COUNT(wos_material) as count","katashiki_suffix IN(".$ds.")","row");
                                                $load_model_name = '
                                                <tr class="bg-secondary text-white">
                                                    <td colspan="2" align="center">'.$dmn->model_name.'</td>
                                                    <td align="center">'.$sum_by_model_name->plan.'</td>
                                                    <td align="center">'.$sum_by_model_name_mn->count.'</td>
                                                    <td align="center"></td>
                                                </tr>';
                                                $data_plan = $this->model->gds("plan_wos_kap2","*","model_code = '".$dmc->model_code."' AND model_name = '".$dmn->model_name."' AND batch = '$batch'","result");
                                                foreach ($data_plan as $dp) {
                                                    $tabungan = $this->model->gds("tabungan_vlt_kap2","COUNT(sapnik) AS tabungan","katashiki_suffix = '".$dp->suffix."'","row");
                                                    if(!empty($tabungan->tabungan)){
                                                        $tabungan = $tabungan->tabungan;
                                                    }else{
                                                        $tabungan = "";
                                                    }
                                                    if($dp->plan <= $tabungan){
                                                        $status = "OK";
                                                        $bg = "bg-success text-white";
                                                    }else{
                                                        $status = "NOK";
                                                        $bg = "bg-danger text-white";
                                                    }
                                                    $load_plan .= '
                                                    <tr>
                                                        <td>'.$dp->katashiki.'</td>
                                                        <td align="center">'.$dp->suffix.'</td>
                                                        <td align="center" id="row_plan_'.$dp->suffix.'">'.$dp->plan.'</td>
                                                        <td align="center">'.$tabungan.'</td>
                                                        <td align="center" class="'.$bg.'">'.$status.'</td>
                                                    </tr>';
                                                    $arr_suffix[$dp->suffix] = $dp->plan;
                                                }
                                                $load_plan .= $load_model_name;
                                            }
                                            $load_plan .= $load_model_code;
                                        }
                                        $load = $load_plan;
                                        $data_table = "isi";
                                    }else{
                                        $data_table = "kosong";
                                        $load = '
                                        <tr class="bg-warning text-dark">
                                            <td colspan="8" align="center" class="align-middle"><i>Data kosong</i></td>
                                        </tr>';
                                    }
                                    echo $load;
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <?php
                        if($data_table == "isi"){
                            $get_suffix_gt = $this->model->gds("plan_wos_kap2","suffix","model_name != '' AND batch = '$batch'","result");
                            $data_suffix_gt = '';
                            if(!empty($get_suffix_gt)){
                                foreach ($get_suffix_gt as $get_suffix_gt) {
                                    $data_suffix_gt .= "'".$get_suffix_gt->suffix."',";
                                }
                                $ds = rtrim($data_suffix_gt,",");
                            }else{
                                $ds = "'0'";
                            }
                            $grand_total = $this->model->gds("plan_wos_kap2","SUM(plan) as plan","suffix !='' AND batch = '$batch'","row");
                            $sum_by_model_name_gt = $this->model->gds("tabungan_vlt_kap2","COUNT(wos_material) as count","katashiki_suffix IN(".$ds.")","row");
                            echo '
                            <table class="table table-sm table-warning" style="font-size:12px; width:97%;">
                                <tr class="bg-warning font-weight-bold">
                                    <td colspan="2" align="center" width="34%">GRAND TOTAL</td>
                                    <td align="center" width="14%">'.$grand_total->plan.'</td>
                                    <td align="center" width="14%">'.$sum_by_model_name_gt->count.'</td>
                                    <td align="center" width="10%"></td>
                                <tr>
                            </table>';
                        }
                    }
                }
                ?>
            </div>
        </div>
    </div>
</div>
