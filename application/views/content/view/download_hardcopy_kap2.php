<?php
$now = date('d-m-Y');
$pdd = $this->input->post("pdd");
$plan_jig_in = $this->input->post("plan_jig_in");
header ("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment;filename = HARD COPY WOS KAP2 PDD ".date("d-m-Y",strtotime($pdd)).".xls");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body style="font-size:7pt;">
    <?php
        $data_wos = $this->model->union_heijunka_kap2("ORDER BY batch,No DESC");
        $count_wos = count($data_wos);
        $wos100 = ceil($count_wos / 100);
        $limit = 50;
        $table1 = '';
        $table2 = '';
        $nomor = 1;
        $black = 'style="color:#fff;background:#000"';
        $white = 'style="color:#000;background:#fff"';
        $grey = 'style="color:#000;background:#A9A9A9"';
        $red = 'style="color:#fff;background:red"';
        for ($a=0; $a < $wos100; $a++) {
            if($a <= 0){
                $start1 = 0;
                $start2 = 50;
                $end_hit = 100;
            }else{
                $end_hit += 100;
                $start1 = ($end_hit-100);
                $start2 = $start1 + 50;
            }
            $data_wos_hc2 = $this->model->union_heijunka_limit_kap2($start2,$limit,"ORDER BY batch,No DESC");
            $data_wos_hc1 = $this->model->union_heijunka_limit_kap2($start1,$limit,"ORDER BY batch,No DESC");
            if(!empty($data_wos_hc1)){
                $table1 .= '
                    <table width="100%" border="1">';
                foreach ($data_wos_hc1 as $data_wos_hc1) {
                    $suffix1 = $data_wos_hc1->Katashiki_Sfx;
                    $get_std1 = $this->model->gds("hc_standard","*","suffix = '$suffix1'","row");
                    //PID Color
                    if($data_wos_hc1->Model_Name == "TD-Link"){
                        $pid1 = $grey;
                    }else{
						if(!empty($get_std1)){
							switch ($get_std1->pid) {
								case 'WHITE':
									$pid1 = $white;
									break;
								case 'BLACK':
									$pid1 = $black;
									break;
								case 'GREY':
									$pid1 = $grey;
									break;
								default:
									$pid1 = $red;
									break;
							}
						}else{
							$pid1 = $red;
						}
                    }
                    //VARIANT Color
                    if($data_wos_hc1->Model_Name == "TD-Link"){
                        $variant1 = $grey;
                    }else{
						if(!empty($get_std1)){
							switch ($get_std1->variant) {
								case 'WHITE':
									$variant1 = $white;
									break;
								case 'BLACK':
									$variant1 = $black;
									break;
								case 'GREY':
									$variant1 = $grey;
									break;
								default:
									$variant1 = $red;
									break;
							}
						}else{
							$variant1 = $red;
						}
                    }
                    //VIN Color
                    if($data_wos_hc1->Model_Name == "TD-Link"){
                        $vin1 = $grey;
                    }else{
						if(!empty($get_std1)){
							switch ($get_std1->vin) {
								case 'WHITE':
									$vin1 = $white;
									break;
								case 'BLACK':
									$vin1 = $black;
									break;
								case 'GREY':
									$vin1 = $grey;
									break;
								default:
									$vin1 = $red;
									break;
							}
						}else{
							$vin1 = $red;
						}
                    }
                    //TYPE Color
                    if($data_wos_hc1->Model_Name == "TD-Link"){
                        $type1 = $black;
                    }else{
						if(!empty($get_std1)){
							switch ($get_std1->type) {
								case 'WHITE':
									$type1 = $white;
									break;
								case 'BLACK':
									$type1 = $black;
									break;
								case 'GREY':
									$type1 = $grey;
									break;
								default:
									$type1 = $red;
									break;
							}
						}else{
							$type1 = $red;
						}
                    }
                    //PDD Color
                    if($data_wos_hc1->Model_Name == "TD-Link"){
                        $pdd1 = $grey;
                    }else{
						if(!empty($get_std1)){
							switch ($get_std1->pdd) {
								case 'WHITE':
									$pdd1 = $white;
									break;
								case 'BLACK':
									$pdd1 = $black;
									break;
								case 'GREY':
									$pdd1 = $grey;
									break;
								default:
									$pdd1 = $red;
									break;
							}
						}else{
							$pdd1 = $red;
						}
                    }
                    //Zero Defect Color
                    if($data_wos_hc1->Model_Name == "TD-Link"){
                        $zero_defect1 = $grey;
                    }else{
						if(!empty($get_std1)){
							switch ($get_std1->zero_defect) {
								case 'WHITE':
									$zero_defect1 = $white;
									break;
								case 'BLACK':
									$zero_defect1 = $black;
									break;
								case 'GREY':
									$zero_defect1 = $grey;
									break;
								default:
									$zero_defect1 = $red;
									break;
							}
						}else{
							$zero_defect1 = $red;
						}
                    }
                    //Val Zero Defect
                    if(!empty($get_std1->val_zero_defect)){
                        $val_zero_defect = $get_std1->val_zero_defect;
                    }else{
                        $val_zero_defect = "";
                    }
                    //PID
                    $pid_val_1 = "5X".date("md",strtotime($pdd)).sprintf("%04d",$nomor);

                    $exp = explode(" ",$data_wos_hc1->WOS_Material_Description);
                    $mat_des = '';
                    if(!empty($exp[0])){
                        $mat_des .= $exp[0].' ';
                    }
                    if(!empty($exp[1])){
                        $mat_des .= '('.$exp[1].') ';
                    }
                    if(!empty($exp[2])){
                        $mat_des .= $exp[2].' ';
                    }
                    if(!empty($exp[3])){
                        $mat_des .= $exp[3].' ';
                    }
                    if(!empty($exp[4])){
                        $mat_des .= $exp[4].' ';
                    }
                    if(!empty($exp[5])){
                        $mat_des .= $exp[5].' ';
                    }
                    if(!empty($exp[6])){
                        $mat_des .= $exp[6].' ';
                    }
                    if(!empty($exp[7])){
                        $mat_des .= $exp[7].' ';
                    }
                    if(!empty($exp[8])){
                        $mat_des .= $exp[8].' ';
                    }
                    if(!empty($exp[9])){
                        $mat_des .= $exp[9].' ';
                    }
                    if(!empty($exp[10])){
                        $mat_des .= $exp[10].' ';
                    }
                    $table1 .= '
                        <tr align="center">
                            <td '.$pid1.'>'.$nomor++.'</td>
                            <td '.$pid1.'>'.$data_wos_hc1->Lot_Code.'</td>
                            <td '.$pid1.'>'.$pid_val_1.'</td>
                            <td '.$variant1.' align="left"><div style="width:230px;">'.$mat_des.'</div></td>
                            <td '.$vin1.'>'.$data_wos_hc1->SAPNIK.'</td>
                            <td '.$type1.'>'.$data_wos_hc1->Model_Name.'</td>
                            <td '.$pdd1.'>'.strtoupper(date("d M Y",strtotime($data_wos_hc1->Plan_Delivery_Date))).'</td>
                            <td '.$zero_defect1.'>'.$val_zero_defect.'</td>
                        </tr>';
                }
                $table1 .= '</table>';
            }
            
            if(!empty($data_wos_hc2)){
                $table2 .= '
                    <table width="100%" border="1">';
                foreach ($data_wos_hc2 as $data_wos_hc2) {
                    $suffix2 = $data_wos_hc2->Katashiki_Sfx;
                    $get_std2 = $this->model->gds("hc_standard","*","suffix = '$suffix2'","row");
                    //PID Color
                    if($data_wos_hc2->Model_Name == "TD-Link"){
                        $pid2 = $grey;
                    }else{
						if(!empty($get_std2)){
							switch ($get_std2->pid) {
								case 'WHITE':
									$pid2 = $white;
									break;
								case 'BLACK':
									$pid2 = $black;
									break;
								case 'GREY':
									$pid2 = $grey;
									break;
								default:
									$pid2 = $red;
									break;
							}
						}else{
							$pid2 = $red;
						}
                    }
                    //VARIANT Color
                    if($data_wos_hc2->Model_Name == "TD-Link"){
                        $variant2 = $grey;
                    }else{
						if(!empty($get_std2)){
							switch ($get_std2->variant) {
								case 'WHITE':
									$variant2 = $white;
									break;
								case 'BLACK':
									$variant2 = $black;
									break;
								case 'GREY':
									$variant2 = $grey;
									break;
								default:
									$variant2 = $red;
									break;
							}
						}else{
							$variant2 = $red;
						}
                    }
                    //VIN Color
                    if($data_wos_hc2->Model_Name == "TD-Link"){
                        $vin2 = $grey;
                    }else{
						if(!empty($get_std2)){
							switch ($get_std2->vin) {
								case 'WHITE':
									$vin2 = $white;
									break;
								case 'BLACK':
									$vin2 = $black;
									break;
								case 'GREY':
									$vin2 = $grey;
									break;
								default:
									$vin2 = $red;
									break;
							}
						}else{
							$vin2 = $red;
						}
                    }
                    //TYPE Color
                    if($data_wos_hc2->Model_Name == "TD-Link"){
                        $type2 = $black;
                    }else{
						if(!empty($get_std2)){
							switch ($get_std2->type) {
								case 'WHITE':
									$type2 = $white;
									break;
								case 'BLACK':
									$type2 = $black;
									break;
								case 'GREY':
									$type2 = $grey;
									break;
								default:
									$type2 = $red;
									break;
							}
						}else{
							$type2 = $red;
						}
                    }
                    //PDD Color
                    if($data_wos_hc2->Model_Name == "TD-Link"){
                        $pdd2 = $grey;
                    }else{
						if(!empty($get_std2)){
							switch ($get_std2->pdd) {
								case 'WHITE':
									$pdd2 = $white;
									break;
								case 'BLACK':
									$pdd2 = $black;
									break;
								case 'GREY':
									$pdd2 = $grey;
									break;
								default:
									$pdd2 = $red;
									break;
							}
						}else{
							$pdd2 = $red;
						}
                    }
                    //Zero Defect Color
                    if($data_wos_hc2->Model_Name == "TD-Link"){
                        $zero_defect2 = $grey;
                    }else{
						if(!empty($get_std2)){
							switch ($get_std2->zero_defect) {
								case 'WHITE':
									$zero_defect2 = $white;
									break;
								case 'BLACK':
									$zero_defect2 = $black;
									break;
								case 'GREY':
									$zero_defect2 = $grey;
									break;
								default:
									$zero_defect2 = $red;
									break;
							}
						}else{
							$zero_defect2 = $red;
						}
                    }
                    //Val Zero Defect
                    if(!empty($get_std2->val_zero_defect)){
                        $val_zero_defect_2 = $get_std2->val_zero_defect;
                    }else{
                        $val_zero_defect_2 = "";
                    }
                    //PID
                    $pid_val_2 = "5X".date("md",strtotime($pdd)).sprintf("%04d",$nomor);

                    $exp = explode(" ",$data_wos_hc2->WOS_Material_Description);
                    $mat_des = '';
                    if(!empty($exp[0])){
                        $mat_des .= $exp[0].' ';
                    }
                    if(!empty($exp[1])){
                        $mat_des .= '('.$exp[1].') ';
                    }
                    if(!empty($exp[2])){
                        $mat_des .= $exp[2].' ';
                    }
                    if(!empty($exp[3])){
                        $mat_des .= $exp[3].' ';
                    }
                    if(!empty($exp[4])){
                        $mat_des .= $exp[4].' ';
                    }
                    if(!empty($exp[5])){
                        $mat_des .= $exp[5].' ';
                    }
                    if(!empty($exp[6])){
                        $mat_des .= $exp[6].' ';
                    }
                    if(!empty($exp[7])){
                        $mat_des .= $exp[7].' ';
                    }
                    if(!empty($exp[8])){
                        $mat_des .= $exp[8].' ';
                    }
                    if(!empty($exp[9])){
                        $mat_des .= $exp[9].' ';
                    }
                    if(!empty($exp[10])){
                        $mat_des .= $exp[10].' ';
                    }
                    $table2 .= '
                        <tr align="center">
                            <td '.$pid2.'>'.$nomor++.'</td>
                            <td '.$pid2.'>'.$data_wos_hc2->Lot_Code.'</td>
                            <td '.$pid2.'>'.$pid_val_2.'</td>
                            <td '.$variant2.' align="left"><div style="width:230px;">'.$mat_des.'</div></td>
                            <td '.$vin2.'>'.$data_wos_hc2->SAPNIK.'</td>
                            <td '.$type2.'>'.$data_wos_hc2->Model_Name.'</td>
                            <td '.$pdd2.'>'.strtoupper(date("d M Y",strtotime($data_wos_hc2->Plan_Delivery_Date))).'</td>
                            <td '.$zero_defect2.'>'.$val_zero_defect_2.'</td>
                        </tr>';
                }
                $table2 .= '</table>';
            }
        }
    ?>
    <table width="100%">
        <tr>
            <td valign="top"><?= $table1 ?></td>
            <td style="5px;"></td>
            <td valign="top"><?= $table2 ?></td>
        </tr>
    </table>
</body>
</html>
<script>
    function voice(url) {
        var source = "<?=str_replace("index.php","",base_url("assets/voice/"))?>"+url;
        var audio = new Audio();
        audio.addEventListener("load",function() {
            audio.play();
        }, true);
        audio.src = source;
        audio.autoplay = true;
    }

    voice("hardcopy_download.mp3");
</script>
