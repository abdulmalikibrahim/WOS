<?php
defined('BASEPATH') or exit('No direct script access allowed');
date_default_timezone_set('Asia/Jakarta');
include_once(dirname(__FILE__) . "/Construct.php");
// header("Content-Type: text/plain");

class C_Heijunka_WOS extends Construct
{
    public function update_number()
    {
        $check_batch = $this->model->gds_heijunka("master","batch","SAPNIK != '' GROUP BY batch ORDER BY batch ASC","result");
        foreach ($check_batch as $check_batch) {
            $batch = $check_batch->batch;
            $get_master = $this->model->gds_heijunka("master", "No,SAPNIK,tone", "SAPNIK != '' AND batch = '$batch' ORDER BY Heijunka_Model, Heijunka_Sub_Model, Heijunka_Suffix, Heijunka_Color,heijunka_tone ASC", "result");
            if (!empty($get_master)) {
                $i = 1;
                foreach ($get_master as $get_master) {
                    $data_no[] = [
                        "No" => $i,
                        "SAPNIK" => $get_master->SAPNIK,
                    ];
                    if($get_master->tone == "TWO TONE"){
                        $d_twotoneid[] = [
                            "vin" => $get_master->SAPNIK,
                            "id_new" => $i,
                        ];
                    }else{
                        $d_twotoneid = [];
                    }
                    $i++;
                }
                $update = $this->model->update_batch_heijunka("master", "SAPNIK", $data_no);
                if(!empty($d_twotoneid)){
                    $update_tt = $this->model->update_batch_heijunka("twotone_id", "vin", $d_twotoneid);
                    if (!$update) {
                        $get_ttid = $this->model->gds_heijunka("twotone_id","id_new,id_old","vin !=","result");
                        if(!empty($get_ttid)){
                            foreach ($get_ttid as $ttid) {
                                $check_vin_old = $this->model->gds_heijunka("master", "No,SAPNIK", "No = '" . $ttid->id_old . "'", "row");
                                $data_old[] = [
                                    "No" => $ttid->id_new,
                                    "SAPNIK" => $check_vin_old->SAPNIK,
                                ];
                                $check_vin_new = $this->model->gds_heijunka("master", "No,SAPNIK", "No = '" . $ttid->id_new . "'", "row");
                                $data_new[] = [
                                    "No" => $ttid->id_old,
                                    "SAPNIK" => $check_vin_new->SAPNIK,
                                ];
                            }
                            $update_tt_old = $this->model->update_batch_heijunka("master", "SAPNIK", $data_old);
                            $update_tt_new = $this->model->update_batch_heijunka("master", "SAPNIK", $data_new);
                        }
                    }
                }
            }
        }
    }

    public function urutkan_nomor()
    {
        $check_batch = $this->model->gds_heijunka("master","batch","SAPNIK != '' GROUP BY batch ORDER BY batch ASC","result");
        foreach ($check_batch as $check_batch) {
            $batch = $check_batch->batch;
            $get_master = $this->model->gds_heijunka("master", "No,SAPNIK", "SAPNIK != '' AND batch = '$batch' ORDER BY No ASC", "result");
            if (!empty($get_master)) {
                $i = 1;
                foreach ($get_master as $get_master) {
                    $data_no[] = [
                        "No" => $i++,
                        "SAPNIK" => $get_master->SAPNIK,
                    ];
                }
                $update = $this->model->update_batch_heijunka("master", "SAPNIK", $data_no);
            }
        }
        die();
    }

    public function heijunka_color()
    {
		$docking = $this->input->get("docking");
        $batch = $this->model->gds_heijunka("master","batch","batch != '' GROUP BY batch ORDER BY batch ASC","result");
        foreach ($batch as $batch) {
            $b = $batch->batch;
			if(!empty($docking)){
				$row = $this->model->gds_heijunka("master", "Model_Name", "No != '' AND batch = '$b' AND Model != 'D74A' GROUP BY Model_Name", "result");
			}else{
				$row = $this->model->gds_heijunka("master", "Model_Name", "No != '' AND batch = '$b' GROUP BY Model_Name", "result");
			}
            foreach ($row as $row) {
                $Model_Name = $row->Model_Name;
                $row_suffix = $this->model->gds_heijunka("master", "Bot_Color,Katashiki_Sfx", "Model_Name = '$Model_Name' AND batch = '$b' GROUP BY Katashiki_Sfx, Bot_Color", "result");
                foreach ($row_suffix as $row_suffix) {
                    $row_color = $this->model->gds_heijunka("master", "Bot_Color", "Model_Name = '$Model_Name'  AND Katashiki_Sfx = '" . $row_suffix->Katashiki_Sfx . "' AND Bot_Color = '" . $row_suffix->Bot_Color . "' AND batch = '$b' GROUP BY Bot_Color", "result");
                    foreach ($row_color as $row_color) {
                        $Count_Color = $this->model->gds_heijunka("master", "COUNT(No) as Count", "Katashiki_Sfx = '" . $row_suffix->Katashiki_Sfx . "' AND Bot_Color = '" . $row_color->Bot_Color . "' AND batch = '$b'", "row");
                        $row_suffix_color = $this->model->gds_heijunka("master", "No,SAPNIK,Katashiki_Sfx,Model_Name", "Katashiki_Sfx = '" . $row_suffix->Katashiki_Sfx . "' AND Bot_Color = '" . $row_color->Bot_Color . "' AND batch = '$b' ORDER BY No ASC", "result");
                        foreach ($row_suffix_color as $row_suffix_color) {
                            $No = $row_suffix_color->No;
                            $Hitung_Heijunka = $No / $Count_Color->Count;
                            $Pembulatan_Hitung = round($Hitung_Heijunka, 9);
    
                            $data_update[] = [
                                "SAPNIK" => $row_suffix_color->SAPNIK,
                                "Heijunka_Color" => $Pembulatan_Hitung,
                            ];
                        }
                    }
                }
                $Model_Name_history = $row->Model_Name;
            }
            $data_history = [
                "Model" => $Model_Name_history,
                "Status" => 'Sukses',
            ];
            $Update_Heijunka = $this->model->update_batch_heijunka("master", "SAPNIK", $data_update);
            if ($Update_Heijunka) {
                $this->update_number();
                $data_history = [
                    "Model" => $Model_Name_history,
                    "Status" => 'Gagal',
                ];
            }
        }
        $Update_Histroy = $this->model->update_heijunka("history", "Heijunka = 'Color'", $data_history);

		if(!empty($docking)){
			$data_history = ["Status" => 'Sukses'];
			$Update_Histroy = $this->model->update_heijunka("history", "Heijunka = 'Suffix'", $data_history);
			$data_history = ["Status" => 'Sukses'];
			$Update_Histroy = $this->model->update_heijunka("history", "Heijunka = 'Model'", $data_history);
			$data_history = ["Status" => 'Sukses'];
			$Update_Histroy = $this->model->update_heijunka("history", "Heijunka = 'Transmisi'", $data_history);
		}

        if (!$Update_Histroy) {
            $fb = [
                "status" => "sukses",
            ];
        } else {
            $fb = [
                "status" => "gagal",
            ];
        }
        echo json_encode($fb);
        $this->urutkan_nomor();
        die();
    }

    public function heijunka_twotone()
    {
        
		$docking = $this->input->get("docking");
        $batch = $this->model->gds_heijunka("master","batch","batch != '' GROUP BY batch ORDER BY batch ASC","result");
        foreach ($batch as $batch) {
            $b = $batch->batch;
			if(!empty($docking)){
				$model_twotone = $this->model->gds_heijunka("master","Model","tone = 'TWO TONE' AND Model != 'D74A' AND batch = '$b' GROUP BY Model","result");
			}else{
				$model_twotone = $this->model->gds_heijunka("master","Model","tone = 'TWO TONE' AND batch = '$b' GROUP BY Model","result");
			}
            if(!empty($model_twotone)){
                $final_status = '';
                foreach ($model_twotone as $model_twotone) {
                    $model = $model_twotone->Model;
                    $data_no = $this->model->gds_heijunka("master","No","Model = '$model' AND batch = '$b' ORDER BY batch,No DESC","result");
                    foreach ($data_no as $data_no) {
                        $d_no[] = $data_no->No;
                    }
                    $tone = ["SINGLE TONE","TWO TONE"];
                    $status = '';
                    foreach ($tone as $key => $tone) {
                        $data_model = $this->model->gds_heijunka("master","No,SAPNIK,Color_Code","Model = '$model' AND tone = '$tone' AND batch = '$b' ORDER BY batch,No DESC","result");
                        $count_data = count($data_model);
                        $no = 1;
                        foreach ($data_model as $data_model) {
                            $heijunka_tone[] = [
                                "SAPNIK" => $data_model->SAPNIK,
                                "heijunka_tone" =>  round($no/$count_data,9)
                            ];
                            $no++;
                        }
                        $update = $this->model->update_batch_heijunka("master","SAPNIK",$heijunka_tone);
                        unset($heijunka_tone);
                        if(!$update){
                            $status .= 'OK ';
                        }else{
                            $status .= 'NG ';
                        }
                    }
                    if(substr_count($status,'NG') <= 0){
                        $data_heijunka = $this->model->gds_heijunka("master","SAPNIK","Model = '$model' AND batch = '$b' ORDER BY heijunka_tone ASC","result");
                        $new_no = 0;
                        foreach ($data_heijunka as $data_heijunka) {
                            $heijunka_tone[] = [
                                "SAPNIK" => $data_heijunka->SAPNIK,
                                "No" => $d_no[$new_no],
                            ];
                            $new_no++;
                        }
                        $update = $this->model->update_batch_heijunka("master","SAPNIK",$heijunka_tone);
                        unset($heijunka_tone);
                        if(!$update){
                            $final_status .= 'OK ';
                        }else{
                            $final_status .= 'NG ';
                        }
                    }else{
                        $final_status .= 'NG ';
                    }
                    unset($d_no);
                }

                if(substr_count($final_status,'NG') <= 0){
                    $fb = ["status" => "sukses"];
                }else{
                    $fb = ["status" => "ng"];
                }
            }else{
                $fb = ["status" => "tidak ada two tone"];
            }
        }
        echo json_encode($fb);
        die();
    }

