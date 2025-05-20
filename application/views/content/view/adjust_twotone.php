<style>
    .tableFixHead { overflow: auto; height: 100px; }
    .tableFixHead thead th { position: sticky; top: -1px; z-index: 1; }

    /* Just common table stuff. Really. */
    table  { border-collapse: collapse; border:0px !important; }
    th, td { padding: 0px 0px; }
    th     { background:#eee; color:#000 }
    .tooltip-inner {
        max-width: 200px;
        padding: 3px 8px;
        color: #fff;
        text-align: center;
        background-color: #F6470A;
        border-radius: .25rem;
    }
    th:first-child, td:first-child {
        position:sticky;
        left:0px;
    
    }
    .tooltip.bs-tooltip-auto[x-placement^=top] .arrow::before, .tooltip.bs-tooltip-top .arrow::before {
        margin-left: -3px;
        content: "";
        border-width: 5px 5px 0;
        border-top-color: #F6470A;
    }
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { 
        -webkit-appearance: none; 
        margin: 0; 
    }
</style>
<div class="row justify-content-center">
    <div class="col-12" align="center">
        <h1 class="m-0 text-white" style="font-size:2.5rem;"><?=$title?></h1>
    </div>
</div>
<div class="row justify-content-center">
    <div class="col-lg-12 align-items-center mt-3">
        <div class="card card-body">
            <div class="row">
                <div class="col-lg-5">
                    <?php 
                    $tt = $this->model->gds("twotone","twotone","twotone !=","result");
                    foreach ($tt as $tt) {
                        $data_tt[] = $tt->twotone;
                    }
                    $data_tt = implode("','",$data_tt);
                    $total_tt = $this->model->gds("tabungan_vlt","COUNT(color_code) as count","color_code IN ('".$data_tt."')","row");
                    //Check unit yang mengandung two tone
                    $color_two_tone = $this->model->gds("twotone","twotone","twotone !=","result");
                    $twotone = '';
                    if(!empty($color_two_tone)){
                        foreach ($color_two_tone as $color_two_tone) {
                            $ctt[] = $color_two_tone->twotone; //color two tone
                        }
                        $get_unit = $this->model->gds("tabungan_vlt","wos_material_description","color_code IN('".implode("','",$ctt)."') AND color_code != '' GROUP BY lot_code","result"); //SEBELUMNYA MENGGUNAKAN GROUP color_code
                        if(!empty($get_unit)){
                            foreach ($get_unit as $get_unit) {
                                $explode_get_unit = explode(" ",$get_unit->wos_material_description);
                                $gu[$explode_get_unit[2]] = $explode_get_unit[2]; // buat array unit yang ada two tone nya
                            }
                        }else{
                            $gu[] = 0; // jika tidak di temukan twotone di database get unit kosong
                        }
                    }else{
                        $gu[] = 0; // jika tidak di temukan twotone di database get unit kosong
                    }
                    ?>
                    <table class="table-bordered w-100">
                        <tr align="center">
                            <th>STOCK</th>
                            <th>SINGLE TONE</th>
                            <th>TWO TONE</th>
                        </tr>
                        <tr>
                            <?php
                            $total_setting = $this->model->gds("twotone_setting","SUM(qty) as total_qty","suffix_pdd !=","row");
                            if(!empty($total_setting->total_qty)){
                                $total_qty = $total_setting->total_qty;
                            }else{
                                $total_qty = "";
                            }
                            ?>
                            <td style="width:100px;" class="p-2" align="center"><h5 class="m-0"><?=$total_tt->count?></h5></td>
                            <td style="width:100px;" class="p-2" align="center"><h5 class="m-0" id="total-single-tone">0</h5></td>
                            <td style="width:100px;" class="p-2" align="center"><h5 class="m-0" id="adjust"><?=$total_qty*1;?></h5></td>
                            <td style="width:100px; border-right:0px; border-top:0px; border-bottom:0px;" id="td_button_simpan"></td>
                        </tr>
                    </table>
                </div>
                <div class="col-lg-7 pl-0" align="right">
                    <a href="<?=base_url("tabungan")?>" class="btn btn-sm btn-danger" title="Main Menu" style="height:35px;">Kembali</a>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-12">
                    <div class="table-responsive mt-3" style="height: calc(100vh - 187px);">
                        <h5 class="mb-2">STOCK TABUNGAN</h5>
                        <table border="1" class="tableFixHead" style="font-size:8pt;">
                            <thead class="thead-light">
                                <tr align="center" class="bg-secondary text-light">
                                    <th class="align-middle" style="z-index:2;"><div style="width:210px;">TONE</div></th>
                                    <?php 
                                    $suffix_tt = $this->model->join_data("tabungan_vlt a","plan_wos b","a.katashiki_suffix = b.suffix","a.katashiki_suffix,COUNT(*) as count","a.wos_material_description REGEXP '".implode("|",$gu)."' AND b.plan IS NOT NULL AND a.sapnik != '' GROUP BY katashiki_suffix ORDER BY katashiki_suffix ASC","result");
                                    if(!empty($suffix_tt)){
                                        foreach ($suffix_tt as $stt) {
                                            echo '<th class="align-middle"><div style="width:50px">'.$stt->katashiki_suffix.'</div></th>';
                                            $two_tone_suffix[$stt->katashiki_suffix] = $stt->count;
                                            $get_single_tone = $this->model->gds("tabungan_vlt","COUNT(sapnik) as count","color_code NOT IN('".$data_tt."') AND katashiki_suffix = '".$stt->katashiki_suffix."'","row");
                                            $single_tone_suffix[$stt->katashiki_suffix] = $get_single_tone->count;
                                        }
                                    }
                                    ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $data_color_tt = $this->model->gds("twotone","twotone","twotone !=","result");
                                if(!empty($data_color_tt)){
                                    // $in_tt = "";
                                    // foreach ($data_color_tt as $dctt) {
                                    //     $in_tt .= "'".$dctt->twotone."',";
                                    // }
                                    // $data_pdd_tt = $this->model->gds("tabungan_vlt","plan_delivery_date","color_code IN(".rtrim($in_tt,",").") AND sapnik != '' GROUP BY plan_delivery_date ORDER BY plan_delivery_date ASC","result");
                                    // foreach ($data_pdd_tt as $data_pdd_tt) {
                                    //     echo '<tr>
                                    //             <td align="center" class="p-1 bg-secondary text-white font-weight-bold">'.date("d-m-Y",strtotime($data_pdd_tt->plan_delivery_date)).'</td>';
                                    //             foreach ($two_tone_suffix as $key => $value) {
                                    //                 $count_pdd = $this->model->gds("tabungan_vlt","COUNT(*) as count","katashiki_suffix = '".$key."' AND color_code IN(".rtrim($in_tt,",").") AND plan_delivery_date = '".$data_pdd_tt->plan_delivery_date."'","row");
                                    //                 if(empty($count_pdd->count)){
                                    //                     $count_pdd = "";
                                    //                 }else{
                                    //                     $count_pdd = $count_pdd->count;
                                    //                 }
                                    //                 echo '<td align="center" class="p-1">'.$count_pdd.'</td>';
                                    //             }
                                    //     echo '</tr>';
                                    // }
                                    echo '</tr>';
                                    echo '<tr class="font-weight-bold"><td align="center" class="bg-secondary text-light">1 TONE</td>';
                                    foreach ($single_tone_suffix as $key => $value) {
                                        if($value > 0){
                                            $val_st = $value;
                                        }else{
                                            $val_st = "";
                                        }
                                        echo '<td align="center" class="p-1 bg-secondary text-light" id="single_tone_'.$key.'">'.$val_st.'</td>';
                                    }
                                    echo '<tr class="font-weight-bold"><td align="center" class="bg-info text-light">2 TONE</td>';
                                    foreach ($two_tone_suffix as $key => $value) {
                                        if($value > 0){
                                            $val_tt = $value;
                                        }else{
                                            $val_tt = "";
                                        }
                                        echo '<td align="center" class="p-1 bg-info text-light">'.$val_tt.'</td>';
                                    }
                                    echo '<tr class="font-weight-bold"><td align="center" class="bg-light text-dark">Total</td>';
                                    foreach ($single_tone_suffix as $key => $value) {
                                        $st = $value;
                                        $tt = $two_tone_suffix[$key];
                                        $total = $st + $tt;
                                        echo '<td align="center" class="p-1">'.$total.'</td>';
                                    }
                                    echo '</tr>';
                                }else{
                                    echo '<td colspan="25" align="center" class="p-1"><i>Data kosong</i></td>';
                                }
                                ?>
                            </tbody>
                        </table>

                        <h5 class="mb-2 mt-2">PLAN VS ACTUAL</h5>
                        <table border="1" class="tableFixHead" style="font-size:8pt; height:50px;">
                            <thead class="thead-light">
                                <tr align="center" class="bg-secondary text-light">
                                    <th class="align-middle" style="z-index:2;"><div style="width:210px;">-</div></th>
                                    <?php 
                                    // $suffix_tt = $this->model->gds("tabungan_vlt","katashiki_suffix,COUNT(*) as count","wos_material_description REGEXP '".implode("|",$gu)."' AND sapnik != '' GROUP BY katashiki_suffix ORDER BY katashiki_suffix ASC","result");
                                    if(!empty($suffix_tt)){
                                        foreach ($suffix_tt as $stt) {
                                            echo '<th class="align-middle"><div style="width:50px" class="suffix">'.$stt->katashiki_suffix.'</div></th>';
                                            $plan_wos = $this->model->gds("plan_wos","SUM(plan) as plan","suffix = '".$stt->katashiki_suffix."'","row");
                                            if(!empty($plan_wos)){
                                                $data_plan_wos[$stt->katashiki_suffix] = $plan_wos->plan;
                                            }else{
                                                $data_plan_wos[$stt->katashiki_suffix] = "";
                                            }
                                        }
                                    }
                                    ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $data_color_tt = $this->model->gds("twotone","twotone","twotone !=","result");
                                if(!empty($data_color_tt)){
                                    echo '</tr>';
                                    echo '<tr class="font-weight-bold"><td align="center" class="bg-white">PLAN</td>';
                                    foreach ($data_plan_wos as $key => $value) {
                                        echo '<td align="center" class="p-1 class-plan" id="plan_'.$key.'">'.$value.'</td>';
                                    }
                                    echo '<tr class="font-weight-bold"><td align="center" class="bg-white">ACTUAL</td>';
                                    foreach ($data_plan_wos as $key => $value) {
                                        $get_set_tt = $this->model->gds("twotone_setting","SUM(qty) as qty","suffix = '$key'","row");
                                        if(empty($get_set_tt->qty)){
                                            $qty_tt = "";
                                            $qty_tt_set[$key] = 0;
                                        }else{
                                            $qty_tt = $get_set_tt->qty;
                                            $qty_tt_set[$key] = $qty_tt;
                                        }
                                        $get_set_st = $this->model->gds("singletone_setting","SUM(qty) as qty","suffix = '$key'","row");
                                        if(empty($get_set_st->qty)){
                                            $qty_st = "";
                                            $qty_st_set[$key] = 0;
                                        }else{
                                            $qty_st = $get_set_st->qty;
                                            $qty_st_set[$key] = $qty_st;
                                        }
                                        
                                        if(($qty_st + $qty_tt) > 0){
                                            $qty = $qty_st + $qty_tt;
                                        }else{
                                            $qty = "";
                                        }
                                        echo '<td align="center" class="p-1 " id="actual_'.$key.'">'.$qty.'</td>';
                                    }
                                    echo '<tr class="font-weight-bold"><td align="center" class="bg-light text-dark">STATUS</td>';
                                    foreach ($data_plan_wos as $key => $value) {
                                        $qty_tt = $qty_tt_set[$key];
                                        $qty_st = $qty_st_set[$key];
                                        $qty = $qty_st + $qty_tt;
                                        if(!empty($qty)){
                                            if($qty > $value){
                                                echo '<td align="center" class="p-1 bg-danger text-light" id="adjust_'.$key.'" data-standard="'.$value.'">NG</td>';
                                            }else{
                                                echo '<td align="center" class="p-1 bg-success text-light" id="adjust_'.$key.'" data-standard="'.$value.'">OK</td>';
                                            }
                                        }else{
                                            echo '<td align="center" class="p-1 text-light" id="adjust_'.$key.'" data-standard="'.$value.'"></td>';
                                        }
                                    }
                                    echo '</tr>';
                                }else{
                                    echo '<td colspan="25" align="center" class="p-1"><i>Data kosong</i></td>';
                                }
                                ?>
                            </tbody>
                        </table>
                        
                        <div class="row mt-2">
                            <div class="col">
                                <h5 class="mt-3 mb-2">ADJUSTMENT</h5>
                            </div>
                            <div class="col d-flex align-items-end justify-content-end">
                                <button onclick="autofullfillnumber()" class="btn btn-sm btn-info mb-2">Auto Full Fill</button>
                            </div>
                        </div>
                        <form action="<?=base_url("save_adjust")?>" method="post" id="submit_adjust">
                            <table border="1" class="tableFixHead" style="font-size:8pt;">
                                <thead class="thead-light">
                                    <tr align="center" class="bg-secondary text-light">
                                        <th class="align-middle" style="z-index:5;"><div style="width:70px;">PDD</div></th>
                                        <th class="align-middle" style="z-index:2;"><div style="width:70px;">TONE</div></th>
                                        <th class="align-middle" style="z-index:2;"><div style="width:70px;">TOTAL</div></th>
                                        <?php
                                        // $suffix_tt = $this->model->gds("tabungan_vlt","katashiki_suffix,COUNT(*) as count","wos_material_description REGEXP '".implode("|",$gu)."' AND sapnik != '' GROUP BY katashiki_suffix ORDER BY katashiki_suffix ASC","result");
                                        if(!empty($suffix_tt)){
                                            foreach ($suffix_tt as $stt) {
                                                echo 
                                                '<th class="align-middle" style="z-index:3;">
                                                    <div style="width:50px;">
                                                        '.$stt->katashiki_suffix.'
                                                        <div class="bg-success text-light w-100 remain-suffix" id="remain-'.$stt->katashiki_suffix.'">0</div>
                                                    </div>
                                                </th>';
                                                $data_suffix[$stt->katashiki_suffix] = $stt->count;
                                            }
                                        }
                                        ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $data_color_tt = $this->model->gds("twotone","twotone","twotone !=","result");
                                    if(!empty($data_color_tt)){
                                        $in_tt = "";
                                        foreach ($data_color_tt as $dctt) {
                                            $in_tt .= "'".$dctt->twotone."',";
                                        }
                                        $pdd = $this->model->gds("tabungan_vlt","plan_delivery_date","sapnik != '' GROUP BY plan_delivery_date ORDER BY plan_delivery_date ASC","result");
                                        foreach ($pdd as $pdd) {
                                            $pdd_value = $pdd->plan_delivery_date;
                                            $pddjquery = "'".$pdd_value."'";
                                            $single = $this->model->gds("tabungan_vlt","COUNT(wos_material) as total","wos_material_description LIKE '% D55L %' AND color_code NOT IN(".rtrim($in_tt,",").") AND plan_delivery_date = '".$pdd_value."' OR wos_material_description LIKE '% D74A %' AND color_code NOT IN(".rtrim($in_tt,",").") AND plan_delivery_date = '".$pdd_value."'","row");
                                            //SINGLE TONE
                                            echo '<tr>
                                                    <td align="center" class="p-1 bg-secondary text-white font-weight-bold" rowspan="2" style="z-index:2;">
                                                        <div style="min-width:60px; font-size:12pt;">'.date("d M",strtotime($pdd_value)).'</div>
                                                    </td>
                                                    <td align="center" class="p-1 bg-single-tone font-weight-bold">Single</td>
                                                    <td align="center" class="p-1 bg-single-tone font-weight-bold">'.$single->total.'</td>';
                                                    foreach ($data_suffix as $suffix => $count) {
                                                        //check suffix PDD di singletone_setting
                                                        $suffix_pdd = $suffix."|".$pdd_value;
                                                        $suffixval = "'".$suffix."'";
                                                        $check_setting = $this->model->gds("singletone_setting","suffix_pdd,qty","suffix_pdd = '$suffix_pdd'","row");
                                                        if(!empty($check_setting)){
                                                            $qty_setting = $check_setting->qty;
                                                            $bg_gray = "bg-single-tone";
                                                        }else{
                                                            $qty_setting = "";
                                                            $bg_gray = "";
                                                        }

                                                        //hitung jumlah data berdasarkan PDD dan suffix
                                                        $count_pdd = $this->model->gds("tabungan_vlt","COUNT(*) as count","katashiki_suffix = '".$suffix."' AND color_code NOT IN(".rtrim($in_tt,",").") AND plan_delivery_date = '".$pdd_value."'","row");
                                                        if(empty($count_pdd->count)){
                                                            $count_pdd = "";
                                                        }else{
                                                            $count_pdd = $count_pdd->count;
                                                        }

                                                        //jika terdapat data tabungan berdasarkan suffix
                                                        $t_suffix = $this->model->gds("tabungan_vlt","COUNT(wos_material) as total","katashiki_suffix = '".$suffix."' AND plan_delivery_date = '".$pdd_value."' AND color_code NOT IN(".rtrim($in_tt,",").")","row");
                                                        if(!empty($t_suffix->total)){
                                                            //input data type number
                                                            echo '
                                                            <td align="center" class="p-0 bg-single-tone font-weight-bold">
                                                                <input type="number" value="'.$qty_setting.'" class="border-0 bg-single-tone input-data suffix_'.$suffix.' '.$bg_gray.'" data-suffix="'.$suffix.'" name="input-data['.$suffix.'|'.$pdd_value.'|singletone]" id="input_'.$suffix.'_'.$pdd_value.'_1" data-plan="'.$count_pdd.'" placeholder="'.$count_pdd.'" onkeyup="validasi('.$count_pdd.',this.value,'.$data_plan_wos[$suffix].','.$suffixval.','.$pddjquery.',1)" style="width:45px; text-align:center; height:100%;">
                                                            </td>';
                                                        }else{
                                                            echo '
                                                            <td align="center" class="p-1 bg-single-tone font-weight-bold"></td>';
                                                        }
                                                    }
                                            echo '</tr>';

                                            //TWO TONE
                                            $two = $this->model->gds("tabungan_vlt","COUNT(wos_material) as total","wos_material_description LIKE '% D55L %' AND color_code IN(".rtrim($in_tt,",").") AND plan_delivery_date = '".$pdd_value."' OR wos_material_description LIKE '% D74A %' AND color_code IN(".rtrim($in_tt,",").") AND plan_delivery_date = '".$pdd_value."'","row");
                                            echo '<tr>
                                            <td align="center" class="p-1 bg-two-tone font-weight-bold">Two</td>
                                            <td align="center" class="p-1 bg-two-tone font-weight-bold">'.$two->total.'</td>';
                                            foreach ($data_suffix as $suffix => $count) {
                                                $suffixval = "'".$suffix."'";
                                                //check suffix PDD di singletone_setting
                                                $suffix_pdd = $suffix."|".$pdd_value;
                                                $check_setting = $this->model->gds("twotone_setting","suffix_pdd,qty","suffix_pdd = '$suffix_pdd'","row");
                                                if(!empty($check_setting)){
                                                    $qty_setting = $check_setting->qty;
                                                    $bg_gray = "bg-two-tone";
                                                }else{
                                                    $qty_setting = "";
                                                    $bg_gray = "";
                                                }

                                                //hitung jumlah data berdasarkan PDD dan suffix
                                                $count_pdd = $this->model->gds("tabungan_vlt","COUNT(*) as count","katashiki_suffix = '".$suffix."' AND color_code IN(".rtrim($in_tt,",").") AND plan_delivery_date = '".$pdd_value."'","row");
                                                if(empty($count_pdd->count)){
                                                    $count_pdd = "";
                                                }else{
                                                    $count_pdd = $count_pdd->count;
                                                }

                                                //jika terdapat data tabungan berdasarkan suffix
                                                $t_suffix = $this->model->gds("tabungan_vlt","COUNT(wos_material) as total","katashiki_suffix = '".$suffix."' AND plan_delivery_date = '".$pdd_value."' AND color_code IN(".rtrim($in_tt,",").")","row");
                                                if(!empty($t_suffix->total)){
                                                    //input data type number
                                                    echo '
                                                    <td align="center" class="p-0 bg-two-tone font-weight-bold">
                                                        <input type="number" value="'.$qty_setting.'" class="border-0 bg-two-tone input-data suffix_'.$suffix.' '.$bg_gray.'" data-suffix="'.$suffix.'" name="input-data['.$suffix.'|'.$pdd_value.'|twotone]" id="input_'.$suffix.'_'.$pdd_value.'_2" data-plan="'.$count_pdd.'" placeholder="'.$count_pdd.'" onkeyup="validasi('.$count_pdd.',this.value,'.$data_plan_wos[$suffix].','.$suffixval.','.$pddjquery.',2)" style="width:45px; text-align:center; height:100%;">
                                                    </td>';
                                                }else{
                                                    echo '
                                                    <td align="center" class="p-1 bg-two-tone font-weight-bold"></td>';
                                                }
                                            }
                                        }
                                    }else{
                                        echo '<td colspan="25" align="center" class="p-1"><i>Data kosong</i></td>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                            <div id="suffix_active"></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
