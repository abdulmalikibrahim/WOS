<!DOCTYPE>
<html>

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="<?= base_url("assets/images/favicon.png") ?>" type="image/x-icon">
    <link rel="stylesheet" href="<?= base_url("assets/plugins/font-awesome-6/css/all.min.css") ?>"/>
    <!-- Required Fremwork -->
    <link rel="stylesheet" type="text/css" href="<?= base_url("assets/css/bootstrap/css/bootstrap.min.css") ?>">
    <link rel="stylesheet" type="text/css" href="<?= base_url("assets/plugins/sweetalert2/dist/sweetalert2.min.css") ?>">
    <link rel="stylesheet" type="text/css" href="<?= base_url("assets/css/mycss.css") ?>">
    <title>HEIJUNKA WOS</title>
    <style type="text/css">
        html,
        body {
            height: 100%;
            background-color: #A9A9A9;
        }

        .container {
            width: 100%;
            height: 100%;
            display: table;
        }

        .container-popup {
            position: relative;
            margin: 15% auto;
            font-family: calibri;
            padding: 4px 3px;
            border-radius: 5px;
            background-color: #008B8B;
            color: #FFF;
            width: 40%;
            overflow: auto;
            display: none;
            border: #006400 solid;
        }

        .close {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
        }

        .choose {
            border:8px solid blue;
        }
    </style>
    <?php
    $i = 1;
    ini_set('display_errors', 0);
    if ($this->input->get('Increment') == 'Yes') {
        $row_master = $this->model->gds_heijunka("master", "*", "No != '' ORDER BY Heijunka_Model ASC, Heijunka_Suffix ASC, Heijunka_Color,heijunka_tone ASC", "result");
        foreach ($row_master as $row_master) {
            $data = [
                "No" => $i++,
            ];
            $update_no = $this->model->update_heijunka("master", "SAPNIK = '" . $row_master->SAPNIK . "'", $data);
        }
        $row_compo = $this->model->gds_heijunka("master", "*", "No != '' ORDER BY Heijunka_Model ASC, Heijunka_Suffix ASC, Heijunka_Color ASC", "result");
        foreach ($row_compo as $row_compo) {
            $Before = $row_compo->No - 1;
            $After = $row_compo->No + 1;
            $B = $this->model->gds_heijunka("master", "*", "No = '$Before'", "row");
            $A = $this->model->gds_heijunka("master", "*", "No = '$After'", "row");
            $compo_both = $row_compo->Bot_Color . "," . $A->Bot_Color;
            $compo_transmisi = $B->Transmisi . "," . $row_compo->Transmisi . "," . $A->Transmisi;
            $dupdate_compo = [
                "Compo_Transmisi" => $compo_transmisi,
                "Compo_Bot_Color" => $compo_both,
            ];
            $update_compo = $this->model->update_heijunka("master", "SAPNIK = '" . $row_compo->SAPNIK . "'", $dupdate_compo);
            if ($row_compo->Bot_Color != $B->Bot_Color) {
                $dupdate_no_bot = [
                    "No_Bot_Color" => "1",
                ];
                $update_no_bot = $this->model->update_heijunka("master", "SAPNIK = '" . $row_compo->SAPNIK . "'", $dupdate_no_bot);
            } else {
                $No_Bot_Color = $B->No_Bot_Color + 1;
                $dupdate_no_bot = [
                    "No_Bot_Color" => $No_Bot_Color,
                ];
                if ($B->No_Bot_Color == '1' and $A->No_Bot_Color == '3') {
                    $dupdate_no_bot = [
                        "No_Bot_Color" => $No_Bot_Color,
                        "Status_Bot_Color" => 'X',
                    ];
                }
                if ($B->No_Bot_Color == '3') {
                    $dupdate_no_bot = [
                        "No_Bot_Color" => "1",
                    ];
                }
                $update_no_bot = $this->model->update_heijunka("master", "SAPNIK = '" . $row_compo->SAPNIK . "'", $dupdate_no_bot);
            }
        }
        if ($update_no_bot) {
    ?>
            <script type="text/javascript">
                location = 'index.php';
            </script>
            <?php
        }
    }
    if (!empty($this->input->get('CEK_BOTH'))) {
        $dupdate_both = ["Status_Bot_Color" => ''];
        $clear = $this->model->update_heijunka("master", "Status_Bot_Color = 'X'", $dupdate_both);
        $row_mark_status = $this->model->gds_heijunka("master", "SAPNIK", "No_Bot_Color = '3'", "result");
        if (!empty($row_mark_status)) {
            foreach ($row_mark_status as $row_mark_status) {
                $dupdate = ['Status_Bot_Color' => 'X'];
                $update = $this->model->update_heijunka("master", "SAPNIK = '" . $row_mark_status->SAPNIK . "'", $dupdate);
            }
            if (!$update) {
            ?>
                <audio src="../Music/please-wait.mp3" autoplay /></audio>
                <script type="text/javascript">
                    location = 'index.php';
                </script>
        <?php
            }
        }
    }
    if (!empty($this->input->get('REFRESH'))) {
        ?>
        <script type="text/javascript">
            location = 'index.php';
        </script>
    <?php
    }
    ?>