    public function heijunka_suffix()
    {
        $batch = $this->model->gds_heijunka("master","batch","batch != '' GROUP BY batch ORDER BY batch ASC","result");
        foreach ($batch as $batch) {
            $b = $batch->batch;
            $row = $this->model->gds_heijunka("master", "Model_Name", "No != '' AND batch = '$b' GROUP BY Model_Name", "result");
            foreach ($row as $row) {
                $Model_Name = $row->Model_Name;
                $row_suffix = $this->model->gds_heijunka("master", "Transmisi,Katashiki_Sfx", "Model_Name = '$Model_Name' AND batch = '$b' GROUP BY Katashiki_Sfx, Transmisi", "result");
                foreach ($row_suffix as $row_suffix) {
                    $row_color = $this->model->gds_heijunka("master", "Bot_Color,Transmisi", "Model_Name = '$Model_Name' AND Katashiki_Sfx = '" . $row_suffix->Katashiki_Sfx . "' AND Transmisi = '" . $row_suffix->Transmisi . "' AND batch = '$b' GROUP BY Katashiki_Sfx, Transmisi", "result");
                    foreach ($row_color as $row_color) {
                        $Count_Color = $this->model->gds_heijunka("master", "COUNT(No) as Count", "Katashiki_Sfx = '" . $row_suffix->Katashiki_Sfx . "' AND Transmisi = '" . $row_color->Transmisi . "' AND Model_Name = '$Model_Name' AND batch = '$b'", "row");
                        $row_suffix_color = $this->model->gds_heijunka("master", "SAPNIK", "Katashiki_Sfx = '" . $row_suffix->Katashiki_Sfx . "' AND Transmisi = '" . $row_color->Transmisi . "' AND batch = '$b' ORDER BY batch,Heijunka_Color,heijunka_tone ASC", "result");
                        $No = 1;
                        foreach ($row_suffix_color as $row_suffix_color) {
                            $Hitung_Heijunka = $No / $Count_Color->Count;
                            $Pembulatan_Hitung = round($Hitung_Heijunka, 9);

                            $data_update[] = [
                                "SAPNIK" => $row_suffix_color->SAPNIK,
                                "Heijunka_Suffix" => $Pembulatan_Hitung,
                            ];
                            $No++;
                        }
                    }
                }
                $Model_Name_history = $row->Model_Name;
            }
            $Update_Heijunka = $this->model->update_batch_heijunka("master", "SAPNIK", $data_update);
            if (!$Update_Heijunka) {
                $data_history = [
                    "Model" => $Model_Name_history,
                    "Status" => 'Sukses',
                ];
                $this->update_number();
            } else {
                $data_history = [
                    "Model" => $Model_Name_history,
                    "Status" => 'Gagal',
                ];
            }
        }
        $Update_Histroy = $this->model->update_heijunka("history", "Heijunka = 'Suffix'", $data_history);
        if (!$Update_Histroy) {
            $fb = [
                "status" => "sukses",
            ];
        } else {
            $fb = [
                "status" => "gagal",
            ];
        }
        echo json_encode($fb);
        $this->urutkan_nomor();
        die();
    }
    public function heijunka_sub()
    {
        $batch = $this->model->gds_heijunka("master","batch","batch != '' GROUP BY batch ORDER BY batch ASC","result");
        foreach ($batch as $batch) {
            $b = $batch->batch;
            $row = $this->model->gds_heijunka("master", "Model_Name", "No != '' AND batch = '$b' GROUP BY Model_Name", "result");
            foreach ($row as $row) {
                $Model_Name = $row->Model_Name;
                $row_suffix = $this->model->gds_heijunka("master", "SAPNIK", "Model_Name = '$Model_Name' AND batch = '$b' ORDER BY batch,Heijunka_Suffix, Heijunka_Color, heijunka_tone ASC", "result");
                $Count_Color = $this->model->gds_heijunka("master", "COUNT(No) as Count", "Model_Name = '$Model_Name' AND batch = '$b'", "row");
                $No = 1;
                foreach ($row_suffix as $row_suffix) {
                    $Hitung_Heijunka = $No / $Count_Color->Count;
                    $Pembulatan_Hitung = round($Hitung_Heijunka, 9);

                    $data_update[] = [
                        "SAPNIK" => $row_suffix->SAPNIK,
                        "Heijunka_Sub_Model" => $Pembulatan_Hitung,
                    ];
                    $No++;
                }
                $Model_Name_history = $row->Model_Name;
            }
            $Update_Heijunka = $this->model->update_batch_heijunka("master", "SAPNIK", $data_update);
            if (!$Update_Heijunka) {
                $this->update_number();
                $data_history = [
                    "Model" => $Model_Name_history,
                    "Status" => 'Sukses',
                ];
            } else {
                $data_history = [
                    "Model" => $Model_Name_history,
                    "Status" => 'Gagal',
                ];
            }
        }
        $Update_Histroy = $this->model->update_heijunka("history", "Heijunka = 'Model'", $data_history);
        if (!$Update_Histroy) {
            $fb = [
                "status" => "sukses",
            ];
        } else {
            $fb = [
                "status" => "gagal",
            ];
        }
        echo json_encode($fb);
        $this->urutkan_nomor();
        die();
    }
    public function heijunka_model()
    {
        $batch = $this->model->gds_heijunka("master","batch","batch != '' GROUP BY batch ORDER BY batch ASC","result");
        foreach ($batch as $batch) {
            $b = $batch->batch;
            $row = $this->model->gds_heijunka("master", "Model as Model", "No != '' AND batch = '$b' GROUP BY Model", "result");
            foreach ($row as $row) {
                // $Model = $row->Model;
                // $row_suffix = $this->model->gds_heijunka("master", "SAPNIK", "Model = '$Model' AND batch = '$b' ORDER BY batch,Heijunka_Sub_Model, Heijunka_Suffix, Heijunka_Color, heijunka_tone ASC", "result");
                // $Count_Color = $this->model->gds_heijunka("master", "COUNT(No) as Count", "Model = '$Model' AND batch = '$b'", "row");
                // $No = 1;
                // foreach ($row_suffix as $row_suffix) {
                //     $Hitung_Heijunka = $No / $Count_Color->Count;
                //     $Pembulatan_Hitung = round($Hitung_Heijunka, 11);

                //     $data_update[] = [
                //         "SAPNIK" => $row_suffix->SAPNIK,
                //         "Heijunka_Model" => $Pembulatan_Hitung,
                //     ];
                //     $No++;
                // }
                // $Model_history = $row->Model;
				
                $Model = in_array($row->Model, ["D74A", "D55L"]) ? "'D74A','D55L'" : "'".$row->Model."'";
                $row_suffix = $this->model->gds_heijunka("master", "SAPNIK", "Model IN(".$Model.") AND batch = '$b' ORDER BY batch,Heijunka_Sub_Model, Heijunka_Suffix, Heijunka_Color, heijunka_tone ASC", "result");
                $Count_Color = $this->model->gds_heijunka("master", "COUNT(No) as Count", "Model IN(".$Model.") AND batch = '$b'", "row");
                $No = 1;
                foreach ($row_suffix as $row_suffix) {
                    $Hitung_Heijunka = $No / $Count_Color->Count;
                    $Pembulatan_Hitung = round($Hitung_Heijunka, 11);

                    $data_update[] = [
                        "SAPNIK" => $row_suffix->SAPNIK,
                        "Heijunka_Model" => $Pembulatan_Hitung,
                    ];
                    $No++;
                }
                $Model_history = $row->Model;
            }
            $Update_Heijunka = $this->model->update_batch_heijunka("master", "SAPNIK", $data_update);
            if (!$Update_Heijunka) {
                $this->update_number();
                $data_history = [
                    "Model" => $Model_history,
                    "Status" => 'Sukses',
                ];
            } else {
                $data_history = [
                    "Model" => $Model_history,
                    "Status" => 'Gagal',
                ];
            }
        }
        $Update_Histroy = $this->model->update_heijunka("history", "Heijunka = 'Model'", $data_history);
        if (!$Update_Histroy) {
            $fb = [
                "status" => "sukses",
            ];
			$this->heijunka_family_model();
        } else {
            $fb = [
                "status" => "gagal",
            ];
        }
        echo json_encode($fb);
        $this->urutkan_nomor();
        die();
    }
    public function heijunka_family_model()
    {
        $batch = $this->model->gds_heijunka("master","batch","batch != '' GROUP BY batch ORDER BY batch ASC","result");
        foreach ($batch as $batch) {
            $b = $batch->batch;
            $row = $this->model->gds_heijunka("master", "Family_Model as Model", "No != '' AND batch = '$b' GROUP BY Family_Model", "result");
            foreach ($row as $row) {
                $Model = $row->Model;
                $row_suffix = $this->model->gds_heijunka("master", "SAPNIK", "Family_Model = '$Model' AND batch = '$b' ORDER BY batch,Heijunka_Sub_Model, Heijunka_Suffix, Heijunka_Color, heijunka_tone ASC", "result");
                $Count_Color = $this->model->gds_heijunka("master", "COUNT(No) as Count", "Family_Model = '$Model' AND batch = '$b'", "row");
                $No = 1;
                foreach ($row_suffix as $row_suffix) {
                    $Hitung_Heijunka = $No / $Count_Color->Count;
                    $Pembulatan_Hitung = round($Hitung_Heijunka, 11);

                    $data_update[] = [
                        "SAPNIK" => $row_suffix->SAPNIK,
                        "heijunka_family" => $Pembulatan_Hitung,
                    ];
                    $No++;
                }
                $Model_history = $row->Model;
            }
            $Update_Heijunka = $this->model->update_batch_heijunka("master", "SAPNIK", $data_update);
            if (!$Update_Heijunka) {
                $this->update_number();
                $data_history = [
                    "Model" => $Model_history,
                    "Status" => 'Sukses',
                ];
            } else {
                $data_history = [
                    "Model" => $Model_history,
                    "Status" => 'Gagal',
                ];
            }
        }
        $Update_Histroy = $this->model->update_heijunka("history", "Heijunka = 'Model'", $data_history);
        if (!$Update_Histroy) {
            $fb = [
                "status" => "sukses",
            ];
        } else {
            $fb = [
                "status" => "gagal",
            ];
        }
        echo json_encode($fb);
        $this->urutkan_nomor();
        die();
    }
    public function check_ng_both()
    {
        $batch = $this->model->gds_heijunka("master","batch","batch != '' GROUP BY batch ORDER BY batch ASC","result");
        foreach ($batch as $batch) {
            $b = $batch->batch;
            $get_bot_no = $this->model->gds_heijunka("master m","SAPNIK,No,Bot_Color,tone","SAPNIK != '' AND batch = '$b' ORDER BY No ASC","result");
            if(!empty($get_bot_no)){
                $i = 1;
                $key = 0;
                foreach ($get_bot_no as $gbn) {
                    if($key <= 0){
                        $no = 1;
                        $bc_bef = $get_bot_no[$key]->Bot_Color;
                    }else{
                        $bc_bef = $get_bot_no[$key-1]->Bot_Color;
                        if($get_bot_no[$key-1]->Bot_Color == $gbn->Bot_Color){
                            $no = $no;
                        }else{
                            $no = 1;
                        }
                    }

                    if($gbn->Bot_Color == "A"){
                        if($no > 2){
                            $data_ng[] = [
                                "SAPNIK" => $gbn->SAPNIK,
                                "No" => $gbn->No,
                                "status" => "NG",
                                "tone" => $gbn->tone,
                                "both" => $gbn->Bot_Color,
                                "batch" => $gbn->batch,
                            ];
                        }else{
                            $data_ng[] = "";
                        }
                    }else if($gbn->Bot_Color == "B"){
                        if($no > 2){
                            $data_ng[] = [
                                "SAPNIK" => $gbn->SAPNIK,
                                "No" => $gbn->No,
                                "status" => "NG",
                                "tone" => $gbn->tone,
                                "both" => $gbn->Bot_Color,
                                "batch" => $gbn->batch,
                            ];
                        }else{
                            $data_ng[] = "";
                        }
                    }else{
                        $data_ng[] = "";
                    }
                    $no++;
                    $i++;
                    $key++;
                }
                $count_ng = count(array_filter($data_ng));
            }else{
                $count_ng = 0;
            }
        }
        return $count_ng;
    }
    public function heijunka_both()
    {
		$docking = $this->input->get("docking");
        $batch = $this->model->gds_heijunka("master","batch","batch != '' GROUP BY batch ORDER BY batch ASC","result");
        foreach ($batch as $batch) {
            $b = $batch->batch;
			$data_wos = $this->model->gds_heijunka("master","*","No != '' AND batch = '$b' ORDER BY batch,No DESC","result");
            $no_bot_det = 0;
            $No = 1;
            $data_ng_bot_det = [];
            $data_bot_all = [];
            $bot_bef = [];
            $data_ng_to_ok = [];
            $data_ok_to_ng = [];
            foreach ($data_wos as $data_wos) {
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
                        $bot_bef[$No] = "A";
                    }else{
                        if(empty($bot_bef[$No-1])){
                            continue;
                        }
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
                
                if($No > 1){
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
                            "Model" => $data_wos->Model,
                            "Tone" => $data_wos->tone,
                            "No_Convert" => $No,
                        ];
                        $bg_ng_bot_det = "bg-danger text-light";
                        $judge_ng_bot = "NG";
                    }else{
                        $bg_ng_bot_det = "bg-light";
                        $judge_ng_bot = "OK";
                    }

                    $data_bot_all[$data_wos->Model][$data_wos->tone][$judge_ng_bot][$data_wos->Bot_Color.$bot_det][] = [
                        "No" => $data_wos->No,
                        "SAPNIK" => $data_wos->SAPNIK,
                        "Model" => $data_wos->Model,
                        "Tone" => $data_wos->tone,
                        "No_Convert" => $No,
                    ];
                }
                //END BOT DETAIL//
                $No++;
            }

