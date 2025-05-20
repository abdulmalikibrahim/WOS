<?php
defined('BASEPATH') or exit('No direct script access allowed');
date_default_timezone_set('Asia/Jakarta');
include_once(dirname(__FILE__) . "/Construct.php");

class Tabungan extends Construct
{
	// public function month_import($params)
	// {
	// 	$month = date("m",strtotime("2022-".$params."-01"));
	// 	return $month;
	// }
	public function import_tabungan_vlt()
	{
		if(empty($this->input->get("t"))){
			$table_plan_wos = "plan_wos";
			$table_tabungan = "tabungan_vlt";
			$plant = "KAP1";
		}else{
			$table_plan_wos = "plan_wos_kap2";
			$table_tabungan = "tabungan_vlt_kap2";
			$plant = "KAP2";
		}
		//CHECK COLOR CODE FILTERING
		$color_code_filtering = $this->model->gds("filtering_color", "nilai", "plant = '$plant'", "result");
		$colorCodeFilter = !empty($color_code_filtering) ? json_encode($color_code_filtering) : "";
		$clear_data = $this->model->delete("twotone_setting", "suffix_pdd !=");
		$clear_data = $this->model->delete("singletone_setting", "suffix_pdd !=");
		if (isset($_FILES["upload-file"]["name"])) {
			$path = $_FILES["upload-file"]["tmp_name"];
			$object = PHPExcel_IOFactory::load($path);
			$unit_btm = "";
			foreach ($object->getWorksheetIterator() as $worksheet) {
				$highestRow = $worksheet->getHighestRow();
				$highestColumn = $worksheet->getHighestColumn();
				for ($row = 2; $row <= $highestRow; $row++) {
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
							if(empty($pdd[2])){
								$this->voice("gagal.mp3");
								$this->swal_custom_icon("Gagal", "Format PDD tidak sesuai", base_url('assets/images/emot-sedih.jpg'), "rounded-circle");
								redirect("tabungan");
							}
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
						$ed = $worksheet->getCellByColumnAndRow(22, $row)->getValue();
						$order_column = $worksheet->getCellByColumnAndRow(23, $row)->getValue();
						$destination = $worksheet->getCellByColumnAndRow(24, $row)->getValue();
						if(substr_count($lot_number,"BTM") > 0){
							$unit_btm .= "yes";
						}
						if (!empty($sapnik)) {
							if(substr_count($colorCodeFilter,$color_code) <= 0){
								$temp_data[] = array(
									'wos_material' => $wos_material,
									'wos_material_description' => $wos_material_description,
									'sapnik' => str_replace(" ", "", $sapnik),
									'sap_material' => $sap_material,
									'engine_model' => $engine_model,
									'engine_prefix' => $engine_prefix,
									'engine_number' => $engine_number,
									'plant' => $plant,
									'chassis_number' => $chassis_number,
									'lot_code' => $lot_code,
									'lot_number' => $lot_number,
									'katashiki' => $katashiki,
									'katashiki_suffix' => $katashiki_suffix,
									'adm_production_id' => $adm_production_id,
									'tam_production_id' => $tam_production_id,
									'plan_delivery_date' => $plan_delivery_date,
									'plan_jig_in_date' => $plan_jig_in_date,
									'wos_release_date' => $wos_release_date,
									'sapwos_des' => $sapwos_des,
									'location' => $location,
									'color_code' => $color_code,
									'ed' => $ed,
									'order_column' => $order_column,
									'destination' => $destination,
								);
							}
						}
					}
				}
			}
			$clear_data = $this->model->delete($table_tabungan, "sapnik !=");
			$insert = $this->model->insert_batch($table_tabungan, $temp_data);
			if ($insert) {
				$get_plan_suffix = $this->model->gds($table_plan_wos, "suffix,SUM(plan) as plan,model_name", "plan IS NOT NULL OR plan != '' GROUP BY suffix ORDER BY model_name ASC", "result");
				foreach ($get_plan_suffix as $gps) {
					$get_tabungan = $this->model->gds($table_tabungan, "COUNT(sapnik) AS tabungan", "katashiki_suffix = '" . $gps->suffix . "'", "row");
					if (!empty($get_tabungan->tabungan)) {
						$tabungan = $get_tabungan->tabungan;
					} else {
						$tabungan = 0;
					}
					if ($tabungan >= $gps->plan) {
						$status[] = [
							"status" => "OK",
							"unit" => "",
							"suffix" => "",
							"plan" => "",
							"tabungan" => ""
						];
					} else {
						$status[] = [
							"status" => "NOK",
							"unit" => $gps->model_name,
							"suffix" => $gps->suffix,
							"plan" => $gps->plan,
							"tabungan" => $tabungan
						];
					}
				}
				$nok_data = array_filter($status, function ($var) {
					return ($var['status'] == 'NOK');
				});
				if (!empty($nok_data)) {
					if(empty($this->input->get("p"))){
						$table_nok = "<table class='table table-sm table-hover table-bordered' style='font-size:10pt;'><tr><th>Model</th><th>Suffix</th><th>Plan</th><th>Tabungan</th></tr>";
						foreach ($nok_data as $key => $value) {
							$table_nok .= '<tr><td>' . $value["unit"] . '</td><td>' . $value["suffix"] . '</td><td>' . $value["plan"] . '</td><td>' . $value["tabungan"] . '</td></tr>';
						}
						$table_nok .= '</table>';
						$this->swal_custom_icon("Maaf...", "Stock Tabungan Tidak Cukup...<br>Cek kembali ke Menu Plan WOS<br><h5 align='left' class='mt-2 mb-2'>List Tabungan Tidak Cukup</h5>" . $table_nok, base_url('assets/images/emot-sedih.jpg'), "","true");
					}else{
						$this->voice("sukses.mp3");
						$this->swal_custom_icon("Sukses", "Upload tabungan VLT berhasil", base_url('assets/images/happy.png'), "","true");
					}
				} else {
					if(empty($this->input->get("p"))){
						$this->voice("sukses.mp3");
						if(empty($this->input->get("t"))){
							if(!empty($unit_btm)){
								$this->swal_custom_icon("Sukses","Unit Batam Terdeteksi...<br>Mohon berikan judgement terkait unit batam<br><a href='".base_url("adjust_twotone")."' class='btn btn-sm btn-info mt-4'>Adjust Twotone</a><a href='".base_url("delete_unit_batam?kap=1")."' class='btn btn-sm btn-danger ml-3 mt-4'>Delete Unit Batam</a><a href='javascript:void(0)' onclick='swal_close()' class='btn btn-sm btn-secondary mt-4 ml-3'>Tetap Disini</a>",base_url('assets/images/happy.png'),"","false");
							}else{
								$this->swal_custom_icon("Sukses","Stock Tabungan Cukup...<br>Klik Adjust Twotone untuk lanjutkan proses, atau klik tetap disini untuk upload tabungan yang lain.<br><a href='".base_url("adjust_twotone")."' class='btn btn-sm btn-info mt-4'>Adjust Twotone</a><a href='javascript:void(0)' onclick='swal_close()' class='btn btn-sm btn-secondary mt-4 ml-3'>Tetap Disini</a>",base_url('assets/images/happy.png'),"","false");
							}
						}else{
							$this->swal_custom_icon("Sukses","Stock Tabungan Cukup...<br>Proses upload tabungan KAP 2 berhasil. Silahkan lanjut proses docking.<br><a href='javascript:void(0)' data-tipe='kap2' onclick='docking(this)' class='btn btn-sm btn-info mt-4'>Docking</a><a href='javascript:void(0)' onclick='swal_close()' class='btn btn-sm btn-secondary mt-4 ml-3'>Tetap Disini</a>",base_url('assets/images/happy.png'),"","false");
						}
					}else{
						$this->voice("sukses.mp3");
						$this->swal_custom_icon("Sukses", "Upload tabungan VLT berhasil", base_url('assets/images/happy.png'), "","true");
					}
				}
			} else {
				$this->voice("gagal.mp3");
				$this->swal_custom_icon("Gagal", "Gagal import data tabungan", base_url('assets/images/emot-sedih.jpg'), "rounded-circle");
			}
		} else {
			$this->voice("gagal.mp3");
			$this->swal_custom_icon("Gagal", "Tidak ada file yang masuk", base_url('assets/images/emot-sedih.jpg'), "rounded-circle");
		}
		if(!empty($this->input->get("p"))){
			$this->session->set_userdata(array("tabungan_actual" => "YES"));
			redirect($this->input->get("p"));
		}else{
			redirect("tabungan");
		}
	}

	function delete_unit_batam()
	{
		$kap = $this->input->get("kap");
		$table = $kap == "1" ? "tabungan_vlt" : "tabungan_vlt_kap2";
		$this->model->delete($table,"lot_number LIKE '%BTM%'");
		$this->voice("sukses.mp3");
		$this->swal_custom_icon("Sukses","Unit Batam Terhapus...<br>Klik Adjust Twotone untuk lanjutkan proses, atau klik tetap disini untuk upload tabungan yang lain.<br><a href='".base_url("adjust_twotone")."' class='btn btn-sm btn-info mt-4'>Adjust Twotone</a><a href='javascript:void(0)' onclick='swal_close()' class='btn btn-sm btn-secondary mt-4 ml-3'>Tetap Disini</a>",base_url('assets/images/happy.png'),"","false");
		redirect("tabungan");
	}

	public function save_adjust()
	{
		header('Content-Type: application/json; charset=utf-8');
		$data = $this->input->post("input-data");
		$input_data = array_filter($data);
		$unit_single = 0;
		$unit_two = 0;
		foreach ($input_data as $key => $value) {
			$explode = explode("|", $key);
			$suffix = $explode[0];
			//CHECK APAKAH ADA 2 BATCH
			$check_batch = $this->model->gds("plan_wos","COUNT(*) as batch","suffix = '$suffix'","row");
			$pdd = $explode[1];
			$tone = $explode[2];
			if($tone == "singletone"){
				$data_insert_single[$key] = [
					"suffix_pdd" => $suffix."|".$pdd,
					"suffix" => $suffix,
					"pdd" => $pdd,
					"qty" => $value,
				];
				if($check_batch->batch > 1){
					$check_qty_batch = $this->model->gds("plan_wos","batch","suffix = '$suffix' AND plan = '$value'","row");
					if(!empty($check_qty_batch->batch)){
						$qty_batch1 = $check_qty_batch->batch == "1" ? $value : "0";
						$qty_batch2 = $check_qty_batch->batch == "2" ? $value : "0";
					}else{
						$check_qty_batch = $this->model->gds("plan_wos","plan","suffix = '$suffix' AND batch = '1'","row");
						if($check_qty_batch->plan > $value){
							$qty_batch1 = round($value*50/100,0);
						}else{
							$qty_batch1 = $check_qty_batch->plan;
						}
						$qty_batch2 = $value - $qty_batch1;
					}
					$data_insert_single[$key]["qty_batch_1"] = $qty_batch1;
					$data_insert_single[$key]["qty_batch_2"] = $qty_batch2;
				}else{
					$check_batch_avail = $this->model->gds("plan_wos","batch","suffix = '$suffix'","row");
					if($check_batch_avail->batch == "1"){
						$data_insert_single[$key]["qty_batch_1"] = $value;
						$data_insert_single[$key]["qty_batch_2"] = "0";
					}else{
						$data_insert_single[$key]["qty_batch_1"] = "0";
						$data_insert_single[$key]["qty_batch_2"] = $value;
					}
				}
				$unit_single += $value;
			}else{
				$data_insert_two[$key] = [
					"suffix_pdd" => $suffix."|".$pdd,
					"suffix" => $suffix,
					"pdd" => $pdd,
					"qty" => $value,
				];
				if($check_batch->batch > 1){
					$check_qty_batch = $this->model->gds("plan_wos","batch","suffix = '$suffix' AND plan = '$value'","row");
					if(!empty($check_qty_batch->batch)){
						$qty_batch1 = $check_qty_batch->batch == "1" ? $value : "0";
						$qty_batch2 = $check_qty_batch->batch == "2" ? $value : "0";
					}else{
						$check_qty_batch = $this->model->gds("plan_wos","plan","suffix = '$suffix' AND batch = '1'","row");
						if($check_qty_batch->plan > $value){
							$qty_batch1 = round($value*50/100,0);
						}else{
							$qty_batch1 = $check_qty_batch->plan;
						}
						$qty_batch2 = $value - $qty_batch1;
					}
					$data_insert_two[$key]["qty_batch_1"] = $qty_batch1;
					$data_insert_two[$key]["qty_batch_2"] = $qty_batch2;
				}else{
					$check_batch_avail = $this->model->gds("plan_wos","batch","suffix = '$suffix'","row");
					if($check_batch_avail->batch == "1"){
						$data_insert_two[$key]["qty_batch_1"] = $value;
						$data_insert_two[$key]["qty_batch_2"] = "0";
					}else{
						$data_insert_two[$key]["qty_batch_1"] = "0";
						$data_insert_two[$key]["qty_batch_2"] = $value;
					}
				}
				$unit_two += $value;
			}
		}
		$single_suffix_count = count($data_insert_single);
		$two_suffix_count = count($data_insert_two);
		// print_r($data_insert_single);
		// echo '<br>';
		// print_r($data_insert_two);
		// die();
		$pesan = '';
		$clear_data = $this->model->delete("twotone_setting", "suffix_pdd !="); //hapus semua data di twotone setting
		if(!empty($data_insert_two)){
			$insert = $this->model->insert_batch("twotone_setting", $data_insert_two); //insert semua data twotone
		}
		$pesan .= 'Two Tone : '.$unit_two.' Unit ('.$two_suffix_count.' Suffix)<br>';

		$clear_data = $this->model->delete("singletone_setting", "suffix_pdd !="); //hapus semua data di single setting
		if(!empty($data_insert_single)){
			$insert = $this->model->insert_batch("singletone_setting", $data_insert_single); //insert semua data single tone
		}
		$pesan .= 'Single Tone : '.$unit_single.' Unit ('.$single_suffix_count.' Suffix)<br>';

		if ($insert) {
			$this->voice("sukses.mp3");
			$this->swal_custom_icon("Adjust OK", "Keterangan :<br>".$pesan."<br>Klik di bawah ini untuk proses docking.<br><a href='".base_url("tabungan?p=docking")."' class='btn btn-sm btn-info mt-4'>Docking</a>", base_url('assets/images/happy.png'), "","false");
		} else {
			$this->voice("gagal.mp3");
			$this->swal_custom_icon("Gagal", "Proses simpan data adjust gagal", base_url('assets/images/emot-sedih.jpg'), "rounded-circle","true");
		}
		redirect("adjust_twotone");
	}

	public function docking_truncate()
	{
		if(empty($this->input->get("t"))){
			$clear_table = $this->model->delete("t_docking", "sapnik != '0'");
		}else{
			$clear_table = $this->model->delete("t_docking_kap2", "sapnik != '0'");
		}
		if (!$clear_table) {
			if(empty($this->input->get("t"))){
				$clear_master_heijunka = $this->model->delete_heijunka("master", "SAPNIK !=");
				$clear_master_heijunka = $this->model->delete_heijunka("master_td_link", "SAPNIK !=");
				$clear_ttid_heijunka = $this->model->delete_heijunka("twotone_id", "vin !=");
				$c_history = [
					"Model" => "",
					"Status" => "",
				];
				$clear_history = $this->model->update_heijunka("history", "Heijunka !=", $c_history);
				if (!$clear_master_heijunka) {
					echo "sukses";
					$data_snd = [
						"snd" => 0,
						"tnd" => 0,
					];
					$this->session->set_userdata($data_snd);
				} else {
					echo "gagal";
				}
			}else{
				$clear_master_heijunka = $this->model->delete_heijunka("master_kap2", "SAPNIK !=");
				$clear_ttid_heijunka = $this->model->delete_heijunka("twotone_id_kap2", "vin !=");
				$c_history = [
					"Model" => "",
					"Status" => "",
				];
				$clear_history = $this->model->update_heijunka("history_kap2", "Heijunka !=", $c_history);
				if (!$clear_master_heijunka) {
					echo "sukses";
					$data_snd = [
						"snd" => 0,
						"tnd" => 0,
					];
					$this->session->set_userdata($data_snd);
				} else {
					echo "gagal";
				}
			}
		} else {
			echo "gagal";
		}
		die();
	}

	public function docking()
	{
		$suffix = $this->input->get("suffix");
		$snd = $this->snd;
		$tnd = $this->tnd + $this->input->get("plan");
		$batch = $this->input->get("batch");
		$db_twotone = $this->model->gds("twotone", "*", "twotone !=", "result");
		$in_tt = "";
		foreach ($db_twotone as $db_twotone) {
			$in_tt .= "'" . $db_twotone->twotone . "',";
		}
		$in_tt = rtrim($in_tt, ",");
		
		$sapnik_batch = $this->model->gds_heijunka("master","SAPNIK","Lot_Code = '$suffix'","result");
		$sapnik_not_in = '';
		if(!empty($sapnik_batch)){
			$data_sapnik_batch = [];
			foreach ($sapnik_batch as $sapnik_batch) {
				$data_sapnik_batch[] = $sapnik_batch->SAPNIK;
			}
			$sapnik_not_in = implode("','",$data_sapnik_batch);
		}
		$filtering_sapnik_not_in = '';
		if(!empty($sapnik_not_in)){
			$filtering_sapnik_not_in = "AND sapnik NOT IN('".$sapnik_not_in."')";
		}

		//Cari suffix ini ada di plan twotone atau tidak
		$validasi_twotone = $this->model->gds("twotone_setting", "suffix,pdd,SUM(qty) as qty", "suffix = '".$suffix."'", "row");
		$total_docking_all = $this->model->gds("t_docking", "COUNT(sapnik) as count", "suffix !=", "row");
		$no = $total_docking_all->count + 1;
		$column_select = "qty_batch_".$batch;
		if (empty($validasi_twotone->suffix)) { //jika tidak ada settingan twotone
			//Get Data All Single Tone
			$plan = $this->input->get("plan");

			$validasi_singletone = $this->model->gds("singletone_setting", "suffix,pdd,SUM(qty) as qty", "suffix = '".$suffix."'", "row");
			if(empty($validasi_singletone->suffix)){ //jika tidak ada data setting single tone
				// $get_from_tabungan = $this->model->gds("tabungan_vlt", "*", "katashiki_suffix = '$suffix' AND color_code NOT IN(".$in_tt .") AND sapnik != '' ORDER BY plan_delivery_date ASC LIMIT " . $plan . " OFFSET 0", "result");
				
				$get_from_tabungan = $this->model->gds("tabungan_vlt", "*", "katashiki_suffix = '$suffix' AND color_code NOT IN(".$in_tt .") ".$filtering_sapnik_not_in." AND sapnik != '' ORDER BY plan_delivery_date ASC LIMIT " . $plan . " OFFSET 0", "result");

				if (!empty($get_from_tabungan)) {
					foreach ($get_from_tabungan as $gft) {
						if(empty($gft->twotone)){
							$data_docking[] = [
								"sapnik" => $gft->sapnik,
								"suffix" => $gft->katashiki_suffix,
								"pdd" => $gft->plan_delivery_date,
								"status_tone" => "SingleTone",
								"batch" => $batch,
								"suffix_batch" => $gft->katashiki_suffix."-".$batch,
							];

							//Data untuk HEIJUNKA WOS
							$model = $this->model->gds("plan_wos", "model_code,model_name", "suffix = '" . $gft->katashiki_suffix . "' AND batch = '".$batch."'", "row");
							if (!empty($model)) {
								$model_code = $model->model_code;
								$model_name = $model->model_name;
							} else {
								$model_code = "";
								$model_name = "";
							}

							$transmisi = substr($gft->katashiki, 8, 1);
							if ($transmisi != "M") {
								$transmisi = "Q";
							} else {
								$transmisi = "M";
							}

							$color_row = $this->model->gds_heijunka("color_model", "Background,Font,Family_Model", "Model_Name = '$model_name'", "row");
							if (!empty($color_row)) {
								$family_model = $color_row->Family_Model;
								$color = $color_row->Background . "," . $color_row->Font;
							} else {
								$family_model = "";
								$color = "";
							}

							$model_color = $gft->color_code;
							$get_bot_color = $this->model->gds_heijunka("color_both", "Bot", "Model_Warna LIKE '%$model_color%'", "row");
							if (!empty($get_bot_color)) {
								$bot_color = $get_bot_color->Bot;
							} else {
								$bot_color = "";
							}

							if (!empty($gft->wos_release_date)) {
								$wos_release_date = date("d-M-Y", strtotime($gft->wos_release_date));
							} else {
								$wos_release_date = "";
							}

							$check_tone = $this->model->gds("twotone", "twotone", "twotone = '" . $gft->color_code . "'", "row");
							if (!empty($check_tone->twotone)) {
								$tone = "TWO TONE";
							} else {
								$tone = "SINGLE TONE";
							}
							$data_docking_heijunka[] = [
								"No" => $no++,
								"WOS_Material" => $gft->wos_material,
								"WOS_Material_Description" => $gft->wos_material_description,
								"SAPNIK" => $gft->sapnik,
								"SAP_Material" => $gft->sap_material,
								"Engine_Model" => $gft->engine_model,
								"Engine_Prefix" => $gft->engine_prefix,
								"Engine_Number" => $gft->engine_number,
								"Plant" => $gft->plant,
								"Chassis_Number" => $gft->chassis_number,
								"Lot_Code" => $gft->lot_code,
								"Lot_Number" => $gft->lot_number,
								"Katashiki" => $gft->katashiki,
								"Katashiki_Sfx" => $gft->katashiki_suffix,
								"ADM_Production_Id" => $gft->adm_production_id,
								"TAM_Production_Id" => $gft->tam_production_id,
								"Plan_Delivery_Date" => date("d-M-Y", strtotime($gft->plan_delivery_date)),
								"Plan_Jig_In_Date" => date("d-M-Y", strtotime($gft->plan_jig_in_date)),
								"WOS_Release_Date" => $wos_release_date,
								"SAPWOS_DES" => $gft->sapwos_des,
								"Location" => 'No',
								"Color_Code" => $gft->color_code,
								"tone" => $tone,
								"Model" => $model_code,
								"Model_Name" => $model_name,
								"ED" => $gft->ed,
								"Order" => $gft->order_column,
								"Dest" => $gft->destination,
								"Transmisi" => $transmisi,
								"Color" => $color,
								"Bot_Color" => $bot_color,
								"Family_Model" => $family_model,
								"model_both" => $model_code.$bot_color,
								"batch" => $batch,
							];
						}
					}
				}
			}else{ //jika ada settingan singletone
				//insert docking twotone first
				$get_plan_singletone = $this->model->gds("singletone_setting", "pdd,$column_select as qty", "suffix = '$suffix'", "result");
				if (!empty($get_plan_singletone)) {
					foreach ($get_plan_singletone as $gpt) {
						//get data from tabungan vlt
						// $get_from_tabungan = $this->model->gds("tabungan_vlt", "*", "katashiki_suffix = '$suffix' AND color_code NOT IN(" . $in_tt . ") AND plan_delivery_date = '" . $gpt->pdd . "' AND sapnik != '' ORDER BY plan_delivery_date ASC LIMIT " . $gpt->qty . " OFFSET 0", "result");
						
						$get_from_tabungan = $this->model->gds("tabungan_vlt", "*", "katashiki_suffix = '$suffix' AND color_code NOT IN(".$in_tt .") AND plan_delivery_date = '" . $gpt->pdd . "' ".$filtering_sapnik_not_in." AND sapnik != '' ORDER BY plan_delivery_date ASC LIMIT " . $gpt->qty . " OFFSET 0", "result");

						if (!empty($get_from_tabungan)) {
							foreach ($get_from_tabungan as $gft) {
								$data_docking[] = [
									"sapnik" => $gft->sapnik,
									"suffix" => $gft->katashiki_suffix,
									"pdd" => $gft->plan_delivery_date,
									"status_tone" => "SingleTone",
									"batch" => $batch,
									"suffix_batch" => $gft->katashiki_suffix."-".$batch,
								];
								$data_sapnik[] = "'" . $gft->sapnik . "'";

								//Data untuk HEIJUNKA WOS
								$model = $this->model->gds("plan_wos", "model_code,model_name", "suffix = '" . $gft->katashiki_suffix . "' AND batch = '".$batch."'", "row");
								if (!empty($model)) {
									$model_code = $model->model_code;
									$model_name = $model->model_name;
								} else {
									$model_code = "";
									$model_name = "";
								}

								$transmisi = substr($gft->katashiki, 8, 1);
								if ($transmisi != "M") {
									$transmisi = "Q";
								} else {
									$transmisi = "M";
								}

								$color_row = $this->model->gds_heijunka("color_model", "Background,Font,Family_Model", "Model_Name = '$model_name'", "row");
								if (!empty($color_row)) {
									$family_model = $color_row->Family_Model;
									$color = $color_row->Background . "," . $color_row->Font;
								} else {
									$family_model = "";
									$color = "";
								}

								$model_color = $gft->color_code;
								$get_bot_color = $this->model->gds_heijunka("color_both", "Bot", "Model_Warna LIKE '%$model_color%'", "row");
								if (!empty($get_bot_color)) {
									$bot_color = $get_bot_color->Bot;
								} else {
									$bot_color = "";
								}

								if (!empty($gft->wos_release_date)) {
									$wos_release_date = date("d-M-Y", strtotime($gft->wos_release_date));
								} else {
									$wos_release_date = "";
								}

								$check_tone = $this->model->gds("twotone", "twotone", "twotone = '" . $gft->color_code . "'", "row");
								if (!empty($check_tone->twotone)) {
									$tone = "TWO TONE";
								} else {
									$tone = "SINGLE TONE";
								}
								$data_docking_heijunka[] = [
									"No" => $no++,
									"WOS_Material" => $gft->wos_material,
									"WOS_Material_Description" => $gft->wos_material_description,
									"SAPNIK" => $gft->sapnik,
									"SAP_Material" => $gft->sap_material,
									"Engine_Model" => $gft->engine_model,
									"Engine_Prefix" => $gft->engine_prefix,
									"Engine_Number" => $gft->engine_number,
									"Plant" => $gft->plant,
									"Chassis_Number" => $gft->chassis_number,
									"Lot_Code" => $gft->lot_code,
									"Lot_Number" => $gft->lot_number,
									"Katashiki" => $gft->katashiki,
									"Katashiki_Sfx" => $gft->katashiki_suffix,
									"ADM_Production_Id" => $gft->adm_production_id,
									"TAM_Production_Id" => $gft->tam_production_id,
									"Plan_Delivery_Date" => date("d-M-Y", strtotime($gft->plan_delivery_date)),
									"Plan_Jig_In_Date" => date("d-M-Y", strtotime($gft->plan_jig_in_date)),
									"WOS_Release_Date" => $wos_release_date,
									"SAPWOS_DES" => $gft->sapwos_des,
									"Location" => 'No',
									"Color_Code" => $gft->color_code,
									"tone" => $tone,
									"Model" => $model_code,
									"Model_Name" => $model_name,
									"ED" => $gft->ed,
									"Order" => $gft->order_column,
									"Dest" => $gft->destination,
									"Transmisi" => $transmisi,
									"Color" => $color,
									"Bot_Color" => $bot_color,
									"Family_Model" => $family_model,
									"model_both" => $model_code.$bot_color,
									"batch" => $batch,
								];
							}
						}
					}
				}
			}
		} else { //jika ada settingan twotone
			//insert docking twotone first
			$get_plan_twotone = $this->model->gds("twotone_setting", "pdd,$column_select as qty", "suffix = '$suffix'", "result");
			if (!empty($get_plan_twotone)) {
				foreach ($get_plan_twotone as $gpt) { 
					//get data from tabungan vlt
					// $get_from_tabungan = $this->model->gds("tabungan_vlt", "*", "katashiki_suffix = '$suffix' AND color_code IN(" . $in_tt . ") AND plan_delivery_date = '" . $gpt->pdd . "' AND sapnik != '' ORDER BY plan_delivery_date ASC LIMIT " . $gpt->qty . " OFFSET 0", "result");
					
					//CHECK APAKAH SUFFIX ADA 2 BATCH
					$limitasi = $gpt->qty;

					$get_from_tabungan = $this->model->gds("tabungan_vlt", "*", "katashiki_suffix = '$suffix' AND color_code IN(".$in_tt .") AND plan_delivery_date = '" . $gpt->pdd . "' ".$filtering_sapnik_not_in." AND sapnik != '' ORDER BY plan_delivery_date ASC LIMIT " . $limitasi . " OFFSET 0", "result");

					if (!empty($get_from_tabungan)) {
						foreach ($get_from_tabungan as $gft) {
							$data_docking[] = [
								"sapnik" => $gft->sapnik,
								"suffix" => $gft->katashiki_suffix,
								"pdd" => $gft->plan_delivery_date,
								"status_tone" => "TwoTone",
								"batch" => $batch,
								"suffix_batch" => $gft->katashiki_suffix."-".$batch,
							];
							$data_sapnik[] = "'" . $gft->sapnik . "'";

							//Data untuk HEIJUNKA WOS
							$model = $this->model->gds("plan_wos", "model_code,model_name", "suffix = '" . $gft->katashiki_suffix . "' AND batch = '".$batch."'", "row");
							if (!empty($model)) {
								$model_code = $model->model_code;
								$model_name = $model->model_name;
							} else {
								$model_code = "";
								$model_name = "";
							}

							$transmisi = substr($gft->katashiki, 8, 1);
							if ($transmisi != "M") {
								$transmisi = "Q";
							} else {
								$transmisi = "M";
							}

							$color_row = $this->model->gds_heijunka("color_model", "Background,Font,Family_Model", "Model_Name = '$model_name'", "row");
							if (!empty($color_row)) {
								$family_model = $color_row->Family_Model;
								$color = $color_row->Background . "," . $color_row->Font;
							} else {
								$family_model = "";
								$color = "";
							}

							$model_color = $gft->color_code;
							$get_bot_color = $this->model->gds_heijunka("color_both", "Bot", "Model_Warna LIKE '%$model_color%'", "row");
							if (!empty($get_bot_color)) {
								$bot_color = $get_bot_color->Bot;
							} else {
								$bot_color = "";
							}

							if (!empty($gft->wos_release_date)) {
								$wos_release_date = date("d-M-Y", strtotime($gft->wos_release_date));
							} else {
								$wos_release_date = "";
							}

							$check_tone = $this->model->gds("twotone", "twotone", "twotone = '" . $gft->color_code . "'", "row");
							if (!empty($check_tone->twotone)) {
								$tone = "TWO TONE";
							} else {
								$tone = "SINGLE TONE";
							}
							$data_docking_heijunka[] = [
								"No" => $no++,
								"WOS_Material" => $gft->wos_material,
								"WOS_Material_Description" => $gft->wos_material_description,
								"SAPNIK" => $gft->sapnik,
								"SAP_Material" => $gft->sap_material,
								"Engine_Model" => $gft->engine_model,
								"Engine_Prefix" => $gft->engine_prefix,
								"Engine_Number" => $gft->engine_number,
								"Plant" => $gft->plant,
								"Chassis_Number" => $gft->chassis_number,
								"Lot_Code" => $gft->lot_code,
								"Lot_Number" => $gft->lot_number,
								"Katashiki" => $gft->katashiki,
								"Katashiki_Sfx" => $gft->katashiki_suffix,
								"ADM_Production_Id" => $gft->adm_production_id,
								"TAM_Production_Id" => $gft->tam_production_id,
								"Plan_Delivery_Date" => date("d-M-Y", strtotime($gft->plan_delivery_date)),
								"Plan_Jig_In_Date" => date("d-M-Y", strtotime($gft->plan_jig_in_date)),
								"WOS_Release_Date" => $wos_release_date,
								"SAPWOS_DES" => $gft->sapwos_des,
								"Location" => 'No',
								"Color_Code" => $gft->color_code,
								"tone" => $tone,
								"Model" => $model_code,
								"Model_Name" => $model_name,
								"ED" => $gft->ed,
								"Order" => $gft->order_column,
								"Dest" => $gft->destination,
								"Transmisi" => $transmisi,
								"Color" => $color,
								"Bot_Color" => $bot_color,
								"Family_Model" => $family_model,
								"model_both" => $model_code.$bot_color,
								"batch" => $batch,
							];
						}
						$in_sapnik = implode(",", $data_sapnik);
					} else {
						$in_sapnik = 0;
					}
				}
				$total_twotone = !empty($data_sapnik) ? count($data_sapnik) : 0;
			} else {
				$total_twotone = 0;
				$in_sapnik = 0;
			}

			//insert docking single tone
			//get data from tabungan vlt jangan ambil data sapnik yang sudah di ambil dari twotone
			$plan_single_tone = $this->input->get("plan") - $total_twotone;
			if ($plan_single_tone > 0) { //jika terdapat planning single tone setalah plan yang tersedia di kurangi twotone
				if($in_sapnik != '0'){
					// $get_from_tabungan = $this->model->join_data("tabungan_vlt a","twotone b","a.color_code = b.twotone","*","a.katashiki_suffix = '$suffix' AND a.sapnik NOT IN(" . $in_sapnik . ") AND a.sapnik != ''","result");
					// $get_from_tabungan = $this->model->gds("tabungan_vlt", "*", "katashiki_suffix = '$suffix' AND sapnik NOT IN(" . $in_sapnik . ") AND color_code NOT IN(".$in_tt .") AND sapnik != '' ORDER BY plan_delivery_date ASC LIMIT " . $plan_single_tone . " OFFSET 0", "result");
					
					$get_from_tabungan = $this->model->gds("tabungan_vlt", "*", "katashiki_suffix = '$suffix' AND sapnik NOT IN(" . $in_sapnik . ") AND color_code NOT IN(".$in_tt .") ".$filtering_sapnik_not_in." AND sapnik != '' ORDER BY plan_delivery_date ASC LIMIT " . $plan_single_tone . " OFFSET 0", "result");
				}else{
					// $get_from_tabungan = $this->model->gds("tabungan_vlt", "*", "katashiki_suffix = '$suffix' AND color_code NOT IN(".$in_tt .") AND sapnik != '' ORDER BY plan_delivery_date ASC LIMIT " . $plan_single_tone . " OFFSET 0", "result");
					
					$get_from_tabungan = $this->model->gds("tabungan_vlt", "*", "katashiki_suffix = '$suffix' AND color_code NOT IN(".$in_tt .") ".$filtering_sapnik_not_in." AND sapnik != '' ORDER BY plan_delivery_date ASC LIMIT " . $plan_single_tone . " OFFSET 0", "result");
				}
				if (!empty($get_from_tabungan)) {
					foreach ($get_from_tabungan as $gft) {
						if(empty($gft->twotone)){
							$data_docking[] = [
								"sapnik" => $gft->sapnik,
								"suffix" => $gft->katashiki_suffix,
								"pdd" => $gft->plan_delivery_date,
								"status_tone" => "SingleTone",
								"batch" => $batch,
								"suffix_batch" => $gft->katashiki_suffix."-".$batch,
							];

							//Data untuk HEIJUNKA WOS
							$model = $this->model->gds("plan_wos", "model_code,model_name", "suffix = '" . $gft->katashiki_suffix . "' AND batch = '".$batch."'", "row");
							if (!empty($model)) {
								$model_code = $model->model_code;
								$model_name = $model->model_name;
							} else {
								$model_code = "";
								$model_name = "";
							}

							$transmisi = substr($gft->katashiki, 8, 1);
							if ($transmisi != "M") {
								$transmisi = "Q";
							} else {
								$transmisi = "M";
							}

							$color_row = $this->model->gds_heijunka("color_model", "Background,Font,Family_Model", "Model_Name = '$model_name'", "row");
							if (!empty($color_row)) {
								$family_model = $color_row->Family_Model;
								$color = $color_row->Background . "," . $color_row->Font;
							} else {
								$family_model = "";
								$color = "";
							}

							$model_color = $gft->color_code;
							$get_bot_color = $this->model->gds_heijunka("color_both", "Bot", "Model_Warna LIKE '%$model_color%'", "row");
							if (!empty($get_bot_color)) {
								$bot_color = $get_bot_color->Bot;
							} else {
								$bot_color = "";
							}

							if (!empty($gft->wos_release_date)) {
								$wos_release_date = date("d-M-Y", strtotime($gft->wos_release_date));
							} else {
								$wos_release_date = "";
							}

							$check_tone = $this->model->gds("twotone", "twotone", "twotone = '" . $gft->color_code . "'", "row");
							if (!empty($check_tone->twotone)) {
								$tone = "TWO TONE";
							} else {
								$tone = "SINGLE TONE";
							}
							$data_docking_heijunka[] = [
								"No" => $no++,
								"WOS_Material" => $gft->wos_material,
								"WOS_Material_Description" => $gft->wos_material_description,
								"SAPNIK" => $gft->sapnik,
								"SAP_Material" => $gft->sap_material,
								"Engine_Model" => $gft->engine_model,
								"Engine_Prefix" => $gft->engine_prefix,
								"Engine_Number" => $gft->engine_number,
								"Plant" => $gft->plant,
								"Chassis_Number" => $gft->chassis_number,
								"Lot_Code" => $gft->lot_code,
								"Lot_Number" => $gft->lot_number,
								"Katashiki" => $gft->katashiki,
								"Katashiki_Sfx" => $gft->katashiki_suffix,
								"ADM_Production_Id" => $gft->adm_production_id,
								"TAM_Production_Id" => $gft->tam_production_id,
								"Plan_Delivery_Date" => date("d-M-Y", strtotime($gft->plan_delivery_date)),
								"Plan_Jig_In_Date" => date("d-M-Y", strtotime($gft->plan_jig_in_date)),
								"WOS_Release_Date" => $wos_release_date,
								"SAPWOS_DES" => $gft->sapwos_des,
								"Location" => 'No',
								"Color_Code" => $gft->color_code,
								"tone" => $tone,
								"Model" => $model_code,
								"Model_Name" => $model_name,
								"ED" => $gft->ed,
								"Order" => $gft->order_column,
								"Dest" => $gft->destination,
								"Transmisi" => $transmisi,
								"Color" => $color,
								"Bot_Color" => $bot_color,
								"Family_Model" => $family_model,
								"model_both" => $model_code.$bot_color,
								"batch" => $batch,
							];
						}
					}
				}
			}
		}

		// print_r($data_docking_heijunka);
		// die();

		
		// echo json_encode($data_docking)."<br>";
		// echo count($data_docking);
		// die();
		//insert docking to HEIJUNKA WOS DATABASE
		if(!empty($data_docking_heijunka)){
			$insert_heijunka_wos = $this->model->insert_batch_heijunka("master", $data_docking_heijunka);
			$insert_data_docking = $this->model->insert_batch("t_docking", $data_docking);
			$ssnd = "";
		}else{
			$data_snd = [
				"snd" => $snd+1,
				"tnd" => $tnd,
			];
			$ssnd = $suffix;
			$this->session->set_userdata($data_snd);
		}
		$total_suffix_docking = $this->model->gds("t_docking", "COUNT(distinct suffix_batch) as suffix", "suffix_batch != ''", "row");
		$count_docking_success = $this->model->gds("t_docking", "COUNT(sapnik) as count", "suffix_batch = '$suffix-$batch'", "row");
		$total_docking_success = $this->model->gds("t_docking", "COUNT(sapnik) as count", "suffix_batch !=''", "row");
		$fb = [
			"actual" => $count_docking_success->count,
			"total_docking" => $total_docking_success->count + $this->tnd,
			"total_suffix_docking" => $total_suffix_docking->suffix + $snd,
			"suffix_not_docking" => $snd,
			"snd" => $ssnd,
			"tnd" => $this->tnd,
		];
		echo json_encode($fb);
		die();
	}

	public function docking_kap2()
	{
		$suffix = $this->input->get("suffix");
		$snd = $this->snd;
		$tnd = $this->tnd + $this->input->get("plan");
		$batch = $this->input->get("batch");
		$db_twotone = $this->model->gds("twotone_kap2", "*", "twotone !=", "result");
		$in_tt = "";
		foreach ($db_twotone as $db_twotone) {
			$in_tt .= "'" . $db_twotone->twotone . "',";
		}
		$in_tt = rtrim($in_tt, ",");
		
		$sapnik_batch = $this->model->gds_heijunka("master_kap2","SAPNIK","Lot_Code = '$suffix'","result");
		$sapnik_not_in = '';
		if(!empty($sapnik_batch)){
			$data_sapnik_batch = [];
			foreach ($sapnik_batch as $sapnik_batch) {
				$data_sapnik_batch[] = $sapnik_batch->SAPNIK;
			}
			$sapnik_not_in = implode("','",$data_sapnik_batch);
		}
		$filtering_sapnik_not_in = '';
		if(!empty($sapnik_not_in)){
			$filtering_sapnik_not_in = "AND sapnik NOT IN('".$sapnik_not_in."')";
		}

		//Cari suffix ini ada di plan twotone atau tidak
		$validasi_twotone = $this->model->gds("twotone_setting_kap2", "suffix,pdd,SUM(qty) as qty", "suffix = '".$suffix."'", "row");
		$total_docking_all = $this->model->gds("t_docking_kap2", "COUNT(sapnik) as count", "suffix !=", "row");
		$no = $total_docking_all->count + 1;
		$column_select = "qty_batch_".$batch;
		if (empty($validasi_twotone->suffix)) { //jika tidak ada settingan twotone
			//Get Data All Single Tone
			$plan = $this->input->get("plan");

			$validasi_singletone = $this->model->gds("singletone_setting_kap2", "suffix,pdd,SUM(qty) as qty", "suffix = '".$suffix."'", "row");
			if(empty($validasi_singletone->suffix)){ //jika tidak ada data setting single tone
				// $get_from_tabungan = $this->model->gds("tabungan_vlt_kap2", "*", "katashiki_suffix = '$suffix' AND color_code NOT IN(".$in_tt .") AND sapnik != '' ORDER BY plan_delivery_date ASC LIMIT " . $plan . " OFFSET 0", "result");
				
				$get_from_tabungan = $this->model->gds("tabungan_vlt_kap2", "*", "katashiki_suffix = '$suffix' AND color_code NOT IN(".$in_tt .") ".$filtering_sapnik_not_in." AND sapnik != '' ORDER BY plan_delivery_date ASC LIMIT " . $plan . " OFFSET 0", "result");

				if (!empty($get_from_tabungan)) {
					foreach ($get_from_tabungan as $gft) {
						if(empty($gft->twotone)){
							$data_docking[] = [
								"sapnik" => $gft->sapnik,
								"suffix" => $gft->katashiki_suffix,
								"pdd" => $gft->plan_delivery_date,
								"status_tone" => "SingleTone",
								"batch" => $batch,
								"suffix_batch" => $gft->katashiki_suffix."-".$batch,
							];

							//Data untuk HEIJUNKA WOS
							$model = $this->model->gds("plan_wos_kap2", "model_code,model_name", "suffix = '" . $gft->katashiki_suffix . "' AND batch = '".$batch."'", "row");
							if (!empty($model)) {
								$model_code = $model->model_code;
								$model_name = $model->model_name;
							} else {
								$model_code = "";
								$model_name = "";
							}

							$transmisi = substr($gft->katashiki, 8, 1);
							if ($transmisi != "M") {
								$transmisi = "Q";
							} else {
								$transmisi = "M";
							}

							$color_row = $this->model->gds_heijunka("color_model_kap2", "Background,Font,Family_Model", "Model_Name = '$model_name'", "row");
							if (!empty($color_row)) {
								$family_model = $color_row->Family_Model;
								$color = $color_row->Background . "," . $color_row->Font;
							} else {
								$family_model = "";
								$color = "";
							}

							$model_color = $gft->color_code;
							$get_bot_color = $this->model->gds_heijunka("color_both_kap2", "Bot", "Model_Warna LIKE '%$model_color%'", "row");
							if (!empty($get_bot_color)) {
								$bot_color = $get_bot_color->Bot;
							} else {
								$bot_color = "";
							}

							if (!empty($gft->wos_release_date)) {
								$wos_release_date = date("d-M-Y", strtotime($gft->wos_release_date));
							} else {
								$wos_release_date = "";
							}

							$check_tone = $this->model->gds("twotone_kap2", "twotone", "twotone = '" . $gft->color_code . "'", "row");
							if (!empty($check_tone->twotone)) {
								$tone = "TWO TONE";
							} else {
								$tone = "SINGLE TONE";
							}
							$data_docking_heijunka[] = [
								"No" => $no++,
								"WOS_Material" => $gft->wos_material,
								"WOS_Material_Description" => $gft->wos_material_description,
								"SAPNIK" => $gft->sapnik,
								"SAP_Material" => $gft->sap_material,
								"Engine_Model" => $gft->engine_model,
								"Engine_Prefix" => $gft->engine_prefix,
								"Engine_Number" => $gft->engine_number,
								"Plant" => $gft->plant,
								"Chassis_Number" => $gft->chassis_number,
								"Lot_Code" => $gft->lot_code,
								"Lot_Number" => $gft->lot_number,
								"Katashiki" => $gft->katashiki,
								"Katashiki_Sfx" => $gft->katashiki_suffix,
								"ADM_Production_Id" => $gft->adm_production_id,
								"TAM_Production_Id" => $gft->tam_production_id,
								"Plan_Delivery_Date" => date("d-M-Y", strtotime($gft->plan_delivery_date)),
								"Plan_Jig_In_Date" => date("d-M-Y", strtotime($gft->plan_jig_in_date)),
								"WOS_Release_Date" => $wos_release_date,
								"SAPWOS_DES" => $gft->sapwos_des,
								"Location" => 'No',
								"Color_Code" => $gft->color_code,
								"tone" => $tone,
								"Model" => $model_code,
								"Model_Name" => $model_name,
								"ED" => $gft->ed,
								"Order" => $gft->order_column,
								"Dest" => $gft->destination,
								"Transmisi" => $transmisi,
								"Color" => $color,
								"Bot_Color" => $bot_color,
								"Family_Model" => $family_model,
								"model_both" => $model_code.$bot_color,
								"batch" => $batch,
							];
						}
					}
				}
			}else{ //jika ada settingan singletone
				//insert docking twotone first
				$get_plan_singletone = $this->model->gds("singletone_setting_kap2", "pdd,$column_select as qty", "suffix = '$suffix'", "result");
				if (!empty($get_plan_singletone)) {
					foreach ($get_plan_singletone as $gpt) {
						//get data from tabungan vlt
						// $get_from_tabungan = $this->model->gds("tabungan_vlt_kap2", "*", "katashiki_suffix = '$suffix' AND color_code NOT IN(" . $in_tt . ") AND plan_delivery_date = '" . $gpt->pdd . "' AND sapnik != '' ORDER BY plan_delivery_date ASC LIMIT " . $gpt->qty . " OFFSET 0", "result");
						
						$get_from_tabungan = $this->model->gds("tabungan_vlt_kap2", "*", "katashiki_suffix = '$suffix' AND color_code NOT IN(".$in_tt .") AND plan_delivery_date = '" . $gpt->pdd . "' ".$filtering_sapnik_not_in." AND sapnik != '' ORDER BY plan_delivery_date ASC LIMIT " . $gpt->qty . " OFFSET 0", "result");

						if (!empty($get_from_tabungan)) {
							foreach ($get_from_tabungan as $gft) {
								$data_docking[] = [
									"sapnik" => $gft->sapnik,
									"suffix" => $gft->katashiki_suffix,
									"pdd" => $gft->plan_delivery_date,
									"status_tone" => "SingleTone",
									"batch" => $batch,
									"suffix_batch" => $gft->katashiki_suffix."-".$batch,
								];
								$data_sapnik[] = "'" . $gft->sapnik . "'";

								//Data untuk HEIJUNKA WOS
								$model = $this->model->gds("plan_wos_kap2", "model_code,model_name", "suffix = '" . $gft->katashiki_suffix . "' AND batch = '".$batch."'", "row");
								if (!empty($model)) {
									$model_code = $model->model_code;
									$model_name = $model->model_name;
								} else {
									$model_code = "";
									$model_name = "";
								}

								$transmisi = substr($gft->katashiki, 8, 1);
								if ($transmisi != "M") {
									$transmisi = "Q";
								} else {
									$transmisi = "M";
								}

								$color_row = $this->model->gds_heijunka("color_model_kap2", "Background,Font,Family_Model", "Model_Name = '$model_name'", "row");
								if (!empty($color_row)) {
									$family_model = $color_row->Family_Model;
									$color = $color_row->Background . "," . $color_row->Font;
								} else {
									$family_model = "";
									$color = "";
								}

								$model_color = $gft->color_code;
								$get_bot_color = $this->model->gds_heijunka("color_both_kap2", "Bot", "Model_Warna LIKE '%$model_color%'", "row");
								if (!empty($get_bot_color)) {
									$bot_color = $get_bot_color->Bot;
								} else {
									$bot_color = "";
								}

								if (!empty($gft->wos_release_date)) {
									$wos_release_date = date("d-M-Y", strtotime($gft->wos_release_date));
								} else {
									$wos_release_date = "";
								}

								$check_tone = $this->model->gds("twotone_kap2", "twotone", "twotone = '" . $gft->color_code . "'", "row");
								if (!empty($check_tone->twotone)) {
									$tone = "TWO TONE";
								} else {
									$tone = "SINGLE TONE";
								}
								$data_docking_heijunka[] = [
									"No" => $no++,
									"WOS_Material" => $gft->wos_material,
									"WOS_Material_Description" => $gft->wos_material_description,
									"SAPNIK" => $gft->sapnik,
									"SAP_Material" => $gft->sap_material,
									"Engine_Model" => $gft->engine_model,
									"Engine_Prefix" => $gft->engine_prefix,
									"Engine_Number" => $gft->engine_number,
									"Plant" => $gft->plant,
									"Chassis_Number" => $gft->chassis_number,
									"Lot_Code" => $gft->lot_code,
									"Lot_Number" => $gft->lot_number,
									"Katashiki" => $gft->katashiki,
									"Katashiki_Sfx" => $gft->katashiki_suffix,
									"ADM_Production_Id" => $gft->adm_production_id,
									"TAM_Production_Id" => $gft->tam_production_id,
									"Plan_Delivery_Date" => date("d-M-Y", strtotime($gft->plan_delivery_date)),
									"Plan_Jig_In_Date" => date("d-M-Y", strtotime($gft->plan_jig_in_date)),
									"WOS_Release_Date" => $wos_release_date,
									"SAPWOS_DES" => $gft->sapwos_des,
									"Location" => 'No',
									"Color_Code" => $gft->color_code,
									"tone" => $tone,
									"Model" => $model_code,
									"Model_Name" => $model_name,
									"ED" => $gft->ed,
									"Order" => $gft->order_column,
									"Dest" => $gft->destination,
									"Transmisi" => $transmisi,
									"Color" => $color,
									"Bot_Color" => $bot_color,
									"Family_Model" => $family_model,
									"model_both" => $model_code.$bot_color,
									"batch" => $batch,
								];
							}
						}
					}
				}
			}
		} else { //jika ada settingan twotone
			//insert docking twotone first
			$get_plan_twotone = $this->model->gds("twotone_setting_kap2", "pdd,$column_select as qty", "suffix = '$suffix'", "result");
			if (!empty($get_plan_twotone)) {
				foreach ($get_plan_twotone as $gpt) { 
					//get data from tabungan vlt
					// $get_from_tabungan = $this->model->gds("tabungan_vlt_kap2", "*", "katashiki_suffix = '$suffix' AND color_code IN(" . $in_tt . ") AND plan_delivery_date = '" . $gpt->pdd . "' AND sapnik != '' ORDER BY plan_delivery_date ASC LIMIT " . $gpt->qty . " OFFSET 0", "result");
					
					//CHECK APAKAH SUFFIX ADA 2 BATCH
					$limitasi = $gpt->qty;

					$get_from_tabungan = $this->model->gds("tabungan_vlt_kap2", "*", "katashiki_suffix = '$suffix' AND color_code IN(".$in_tt .") AND plan_delivery_date = '" . $gpt->pdd . "' ".$filtering_sapnik_not_in." AND sapnik != '' ORDER BY plan_delivery_date ASC LIMIT " . $limitasi . " OFFSET 0", "result");

					if (!empty($get_from_tabungan)) {
						foreach ($get_from_tabungan as $gft) {
							$data_docking[] = [
								"sapnik" => $gft->sapnik,
								"suffix" => $gft->katashiki_suffix,
								"pdd" => $gft->plan_delivery_date,
								"status_tone" => "TwoTone",
								"batch" => $batch,
								"suffix_batch" => $gft->katashiki_suffix."-".$batch,
							];
							$data_sapnik[] = "'" . $gft->sapnik . "'";

							//Data untuk HEIJUNKA WOS
							$model = $this->model->gds("plan_wos_kap2", "model_code,model_name", "suffix = '" . $gft->katashiki_suffix . "' AND batch = '".$batch."'", "row");
							if (!empty($model)) {
								$model_code = $model->model_code;
								$model_name = $model->model_name;
							} else {
								$model_code = "";
								$model_name = "";
							}

							$transmisi = substr($gft->katashiki, 8, 1);
							if ($transmisi != "M") {
								$transmisi = "Q";
							} else {
								$transmisi = "M";
							}

							$color_row = $this->model->gds_heijunka("color_model_kap2", "Background,Font,Family_Model", "Model_Name = '$model_name'", "row");
							if (!empty($color_row)) {
								$family_model = $color_row->Family_Model;
								$color = $color_row->Background . "," . $color_row->Font;
							} else {
								$family_model = "";
								$color = "";
							}

							$model_color = $gft->color_code;
							$get_bot_color = $this->model->gds_heijunka("color_both_kap2", "Bot", "Model_Warna LIKE '%$model_color%'", "row");
							if (!empty($get_bot_color)) {
								$bot_color = $get_bot_color->Bot;
							} else {
								$bot_color = "";
							}

							if (!empty($gft->wos_release_date)) {
								$wos_release_date = date("d-M-Y", strtotime($gft->wos_release_date));
							} else {
								$wos_release_date = "";
							}

							$check_tone = $this->model->gds("twotone_kap2", "twotone", "twotone = '" . $gft->color_code . "'", "row");
							if (!empty($check_tone->twotone)) {
								$tone = "TWO TONE";
							} else {
								$tone = "SINGLE TONE";
							}
							$data_docking_heijunka[] = [
								"No" => $no++,
								"WOS_Material" => $gft->wos_material,
								"WOS_Material_Description" => $gft->wos_material_description,
								"SAPNIK" => $gft->sapnik,
								"SAP_Material" => $gft->sap_material,
								"Engine_Model" => $gft->engine_model,
								"Engine_Prefix" => $gft->engine_prefix,
								"Engine_Number" => $gft->engine_number,
								"Plant" => $gft->plant,
								"Chassis_Number" => $gft->chassis_number,
								"Lot_Code" => $gft->lot_code,
								"Lot_Number" => $gft->lot_number,
								"Katashiki" => $gft->katashiki,
								"Katashiki_Sfx" => $gft->katashiki_suffix,
								"ADM_Production_Id" => $gft->adm_production_id,
								"TAM_Production_Id" => $gft->tam_production_id,
								"Plan_Delivery_Date" => date("d-M-Y", strtotime($gft->plan_delivery_date)),
								"Plan_Jig_In_Date" => date("d-M-Y", strtotime($gft->plan_jig_in_date)),
								"WOS_Release_Date" => $wos_release_date,
								"SAPWOS_DES" => $gft->sapwos_des,
								"Location" => 'No',
								"Color_Code" => $gft->color_code,
								"tone" => $tone,
								"Model" => $model_code,
								"Model_Name" => $model_name,
								"ED" => $gft->ed,
								"Order" => $gft->order_column,
								"Dest" => $gft->destination,
								"Transmisi" => $transmisi,
								"Color" => $color,
								"Bot_Color" => $bot_color,
								"Family_Model" => $family_model,
								"model_both" => $model_code.$bot_color,
								"batch" => $batch,
							];
						}
						$in_sapnik = implode(",", $data_sapnik);
					} else {
						$in_sapnik = 0;
					}
				}
				$total_twotone = !empty($data_sapnik) ? count($data_sapnik) : 0;
			} else {
				$total_twotone = 0;
				$in_sapnik = 0;
			}

			//insert docking single tone
			//get data from tabungan vlt jangan ambil data sapnik yang sudah di ambil dari twotone
			$plan_single_tone = $this->input->get("plan") - $total_twotone;
			if ($plan_single_tone > 0) { //jika terdapat planning single tone setalah plan yang tersedia di kurangi twotone
				if($in_sapnik != '0'){
					// $get_from_tabungan = $this->model->join_data("tabungan_vlt_kap2 a","twotone b","a.color_code = b.twotone","*","a.katashiki_suffix = '$suffix' AND a.sapnik NOT IN(" . $in_sapnik . ") AND a.sapnik != ''","result");
					// $get_from_tabungan = $this->model->gds("tabungan_vlt_kap2", "*", "katashiki_suffix = '$suffix' AND sapnik NOT IN(" . $in_sapnik . ") AND color_code NOT IN(".$in_tt .") AND sapnik != '' ORDER BY plan_delivery_date ASC LIMIT " . $plan_single_tone . " OFFSET 0", "result");
					
					$get_from_tabungan = $this->model->gds("tabungan_vlt_kap2", "*", "katashiki_suffix = '$suffix' AND sapnik NOT IN(" . $in_sapnik . ") AND color_code NOT IN(".$in_tt .") ".$filtering_sapnik_not_in." AND sapnik != '' ORDER BY plan_delivery_date ASC LIMIT " . $plan_single_tone . " OFFSET 0", "result");
				}else{
					// $get_from_tabungan = $this->model->gds("tabungan_vlt_kap2", "*", "katashiki_suffix = '$suffix' AND color_code NOT IN(".$in_tt .") AND sapnik != '' ORDER BY plan_delivery_date ASC LIMIT " . $plan_single_tone . " OFFSET 0", "result");
					
					$get_from_tabungan = $this->model->gds("tabungan_vlt_kap2", "*", "katashiki_suffix = '$suffix' AND color_code NOT IN(".$in_tt .") ".$filtering_sapnik_not_in." AND sapnik != '' ORDER BY plan_delivery_date ASC LIMIT " . $plan_single_tone . " OFFSET 0", "result");
				}
				if (!empty($get_from_tabungan)) {
					foreach ($get_from_tabungan as $gft) {
						if(empty($gft->twotone)){
							$data_docking[] = [
								"sapnik" => $gft->sapnik,
								"suffix" => $gft->katashiki_suffix,
								"pdd" => $gft->plan_delivery_date,
								"status_tone" => "SingleTone",
								"batch" => $batch,
								"suffix_batch" => $gft->katashiki_suffix."-".$batch,
							];

							//Data untuk HEIJUNKA WOS
							$model = $this->model->gds("plan_wos_kap2", "model_code,model_name", "suffix = '" . $gft->katashiki_suffix . "' AND batch = '".$batch."'", "row");
							if (!empty($model)) {
								$model_code = $model->model_code;
								$model_name = $model->model_name;
							} else {
								$model_code = "";
								$model_name = "";
							}

							$transmisi = substr($gft->katashiki, 8, 1);
							if ($transmisi != "M") {
								$transmisi = "Q";
							} else {
								$transmisi = "M";
							}

							$color_row = $this->model->gds_heijunka("color_model_kap2", "Background,Font,Family_Model", "Model_Name = '$model_name'", "row");
							if (!empty($color_row)) {
								$family_model = $color_row->Family_Model;
								$color = $color_row->Background . "," . $color_row->Font;
							} else {
								$family_model = "";
								$color = "";
							}

							$model_color = $gft->color_code;
							$get_bot_color = $this->model->gds_heijunka("color_both_kap2", "Bot", "Model_Warna LIKE '%$model_color%'", "row");
							if (!empty($get_bot_color)) {
								$bot_color = $get_bot_color->Bot;
							} else {
								$bot_color = "";
							}

							if (!empty($gft->wos_release_date)) {
								$wos_release_date = date("d-M-Y", strtotime($gft->wos_release_date));
							} else {
								$wos_release_date = "";
							}

							$check_tone = $this->model->gds("twotone_kap2", "twotone", "twotone = '" . $gft->color_code . "'", "row");
							if (!empty($check_tone->twotone)) {
								$tone = "TWO TONE";
							} else {
								$tone = "SINGLE TONE";
							}
							$data_docking_heijunka[] = [
								"No" => $no++,
								"WOS_Material" => $gft->wos_material,
								"WOS_Material_Description" => $gft->wos_material_description,
								"SAPNIK" => $gft->sapnik,
								"SAP_Material" => $gft->sap_material,
								"Engine_Model" => $gft->engine_model,
								"Engine_Prefix" => $gft->engine_prefix,
								"Engine_Number" => $gft->engine_number,
								"Plant" => $gft->plant,
								"Chassis_Number" => $gft->chassis_number,
								"Lot_Code" => $gft->lot_code,
								"Lot_Number" => $gft->lot_number,
								"Katashiki" => $gft->katashiki,
								"Katashiki_Sfx" => $gft->katashiki_suffix,
								"ADM_Production_Id" => $gft->adm_production_id,
								"TAM_Production_Id" => $gft->tam_production_id,
								"Plan_Delivery_Date" => date("d-M-Y", strtotime($gft->plan_delivery_date)),
								"Plan_Jig_In_Date" => date("d-M-Y", strtotime($gft->plan_jig_in_date)),
								"WOS_Release_Date" => $wos_release_date,
								"SAPWOS_DES" => $gft->sapwos_des,
								"Location" => 'No',
								"Color_Code" => $gft->color_code,
								"tone" => $tone,
								"Model" => $model_code,
								"Model_Name" => $model_name,
								"ED" => $gft->ed,
								"Order" => $gft->order_column,
								"Dest" => $gft->destination,
								"Transmisi" => $transmisi,
								"Color" => $color,
								"Bot_Color" => $bot_color,
								"Family_Model" => $family_model,
								"model_both" => $model_code.$bot_color,
								"batch" => $batch,
							];
						}
					}
				}
			}
		}

		// print_r($data_docking_heijunka);
		// die();

		
		// echo json_encode($data_docking)."<br>";
		// echo count($data_docking);
		// die();
		//insert docking to HEIJUNKA WOS DATABASE
		if(!empty($data_docking_heijunka)){
			$insert_heijunka_wos = $this->model->insert_batch_heijunka("master_kap2", $data_docking_heijunka);
			$insert_data_docking = $this->model->insert_batch("t_docking_kap2", $data_docking);
			$ssnd = "";
		}else{
			$data_snd = [
				"snd" => $snd+1,
				"tnd" => $tnd,
			];
			$ssnd = $suffix;
			$this->session->set_userdata($data_snd);
		}
		$total_suffix_docking = $this->model->gds("t_docking_kap2", "COUNT(distinct suffix_batch) as suffix", "suffix_batch != ''", "row");
		$count_docking_success = $this->model->gds("t_docking_kap2", "COUNT(sapnik) as count", "suffix_batch = '$suffix-$batch'", "row");
		$total_docking_success = $this->model->gds("t_docking_kap2", "COUNT(sapnik) as count", "suffix_batch !=''", "row");
		$fb = [
			"actual" => $count_docking_success->count,
			"total_docking" => $total_docking_success->count + $this->tnd,
			"total_suffix_docking" => $total_suffix_docking->suffix + $snd,
			"suffix_not_docking" => $snd,
			"snd" => $ssnd,
			"tnd" => $this->tnd,
		];
		echo json_encode($fb);
		die();
	}

	public function heijunka_tone()
	{
		$get_tone = $this->model->gds_heijunka("master", "tone", "SAPNIK != '' GROUP BY tone", "result");
		if (!empty($get_tone)) {
			foreach ($get_tone as $get_tone) {
				$tone = $get_tone->tone;
				$g_master_x_tone = $this->model->gds_heijunka("master", "SAPNIK", "tone = '$tone'", "result");
				$total = $this->model->gds_heijunka("master", "COUNT(SAPNIK) AS count", "tone = '$tone'", "row");
				if (!empty($g_master_x_tone)) {
					$no = 1;
					foreach ($g_master_x_tone as $gmxt) {
						$hitung_heijunka = round($no / $total->count, 9);
						$data[] = [
							"SAPNIK" => $gmxt->SAPNIK,
							"heijunka_tone" => $hitung_heijunka,
						];
						$no++;
					}
				}
			}
			$update_heijunka = $this->model->update_batch_heijunka("master", "SAPNIK", $data);
		}

		$get_data_tone = $this->model->gds_heijunka("master", "tone,SAPNIK", "SAPNIK != '' ORDER BY heijunka_tone ASC", "result");
		$j = 1;
		foreach ($get_data_tone as $get_data_tone) {
			$data_no[] = [
				"SAPNIK" => $get_data_tone->SAPNIK,
				"No" => $j,
			];
			if ($get_data_tone->tone == "TWO TONE") {
				$twotone_id[] = [
					"vin" => $get_data_tone->SAPNIK,
					"id_old" => ($j-0.5),
					"id_new" => $j,
				];
			}
			$j++;
		}
		$update_no = $this->model->update_batch_heijunka("master", "SAPNIK", $data_no);
		$insert_tt_id = $this->model->insert_batch_heijunka("twotone_id", $twotone_id);
		die();
	}

	public function clear_tabungan()
	{
		if(empty($this->input->get("t"))){
			$table_tabungan = "tabungan_vlt";
		}else{
			$table_tabungan = "tabungan_vlt_kap2";
		}
		$clear_tabungan = $this->model->delete($table_tabungan, "sapnik !=");
		$this->voice("sukses.mp3");
		$this->swal_custom_icon("Sukses", "OK, Silahkan upload Tabungan VLT", base_url('assets/images/happy.png'), "","true");
		redirect("tabungan");
	}

	public function load_tabungan()
	{
		if(empty($this->input->get("t"))){
			$table_tabungan = "tabungan_vlt";
		}else{
			$table_tabungan = "tabungan_vlt_kap2";
		}
		$data_tabungan = $this->model->gds($table_tabungan,"*","sapnik !=","result");
		$load = '';
		if(!empty($data_tabungan)){
			$no = 1;
			foreach ($data_tabungan as $dt) {
				$load .= '
				<tr>
					<td align="center">'.$no.'</td>
					<td>'.$dt->wos_material.'</td>
					<td>'.$dt->wos_material_description.'</td>
					<td>'.$dt->sapnik.'</td>
					<td>'.$dt->sap_material.'</td>
					<td align="center">'.$dt->engine_model.'</td>
					<td align="center">'.$dt->engine_prefix.'</td>
					<td>'.$dt->engine_number.'</td>
					<td align="center">'.$dt->plant.'</td>
					<td>'.$dt->chassis_number.'</td>
					<td align="center">'.$dt->lot_code.'</td>
					<td align="center">'.$dt->lot_number.'</td>
					<td align="center">'.$dt->katashiki.'</td>
					<td align="center">'.$dt->katashiki_suffix.'</td>
					<td align="center">'.$dt->adm_production_id.'</td>
					<td align="center">'.$dt->tam_production_id.'</td>
					<td align="center">'.$dt->plan_delivery_date.'</td>
					<td align="center">'.$dt->plan_jig_in_date.'</td>
					<td align="center">'.$dt->wos_release_date.'</td>
					<td align="center">'.$dt->sapwos_des.'</td>
					<td align="center">'.$dt->location.'</td>
					<td align="center">'.$dt->color_code.'</td>
					<td align="center">'.$dt->ed.'</td>
					<td align="center">'.$dt->order_column.'</td>
					<td align="center">'.$dt->destination.'</td>
				</tr>';
				$no++;
			}
		}else{
			$load .= '
			<tr>
				<td colspan="25" align="center" class="align-middle"><i>Data kosong</i></td>
			</tr>';
		}
		echo $load;
		die();
	}
}