</head>
<?php
    include '../database.php';
    $history_color = $this->model->gds_heijunka("history", "*", "Heijunka = 'Color'", "row");
    $history_sfx = $this->model->gds_heijunka("history", "*", "Heijunka = 'Suffix'", "row");
    $history_model = $this->model->gds_heijunka("history", "*", "Heijunka = 'Model'", "row");
    $history_transmisi = $this->model->gds_heijunka("history", "*", "Heijunka = 'Transmisi'", "row");

    if(!empty($history_color->Status)){
        if (strtoupper($history_color->Status) == "SUKSES") {
            $status_color = '
                <div class="card-footer bg-success font-weight-bold text-white p-0" align="center">' . strtoupper($history_color->Status) . '</div>';
        } else {
            $status_color = '
                <div class="card-footer bg-danger font-weight-bold text-white p-0" align="center">' . strtoupper($history_color->Status) . '</div>';
        }
    }else{
        $status_color = '
            <div class="card-footer bg-danger font-weight-bold text-white p-0" align="center">-</div>';
    }
    if (strtoupper($history_sfx->Status) == "SUKSES") {
        $status_suffix = '
            <div class="card-footer bg-success font-weight-bold text-white p-0" align="center">' . strtoupper($history_sfx->Status) . '</div>';
    } else {
        $status_suffix = '
            <div class="card-footer bg-danger font-weight-bold text-white p-0" align="center">' . strtoupper($history_sfx->Status) . '</div>';
    }
    if (strtoupper($history_model->Status) == "SUKSES") {
        $status_model = '
            <div class="card-footer bg-success font-weight-bold text-white p-0" align="center">' . strtoupper($history_model->Status) . '</div>';
    } else {
        $status_model = '
            <div class="card-footer bg-danger font-weight-bold text-white p-0" align="center">' . strtoupper($history_model->Status) . '</div>';
    }
    if (strtoupper($history_transmisi->Status) == "SUKSES") {
        $status_transmisi = '
            <div class="card-footer bg-success font-weight-bold text-white p-0" align="center">' . strtoupper($history_transmisi->Status) . '</div>';
    } else {
        $status_transmisi = '
            <div class="card-footer bg-danger font-weight-bold text-white p-0" align="center">' . strtoupper($history_transmisi->Status) . '</div>';
    }