            $key_no = 0;
            if(!empty($data_ng_bot_det)){
                $ng_number = "";
                $status_update = "";
                unset($data_ng_max);
				foreach ($data_ng_bot_det as $k_ng => $v_ng) {
					$model = $k_ng;

					// 🟡 kalau docking aktif dan model D74A, lewatin aja
					if (!empty($docking) && $model == "D74A") {
						continue;
					}

					foreach ($v_ng as $kk_ng => $vv_ng) {
						$tone = $kk_ng;
						foreach ($vv_ng as $kkk_ng => $vvv_ng) {
							$ng_number .= ($vvv_ng["No_Convert"]*1)." dengan SAPNIK `".$vvv_ng["SAPNIK"]."`";
							$data_ng_max[$model][$tone][] = [
								"No" => $vvv_ng["No"],
								"SAPNIK" => $vvv_ng["SAPNIK"],
							];
							$bot = $vvv_ng["Bot_det"];
							if($bot == "A"){
								$brd = "ABB";
							}else if($bot == "B"){
								$brd = "ABA";
							}

							if(!empty($data_bot_all[$model][$tone]["OK"][$brd])){
								$jumlah_ok = count($data_bot_all[$model][$tone]["OK"][$brd]);
								if($jumlah_ok > 0){
									$id_bot_ok = rand(0,$jumlah_ok);
									if(!empty($data_bot_all[$model][$tone]["OK"][$brd][$id_bot_ok]["No"])){
										$data_ng_to_ok[] = [
											"no" => $data_bot_all[$model][$tone]["OK"][$brd][$id_bot_ok]["No"],
											"SAPNIK" => $vvv_ng["SAPNIK"],
										];
										$data_ok_to_ng[] = [
											"no" => $vvv_ng["No"],
											"SAPNIK" => $data_bot_all[$model][$tone]["OK"][$brd][$id_bot_ok]["SAPNIK"],
										];

										unset($data_bot_all[$model][$tone]["OK"][$brd][$id_bot_ok]);
									}
								}
							}
						}
					}
				}

                if(!empty($data_ng_to_ok)){
                    if(!empty($data_ok_to_ng)){
                        $data_update = array_merge($data_ng_to_ok,$data_ok_to_ng);
                    }
                }
				
                if(!empty($data_update)){
                    $update = $this->model->update_batch_heijunka("master","SAPNIK",$data_update);
                    $fb = [
                        "status" => "sukses",
                    ];
                }else{
                    $fb = [
                        "status" => "mentok",
                        "res" => "Mohon maaf heijunka both sistem sudah maksimal, masih terdapat ".count($data_ng_bot_det)." data NG yaitu di Nomor ".$ng_number,
                    ];
                }
            }else{
                $fb = [
                    "status" => "sukses",
                ];
            }
        }
		echo json_encode($fb);
		die();
    }
    public function heijunka_check_batch()
    {
        $check_batch = $this->model->gds("plan_wos","COUNT(*) as total","batch != '' GROUP BY batch","row");
        if($check_batch->total <= 1){
            $fb = [
                "status" => "sukses",
            ];
            echo json_encode($fb);
            die();
        }else{
            $check_plan = $this->model->gds("plan_wos","suffix,batch,plan","suffix_batch != ''","result");
            if(!empty($check_plan)){
                foreach ($check_plan as $check_plan) {
                    //BANDINGKAN PLAN DENGAN MASTER
                    $plan = $check_plan->plan;
                    $suffix = $check_plan->suffix;
                    $batch = $check_plan->batch;
                    $qty_master = $this->model->gds_heijunka("master","COUNT(*) as total","Lot_Code = '$suffix' AND batch = '$batch'","row");
                    $actual = $qty_master->total;
                    if($plan != $actual){
                        // 1. AMBIL DATANYA
						$actual_batch1 = $this->model->gds_heijunka("master","COUNT(*) as total","Lot_Code = '$suffix' AND batch = '1'","row");
						$plan_batch1 = $this->model->gds("plan_wos","plan","suffix = '$suffix' AND batch = '1'","row");

						// 2. DEFINE NILAINYA (Pake Ternary Operator biar ringkes)
						// Bacanya: Kalau $actual_batch1 ada isinya, ambil ->total. Kalau gak ada, kasih 0.
						$val_actual = isset($actual_batch1->total) ? $actual_batch1->total : 0;

						// Bacanya: Kalau $plan_batch1 ada isinya, ambil ->plan. Kalau gak ada, kasih 0.
						$val_plan = isset($plan_batch1->plan) ? $plan_batch1->plan : 0;


						// 3. BARU DEH DIBANDINGIN (Aman Sentosa)
						if($val_actual > $val_plan){
							
							// BERAPA KELEBIHAN ACTUAL YANG BISA DI BERIKAN KE BATCH 2 NANTI NYA
							$selisih = $val_actual - $val_plan;

							// AMBIL UNIT DARI BAWAH SESUAI DENGAN SELISIH UNTUK DI MASUKKAN KE BATCH 2
							$update_batch = ["batch" => "2"];
							
							// Query update lo tetep sama, cuma pastiin $selisih valid
							if($selisih > 0) {
								$this->model->update_heijunka("master","sapnik IN (SELECT sapnik FROM (SELECT sapnik FROM master WHERE Lot_Code = '$suffix' AND batch = '1' ORDER BY Plan_Delivery_Date DESC LIMIT $selisih) AS sub)",$update_batch);
							}
						}
                        
                        //CHECK ACTUAL LEBIH BESAR DARI PLAN BATCH 2
                        $plan_batch2 = $this->model->gds("plan_wos","plan","suffix = '$suffix' AND batch = '2'","row");
                        $actual_batch2 = $this->model->gds_heijunka("master","COUNT(*) as total","Lot_Code = '$suffix' AND batch = '2'","row");
                        if(!empty($actual_batch2->total) && !empty($plan_batch2->plan)){
							if($actual_batch2->total > $plan_batch2->plan){
								//BERAPA KELEBIHAN ACTUAL YANG BISA DI BERIKAN KE BATCH 1 NANTI NYA
								$selisih = $actual_batch2->total - $plan_batch2->plan;
								//AMBIL UNIT DARI BAWAH SESUAI DENGAN SELISIH UNTUK DI MASUKKAN KE BATCH 1
								$update_batch = ["batch" => "1"];
								$this->model->update_heijunka("master","sapnik IN (SELECT sapnik FROM (SELECT sapnik FROM master WHERE Lot_Code = '$suffix' AND batch = '2' ORDER BY Plan_Delivery_Date ASC LIMIT $selisih) AS sub)",$update_batch);
							}
                        }
                    }
                }
            }
            $fb = [
                "status" => "sukses",
            ];
            echo json_encode($fb);
            die();
        }
    }

    public function search($array,$search_list)
    {
        $result = array();
        foreach ($array as $key => $value) {
            foreach ($search_list as $k => $v) {
                if(!isset($value[$k]) || $value[$k] != $v)
                {
                    continue 2;
                }
            }
            $result[] = $key;
        }
        return $result;
    }
	public function import_td_link()
	{
		$clear_data = $this->model->delete("twotone_setting", "suffix_pdd !=");
		if (isset($_FILES["upload-file"]["name"])) {
            $total_wos = $this->model->gds_heijunka("master","COUNT(No) as total","No !=","row");
            if(!empty($total_wos->total)){
                $path = $_FILES["upload-file"]["tmp_name"];
                $object = PHPExcel_IOFactory::load($path);
                foreach ($object->getWorksheetIterator() as $worksheet) {
                    $highestRow = $worksheet->getHighestRow();
                    $highestColumn = $worksheet->getHighestColumn();
                    $no = ($highestRow-2)/$total_wos->total;
                    for ($row = $highestRow; $row >= 2; $row--) {
                        $sapnik = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
                        if (!empty($sapnik)) {
                            $wos_material = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
                            $wos_material_description = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
                            $sap_material = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
                            $engine_model = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
                            $engine_prefix = $worksheet->getCellByColumnAndRow(6, $row)->getValue();
                            $engine_number = $worksheet->getCellByColumnAndRow(7, $row)->getValue();
                            $plant = $worksheet->getCellByColumnAndRow(8, $row)->getValue();
                            $chassis_number = $worksheet->getCellByColumnAndRow(9, $row)->getValue();
                            $lot_code = $worksheet->getCellByColumnAndRow(10, $row)->getValue();
                            $lot_number = $worksheet->getCellByColumnAndRow(11, $row)->getValue();
                            $katashiki = $worksheet->getCellByColumnAndRow(12, $row)->getValue();
                            $katashiki_suffix = $worksheet->getCellByColumnAndRow(13, $row)->getValue();
                            $adm_production_id = $worksheet->getCellByColumnAndRow(14, $row)->getValue();
                            $tam_production_id = $worksheet->getCellByColumnAndRow(15, $row)->getValue();
                            $pdd = $worksheet->getCellByColumnAndRow(16, $row)->getValue();
                            if (!empty($pdd)) {
                                $pdd = explode(" ", $pdd);
                                $year_pdd = "20" . $pdd[2];
                                $month_pdd = $this->month_import($pdd[1]);
                                $day_pdd = $pdd[0];
                                $plan_delivery_date = $year_pdd . "-" . $month_pdd . "-" . $day_pdd;
                            } else {
                                $plan_delivery_date = NULL;
                            }
    
                            $pjid = $worksheet->getCellByColumnAndRow(17, $row)->getValue();
                            if (!empty($pjid)) {
                                $year_pjid = substr($pjid, 0, 4);
                                $month_pjid = substr($pjid, 4, 2);
                                $day_pjid = substr($pjid, 6, 2);
                                $plan_jig_in_date = $year_pjid . "-" . $month_pjid . "-" . $day_pjid;
                            } else {
                                $plan_jig_in_date = NULL;
                            }
    
                            $wrd = $worksheet->getCellByColumnAndRow(18, $row)->getValue();
                            if (!empty($wrd)) {
                                $day_wrd = substr($wrd, 0, 2);
                                $month_wrd = substr($wrd, 2, 2);
                                $year_wrd = substr($wrd, 4, 4);
                                $wos_release_date = $year_wrd . "-" . $month_wrd . "-" . $day_wrd;
                            } else {
                                $wos_release_date = NULL;
                            }
                            $sapwos_des = $worksheet->getCellByColumnAndRow(19, $row)->getValue();
                            $location = $worksheet->getCellByColumnAndRow(20, $row)->getValue();
                            $color_code = $worksheet->getCellByColumnAndRow(21, $row)->getValue();
                            $model = $worksheet->getCellByColumnAndRow(22, $row)->getValue();
                            $ed = $worksheet->getCellByColumnAndRow(23, $row)->getValue();
                            $order_column = $worksheet->getCellByColumnAndRow(24, $row)->getValue();
                            $destination = $worksheet->getCellByColumnAndRow(25, $row)->getValue();
                            if (!empty($sapnik)) {
                                $temp_data[] = array(
                                    'No' => $no,
                                    'WOS_Material' => $wos_material,
                                    'WOS_Material_Description' => $wos_material_description,
                                    'SAPNIK' => str_replace(" ", "", $sapnik),
                                    'SAP_Material' => $sap_material,
                                    'Engine_Model' => $engine_model,
                                    'Engine_Prefix' => $engine_prefix,
                                    'Engine_Number' => $engine_number,
                                    'Plant' => $plant,
                                    'Chassis_Number' => $chassis_number,
                                    'Lot_Code' => $lot_code,
                                    'Lot_Number' => $lot_number,
                                    'Katashiki' => $katashiki,
                                    'Katashiki_Sfx' => $katashiki_suffix,
                                    'ADM_Production_Id' => $adm_production_id,
                                    'TAM_Production_Id' => $tam_production_id,
                                    'Plan_Delivery_Date' => $plan_delivery_date,
                                    'Plan_Jig_In_Date' => $plan_jig_in_date,
                                    'WOS_Release_Date' => $wos_release_date,
                                    'SAPWOS_DES' => $sapwos_des,
                                    'Location' => $location,
                                    'Color_Code' => $color_code,
                                    'ED' => $ed,
                                    'Model' => $model,
                                    'Order' => $order_column,
                                    'Dest' => $destination,
                                    "Model_Name" => "TD-Link",
                                    "heijunka_tone" => "TD-Link",
                                );
                                $no += ($highestRow-2)/$total_wos->total;
                            }
                        }
                    }
                }
                $clear_data = $this->model->delete_heijunka("master_td_link", "SAPNIK !=");
                $insert = $this->model->insert_batch_heijunka("master_td_link", $temp_data);
                if ($insert) {
                    $this->urut_td_link();
                    if($this->urut_td_link() == "Sukses"){
                        $this->voice("sukses.mp3");
                        $this->swal_custom_icon("Sukses", "TD Link berhasil di upload", base_url('assets/images/happy.png'), "","true");
                    }else{
                        $this->voice("gagal.mp3");
                        $this->swal_custom_icon("Gagal", 'TD Link renewal number, Please re-upload data TD-Links<br><br><form action="<?=base_url("import_td_link")?>" method="post" id="import_td_link_msg" enctype="multipart/form-data"><div class="input-group"><div class="custom-file" align="left"><input type="file" class="custom-file-input" onchange="upload_td_link()" name="upload-file" accept=".xls"><label class="custom-file-label" for="customFile" id="customFile">Pilih File Excel TD-Link</label></div></div></form>', base_url('assets/images/emot-sedih.jpg'), "rounded-circle","false");
                    }
                } else {
                    $this->voice("gagal.mp3");
                    $this->swal_custom_icon("Gagal", 'TD Link gagal upload<br><br><form action="<?=base_url("import_td_link")?>" method="post" id="import_td_link_msg" enctype="multipart/form-data"><div class="input-group"><div class="custom-file" align="left"><input type="file" class="custom-file-input" onchange="upload_td_link()" name="upload-file" accept=".xls"><label class="custom-file-label" for="customFile" id="customFile">Pilih File Excel TD-Link</label></div></div></form>', base_url('assets/images/emot-sedih.jpg'), "rounded-circle","false");
                }
            }else{
                $this->voice("gagal.mp3");
                $this->swal_custom_icon("Gagal", 'Mohon upload WOS terlebih dahulu', base_url('assets/images/emot-sedih.jpg'), "rounded-circle","false");
            }
		} else {
            $this->voice("gagal.mp3");
			$this->swal_custom_icon("Gagal", 'Tidak ada file yang masuk<br><br><form action="<?=base_url("import_td_link")?>" method="post" id="import_td_link_msg" enctype="multipart/form-data"><div class="input-group"><div class="custom-file" align="left"><input type="file" class="custom-file-input" onchange="upload_td_link()" name="upload-file" accept=".xls"><label class="custom-file-label" for="customFile" id="customFile">Pilih File Excel TD-Link</label></div></div></form>', base_url('assets/images/emot-sedih.jpg'), "rounded-circle","false");
		}
		redirect("heijunka_wos");
	}

    public function urut_td_link()
    {
        $jml_td_link = $this->model->gds_heijunka("master_td_link","COUNT(No) as jml","SAPNIK !=","row");
        $jml_master = $this->model->gds_heijunka("master","COUNT(No) as jml","SAPNIK !=","row");
        $average = number_format($jml_master->jml / $jml_td_link->jml,1,".","");
        $td_link = $this->model->gds_heijunka("master_td_link","SAPNIK","SAPNIK != '' ORDER BY No ASC","result");
        $average_o = 0;
        foreach ($td_link as $td_link) {
            $average_o += $average;
            $average_operation = number_format($average_o,1,".","");
            $data_update[] = [
                "No" => $average_operation,
                "SAPNIK" => $td_link->SAPNIK,
            ];
        }
        $update_master_td_link = $this->model->update_batch_heijunka("master_td_link", "SAPNIK", $data_update);
        if(!$update_master_td_link){
            $fb = "Sukses";
        }else{
            $fb = "Gagal";
        }
        return $fb;
    }

    public function tukar_sapnik()
    {
        $nik1 = $this->session->userdata("nik1");
        $nik2 = $this->session->userdata("nik2");

        $no1 = $this->model->data_wos("No","SAPNIK = '$nik1'");
        $no2 = $this->model->data_wos("No","SAPNIK = '$nik2'");

        if(!empty($no1->No) && !empty($no2->No)){
            $data_tukar[] = [
                "No" => $no1->No,
                "SAPNIK" => $nik2,
            ];
            
            $data_tukar[] = [
                "No" => $no2->No,
                "SAPNIK" => $nik1,
            ];
            $update = $this->model->update_batch_heijunka("master", "SAPNIK", $data_tukar);
            if(!$update){
                $status = "sukses";
            }else{
                $status = "Proses pertukaran posisi gagal.";
            }
        }else{
            $status = "Proses pertukaran posisi gagal. Silahkan coba kembali.". $nik1." => ".$nik2;
            // $status = "Proses pertukaran posisi gagal. Silahkan coba kembali. <br>No1 : ".$no1->No."<br>No2 : ".$no2->No;
        }
        echo $status;
        die();
    }

    public function set_session()
    {
        $nik = $this->input->get("nik");
        $type_switch = $this->input->get("type_switch");
        $data_session = ["nik".$type_switch => $nik];
        $update_session = $this->session->set_userdata($data_session);
        if(!$update_session){
            echo "Sukses update session NIK ".$type_switch." => ".$nik;
        }else{
            echo "Error update session";
        }
        die();
    }

    public function import_wos()
    {
		$clear_data = $this->model->delete_heijunka("master", "No !=");
		$clear_data = $this->model->delete_heijunka("master_td_link", "No !=");
		if (isset($_FILES["upload-file"]["name"])) {
            $path = $_FILES["upload-file"]["tmp_name"];
            $object = PHPExcel_IOFactory::load($path);
			$no_wos = 1;
            foreach ($object->getWorksheetIterator() as $worksheet) {
                $highestRow = $worksheet->getHighestRow();
                $highestColumn = $worksheet->getHighestColumn();
                for ($row = $highestRow; $row >= 2; $row--) {
                    $sapnik = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
                    $katashiki = $worksheet->getCellByColumnAndRow(12, $row)->getValue();
                    if (!empty($sapnik) && !empty($katashiki) && strlen($sapnik) >= 17) {
                        $no = $no_wos++;
                        $wos_material = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
                        $wos_material_description = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
                        $sap_material = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
                        $engine_model = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
                        $engine_prefix = $worksheet->getCellByColumnAndRow(6, $row)->getValue();
                        $engine_number = $worksheet->getCellByColumnAndRow(7, $row)->getValue();
                        $plant = $worksheet->getCellByColumnAndRow(8, $row)->getValue();
                        $chassis_number = $worksheet->getCellByColumnAndRow(9, $row)->getValue();
                        $lot_code = $worksheet->getCellByColumnAndRow(10, $row)->getValue();
                        $lot_number = $worksheet->getCellByColumnAndRow(11, $row)->getValue();
                        $katashiki_suffix = $worksheet->getCellByColumnAndRow(13, $row)->getValue();
                        $adm_production_id = $worksheet->getCellByColumnAndRow(14, $row)->getValue();
                        $tam_production_id = $worksheet->getCellByColumnAndRow(15, $row)->getValue();
                        $pdd = $worksheet->getCellByColumnAndRow(16, $row)->getValue();
                        if (!empty($pdd)) {
                            $pdd = explode(" ", $pdd);
                            if(strlen($pdd[2]) <= 2){
                                $year_pdd = "20" . $pdd[2];
                            }else{
                                $year_pdd = $pdd[2];
                            }
                            $month_pdd = $this->month_import($pdd[1]);
                            $day_pdd = $pdd[0];
                            $plan_delivery_date = $year_pdd . "-" . $month_pdd . "-" . $day_pdd;
                        } else {
                            $plan_delivery_date = NULL;
                        }

                        $pjid = $worksheet->getCellByColumnAndRow(17, $row)->getValue();
                        if (!empty($pjid)) {
                            $year_pjid = substr($pjid, 0, 4);
                            $month_pjid = substr($pjid, 4, 2);
                            $day_pjid = substr($pjid, 6, 2);
                            $plan_jig_in_date = $year_pjid . "-" . $month_pjid . "-" . $day_pjid;
                        } else {
                            $plan_jig_in_date = NULL;
                        }

                        $wrd = $worksheet->getCellByColumnAndRow(18, $row)->getValue();
                        if (!empty($wrd)) {
                            $day_wrd = substr($wrd, 0, 2);
                            $month_wrd = substr($wrd, 2, 2);
                            $year_wrd = substr($wrd, 4, 4);
                            $wos_release_date = $year_wrd . "-" . $month_wrd . "-" . $day_wrd;
                        } else {
                            $wos_release_date = NULL;
                        }
                        $sapwos_des = $worksheet->getCellByColumnAndRow(19, $row)->getValue();
                        $location = $worksheet->getCellByColumnAndRow(20, $row)->getValue();
                        $color_code = $worksheet->getCellByColumnAndRow(21, $row)->getValue();
                        $model = $worksheet->getCellByColumnAndRow(22, $row)->getValue();
                        $ed = $worksheet->getCellByColumnAndRow(23, $row)->getValue();
                        $order_column = $worksheet->getCellByColumnAndRow(24, $row)->getValue();
                        $destination = $worksheet->getCellByColumnAndRow(25, $row)->getValue();
                        if (!empty($sapnik)) {
                            if($model == "X01X"){
                                $temp_data_td_link[] = array(
                                    'No' => $no,
                                    'WOS_Material' => $wos_material,
                                    'WOS_Material_Description' => $wos_material_description,
                                    'SAPNIK' => str_replace(" ", "", $sapnik),
                                    'SAP_Material' => $sap_material,
                                    'Engine_Model' => $engine_model,
                                    'Engine_Prefix' => $engine_prefix,
                                    'Engine_Number' => $engine_number,
                                    'Plant' => $plant,
                                    'Chassis_Number' => $chassis_number,
                                    'Lot_Code' => $lot_code,
                                    'Lot_Number' => $lot_number,
                                    'Katashiki' => $katashiki,
                                    'Katashiki_Sfx' => $katashiki_suffix,
                                    'ADM_Production_Id' => $adm_production_id,
                                    'TAM_Production_Id' => $tam_production_id,
                                    'Plan_Delivery_Date' => $plan_delivery_date,
                                    'Plan_Jig_In_Date' => $plan_jig_in_date,
                                    'WOS_Release_Date' => $wos_release_date,
                                    'SAPWOS_DES' => $sapwos_des,
                                    'Location' => $location,
                                    'Color_Code' => $color_code,
                                    'ED' => $ed,
                                    'Model' => $model,
                                    "Model_Name" => "TD-Link",
                                    'Order' => $order_column,
                                    'Dest' => $destination,
                                    "heijunka_tone" => "TD-Link",
                                );
                            }else{
                                $get_model = $this->model->gds("plan_wos_base", "model_code,model_name", "suffix = '" . $katashiki_suffix . "'", "row");
                                $transmisi = "";
                                $color = "";
                                $bot_color = "";
                                $model_code = $model;
                                $model_name = "";
                                if(!empty($get_model)){
                                    $transmisi = substr($katashiki_suffix, 8, 1);
                                    $transmisi = "M";
                                    if ($transmisi != "M") {
                                        $transmisi = "Q";
                                    }

                                    $model_code = !empty($get_model->model_code) ? $get_model->model_code : "";
                                    $model_name = !empty($get_model->model_name) ? $get_model->model_name : "";
                                }
                                    
                                $color_row = $this->model->gds_heijunka("color_model", "Background,Font", "Model_Name = '".$model_name."'", "row");
                                if (!empty($color_row)) {
                                    $color = $color_row->Background . "," . $color_row->Font;
                                } else {
                                    $color = "";
                                }

                                $model_color = $model_code . $color_code;
                                $get_bot_color = $this->model->gds_heijunka("color_both", "Bot", "Model_Warna = '$model_color'", "row");
                                if (!empty($get_bot_color)) {
                                    $bot_color = $get_bot_color->Bot;
                                } else {
                                    $bot_color = "";
                                }

                                if($model == "X02X"){
                                    $model_code = "X02X";
                                }
    
                                $temp_data_wos[] = array(
                                    'No' => $no,
                                    'WOS_Material' => $wos_material,
                                    'WOS_Material_Description' => $wos_material_description,
                                    'SAPNIK' => str_replace(" ", "", $sapnik),
                                    'SAP_Material' => $sap_material,
                                    'Engine_Model' => $engine_model,
                                    'Engine_Prefix' => $engine_prefix,
                                    'Engine_Number' => $engine_number,
                                    'Plant' => $plant,
                                    'Chassis_Number' => $chassis_number,
                                    'Lot_Code' => $lot_code,
                                    'Lot_Number' => $lot_number,
                                    'Katashiki' => $katashiki,
                                    'Katashiki_Sfx' => $katashiki_suffix,
                                    'ADM_Production_Id' => $adm_production_id,
                                    'TAM_Production_Id' => $tam_production_id,
                                    'Plan_Delivery_Date' => $plan_delivery_date,
                                    'Plan_Jig_In_Date' => $plan_jig_in_date,
                                    'WOS_Release_Date' => $wos_release_date,
                                    'SAPWOS_DES' => $sapwos_des,
                                    'Location' => $location,
                                    'Color_Code' => $color_code,
                                    'ED' => $ed,
                                    'Model' => $model_code,
                                    "Model_Name" => $model_name,
                                    'Order' => $order_column,
                                    'Dest' => $destination,
                                    "Transmisi" => $transmisi,
                                    "Color" => $color,
                                    "Bot_Color" => $bot_color,
                                );
                            }
                        }
                    }
                }
            }
            $upload_wos = $this->model->insert_batch_heijunka("master", $temp_data_wos);
            if($upload_wos){
                if(!empty($temp_data_td_link)){
                    $upload_td_link = $this->model->insert_batch_heijunka("master_td_link", $temp_data_td_link);
                }else{
                    $upload_td_link = true;
                }
                if($upload_td_link){
                    $this->voice("sukses.mp3");
                    $this->swal_custom_icon("Sukses", "Data WOS berhasil di upload", base_url('assets/images/happy.png'), "","true");
                }else{
                    $this->voice("gagal.mp3");
                    $this->swal_custom_icon("Gagal", 'Data WOS TD-Link gagal di upload, mohon upload ulang WOS', base_url('assets/images/emot-sedih.jpg'), "rounded-circle","false");
                }
            }else{
                $this->voice("gagal.mp3");
                $this->swal_custom_icon("Gagal", 'Data WOS gagal di upload, mohon upload ulang WOS', base_url('assets/images/emot-sedih.jpg'), "rounded-circle","false");
            }
        }else{
            $this->voice("gagal.mp3");
            $this->swal_custom_icon("Gagal", 'File upload kosong', base_url('assets/images/emot-sedih.jpg'), "rounded-circle","false");
        }
		redirect("heijunka_wos");
    }

    public function import_wos_backup_print()
    {
		$clear_data = $this->model->delete_heijunka("master_print", "No !=");
		if (isset($_FILES["upload-file"]["name"])) {
            $path = $_FILES["upload-file"]["tmp_name"];
            $object = PHPExcel_IOFactory::load($path);
			$no_wos = 1;
            foreach ($object->getWorksheetIterator() as $worksheet) {
                $highestRow = $worksheet->getHighestRow();
                $highestColumn = $worksheet->getHighestColumn();
                for ($row = $highestRow; $row >= 2; $row--) {
                    $sapnik = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
                    if (!empty($sapnik)) {
                        $no = $no_wos++;
                        $wos_material = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
                        $wos_material_description = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
                        $sap_material = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
                        $engine_model = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
                        $engine_prefix = $worksheet->getCellByColumnAndRow(6, $row)->getValue();
                        $engine_number = $worksheet->getCellByColumnAndRow(7, $row)->getValue();
                        $plant = $worksheet->getCellByColumnAndRow(8, $row)->getValue();
                        $chassis_number = $worksheet->getCellByColumnAndRow(9, $row)->getValue();
                        $lot_code = $worksheet->getCellByColumnAndRow(10, $row)->getValue();
                        $lot_number = $worksheet->getCellByColumnAndRow(11, $row)->getValue();
                        $katashiki = $worksheet->getCellByColumnAndRow(12, $row)->getValue();
                        $katashiki_suffix = $worksheet->getCellByColumnAndRow(13, $row)->getValue();
                        $adm_production_id = $worksheet->getCellByColumnAndRow(14, $row)->getValue();
                        $tam_production_id = $worksheet->getCellByColumnAndRow(15, $row)->getValue();
                        $model = $worksheet->getCellByColumnAndRow(22, $row)->getValue();
                        $pdd = $worksheet->getCellByColumnAndRow(16, $row)->getValue();
                        if (!empty($pdd)) {
                            $pdd = explode(" ", $pdd);
                            $year_pdd = "20" . $pdd[2];
                            $month_pdd = $this->month_import($pdd[1]);
                            $day_pdd = $pdd[0];
                            $plan_delivery_date = $year_pdd . "-" . $month_pdd . "-" . $day_pdd;
                        } else {
                            $plan_delivery_date = NULL;
                        }

                        $pjid = $worksheet->getCellByColumnAndRow(17, $row)->getValue();
                        if (!empty($pjid)) {
                            $year_pjid = substr($pjid, 0, 4);
                            $month_pjid = substr($pjid, 4, 2);
                            $day_pjid = substr($pjid, 6, 2);
                            $plan_jig_in_date = $year_pjid . "-" . $month_pjid . "-" . $day_pjid;
                        } else {
                            $plan_jig_in_date = NULL;
                        }

                        $wrd = $worksheet->getCellByColumnAndRow(18, $row)->getValue();
                        if (!empty($wrd)) {
                            $day_wrd = substr($wrd, 0, 2);
                            $month_wrd = substr($wrd, 2, 2);
                            $year_wrd = substr($wrd, 4, 4);
                            $wos_release_date = $year_wrd . "-" . $month_wrd . "-" . $day_wrd;
                        } else {
                            $wos_release_date = NULL;
                        }
                        $sapwos_des = $worksheet->getCellByColumnAndRow(19, $row)->getValue();
                        $location = $worksheet->getCellByColumnAndRow(20, $row)->getValue();
                        $color_code = $worksheet->getCellByColumnAndRow(21, $row)->getValue();
                        $model = $worksheet->getCellByColumnAndRow(22, $row)->getValue();
                        $ed = $worksheet->getCellByColumnAndRow(23, $row)->getValue();
                        $order_column = $worksheet->getCellByColumnAndRow(24, $row)->getValue();
                        $destination = $worksheet->getCellByColumnAndRow(25, $row)->getValue();
                        if (!empty($sapnik)) {
							$transmisi = substr($katashiki_suffix, 8, 1);
							if ($transmisi != "M") {
								$transmisi = "Q";
							} else {
								$transmisi = "M";
							}

							// $color_row = $this->model->gds_heijunka("color_model", "Background,Font", "Model_Name = '".$get_model->model_name."'", "row");
							// if (!empty($color_row)) {
							// 	$color = $color_row->Background . "," . $color_row->Font;
							// } else {
							// 	$color = "";
							// }

							$temp_data_wos[] = array(
								'No' => $no,
								'WOS_Material' => $wos_material,
								'WOS_Material_Description' => $wos_material_description,
								'SAPNIK' => str_replace(" ", "", $sapnik),
								'SAP_Material' => $sap_material,
								'Engine_Model' => $engine_model,
								'Engine_Prefix' => $engine_prefix,
								'Engine_Number' => $engine_number,
								'Plant' => $plant,
								'Chassis_Number' => $chassis_number,
								'Lot_Code' => $lot_code,
								'Lot_Number' => $lot_number,
								'Katashiki' => $katashiki,
								'Katashiki_Sfx' => $katashiki_suffix,
								'ADM_Production_Id' => $adm_production_id,
								'TAM_Production_Id' => $tam_production_id,
								'Plan_Delivery_Date' => $plan_delivery_date,
								'Plan_Jig_In_Date' => $plan_jig_in_date,
								'WOS_Release_Date' => $wos_release_date,
								'SAPWOS_DES' => $sapwos_des,
								'Location' => $location,
								'Color_Code' => $color_code,
								'ED' => $ed,
								'Model' => $model,
								'Order' => $order_column,
								'Dest' => $destination,
								"Transmisi" => $transmisi,
								// "Color" => $color,
							);
                        }
                    }
                }
            }
            $upload_wos = $this->model->insert_batch_heijunka("master_print", $temp_data_wos);
            if($upload_wos){
				$this->voice("sukses.mp3");
				$this->swal_custom_icon("Sukses", "Data WOS berhasil di upload", base_url('assets/images/happy.png'), "","true");
            }else{
                $this->voice("gagal.mp3");
                $this->swal_custom_icon("Gagal", 'Data WOS gagal di upload, mohon upload ulang WOS', base_url('assets/images/emot-sedih.jpg'), "rounded-circle","false");
            }
        }else{
            $this->voice("gagal.mp3");
            $this->swal_custom_icon("Gagal", 'File upload kosong', base_url('assets/images/emot-sedih.jpg'), "rounded-circle","false");
        }
		redirect("heijunka_wos_print");
    }

	public function print_card()
	{
		$this->load->view("content/view/print_card");
	}

	public function save_edit_master_sp($p)
	{
		$breakdown = $this->input->post("breakdown");
		$part_name = $this->input->post("part_name");
		$original_part = $this->input->post("original_part");
		$route = $this->input->post("route");
		$qty = $this->input->post("qty");
		$keterangan = $this->input->post("keterangan");
		print_r($breakdown);
		die();
	}

    public function import_master_sp()
    {
		$clear_data = $this->model->delete_heijunka("breakdown_sp", "No !=");
		if (isset($_FILES["upload-file"]["name"])) {
            $path = $_FILES["upload-file"]["tmp_name"];
            $object = PHPExcel_IOFactory::load($path);
            foreach ($object->getWorksheetIterator() as $worksheet) {
                $highestRow = $worksheet->getHighestRow();
                $highestColumn = $worksheet->getHighestColumn();
                for ($row = 2; $row <= $highestRow; $row++) {
					$part_number = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
					$breakdown = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
					$part_name = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
					$original_part = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
					$route = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
					$qty = $worksheet->getCellByColumnAndRow(6, $row)->getValue();
					$keterangan = $worksheet->getCellByColumnAndRow(7, $row)->getValue();
					$model = $worksheet->getCellByColumnAndRow(8, $row)->getValue();
					$line = $worksheet->getCellByColumnAndRow(9, $row)->getValue();
					$temp_data[] = array(
						'Part_Number' => $part_number,
						'Breakdown' => $breakdown,
						'Part_Name' => $part_name,
						'Route' => empty($route) ? "-" : $route,
						'Keterangan' => $keterangan,
						'Qty' => $qty,
						'Original_Part' => $original_part,
						'Model' => $model,
						'line' => $line
					);
                }
            }
            $upload_wos = $this->model->insert_batch_heijunka("breakdown_sp", $temp_data);
            if($upload_wos){
				$this->voice("sukses.mp3");
				$this->swal_custom_icon("Sukses", "Master Service Part berhasil di upload", base_url('assets/images/happy.png'), "","true");
            }else{
                $this->voice("gagal.mp3");
                $this->swal_custom_icon("Gagal", 'Master Service Part gagal di upload, mohon upload ulang file', base_url('assets/images/emot-sedih.jpg'), "rounded-circle","false");
            }
        }else{
            $this->voice("gagal.mp3");
            $this->swal_custom_icon("Gagal", 'File upload kosong', base_url('assets/images/emot-sedih.jpg'), "rounded-circle","false");
        }
		redirect("master_service_part");
    }

	public function clear_master_sp()
	{
		$clear_tabungan = $this->model->delete_heijunka("breakdown_sp", "No !=");
		$this->voice("sukses.mp3");
		$this->swal_custom_icon("Sukses", "Data Master Berhasil Dibersihkan", base_url('assets/images/happy.png'), "","true");
		redirect("master_service_part");
	}

    public function import_pro_number()
    {
		if (isset($_FILES["upload-file"]["name"])) {
            $path = $_FILES["upload-file"]["tmp_name"];
            $object = PHPExcel_IOFactory::load($path);
            foreach ($object->getWorksheetIterator() as $worksheet) {
				$bulan = PHPExcel_Style_NumberFormat::toFormattedString($worksheet->getCellByColumnAndRow(0, 1)->getValue(),'mm');
                $highestRow = $worksheet->getHighestRow();
                $highestColumn = $worksheet->getHighestColumn();
				$no = 1;
                for ($row = 2; $row <= $highestRow; $row++) {
					$part_number = $worksheet->getCellByColumnAndRow(0, $row)->getValue();
					$qty = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
					$pro_number = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
					$temp_data[] = array(
						'part_number' => $part_number,
						'qty' => $qty,
						'pro_number' => $pro_number,
					);
                }
            }
			if(!empty($temp_data)){
				if($bulan == $this->input->post("month")){
					$clear_data = $this->model->delete_heijunka("pro_number", "id !=");
					$upload_wos = $this->model->insert_batch_heijunka("pro_number", $temp_data);
					if($upload_wos){
						$this->voice("sukses.mp3");
						$this->swal_custom_icon("Sukses", "Master Service Part berhasil di upload", base_url('assets/images/happy.png'), "","true");
					}else{
						$this->voice("gagal.mp3");
						$this->swal_custom_icon("Gagal", 'Master Service Part gagal di upload, mohon upload ulang file', base_url('assets/images/emot-sedih.jpg'), "rounded-circle","false");
					}
				}else{
					$this->voice("gagal.mp3");
					$this->swal_custom_icon("Upload PRO Gagal", 'PRO number tidak sesuai dengan periode bulan.', base_url('assets/images/emot-sedih.jpg'), "rounded-circle","false");
				}
			}else{
				$this->voice("gagal.mp3");
				$this->swal_custom_icon("Gagal", 'Data yang anda upload kosong', base_url('assets/images/emot-sedih.jpg'), "rounded-circle","false");
			}
        }else{
            $this->voice("gagal.mp3");
            $this->swal_custom_icon("Gagal", 'File upload kosong', base_url('assets/images/emot-sedih.jpg'), "rounded-circle","false");
        }
		redirect("pro_number");
    }

	public function clear_pro_number()
	{
		$clear_tabungan = $this->model->delete_heijunka("pro_number", "id !=");
		$this->voice("sukses.mp3");
		$this->swal_custom_icon("Sukses", "Data Master Berhasil Dibersihkan", base_url('assets/images/happy.png'), "","true");
		redirect("pro_number");
	}

    public function import_sp()
    {
		$clear_data = $this->model->delete_heijunka("create_wos", "No !=");
		if (isset($_FILES["upload-file"]["name"])) {
            $temp_data = [];
            $path = $_FILES["upload-file"]["tmp_name"];
            $object = PHPExcel_IOFactory::load($path);
            foreach ($object->getWorksheetIterator() as $worksheet) {
                $highestRow = ($worksheet->getHighestRow()-1);
                for ($row = 5; $row <= $highestRow+1; $row++) {
					$sub = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
					$ed = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
					$customer = $worksheet->getCellByColumnAndRow(6, $row)->getValue();
					$part_no = $worksheet->getCellByColumnAndRow(8, $row)->getValue();
					$type = $worksheet->getCellByColumnAndRow(12, $row)->getValue();
					$qty = $worksheet->getCellByColumnAndRow(13, $row)->getValue();
					$po_sto = $worksheet->getCellByColumnAndRow(17, $row)->getValue();
					$po_ed = $worksheet->getCellByColumnAndRow(18, $row)->getValue();
					$po_tam = $worksheet->getCellByColumnAndRow(19, $row)->getValue();
					$reference = $worksheet->getCellByColumnAndRow(20, $row)->getValue();
					if($qty > 0){
                        $temp_data[] = array(
                            'sub' => $sub,
                            'ed' => $ed,
                            'customer' => $customer,
                            'Part_Number' => $part_no,
                            'type' => $type,
                            'Qty' => $qty,
                            'po_sto' => $po_sto,
                            'po_ed' => $po_ed,
                            'po_tam' => $po_tam,
                            'reference' => $reference,
                        );
					}
                }
            }
            if(!empty($temp_data)){
                $upload_wos = $this->model->insert_batch_heijunka("create_wos", $temp_data);
                if($upload_wos){
                    $this->voice("sukses.mp3");
                    $width = "'100%'";
                    $this->session->set_flashdata("swal",'
                    <script>
                        swal.fire({
                            iconHtml: "<img src='.base_url('assets/images/happy.png').' width='.$width.'>",
                            customClass: {
                                icon: "border-0"
                            },
                            title:"Sukses",
                            html:"Service Part berhasil di upload",
                            showConfirmButton:true,
                            confirmButtonText:"Check Pokayoke",
                        }).then((result) => {
                            if(result.isConfirmed){
                                location.reload();
                            }
                        });
                    </script>');
                }else{
                    $this->voice("gagal.mp3");
                    $this->swal_custom_icon("Gagal", 'Service Part gagal di upload, mohon upload ulang file', base_url('assets/images/emot-sedih.jpg'), "rounded-circle","false");
                }
            }else{
                $this->voice("gagal.mp3");
                $this->swal_custom_icon("Gagal", 'File upload kosong atau format tidak sesuai', base_url('assets/images/emot-sedih.jpg'), "rounded-circle","false");
            }
        }else{
            $this->voice("gagal.mp3");
            $this->swal_custom_icon("Gagal", 'File upload kosong', base_url('assets/images/emot-sedih.jpg'), "rounded-circle","false");
        }
		redirect("create_wos");
    }

	public function clear_sp()
	{
		$clear_tabungan = $this->model->delete_heijunka("create_wos", "No !=");
		$this->voice("sukses.mp3");
		$this->swal_custom_icon("Sukses", "Data Berhasil Dibersihkan", base_url('assets/images/happy.png'), "","true");
		redirect("create_wos");
	}

    public function filtering_color($tipe)
    {
        if($tipe == "page"){
            $data["dataKap1"] = $this->model->gds("filtering_color","*","plant = 'KAP1'","result");
            $data["dataKap2"] = $this->model->gds("filtering_color","*","plant = 'KAP2'","result");
            $data["title"] = "Filtering Color";
            // $data["javascript"] = "filtering_color";
            $data["content"] = "view/filtering_color";
            $this->load->view('layout/index',$data);
        }

        if($tipe == "add"){
            $plant = $this->input->get("p");
            $color = $this->input->post("color");
            $plantInput = $plant == "kap1" ? "KAP1" : "KAP2";
            $data = [
                "plant" => $plantInput,
                "nilai" => strtoupper($color),
            ];
            //CHECK DATA
            $check = $this->model->gds("filtering_color","id","plant = '$plantInput' AND nilai = '".strtoupper($color)."'","row");
            if(!empty($check)){
                $this->voice("gagal.mp3");
                $this->swal_custom_icon("Gagal", "Data Sudah Ada", base_url('assets/images/emot-sedih.jpg'), "rounded-circle","false");
                redirect("filtering_color/page");
            }
            
            $proses = $this->model->insert("filtering_color",$data);
            if($proses){
                $this->voice("gagal.mp3");
                $this->swal_custom_icon("Gagal", "Data Gagal di input", base_url('assets/images/emot-sedih.jpg'), "rounded-circle","false");
            }else{
                $this->voice("sukses.mp3");
                $this->swal_custom_icon("Sukses", "Data Berhasil di input", base_url('assets/images/happy.png'), "","true");
            }
            redirect("filtering_color/page");
        }

        if($tipe == "delete"){
            $id = $this->input->get("i");
            $proses = $this->model->delete("filtering_color","id = '$id'");
            if($proses){
                $this->voice("gagal.mp3");
                $this->swal_custom_icon("Gagal", "Data Gagal di hapus", base_url('assets/images/emot-sedih.jpg'), "rounded-circle","false");
            }else{
                $this->voice("sukses.mp3");
                $this->swal_custom_icon("Sukses", "Data Berhasil di hapus", base_url('assets/images/happy.png'), "","true");
            }
            redirect("filtering_color/page");
        }
    }

    public function filtering_suffix($tipe)
    {
        if($tipe == "page"){
            $data["dataKap1"] = $this->model->gds("filtering_suffix","*","plant = 'KAP1'","result");
            $data["dataKap2"] = $this->model->gds("filtering_suffix","*","plant = 'KAP2'","result");
            $data["title"] = "Filtering Suffix";
            // $data["javascript"] = "filtering_suffix";
            $data["content"] = "view/filtering_suffix";
            $this->load->view('layout/index',$data);
        }

        if($tipe == "add"){
            $plant = $this->input->get("p");
            $color = $this->input->post("color");
            $plantInput = $plant == "kap1" ? "KAP1" : "KAP2";
            $data = [
                "plant" => $plantInput,
                "nilai" => strtoupper($color),
            ];
            //CHECK DATA
            $check = $this->model->gds("filtering_suffix","id","plant = '$plantInput' AND nilai = '".strtoupper($color)."'","row");
            if(!empty($check)){
                $this->voice("gagal.mp3");
                $this->swal_custom_icon("Gagal", "Data Sudah Ada", base_url('assets/images/emot-sedih.jpg'), "rounded-circle","false");
                redirect("filtering_suffix/page");
            }
            
            $proses = $this->model->insert("filtering_suffix",$data);
            if($proses){
                $this->voice("gagal.mp3");
                $this->swal_custom_icon("Gagal", "Data Gagal di input", base_url('assets/images/emot-sedih.jpg'), "rounded-circle","false");
            }else{
                $this->voice("sukses.mp3");
                $this->swal_custom_icon("Sukses", "Data Berhasil di input", base_url('assets/images/happy.png'), "","true");
            }
            redirect("filtering_suffix/page");
        }

        if($tipe == "delete"){
            $id = $this->input->get("i");
            $proses = $this->model->delete("filtering_suffix","id = '$id'");
            if($proses){
                $this->voice("gagal.mp3");
                $this->swal_custom_icon("Gagal", "Data Gagal di hapus", base_url('assets/images/emot-sedih.jpg'), "rounded-circle","false");
            }else{
                $this->voice("sukses.mp3");
                $this->swal_custom_icon("Sukses", "Data Berhasil di hapus", base_url('assets/images/happy.png'), "","true");
            }
            redirect("filtering_suffix/page");
        }
    }

    public function wos_duplicate_checking()
    {
        $statuskap1 = $this->model->gds("checking_wos","pdd_input","kap = '1' GROUP BY pdd_input","result");
        $pddinputkap1 = [];
        foreach ($statuskap1 as $statuskap1) {
            $pddinputkap1[] = $statuskap1->pdd_input;
        }
        $statuskap2 = $this->model->gds("checking_wos","pdd_input","kap = '2' GROUP BY pdd_input","result");
        $pddinputkap2 = [];
        foreach ($statuskap2 as $statuskap2) {
            $pddinputkap2[] = $statuskap2->pdd_input;
        }
        $data["pddinputkap1"] = json_encode($pddinputkap1);
        $data["pddinputkap2"] = json_encode($pddinputkap2);
        $data["title"] = "WOS Duplicate Checking";
        $data["content"] = "view/wos_duplicate_checking";
        $data["javascript"] = "wos_duplicate_checking";
        $this->load->view('layout/index',$data);
    }

    function getDataVINChecking($kap)
    {
        $data = $this->model->gds("checking_wos","vin,suffix,model,pdd,pdd_input","kap = '$kap' AND STR_TO_DATE(pdd_input, '%Y-%m-%d') = (SELECT MAX(STR_TO_DATE(pdd_input, '%Y-%m-%d')) FROM checking_wos)","result");
        $fb = ["statusCode" => 200, "data" => $data];
        echo json_encode($fb);
        die();    
    }

    function checkUpload()
    {
        $pddInput = $this->input->get("pdd");
        $plant = $this->input->get("plant");
        //CHECK PDD
        $checkPDD = $this->model->gds("checking_wos","vin","pdd_input = '$pddInput' AND kap = '$plant'","row");
        $fb = ["statusCode" => 200];
        if(!empty($checkPDD)){
            $fb = ["StatusCode" => 500, "res" => "PDD ".date("d-M-Y",strtotime($pddInput))." KAP ".$plant." sudah ada di database, apakah anda ingin melanjutkan proses upload?<br>Jika Iya maka data lama akan kami timpa"];
        }
        echo json_encode($fb);
        die();
    }

    function upload_wos_duplicate_checking()
    {
        header("Content-Type: application/json");
        $this->session->unset_userdata(["duplicate_data","search_vin"]);
        if (isset($_FILES["upload-file"]["name"])) {
            $plant = $this->input->post("plant");
            $pddInput = $this->input->post("pdd");
            $reupload = $this->input->post("reupload");
            $path = $_FILES["upload-file"]["tmp_name"];
            $object = PHPExcel_IOFactory::load($path);
            $temp_data = [];
            $duplicate_data = [];

            //DELETE DATA
            if($reupload == "1"){
                $this->model->delete("checking_wos","kap = '$plant' AND pdd_input = '$pddInput'");
            }
            $dataVIN = $this->model->gds("checking_wos","vin,model,pdd_input as pdd","vin != ''","result_array");
            $vinModelList = array_map(function($r){ return $r['vin'].'|'.$r['model']; }, $dataVIN);
            $pddList = array_column($dataVIN, 'pdd');

            // Fetch all SAPNIK from bank_vlt for validation
            $bankVltData = $this->model->gds("bank_vlt","sapnik","sapnik != ''","result_array");
            $bankVltList = array_column($bankVltData, 'sapnik');

            foreach ($object->getWorksheetIterator() as $worksheet) {
                $highestRow = ($worksheet->getHighestRow()-1);
                for ($row = 2; $row <= $highestRow; $row++) {
					$sapnik = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
					$suffix = $worksheet->getCellByColumnAndRow(10, $row)->getValue();
					$admProdID = $worksheet->getCellByColumnAndRow(14, $row)->getValue();
					$color = $worksheet->getCellByColumnAndRow(21, $row)->getValue();
					$pdd = date("Y-m-d",strtotime($worksheet->getCellByColumnAndRow(16, $row)->getValue()));
					$model = $worksheet->getCellByColumnAndRow(22, $row)->getValue();
                    $bulanPDD = substr($admProdID, 2, 2);
                    $tanggalPDD = substr($admProdID, 4, 2);
                    $datePDD = $bulanPDD."-".$tanggalPDD;
                    $pddDb = date("Y")."-".$bulanPDD."-".$tanggalPDD;

                    if(empty($sapnik)){
                        continue;
                    }

                    if(date("m-d",strtotime($pddInput)) != $datePDD){
                        $this->swal_custom_icon("Gagal", 'PDD yang anda pilih dan file yang anda masukkan berbeda.', base_url('assets/images/emot-sedih.jpg'), "rounded-circle","false");
                        redirect('wos_duplicate_checking');
                    }

                    //CHECK APAKAH ADA VIN + MODEL YANG SAMA
                    $vinModelKey = $sapnik.'|'.$model;
                    if(!in_array($vinModelKey, $vinModelList)){
                        $temp_data[] = array(
                            'vin' => $sapnik,
                            'suffix' => $suffix,
                            'color' => $color,
                            'model' => $model,
                            'pdd' => $pdd,
                            'pdd_input' => $pddDb,
                            'kap' => $plant,
                        );
                    }else{
                        $duplicate_data[] = array(
                            'vin' => $sapnik,
                            'suffix' => $suffix,
                            'color' => $color,
                            'model' => $model,
                            'pdd' => $pdd,
                            'pdd_input' => $pddDb,
                            'pdd_duplicate' => $pddList[array_search($vinModelKey, $vinModelList)],
                            'kap' => 'KAP '.$plant,
                        );
                    }
                }
            }

            if(empty($duplicate_data)){
                $upload_wos = $this->model->insert_batch("checking_wos", $temp_data);
				$this->voice("sukses.mp3");
                $this->swal_custom_icon("Sukses", "Sukses Upload dan tidak ada data yang duplicate", base_url('assets/images/happy.png'), "","true");
            }else{
                $this->voice("gagal.mp3");
                $this->swal_custom_icon("Gagal", 'Terdapat data VIN yang double', base_url('assets/images/emot-sedih.jpg'), "rounded-circle","false");
                $dataSess = ["duplicate_data" => $duplicate_data];
                $this->session->set_userdata($dataSess);
            }
        }else{
            $this->voice("gagal.mp3");
            $this->swal_custom_icon("Gagal", 'File upload kosong', base_url('assets/images/emot-sedih.jpg'), "rounded-circle","false");
        }
		redirect("wos_duplicate_checking");  
    }

    function downloadDataDuplicate()
    {
        $data = $this->session->userdata("duplicate_data");
        $table = '<table border="1" style="border-collapse: collapse;">';
        $table .= '<tr>';
        $table .= '<th>No</th>';
        $table .= '<th>Line</th>';
        $table .= '<th>SAPNIK</th>';
        $table .= '<th>Suffix</th>';
        $table .= '<th>Model</th>';
        $table .= !empty($this->session->userdata("search_vin")) ? '<th>PDD</th>' : '<th>PDD Duplicate</th>';
        $table .= '</tr>';
        $no = 1;
        foreach ($data as $data) {
            $table .= '<tr>';
            $table .= '<td>'.$no++.'</td>';
            $table .= '<td>'.$data["kap"].'</td>';
            $table .= '<td>'.$data["vin"].'</td>';
            $table .= '<td>'.$data["suffix"].'</td>';
            $table .= '<td>'.$data["model"].'</td>';
            $table .= '<td>'.date("d M Y",strtotime($data["pdd_duplicate"])).'</td>';
            $table .= '</tr>';
        }
        $table .= '</table>';
        echo $table;
        $name_file = !empty($this->session->userdata("search_vin")) ? "SEARCH VIN.xls" : "VIN DUPLICATE PDD ".date("d-m-Y",strtotime($data["pdd_input"])).".xls";
        header ("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment;filename = ".$name_file);
        die(); 
    }

    function clearDataDouble()
    {
        $this->session->unset_userdata(["duplicate_data","search_vin"]);
        $fb = ["statusCode" => 200];
        echo json_encode($fb);
        die();    
    }

    function search_vin()
    {
        $this->session->unset_userdata(["duplicate_data","search_vin"]);
        $vin_search = $this->input->post("vin_search");
        $vinExplode = array_filter(explode("\n",$vin_search));
        $vinExplode = array_map('trim',$vinExplode);
        $dataVin = "'".implode("','",$vinExplode)."'";
        $searchVin = $this->model->gds("checking_wos","*","vin IN ($dataVin)","result");
        $dataSearch = [];
        if(!empty($searchVin)){
            foreach ($searchVin as $data) {
                $dataSearch[] = array(
                    'vin' => $data->vin,
                    'suffix' => $data->suffix,
                    'model' => $data->model,
                    'pdd' => $data->pdd,
                    'pdd_input' => $data->pdd_input,
                    'pdd_duplicate' => $data->pdd_input,
                    'kap' => 'KAP '.$data->kap,
                );
            }
        }
        $this->session->set_userdata(["duplicate_data" => $dataSearch, "search_vin" => "yes"]);
        redirect("wos_duplicate_checking");
    }

    function downloadVIN()
    {
        $start = $this->input->post("start");
        $end = $this->input->post("end");
        $line = str_replace("KAP ","",$this->input->post("line-download"));
        $getData = $this->model->gds("checking_wos","*","kap = '$line' AND pdd_input BETWEEN '$start' AND '$end'","result");

        $name_file = "DOWNLOAD DATA VIN KAP ".$line." PDD ".date("d-m-Y",strtotime($start))." - ".date("d-m-Y",strtotime($end)).".xls";

        // Set header lebih dulu sebelum ada output
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$name_file\"");
        header("Pragma: no-cache");
        header("Expires: 0");

        // Buat tabel
        echo '<table border="1" style="border-collapse: collapse;">';
        echo '<tr>';
        echo '<th>No</th>';
        echo '<th>Line</th>';
        echo '<th>SAPNIK</th>';
        echo '<th>Suffix</th>';
        echo '<th>Model</th>';
        echo '<th>PDD</th>';
        echo '</tr>';
        
        $no = 1;
        if (!empty($getData)) {
            foreach ($getData as $data) {
                echo '<tr>';
                echo '<td>'.$no++.'</td>';
                echo '<td>KAP '.$data->kap.'</td>';
                echo '<td>'.$data->vin.'</td>';
                echo '<td>'.$data->suffix.'</td>';
                echo '<td>'.$data->model.'</td>';
                echo '<td>'.date("d M Y",strtotime($data->pdd_input)).'</td>';
                echo '</tr>';
            }
        }
        echo '</table>';

        // Hentikan eksekusi agar tidak ada output tambahan yang merusak file Excel
        exit;
    }

	function dummy_process_kap()
	{
        $start_no = empty($this->input->get("start_no")) ? 1 : $this->input->get("start_no");
        $pdd = $this->session->userdata("pdd_pis_kap1");
        if(empty($pdd)){
            $this->swal_custom_icon("Gagal", 'Sepertinya anda belum download PIS atau HardCopy, Silahkan download terlebih dahulu.', base_url('assets/images/emot-sedih.jpg'), "rounded-circle","false");
            redirect("heijunka_wos");
        }
		header('Content-Type: application/json');
		//DELETE D74A LINK
		$this->model->delete_heijunka("master","SAPNIK LIKE '%D74LINK%'");
		$this->model->delete_heijunka("history","Heijunka = 'D74A-LINK'");

        $new_data_wos = [];
        $check_batch = $this->model->gds_heijunka("master","batch","SAPNIK != '' GROUP BY batch ORDER BY batch ASC","result");
        $no_loop = $start_no;
        foreach ($check_batch as $check_batch) {
            $batch = $check_batch->batch;
            $total_d74a_link = $this->model->gds_heijunka("master","COUNT(SAPNIK) as total","Model = 'D74A' AND batch = '$batch'","row");
            $total_master_kap2 = $this->model->gds_heijunka("master_kap2","COUNT(SAPNIK) as total","SAPNIK !='' AND batch = '$batch'","row");
            // //POS
            // if(empty($total_master_kap2->total)){
            //     $this->swal_custom_icon("Gagal", 'WOS KAP 1 belum ada, silahkan buat WOS KAP 1 terlebih dahulu.', base_url('assets/images/emot-sedih.jpg'), "rounded-circle","false");
            //     redirect("heijunka_wos");
            // }

            $insert_d74a_link = [];
            $data_wos = [];
            $master = $this->model->gds_heijunka("master","*","Model = 'D74A' AND batch = '$batch' ORDER BY No DESC","result");
            $count_wos = count($master);
            //CHANGE VIN & MODEL TO DUMMY
            if(!empty($master)){
                foreach ($master as $master) {
                    $pid = "3Z".date("md",strtotime($pdd)).sprintf("%04d",$no_loop);
                    $model = $master->Model;
                    $new_vin = "D74LINK".$pid;
                    $insert_d74a_link[] = [
                        "WOS_Material" => $master->WOS_Material,
                        "WOS_Material_Description" => $master->WOS_Material_Description,
                        "SAPNIK" => $new_vin,
                        "SAP_Material" => $master->SAP_Material,
                        "Engine_Model" => $master->Engine_Model,
                        "Engine_Prefix" => $master->Engine_Prefix,
                        "Engine_Number" => $master->Engine_Number,
                        "Plant" => $master->Plant,
                        "Chassis_Number" => " ".$new_vin." ",
                        "Lot_Code" => $master->Lot_Code,
                        "Lot_Number" => $master->Lot_Number,
                        "Katashiki" => $master->Katashiki,
                        "Katashiki_Sfx" => $master->Katashiki_Sfx,
                        "ADM_Production_Id" => $pid,
                        "TAM_Production_Id" => $master->TAM_Production_Id,
                        "Plan_Delivery_Date" => date("d-M-Y", strtotime($master->Plan_Delivery_Date)),
                        "Plan_Jig_In_Date" => date("d-M-Y", strtotime($master->Plan_Jig_In_Date)),
                        "WOS_Release_Date" => $master->WOS_Release_Date,
                        "SAPWOS_DES" => $master->SAPWOS_DES,
                        "Location" => 'No',
                        "Color_Code" => $master->Color_Code,
                        "tone" => $master->tone,
                        "Model" => "X10X",
                        "Model_Name" => $master->Model_Name,
                        "ED" => $master->ED,
                        "Order" => $master->Order,
                        "Dest" => $master->Dest,
                        "Transmisi" => $master->Transmisi,
                        "Color" => $master->Color,
                        "Bot_Color" => $master->Bot_Color,
                        "Family_Model" => $master->Family_Model,
                        "model_both" => $master->model_both,
                        "heijunka_tone" => "D74A-LINK",
                        "sapnik_ori" => $master->SAPNIK,
                        "batch" => $batch,
                    ];
                    $no_loop++;
                }
				$data_wos = array_merge($data_wos, $insert_d74a_link); // Gabungkan hasil
				$data_wos = array_reverse($data_wos);
                $no = 1;
                $pos = number_format($total_master_kap2->total / $total_d74a_link->total,2,".",",");
                foreach ($data_wos as $key => $value) {
                    $new_no = $pos*$no;
                    $data_wos[$no-1]["No"] = $new_no;
                    $no++;
                }
                $new_data_wos[$batch] = $data_wos;
            }
        }
        if(!empty($data_wos)){
            //INSERT DATA
            foreach ($new_data_wos as $key => $value) {
                $this->model->insert_batch_heijunka("master_kap2",$value);
            }
            $update_history = [
                "Heijunka" => "D74A-LINK",
                "Status" => "Sukses",
            ];
            $this->model->insert_heijunka("history",$update_history);
        }else{
            $this->swal_custom_icon("Gagal", 'Sistem tidak mendapatkan data KAP 2, mohon coba kembali.', base_url('assets/images/emot-sedih.jpg'), "rounded-circle","false");
            redirect("heijunka_wos");
        }
		$this->swal_custom_icon("Sukses", "Proses update D74A LINK berhasil", base_url('assets/images/happy.png'), "","true");
		redirect("heijunka_wos_kap2?no_dummy=no");
	}

	function heijunka_change_order()
	{
		$getMaster = $this->model->gds_heijunka("master","SAPNIK as sapnik, Lot_Code as suffix,fixed_dum","SAPNIK != '' ORDER BY Plan_Delivery_Date ASC","result_array");
		$getPIS = $this->model->gds("pis_kap1","SAPNIK as sapnik, Lot_Code as suffix, No","SAPNIK != '' ORDER BY Plan_Delivery_Date ASC","result_array");

		$pisBySuffix = [];
		foreach ($getPIS as $pis) {
			$suffix = $pis["suffix"];
			// bisa simpan banyak nilai "No" per suffix
			$pisBySuffix[$suffix][] = $pis["No"];
		}

		$result = [];
		foreach ($getMaster as $master) {
			$suffix = $master["suffix"];
			$fixed_dum = $master["fixed_dum"];
			if($fixed_dum <= 0){
				$no = null;
	
				// cek apakah suffix ini punya data No
				if (!empty($pisBySuffix[$suffix])) {
					// ambil elemen pertama
					$no = array_shift($pisBySuffix[$suffix]);
	
					// kalau udah kosong, hapus key-nya biar rapi
					if (empty($pisBySuffix[$suffix])) {
						unset($pisBySuffix[$suffix]);
					}
				}
	
				$result[] = [
					"SAPNIK" => $master["sapnik"],
					"Lot_Code" => $suffix,
					"No" => (float)$no,
					"Heijunka_Suffix" => (float)$no,
					"Heijunka_Sub_Model" => (float)$no,
					"Heijunka_Model" => (float)$no,
					"heijunka_family" => (float)$no,
				];
			}
		}
		if(!empty($result)){
			$this->db_heijunka = $this->load->database('db_heijunka_wos', TRUE);
			$this->db_heijunka->update_batch('master', $result, 'SAPNIK');
	
			//REVERSE DATA MASTER
			$getDataMaster = $this->model->gds_heijunka("master","No,SAPNIK","SAPNIK != '' ORDER BY No DESC","result_array");
			$nomor = 1;
			$resultReverse = [];
			foreach ($getDataMaster as $getDataMaster) {
				$resultReverse[] = [
					"SAPNIK" => $getDataMaster["SAPNIK"],
					"No" => $nomor,
					"Heijunka_Suffix" => $nomor,
					"Heijunka_Sub_Model" => $nomor,
					"Heijunka_Model" => $nomor,
					"heijunka_family" => $nomor,
					"fixed_dum" => 1,
				];
				$nomor++;
			}
			$this->db_heijunka->update_batch('master', $resultReverse, 'SAPNIK');
		}
		$fb = ["status" => "sukses"];
        echo json_encode($fb);
		die();
	}

    public function aoc()
    {
        $modelRows = $this->db->query("SELECT DISTINCT model FROM checking_wos WHERE model IS NOT NULL AND model != '' ORDER BY model")->result_array();
        $data["modelList"] = array_column($modelRows, 'model');
        $data["title"]      = "AOC";
        $data["content"]    = "view/aoc";
        $data["javascript"] = "aoc";
        $this->load->view('layout/index', $data);
    }

    public function getAocData()
    {
        $kap    = $this->db->escape_str($this->input->get("kap"));
        $result = $this->db->query(
            "SELECT id, vin, suffix, color, model, pdd_input, tentative, firm, 'checking_wos' AS source
             FROM (SELECT id, vin, suffix, color, model, pdd_input, tentative, firm
                   FROM checking_wos WHERE kap = '$kap' ORDER BY id DESC LIMIT 5000) t
             UNION ALL
             SELECT id, vin, suffix, color, model, NULL AS pdd_input,
                    CASE WHEN type = 'tentative' THEN order_date ELSE NULL END AS tentative,
                    CASE WHEN type = 'firm'      THEN order_date ELSE NULL END AS firm,
                    'temporary' AS source
             FROM temporary_data_tentative WHERE kap = '$kap'
             ORDER BY source ASC, id ASC"
        )->result();
        echo json_encode(["statusCode" => 200, "data" => $result]);
        die();
    }

    public function getContinuationPoint()
    {
        $kap  = $this->db->escape_str($this->input->get("kap"));
        $type = $this->input->get("type"); // tentative | firm

        $rows = $this->db->query(
            "SELECT id, tentative, firm
             FROM (SELECT id, tentative, firm FROM checking_wos WHERE kap = '$kap' ORDER BY id DESC LIMIT 5000) t
             ORDER BY id ASC"
        )->result_array();

        $last_firm_pos      = 0;
        $last_tentative_pos = 0;
        foreach ($rows as $i => $row) {
            $pos = $i + 1;
            if (!empty($row['firm']))      $last_firm_pos      = $pos;
            if (!empty($row['tentative'])) $last_tentative_pos = $pos;
        }

        if ($type === "tentative") {
            $start_pos = ($last_firm_pos < $last_tentative_pos)
                ? $last_firm_pos + 1
                : $last_tentative_pos + 1;
        } else {
            $start_pos = $last_firm_pos + 1;
        }

        $total     = count($rows);
        $available = max(0, $total - $start_pos + 1);

        echo json_encode([
            "statusCode"         => 200,
            "start_pos"          => $start_pos,
            "last_firm_pos"      => $last_firm_pos,
            "last_tentative_pos" => $last_tentative_pos,
            "available"          => $available,
            "total"              => $total,
        ]);
        die();
    }

    public function confirmAocUpdate()
    {
        $kap          = $this->input->post("kap");
        $ids          = json_decode($this->input->post("ids"),          true) ?: [];
        $overflow_ids = json_decode($this->input->post("overflow_ids"), true) ?: [];
        $type         = $this->input->post("type");
        $date         = $this->input->post("date");

        if (!in_array($type, ["tentative", "firm"]) || empty($date)) {
            echo json_encode(["statusCode" => 400, "msg" => "Input tidak valid"]);
            die();
        }

        $summary          = [];
        $overflow_inserted = 0;

        // Regular rows → update checking_wos
        if (!empty($ids)) {
            $rows = $this->db->select("id, model")
                ->from("checking_wos")
                ->where_in("id", $ids)
                ->get()->result_array();

            $this->db->where_in("id", $ids);
            $this->db->update("checking_wos", [$type => $date]);

            foreach ($rows as $r) {
                $m = ($r['model'] ?: '-');
                $summary[$m] = ($summary[$m] ?? 0) + 1;
            }
        }

        // Overflow rows → copy into temporary_data_tentative
        if (!empty($overflow_ids)) {
            $ovRows = $this->db->select("vin, suffix, color, model")
                ->from("checking_wos")
                ->where_in("id", $overflow_ids)
                ->get()->result_array();

            foreach ($ovRows as $r) {
                $this->db->insert("temporary_data_tentative", [
                    "vin"        => $r['vin'],
                    "suffix"     => $r['suffix'],
                    "color"      => $r['color'],
                    "model"      => $r['model'],
                    "kap"        => $kap,
                    "order_date" => $date,
                    "type"       => $type,
                ]);
                $m = ($r['model'] ?: '-');
                $summary[$m] = ($summary[$m] ?? 0) + 1;
                $overflow_inserted++;
            }
        }

        ksort($summary);
        echo json_encode([
            "statusCode"        => 200,
            "summary"           => $summary,
            "updated"           => count($ids),
            "overflow_inserted" => $overflow_inserted,
        ]);
        die();
    }

    public function getOverflowPreview()
    {
        $kap   = $this->db->escape_str($this->input->get("kap"));
        $date  = $this->db->escape_str($this->input->get("date"));
        $count = (int)$this->input->get("count");

        $two_days_back = date("Y-m-d", strtotime($date . " -2 days"));

        $models = json_decode($this->input->get("models"), true);

        $this->db->select("id, vin, suffix, color, model, pdd_input, tentative, firm")
            ->from("checking_wos")
            ->where("kap", $kap)
            ->where("pdd_input", $two_days_back);
        if (is_array($models) && !empty($models)) {
            $this->db->where_in("model", $models);
        }
        $result = $this->db->order_by("id", "ASC")
            ->limit($count)
            ->get()->result();

        echo json_encode([
            "statusCode" => 200,
            "data"       => $result,
            "pdd_used"   => $two_days_back,
        ]);
        die();
    }

    public function getAocHistory()
    {
        $kap = $this->db->escape_str($this->input->get("kap"));

        // From checking_wos
        $tentRows = $this->db->query(
            "SELECT tentative AS order_date, COUNT(*) AS total
             FROM checking_wos
             WHERE kap = '$kap' AND tentative IS NOT NULL AND tentative != '0000-00-00'
             GROUP BY tentative ORDER BY tentative DESC LIMIT 30"
        )->result_array();

        $firmRows = $this->db->query(
            "SELECT firm AS order_date, COUNT(*) AS total
             FROM checking_wos
             WHERE kap = '$kap' AND firm IS NOT NULL AND firm != '0000-00-00'
             GROUP BY firm ORDER BY firm DESC LIMIT 30"
        )->result_array();

        // From temporary_data_tentative
        $tmpRows = $this->db->query(
            "SELECT order_date, type, COUNT(*) AS total
             FROM temporary_data_tentative
             WHERE kap = '$kap'
             GROUP BY order_date, type ORDER BY order_date DESC LIMIT 30"
        )->result_array();

        $merged = [];
        foreach ($tentRows as $r) {
            $d = $r['order_date'];
            if (!isset($merged[$d])) $merged[$d] = ['order_date' => $d, 'total_tentative' => 0, 'total_firm' => 0];
            $merged[$d]['total_tentative'] += (int)$r['total'];
        }
        foreach ($firmRows as $r) {
            $d = $r['order_date'];
            if (!isset($merged[$d])) $merged[$d] = ['order_date' => $d, 'total_tentative' => 0, 'total_firm' => 0];
            $merged[$d]['total_firm'] += (int)$r['total'];
        }
        foreach ($tmpRows as $r) {
            $d = $r['order_date'];
            if (!isset($merged[$d])) $merged[$d] = ['order_date' => $d, 'total_tentative' => 0, 'total_firm' => 0];
            if ($r['type'] === 'tentative') $merged[$d]['total_tentative'] += (int)$r['total'];
            else                            $merged[$d]['total_firm']      += (int)$r['total'];
        }

        usort($merged, function ($a, $b) { return strcmp($b['order_date'], $a['order_date']); });

        echo json_encode(["statusCode" => 200, "data" => array_values($merged)]);
        die();
    }

    // Build per-model Tentative/Firm breakdown for one KAP + order date.
    // Shared by the Detail Order modal (JSON) and the Excel download.
    private function aocHistoryDetailRows($kap, $date)
    {
        // checking_wos
        $cwRows = $this->db->query(
            "SELECT model,
                    SUM(CASE WHEN tentative = '$date' THEN 1 ELSE 0 END) AS total_tentative,
                    SUM(CASE WHEN firm      = '$date' THEN 1 ELSE 0 END) AS total_firm
             FROM checking_wos
             WHERE kap = '$kap' AND (tentative = '$date' OR firm = '$date')
             GROUP BY model"
        )->result_array();

        // temporary_data_tentative
        $tmpRows = $this->db->query(
            "SELECT model,
                    SUM(CASE WHEN type = 'tentative' THEN 1 ELSE 0 END) AS total_tentative,
                    SUM(CASE WHEN type = 'firm'      THEN 1 ELSE 0 END) AS total_firm
             FROM temporary_data_tentative
             WHERE kap = '$kap' AND order_date = '$date'
             GROUP BY model"
        )->result_array();

        $merged = [];
        foreach ($cwRows as $r) {
            $m = $r['model'] ?: '-';
            if (!isset($merged[$m])) $merged[$m] = ['model' => $m, 'total_tentative' => 0, 'total_firm' => 0];
            $merged[$m]['total_tentative'] += (int)$r['total_tentative'];
            $merged[$m]['total_firm']      += (int)$r['total_firm'];
        }
        foreach ($tmpRows as $r) {
            $m = $r['model'] ?: '-';
            if (!isset($merged[$m])) $merged[$m] = ['model' => $m, 'total_tentative' => 0, 'total_firm' => 0];
            $merged[$m]['total_tentative'] += (int)$r['total_tentative'];
            $merged[$m]['total_firm']      += (int)$r['total_firm'];
        }

        ksort($merged);
        return array_values($merged);
    }

    public function getAocHistoryDetail()
    {
        $kap  = $this->db->escape_str($this->input->get("kap"));
        $date = $this->db->escape_str($this->input->get("date"));

        $data = $this->aocHistoryDetailRows($kap, $date);
        echo json_encode(["statusCode" => 200, "data" => $data, "date" => $date]);
        die();
    }

    public function downloadAocHistoryDetail()
    {
        $kap  = $this->db->escape_str($this->input->get("kap"));
        $date = $this->db->escape_str($this->input->get("date"));

        $rows = $this->aocHistoryDetailRows($kap, $date);

        $this->load->library('excel');
        $excel = new PHPExcel();
        $sheet = $excel->setActiveSheetIndex(0);
        $sheet->setTitle('Detail Order');

        // Blue palette
        $cBlueDark  = '1F4E78'; // title banner / total row
        $cBlueMid   = '2E75B6'; // table header
        $cBlueLight = 'D9E1F2'; // zebra stripe
        $cBorder    = '8EAADB';
        $white      = 'FFFFFF';

        // ── Title banner (A1:D1) ─────────────────────────────────────────────
        $sheet->mergeCells('A1:D1');
        $sheet->setCellValue('A1', 'DETAIL ORDER AOC');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 16, 'color' => ['rgb' => $white]],
            'fill'      => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => ['rgb' => $cBlueDark]],
            'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // ── Subtitle (A2:D2) ─────────────────────────────────────────────────
        $sheet->mergeCells('A2:D2');
        $sheet->setCellValue('A2', 'KAP ' . $kap . '   |   Tanggal Order: ' . $date);
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 11, 'color' => ['rgb' => $white]],
            'fill'      => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => ['rgb' => $cBlueMid]],
            'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(20);

        // ── Table header (row 4) ─────────────────────────────────────────────
        $headRow = 4;
        $sheet->setCellValue('A' . $headRow, 'Model');
        $sheet->setCellValue('B' . $headRow, 'Tentative');
        $sheet->setCellValue('C' . $headRow, 'Firm');
        $sheet->setCellValue('D' . $headRow, 'Total');
        $sheet->getStyle("A{$headRow}:D{$headRow}")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 11, 'color' => ['rgb' => $white]],
            'fill'      => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => ['rgb' => $cBlueMid]],
            'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension($headRow)->setRowHeight(20);

        // ── Data rows ────────────────────────────────────────────────────────
        $rowNum   = $headRow + 1;
        $firstRow = $rowNum;
        $sumTent  = 0;
        $sumFirm  = 0;
        $stripe   = false;
        foreach ($rows as $r) {
            $tent = (int)$r['total_tentative'];
            $firm = (int)$r['total_firm'];
            $sheet->setCellValue('A' . $rowNum, $r['model']);
            $sheet->setCellValue('B' . $rowNum, $tent);
            $sheet->setCellValue('C' . $rowNum, $firm);
            $sheet->setCellValue('D' . $rowNum, $tent + $firm);

            if ($stripe) {
                $sheet->getStyle("A{$rowNum}:D{$rowNum}")->getFill()
                      ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                      ->getStartColor()->setRGB($cBlueLight);
            }
            $stripe = !$stripe;

            $sumTent += $tent;
            $sumFirm += $firm;
            $rowNum++;
        }

        // ── Total row ────────────────────────────────────────────────────────
        $sheet->setCellValue('A' . $rowNum, 'TOTAL');
        $sheet->setCellValue('B' . $rowNum, $sumTent);
        $sheet->setCellValue('C' . $rowNum, $sumFirm);
        $sheet->setCellValue('D' . $rowNum, $sumTent + $sumFirm);
        $sheet->getStyle("A{$rowNum}:D{$rowNum}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => $white]],
            'fill' => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => ['rgb' => $cBlueDark]],
        ]);
        $sheet->getRowDimension($rowNum)->setRowHeight(20);

        // ── Borders & alignment across the whole table ───────────────────────
        $sheet->getStyle("A{$headRow}:D{$rowNum}")->getBorders()->getAllBorders()
              ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN)
              ->getColor()->setRGB($cBorder);
        // Model column left-aligned, numbers centered
        $sheet->getStyle("A{$firstRow}:A{$rowNum}")->getAlignment()
              ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle("B{$firstRow}:D{$rowNum}")->getAlignment()
              ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(16);
        $sheet->getColumnDimension('B')->setWidth(14);
        $sheet->getColumnDimension('C')->setWidth(14);
        $sheet->getColumnDimension('D')->setWidth(14);

        $excel->setActiveSheetIndex(0);
        if (ob_get_length()) ob_end_clean();
        $filename = "Detail_Order_AOC_KAP{$kap}_{$date}";
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename=' . $filename . '.xls');
        header('Cache-Control: max-age=0');
        $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $writer->save('php://output');
        exit();
    }
}