?>
<body>
    <div class="row">
        <div class="col-12">
            <div class="row d-flex bg-info">
                <div class="col-12" align="center">
                    <h1 class="m-0 text-white mb-2 mt-2">HEIJUNKA WOS</h1>
                </div>
                <div class="col-12" align="right" style="position:absolute; top:10px; left:-10px;">
                    <a class="btn btn-success text-white" href="javascript:void(0)" onclick="formdate_show()"><i class="fas fa-file-excel pr-2"></i>Excel</a>
                    <a href="<?=base_url()?>" class="btn btn-danger">Main Menu</a>
                </div>
            </div>
            <div class="card">
                <div class="card-header pl-1 pr-1">
                    <div class="row">
                        <div class="col pr-1">
                            <div class="card">
                                <div class="card-header p-2 text-center">
                                    <h6 class="m-0">COLOR</h6>
                                </div>
                                <div class="card-body p-2" align="center">
                                    <form method="GET" action="" class="m-0">
                                        <a href="javascript:void(0)" class="btn btn-info" style="font-size:8pt;" onclick="heijunka('color','suffix','sub','Refresh')">PROCESS</a>
                                    </form>
                                </div>
                                <?= $status_color ?>
                            </div>
                        </div>
                        <div class="col pr-1 pl-1">
                            <div class="card">
                                <div class="card-header p-2 text-center">
                                    <h6 class="m-0">SUFFIX</h6>
                                </div>
                                <div class="card-body p-2" align="center">
                                    <form method="GET" action="" class="m-0">
                                        <a href="javascript:void(0)" class="btn btn-info" style="font-size:8pt;" onclick="heijunka('suffix','sub','model','Refresh')">PROCESS</a>
                                    </form>
                                </div>
                            </div>
                            <?= $status_suffix ?>
                        </div>
                        <div class="col pr-1 pl-1">
                            <div class="card">
                                <div class="card-header p-2 text-center">
                                    <h6 class="m-0">MODEL</h6>
                                </div>
                                <div class="card-body p-2" align="center">
                                    <a href="javascript:void(0)" class="btn btn-info" style="font-size:8pt;" onclick="heijunka('sub','model','twotone','Refresh')">SUB</a>
                                    <a href="javascript:void(0)" class="btn btn-info" style="font-size:8pt;" onclick="heijunka('model','twotone','both','Refresh')">MODEL</a>
                                </div>
                            </div>
                            <?= $status_model ?>
                        </div>
                        <!-- <div class="col pr-1 pl-1">
                            <div class="card">
                                <div class="card-header p-2 text-center">
                                    <h6 class="m-0">TRANSMISI</h6>
                                </div>
                                <div class="card-body p-2" align="center">
                                    <form method="GET" action="" class="m-0">
                                        <a href="javascript:void(0)" class="btn btn-info" style="font-size:8pt;" onclick="heijunka('transmisi','both','')">PROCESS BOTH</a>
                                    </form>
                                </div>
                                <?= $status_transmisi ?>
                            </div>
                        </div> -->
                        <div class="col pr-1 pl-1">
                            <div class="card">
                                <div class="card-header p-2 text-center">
                                    <h6 class="m-0">HEIJUNKA TONE</h6>
                                </div>
                                <div class="card-body p-2" align="center">
                                    <form method="GET" action="" class="m-0">
                                        <a href="javascript:void(0)" class="btn btn-info" style="font-size:8pt;" onclick="heijunka('twotone','both','Selesai','Refresh')">PROCESS</a>
                                    </form>
                                </div>
                            </div>
                            <div class="card-footer font-weight-bold bg-success text-light p-0" align="center">SUKSES</div>
                        </div>
                        <div class="col pr-1 pl-1">
                            <div class="card">
                                <div class="card-header p-2 text-center">
                                    <h6 class="m-0">BOTH TOSSO</h6>
                                </div>
                                <div class="card-body p-2" align="center">
                                    <form method="GET" action="" class="m-0">
                                        <a href="javascript:void(0)" class="btn btn-info" style="font-size:8pt;" onclick="heijunka('both','Selesai','','Refresh')">PROCESS BOTH</a>
                                    </form>
                                </div>
                                <div class="card-footer font-weight-bold text-white p-0" id="card-footer-both" align="center"></div>
                            </div>
                        </div>
                        <div class="col pr-1 pl-1">
                            <div class="card">
                                <div class="card-header p-2 text-center">
                                    <h6 class="m-0">UPLOAD TD-LINK</h6>
                                </div>
                                <div class="card-body p-1">
                                    <form action="<?=base_url("import_td_link")?>" class="mb-0" method="post" id="import_td_link" enctype="multipart/form-data" style="height:36px;">
                                        <div class="input-group">
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" id="upload-excel" name="upload-file" accept=".xls">
                                                <label class="custom-file-label" for="customFile" id="customFile">Pilih File</label>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="card-footer font-weight-bold text-white p-0" id="card-footer-td-link" align="center"></div>
                            </div>
                        </div>
                        <div class="col pl-1">
                            <div class="card">
                                <div class="card-header p-2 text-center">
                                    <h6 class="m-0">RE-UPLOAD WOS</h6>
                                </div>
                                <div class="card-body p-1">
                                    <form action="<?=base_url("import_wos")?>" class="mb-0" method="post" id="import_wos" enctype="multipart/form-data" style="height:36px;">
                                        <div class="input-group">
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" id="upload-wos" name="upload-file" accept=".xls">
                                                <label class="custom-file-label" for="customFile" id="customFile">Pilih File</label>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="card-footer font-weight-bold bg-success text-success p-0" align="center">-</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-1" style="">
                    <div class="row mt-0">
                        <div class="col-12">
                            <div class="row">
                                <?php
                                $get_unit = $this->model->gds_heijunka("master","COUNT(SAPNIK) as total,Model","No != '' GROUP BY Model","result");
                                $total_unit = $this->model->gds_heijunka("master","COUNT(SAPNIK) as total","No != ''","row");
                                $total_td_link = $this->model->gds_heijunka("master_td_link","COUNT(SAPNIK) as total","No != ''","row");
                                if(!empty($get_unit)){
                                    foreach ($get_unit as $get_unit) {
                                        $percent_unit = "<font class='text-danger'>".number_format(($get_unit->total/$total_unit->total)*100,2,",",".")."%</font>";
                                        echo "<div class='col font-weight-bold text-center'>".$get_unit->Model." : ".$get_unit->total." (".$percent_unit.")</div>";
                                    }
                                    if(!empty($total_td_link->total)){
                                        echo "<div class='col font-weight-bold text-center'>TD-Link : ".$total_td_link->total."</div>";
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        <div class="col-12 pr-3">
                            <h4 id="choose_nik1" class="bg-success p-1 mb-0 text-center" style="position: fixed; bottom: 0; right:0; border-radius:5px; color:#fff; width:270px;"></h4>
                            <h4 id="choose_nik2" class="bg-danger p-1 mb-0 text-center" style="position: fixed; bottom: 0; right:0; border-radius:5px; color:#fff; width:270px;"></h4>
                            <h4 id="number_row" hidden></h4>
                            <input type="text" id="tipe_switch" value="1" hidden>
                            <table border="1" class="table_WOS w-100" style="font-size: 6pt;">
                                <tr style="font-weight: bold;" align="center">
                                    <td>No.</td>
                                    <td>WOS Material</td>
                                    <td>WOS Material Description</td>
                                    <td>SAPNIK</td>
                                    <td>SAP Material</td>
                                    <td>Engine Model</td>
                                    <td>Plant</td>
                                    <td>Chassis Number</td>
                                    <td>Lot Code</td>
                                    <td>Katashiki</td>
                                    <td>Katashiki Sfx</td>
                                    <td>ADM Production Id</td>
                                    <td>TAM Production Id</td>
                                    <td>Plan Delivery Date</td>
                                    <td>Plan Jig In Date</td>
                                    <td>WOS Release Date</td>
                                    <td>Location</td>
                                    <td>Color Code</td>
                                    <td>Model</td>
                                    <td>ED</td>
                                    <td>Order</td>
                                    <td>Dest</td>
                                    <td>Bot</td>
                                    <td>BD.</td>
                                    <td>BS</td>
                                    <td>Tone</td>
                                    <td>NG<BR>Model</td>
                                </tr>
                                <?php
                                $No = 1;
                                $i = 1;
                                $key = 0;
                                $ng_both = 0;
								$ng_model = 0;
								$no_bot_det = 0;
                                if($this->input->get("group_model") == "yes"){
                                    $data_wos = $this->model->union_heijunka("ORDER BY FIELD(Model,'D55L','D74A','D52B'), No DESC");
                                }else{
                                    $data_wos = $this->model->union_heijunka("ORDER BY batch,No DESC");
                                }
                                $data_wos_arr = $this->model->gds_heijunka("master","*","No != '' ORDER BY batch,No DESC","result");
                                $count_td_link = $this->model->gds_heijunka("master_td_link","COUNT(No) as count","No !=","row");
                                foreach ($data_wos as $data_wos) {
                                    $explode = explode(',', $data_wos->Color);
                                    $Color_Cell = $explode[0];
                                    $Color_Font = $explode[1];
                                    $tone = $data_wos->tone;
                                    if ($tone == "SINGLE TONE") {
                                        $vtone = "ST";
                                        $bg = "#000";
                                        $color = "#fff";
                                    } else {
                                        $vtone = "TT";
                                        $bg = "#fff";
                                        $color = "#000";
                                    }
                                    
                                    if($key <= 0){
                                        $no = 1;
                                        $bc_bef = $data_wos_arr[$key]->Bot_Color;
                                    }else{
                                        $bc_bef = $data_wos_arr[$key-1]->Bot_Color;
                                        if($data_wos_arr[$key-1]->Bot_Color == $data_wos->Bot_Color){
                                            $no = $no;
                                        }else{
                                            $no = 1;
                                        }
                                    }
                                    if($data_wos->Bot_Color == "A"){
                                        if($no > 2){
                                            $status = "NG";
                                            $ng_both++;
                                        }else{
                                            $status = "OK";
                                        }
                                        $no++;
                                    }else if($data_wos->Bot_Color == "B"){
                                        if($no > 2){
                                            $status = "NG";
                                            $ng_both++;
                                        }else{
                                            $status = "OK";
                                        }
                                        $no++;
                                    }else{
                                        $status = "OK";
                                    }
                                    if(!empty($data_wos->WOS_Release_Date)){
                                        $wos_release_date = date("dmY",strtotime($data_wos->WOS_Release_Date));
                                    }else{
                                        $wos_release_date = "";
                                    }

                                    $sapnik_val = "'".$data_wos->SAPNIK."'";
                                    $no_val = "'".($No+1)."'";
                                    $urutan_val = "'".$data_wos->No."'";
    
                                    if($data_wos->heijunka_tone != "TD-Link"){
                                        if ($data_wos->Bot_Color == 'A') {
                                            $bg_bot = "yellow";
                                            $bot_td = '<td style="color:#000;" bgcolor="'.$bg_bot.'">'.$data_wos->Bot_Color.'</td>';
                                        }
                                        if ($data_wos->Bot_Color == 'B') {
                                            $bg_bot = "#A9A9A9";
                                            $bot_td = '<td style="color:#000;" bgcolor="'.$bg_bot.'">'.$data_wos->Bot_Color.'</td>';
                                        }
                                        if ($data_wos->Bot_Color == 'AB') {
                                            $bg_bot = "red";
                                            $bot_td = '<td style="color:#000;" bgcolor="'.$bg_bot.'">'.$data_wos->Bot_Color.'</td>';
                                        }
    
                                        if ($status == 'OK') {
                                            $bs_td = '<td bgcolor="green" style="color:#fff;">OK</td>';
                                            $tone_td = '<td bgcolor="'.$bg.'" style="color:'.$color.';">'.$vtone.'</td>';
                                        }else if($status == "NG"){
                                            $bs_td = '<td bgcolor="red" style="color:#fff;">NG</td>';
                                            $tone_td = '<td bgcolor="'.$bg.'" style="color:'.$color.';">'.$vtone.'</td>';
                                        }

                                        $choosen = 'style="cursor:pointer; color:'.$Color_Font.'" onclick="choose_nik('.$no_val.','.$sapnik_val.')"';
                                    }else{
                                        $bg_bot = "white";
                                        $bot_td = '<td colspan="3" bgcolor="'.$bg_bot.'" id="bot_td_'.($No+1).'" align="center">TD-Link</td>';
                                        $bs_td = '';
                                        $tone_td = '';
                                        $choosen = '';
                                    }
                                    if($data_wos->Model == "D55L"){
                                        $bg_model = "bg-danger text-dark";
                                    }else if($data_wos->Model == "D74A"){
                                        $bg_model = "bg-danger text-light";
                                    }else if($data_wos->Model == "D52B"){
                                        $bg_model = "bg-info";
                                    }else{
                                        $bg_model = "bg-white";
                                    }

                                    if($data_wos->Dest != 'DOM'){
                                        $bg_color = "bg-dark text-light";
                                    }else{
                                        $bg_color = $bg_model;
                                    }
    
                                    //BOT DETAIL//
                                    if($data_wos->Bot_Color == "A"){
                                        $bot_det = "A";
                                        $bot_bef[$No] = "A";
                                        $bg_det_bot = "bg-danger text-white";
                                    }else if($data_wos->Bot_Color == "B"){
                                        $bot_det = "B";
                                        $bot_bef[$No] = "B";
                                        $bg_det_bot = "bg-success text-white";
                                    }else{
                                        if(empty($bot_bef)){
                                            $bot_det = "A";
                                        }else{
                                            $bot_bef_ab = $bot_bef[$No-1];
                                            if($bot_bef_ab == "A"){
                                                $bot_det = "B";
                                                $bot_bef[$No] = "B";
                                                $bg_det_bot = "bg-success text-white";
                                            }else{
                                                $bot_det = "A";
                                                $bot_bef[$No] = "A";
                                                $bg_det_bot = "bg-danger text-white";
                                            }
                                        }
                                    }

                                    if($bot_bef[$No-1] == $bot_det){
                                        $no_bot_det += 1;
                                    }else{
                                        $no_bot_det = 1;
                                    }
                                    if($no_bot_det > 2){
                                        $data_ng_bot_det[$data_wos->Model][$data_wos->tone][] = [
                                            "No" => $data_wos->No,
                                            "Bot_det" => $bot_det,
                                            "SAPNIK" => $data_wos->SAPNIK,
                                        ];
                                        $bg_ng_bot_det = "bg-danger text-light bot-ng";
                                        $judge_ng_bot = "NG";
                                        $ng_val = "NG";
                                    }else{
                                        $bg_ng_bot_det = "bg-light";
                                        $judge_ng_bot = "OK";
                                        $ng_val = "";
                                    }

                                    $data_bot_all[$data_wos->Model][$data_wos->tone][$judge_ng_bot][$data_wos->Bot_Color.$bot_det][] = [
                                        "No" => $data_wos->No,
                                        "Bot" => $data_wos->Bot_Color,
                                        "SAPNIK" => $data_wos->SAPNIK,
                                    ];
                                    //END BOT DETAIL//
                                    

									if($data_wos->heijunka_tone != 'TD-Link'){
										if($data_wos->Model == "D55L" || $data_wos->Model == "D74A"){
											$ng_model += 1;
										}else{
											$ng_model = 0;
										}

										if($ng_model > 4){
											$bg_ng_model = "bg-warning text-danger";
										}else{
											$bg_ng_model = "";
										}
										?>
										<tr id="tr_<?= ($No+1); ?>" data-id="" class="<?= $bg_model; ?>" align="center" <?= $choosen; ?>>
											<td style="height:25px;" id="td_nomor_<?= ($No+1) ?>" data-id=""><?= $No; ?></td>
											<td><?= $data_wos->WOS_Material; ?></td>
											<td><?= $data_wos->WOS_Material_Description; ?></td>
											<td><?= $data_wos->SAPNIK; ?></td>
											<td><?= $data_wos->SAP_Material; ?></td>
											<td><?= $data_wos->Engine_Model; ?></td>
											<td><?= $data_wos->Plant; ?></td>
											<td>&nbsp;<?= $data_wos->SAPNIK; ?>&nbsp;</td>
											<td><?= $data_wos->Lot_Code; ?></td>
											<td><?= $data_wos->Katashiki; ?></td>
											<td><?= $data_wos->Katashiki_Sfx; ?></td>
											<td><?= $data_wos->ADM_Production_Id; ?></td>
											<td><?= $data_wos->TAM_Production_Id; ?></td>
											<td>&nbsp;<?= strtoupper(date("d M Y",strtotime($data_wos->Plan_Delivery_Date))); ?></td>
											<td><?= date("Ymd",strtotime($data_wos->Plan_Jig_In_Date)); ?></td>
											<td><?= $wos_release_date; ?></td>
											<td><?= $data_wos->Location; ?></td>
											<td><?= $data_wos->Color_Code; ?></td>
											<td class="<?= $bg_model; ?>"><?= $data_wos->Model; ?></td>
											<td><?= $data_wos->ED; ?></td>
											<td><?= $data_wos->Order; ?></td>
											<td class="<?= $bg_color; ?>"><?= $data_wos->Dest; ?></td>
											<?=$bot_td;?>
											<td class="<?=$bg_det_bot?>"><?= $bot_det; ?></td>
                                            <td class="<?=$bg_ng_bot_det;?>"><?= $ng_val; ?></td>
                                            <?=$tone_td?>
											<td class="<?=$bg_ng_model?>"><?= $ng_model; ?></td>
										</tr>
										<?php
									}
                                    $No++;
                                    $i++;
                                    if($data_wos->heijunka_tone != "TD-Link"){
                                        $key++;
                                    }
                                }
                                ?>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal -->
    <div class="modal fade" id="formdate" tabindex="-1" role="dialog" aria-labelledby="formdate-title" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="formdate-title">FORM PLAN JIG IN & PDD</h5>
                </div>
                <div class="modal-body">
                    <form action="<?=base_url("dup")?>" method="post" id="form-download">
                        <div class="row">
                            <div class="col-12">
                                <p class="mb-1">Tipe Excel</p>
                                <select id="tipe" class="form-control">
                                    <option>PIS</option>
                                    <option>Hardcopy</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <p class="mb-1">Plan Jig In</p>
                                <input type="date" class="form-control" value="<?=date("Y-m-d")?>" name="plan_jig_in">
                            </div>
                            <div class="col-12">
                                <p class="mb-1">PDD</p>
                                <input type="date" class="form-control" value="<?=date("Y-m-d")?>" name="pdd">
                            </div>
                            <div class="col-lg-12 mt-2" align="right">
                                <button class="btn btn-info"><i class="fas fa-save pr-2"></i>Simpan</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php $this->load->view("js/heijunka_wos_trial"); ?>
