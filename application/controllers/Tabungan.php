<?php
defined('BASEPATH') or exit('No direct script access allowed');
date_default_timezone_set('Asia/Jakarta');
include_once(dirname(__FILE__) . "/Construct.php");

class Tabungan extends Construct
{
	// Step 1: Preprocess filter sekali aja
	private function preprocessFilter($plan) {
		$count = max(count($plan['model']), count($plan['suffix']), count($plan['color']));
		$rules = [];

		for ($i = 0; $i < $count; $i++) {
			$model  = isset($plan['model'][$i])  ? strtolower(trim($plan['model'][$i]))  : 'all';
			$suffix = isset($plan['suffix'][$i]) ? strtolower(trim($plan['suffix'][$i])) : 'all';
			$color  = isset($plan['color'][$i])  ? strtolower(trim($plan['color'][$i]))  : 'all';

			$rules[] = [$model, $suffix, $color];
		}

		return $rules; // simpan sebagai array of rules
	}

	// Step 2: Check actData lawan semua rules
	private function matchAct($rules, $act) {
		$act = array_map(function($v) {
			return strtolower(trim($v));
		}, $act);

		foreach ($rules as $rule) {
			list($m, $s, $c) = $rule;

			$okModel  = ($m === 'all' || $m === $act['model']);
			$okSuffix = ($s === 'all' || $s === $act['suffix']);
			$okColor  = ($c === 'all' || $c === $act['color']);

			if ($okModel && $okSuffix && $okColor) {
				return true; // langsung stop
			}
		}
		return false;
	}

	public function import_tabungan_vlt()
	{
		if(empty($this->input->get("t")) || $this->input->get("t") == "kap1"){
			$table_plan_wos = "plan_wos";
			$table_tabungan = "tabungan_vlt";
			$plant = "1";
		}else{
			$table_plan_wos = "plan_wos_kap2";
			$table_tabungan = "tabungan_vlt_kap2";
			$plant = "2";
		}
		//CHECK COLOR CODE FILTERING
		$color_filter = $this->model->gds("filter", "model,suffix,color", "kap = '$plant'", "result");
		$colorFilter = [];
		$suffixFilter = [];
		$modelFilter = [];
		foreach ($color_filter as $data) {
			$modelFilter[] = $data->model;
			$suffixFilter[] = $data->suffix;
			$colorFilter[] = $data->color;
		}
							
		$planningFilter = [
			'model'  => $modelFilter,
			'suffix' => $suffixFilter,
			'color'  => $colorFilter
		];
		$rules = $this->preprocessFilter($planningFilter);
		
		$temp_data = [];
		$this->model->delete("twotone_setting", "suffix_pdd !=");
		$this->model->delete("singletone_setting", "suffix_pdd !=");
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
								$this->swal_custom_icon("Gagal", "Format PDD tidak sesuai", base_url('assets/images/emot-sedih.jpg'), "rounded-circle", true);
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
							
							$exp_wmd = explode(" ", $wos_material_description); //EXPLODE WOS MATERIAL DESCRIPTION
							// if(empty($exp_wmd[2]) || empty($exp_wmd[1]) || end($exp_wmd)){
							// 	echo "Struktur WOS Material Description tidak valid.";
							// 	die();
							// }
							$actData = [
								'model'   => $exp_wmd[2],
								'suffix'  => $exp_wmd[1],
								'color'   => end($exp_wmd)
							];
							
							$judgement = $this->matchAct($rules, $actData);
							if($judgement === false){ //JIKA HASIL JUDGEMENT TIDAK TERPENUHI SEMUA MAKA LANJUTKAN INPUT
								// echo $sapnik." => ".$exp_wmd[2]." ".$exp_wmd[1]." ".end($exp_wmd)." ".json_encode($judgement)."<br />";
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
			// die();
			$this->model->delete($table_tabungan, "sapnik !=");
			if(!empty($temp_data)){
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
					$this->swal_custom_icon("Gagal", "Gagal import data tabungan", base_url('assets/images/emot-sedih.jpg'), "rounded-circle", "true");
				}
			}else{
				$this->voice("gagal.mp3");
				$this->swal_custom_icon("Gagal", "Tidak ada data yang di import", base_url('assets/images/emot-sedih.jpg'), "rounded-circle", "true");
			}
		} else {
			$this->voice("gagal.mp3");
			$this->swal_custom_icon("Gagal", "Tidak ada file yang masuk", base_url('assets/images/emot-sedih.jpg'), "rounded-circle", "true");
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
		$docking = $this->input->get("docking");
		$input_data = array_filter($data);
		$unit_single = 0;
		$unit_two = 0;
		$docking = $this->input->get("docking");
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
			if($docking == "yes"){
				$this->swal_custom_icon("Adjust OK", "Keterangan :<br>".$pesan."<br>Klik di bawah ini untuk proses docking.<br><a href='".base_url("tabungan?p=docking&docking=yes&dummy=no")."' onclick='loading(`Sedang Docking...`,``)' class='btn btn-sm btn-info mt-4'>Docking</a>", base_url('assets/images/happy.png'), "","false");
			}else{
				$this->swal_custom_icon("Adjust OK", "Keterangan :<br>".$pesan."<br>Klik di bawah ini untuk proses docking.<br><a href='".base_url("tabungan?p=docking")."' class='btn btn-sm btn-info mt-4'>Docking</a>", base_url('assets/images/happy.png'), "","false");
			}
		} else {
			$this->voice("gagal.mp3");
			$this->swal_custom_icon("Gagal", "Proses simpan data adjust gagal", base_url('assets/images/emot-sedih.jpg'), "rounded-circle","true");
		}
		redirect("adjust_twotone?docking=".$docking);
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

	// V1.2
		public function docking()
		{
			// --- 1. INISIALISASI VARIABEL ---
			$suffix = $this->input->get("suffix");
			$batch = $this->input->get("batch");
			$plan_global = $this->input->get("plan"); // Total unit yang mau diambil
			
			// Var Session
			$snd = $this->snd;
			$tnd = $this->tnd + $plan_global;

			// --- 2. PERSIAPAN FILTER ---
			
			// A. Ambil Daftar Warna Two Tone
			$db_twotone = $this->model->gds("twotone", "*", "twotone !=", "result");
			$in_tt = "";
			foreach ($db_twotone as $db_twotone) {
				$in_tt .= "'" . $db_twotone->twotone . "',";
			}
			$in_tt = rtrim($in_tt, ","); // String: '2T1','2T2',...
			
			// B. Filter SAPNIK yang sudah dipakai (Batch sebelumnya/Existing)
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

			// C. Hitung Nomor Urut Awal
			$total_docking_all = $this->model->gds("t_docking", "COUNT(sapnik) as count", "suffix !=", "row");
			$no = $total_docking_all->count + 1;

			$data_docking = [];
			$data_docking_heijunka = [];
			$current_process_sapnik = []; // Penampung biar gak duplikat saat proses berjalan

			// =========================================================
			// TAHAP 1: CEK JATAH TWO TONE (PRIORITAS UTAMA)
			// =========================================================
			
			// Cek di setting, suffix ini harus bawa berapa Two Tone?
			// Kita SUM qty-nya biar kalau ada split tanggal, keambil semua totalnya.
			$setting_tt = $this->model->gds("twotone_setting", "SUM(qty) as total_tt", "suffix = '".$suffix."'", "row");
			
			$target_tt = 0;
			if(!empty($setting_tt->total_tt)){
				$target_tt = $setting_tt->total_tt;
			}

			// Safety: Jangan sampai target Two Tone melebihi Total Plan inputan
			if($target_tt > $plan_global){
				$target_tt = $plan_global;
			}

			// =========================================================
			// TAHAP 2: AMBIL STOK TWO TONE DULU
			// =========================================================
			if ($target_tt > 0) {
				$get_twotone = $this->model->gds("tabungan_vlt", "*", 
					"katashiki_suffix = '$suffix' 
					AND color_code IN(".$in_tt .") " // Syarat Two Tone
					.$filtering_sapnik_not_in." 
					AND sapnik != '' 
					ORDER BY plan_delivery_date ASC 
					LIMIT " . $target_tt . " OFFSET 0", "result");

				if (!empty($get_twotone)) {
					foreach ($get_twotone as $gft) {
						// Masukkan Data
						$data_docking[] = [
							"sapnik" => $gft->sapnik,
							"suffix" => $gft->katashiki_suffix,
							"pdd" => $gft->plan_delivery_date,
							"status_tone" => "TwoTone",
							"batch" => $batch,
							"suffix_batch" => $gft->katashiki_suffix."-".$batch,
						];

						$data_docking_heijunka[] = $this->prep_heijunka_data($gft, $batch, $no++);
						
						// Catat Sapnik Two Tone yang udah keambil
						$current_process_sapnik[] = $gft->sapnik;
					}
				}
			}

			// Hitung berapa yang sudah didapat dari Two Tone
			$actual_tt_got = count($current_process_sapnik);
			
			// Hitung Sisa Plan yang harus dipenuhi oleh Single Tone
			$sisa_plan_st = $plan_global - $actual_tt_got;

			// =========================================================
			// TAHAP 3: ISI SISANYA DENGAN SINGLE TONE
			// =========================================================
			if ($sisa_plan_st > 0) {
				
				// Filter tambahan: Jangan ambil sapnik yang barusan diambil di Tahap 2 (Two Tone)
				$filter_tambahan = "";
				if(!empty($current_process_sapnik)){
					$str_curr = implode("','", $current_process_sapnik);
					$filter_tambahan = "AND sapnik NOT IN ('".$str_curr."')";
				}

				$get_single = $this->model->gds("tabungan_vlt", "*", 
					"katashiki_suffix = '$suffix' 
					AND color_code NOT IN(".$in_tt .") " // Syarat Single Tone
					.$filtering_sapnik_not_in." 
					".$filter_tambahan."
					AND sapnik != '' 
					ORDER BY plan_delivery_date ASC 
					LIMIT " . $sisa_plan_st . " OFFSET 0", "result");

				if (!empty($get_single)) {
					foreach ($get_single as $gft) {
						$data_docking[] = [
							"sapnik" => $gft->sapnik,
							"suffix" => $gft->katashiki_suffix,
							"pdd" => $gft->plan_delivery_date,
							"status_tone" => "SingleTone",
							"batch" => $batch,
							"suffix_batch" => $gft->katashiki_suffix."-".$batch,
						];
						
						$data_docking_heijunka[] = $this->prep_heijunka_data($gft, $batch, $no++);
					}
				}
			}

			// --- 4. EKSEKUSI INSERT DATABASE ---
			if(!empty($data_docking_heijunka)){
				$this->model->insert_batch_heijunka("master", $data_docking_heijunka);
				$this->model->insert_batch("t_docking", $data_docking);
				$ssnd = "";
			}else{
				// Kalau ZONK
				$data_snd = [
					"snd" => $snd+1,
					"tnd" => $tnd,
				];
				$ssnd = $suffix;
				$this->session->set_userdata($data_snd);
			}
			
			// --- 5. RETURN JSON ---
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

		// --- HELPER FUNCTION (Pastikan function ini ada di controller kamu) ---
		private function prep_heijunka_data($gft, $batch, $no) {
			$model = $this->model->gds("plan_wos", "model_code,model_name", "suffix = '" . $gft->katashiki_suffix . "' AND batch = '".$batch."'", "row");
			$model_code = !empty($model) ? $model->model_code : "";
			$model_name = !empty($model) ? $model->model_name : "";
			$transmisi = (substr($gft->katashiki, 8, 1) != "M") ? "Q" : "M";
			
			$color_row = $this->model->gds_heijunka("color_model", "Background,Font,Family_Model", "Model_Name = '$model_name'", "row");
			$family_model = !empty($color_row) ? $color_row->Family_Model : "";
			$color = !empty($color_row) ? $color_row->Background . "," . $color_row->Font : "";
			
			$model_color = $gft->color_code;
			$get_bot_color = $this->model->gds_heijunka("color_both", "Bot", "Model_Warna LIKE '%$model_color%'", "row");
			$bot_color = !empty($get_bot_color) ? $get_bot_color->Bot : "";
			$wos_release_date = !empty($gft->wos_release_date) ? date("d-M-Y", strtotime($gft->wos_release_date)) : "";
			
			$check_tone = $this->model->gds("twotone", "twotone", "twotone = '" . $gft->color_code . "'", "row");
			$tone = !empty($check_tone->twotone) ? "TWO TONE" : "SINGLE TONE";

			return [
				"No" => $no,
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
	// --- END OF HELPER FUNCTION ---

	// V1.2 KAP 2
		public function docking_kap2()
		{
			// --- 1. INISIALISASI VARIABEL ---
			$suffix = $this->input->get("suffix");
			$batch = $this->input->get("batch");
			$plan_global = $this->input->get("plan"); // Ambil total plan yang dibutuhkan
			
			// Var Session
			$snd = $this->snd;
			$tnd = $this->tnd + $plan_global;

			// --- 2. PERSIAPAN FILTER ---
			
			// A. Ambil Daftar Warna Two Tone (KAP 2)
			$in_tt = "";
			$db_twotone = $this->model->gds("twotone_kap2", "*", "twotone !=", "result");
			foreach ($db_twotone as $db_twotone) {
				$in_tt .= "'" . $db_twotone->twotone . "',";
			}
			$in_tt = rtrim($in_tt, ","); 
			
			// B. Filter SAPNIK yang sudah dipakai (Batch sebelumnya/Existing di Master KAP 2)
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

			// C. Hitung Nomor Urut Awal
			$total_docking_all = $this->model->gds("t_docking_kap2", "COUNT(sapnik) as count", "suffix !=", "row");
			$no = $total_docking_all->count + 1;

			$data_docking = [];
			$data_docking_heijunka = [];
			$current_process_sapnik = []; // Penampung biar gak duplikat saat proses berjalan

			// =========================================================
			// TAHAP 1: CEK JATAH TWO TONE (PRIORITAS UTAMA)
			// =========================================================
			
			// Cek di setting KAP 2
			$setting_tt = $this->model->gds("twotone_setting_kap2", "SUM(qty) as total_tt", "suffix = '".$suffix."'", "row");
			
			
			
			$target_tt = 1000; //GANTI SUPAYA TWOTONE SELALU ADA




			if(!empty($setting_tt->total_tt)){
				$target_tt = $setting_tt->total_tt;
			}

			// Safety: Jangan sampai target Two Tone melebihi Total Plan inputan
			if($target_tt > $plan_global){
				$target_tt = $plan_global;
			}

			// =========================================================
			// TAHAP 2: AMBIL STOK TWO TONE DULU
			// =========================================================
			if ($target_tt > 0) {
				$get_twotone = $this->model->gds("tabungan_vlt_kap2", "*", 
					"katashiki_suffix = '$suffix' 
					AND color_code IN(".$in_tt .") " // Syarat Two Tone
					.$filtering_sapnik_not_in." 
					AND sapnik != '' 
					ORDER BY plan_delivery_date ASC 
					LIMIT " . $target_tt . " OFFSET 0", "result");

				if (!empty($get_twotone)) {
					foreach ($get_twotone as $gft) {
						// Masukkan Data
						$data_docking[] = [
							"sapnik" => $gft->sapnik,
							"suffix" => $gft->katashiki_suffix,
							"pdd" => $gft->plan_delivery_date,
							"status_tone" => "TwoTone",
							"batch" => $batch,
							"suffix_batch" => $gft->katashiki_suffix."-".$batch,
						];

						// Helper khusus KAP 2 (Ada di bawah)
						$data_docking_heijunka[] = $this->prep_heijunka_data_kap2($gft, $batch, $no++);
						
						// Catat Sapnik Two Tone yang udah keambil
						$current_process_sapnik[] = $gft->sapnik;
					}
				}
			}

			// Hitung berapa yang sudah didapat dari Two Tone
			$actual_tt_got = count($current_process_sapnik);
			
			// Hitung Sisa Plan yang harus dipenuhi oleh Single Tone
			$sisa_plan_st = $plan_global - $actual_tt_got;

			// =========================================================
			// TAHAP 3: ISI SISANYA DENGAN SINGLE TONE
			// =========================================================
			if ($sisa_plan_st > 0) {
				
				// Filter tambahan: Jangan ambil sapnik yang barusan diambil di Tahap 2 (Two Tone)
				$filter_tambahan = "";
				if(!empty($current_process_sapnik)){
					$str_curr = implode("','", $current_process_sapnik);
					$filter_tambahan = "AND sapnik NOT IN ('".$str_curr."')";
				}

				$get_single = $this->model->gds("tabungan_vlt_kap2", "*", 
					"katashiki_suffix = '$suffix' 
					AND color_code NOT IN(".$in_tt .") " // Syarat Single Tone
					.$filtering_sapnik_not_in." 
					".$filter_tambahan."
					AND sapnik != '' 
					ORDER BY plan_delivery_date ASC 
					LIMIT " . $sisa_plan_st . " OFFSET 0", "result");

				if (!empty($get_single)) {
					foreach ($get_single as $gft) {
						$data_docking[] = [
							"sapnik" => $gft->sapnik,
							"suffix" => $gft->katashiki_suffix,
							"pdd" => $gft->plan_delivery_date,
							"status_tone" => "SingleTone",
							"batch" => $batch,
							"suffix_batch" => $gft->katashiki_suffix."-".$batch,
						];
						
						$data_docking_heijunka[] = $this->prep_heijunka_data_kap2($gft, $batch, $no++);
					}
				}
			}

			// --- 4. EKSEKUSI INSERT DATABASE KAP 2 ---
			if(!empty($data_docking_heijunka)){
				$this->model->insert_batch_heijunka("master_kap2", $data_docking_heijunka);
				$this->model->insert_batch("t_docking_kap2", $data_docking);
				$ssnd = "";
			}else{
				// Kalau ZONK
				$data_snd = [
					"snd" => $snd+1,
					"tnd" => $tnd,
				];
				$ssnd = $suffix;
				$this->session->set_userdata($data_snd);
			}
			
			// --- 5. RETURN JSON ---
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

		// --- HELPER FUNCTION KHUSUS KAP 2 ---
		// (Perhatikan nama tabelnya pakai _kap2 semua)
		private function prep_heijunka_data_kap2($gft, $batch, $no) {
			$model = $this->model->gds("plan_wos_kap2", "model_code,model_name", "suffix = '" . $gft->katashiki_suffix . "' AND batch = '".$batch."'", "row");
			$model_code = !empty($model) ? $model->model_code : "";
			$model_name = !empty($model) ? $model->model_name : "";
			$transmisi = (substr($gft->katashiki, 8, 1) != "M") ? "Q" : "M";
			
			$color_row = $this->model->gds_heijunka("color_model_kap2", "Background,Font,Family_Model", "Model_Name = '$model_name'", "row");
			$family_model = !empty($color_row) ? $color_row->Family_Model : "";
			$color = !empty($color_row) ? $color_row->Background . "," . $color_row->Font : "";
			
			$model_color = $gft->color_code;
			$get_bot_color = $this->model->gds_heijunka("color_both_kap2", "Bot", "Model_Warna LIKE '%$model_color%'", "row");
			$bot_color = !empty($get_bot_color) ? $get_bot_color->Bot : "";
			$wos_release_date = !empty($gft->wos_release_date) ? date("d-M-Y", strtotime($gft->wos_release_date)) : "";
			
			$check_tone = $this->model->gds("twotone_kap2", "twotone", "twotone = '" . $gft->color_code . "'", "row");
			$tone = !empty($check_tone->twotone) ? "TWO TONE" : "SINGLE TONE";

			return [
				"No" => $no,
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
	// --- END OF HELPER FUNCTION KAP 2 ---

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

	public function clear_tabungan_json()
	{
		header('Content-Type: application/json');
		$kap = $this->input->post('kap');
		$table_tabungan = ($kap === 'kap2') ? 'tabungan_vlt_kap2' : 'tabungan_vlt';

		$this->model->delete($table_tabungan, 'sapnik !=');
		$this->session->unset_userdata('tabungan_actual');

		echo json_encode(['status' => 'ok', 'message' => 'Tabungan ' . strtoupper($kap) . ' berhasil dikosongkan']);
		die();
	}

	public function load_tabungan()
	{
		if(empty($this->input->get("t")) || $this->input->get("t") == "kap1"){
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

    function process_tabungan_dummy_kap1()
    {
        header("Content-Type: text/plain");
        //GET DATA PLAN WOS KAP2
        $data_kap = $this->model->gds("plan_wos", "suffix,plan", "suffix != ''", "result");
        if(empty($data_kap)){
            $this->swal("Error", "Data plan wos anda kosong, silahkan upload terlebih dahulu data plan wos.", "error");
            redirect("plan_wos");
            die();
        }

        $data_input = [];
		$data_setting_tone = [];
        $this->model->delete("tabungan_vlt","sapnik !=");
        $this->model->delete("singletone_setting","suffix_pdd !=");
        $no = 1;
        foreach ($data_kap as $data) {
            $suffix = $data->suffix;
            $plan = $data->plan;
            //GET DATA DETAIL TABUNGAN VLT
            $data_tabungan = $this->model->gds("tabungan_vlt_base","*","katashiki_suffix = '$suffix'","row");
            $tabungan_encode = json_encode($data_tabungan);
            $tabungan_decode = json_decode($tabungan_encode,true);
            for ($i=0; $i < $plan; $i++) {
                if(!empty($tabungan_decode)){
					$wos_material_desc_explode = explode(" ",$tabungan_decode["wos_material_description"]);
					$model = $wos_material_desc_explode[2];
					$sapnik_dummy = $model."DUM";
                    $tabungan_decode["sapnik"] = $sapnik_dummy.date("dmy").sprintf("%04d",$no);
                    $tabungan_decode["chassis_number"] = " ".$sapnik_dummy.date("dmy").sprintf("%04d",$no)." ";
                    $data_input[] = $tabungan_decode;
					$data_setting_tone[$suffix."|".$tabungan_decode["plan_delivery_date"]][] = 1;
                    $no++;
                }
            }
        }

		$input_setting_tone = [];
		foreach ($data_setting_tone as $key => $value) {
			$input_setting_tone[] = [
				"suffix_pdd" => $key,
				"suffix" => explode("|",$key)[0],
				"pdd" => explode("|",$key)[1],
				"qty" => count($value),
				"qty_batch_1" => count($value),
				"qty_batch_2" => "0",
			];
		}

		if(!empty($input_setting_tone)){
			$this->model->insert_batch("singletone_setting",$input_setting_tone);
		}

        if(empty($data_input)){
            $this->swal("Error", "Data input tabungan kosong, silahkan check suffix apakah ada di tabungan vlt base atau qty plan wos anda kosong", "error");
            redirect("plan_wos");
            die();
        }

        $insertBatch = $this->model->insert_batch("tabungan_vlt",$data_input);
        if($insertBatch){
            $this->swal("Sukses", "Proses tabungan vlt dummy berhasil", "success");
        }else{
            $this->swal("Error", "Gagal proses tabungan vlt dummy", "error");
        }
		if($this->input->is_ajax_request()){
			echo json_encode([
				"status" => "success",
				"message" => "Proses tabungan vlt dummy berhasil"
			]);
			die();
		}else{
			redirect("tabungan?dummy=yes");
		}
    }

	public function import_tabungan_d55l()
	{
		$table_plan_wos = "plan_wos_kap2";
		$table_tabungan = "tabungan_vlt_kap2";
		$plant = "2";

		// 1. Ambil Filter Warna
		$color_filter = $this->model->gds("filter", "model,suffix,color", "kap = '$plant'", "result");
		$modelFilter = []; $suffixFilter = []; $colorFilter = [];
		foreach ($color_filter as $data) {
			$modelFilter[]  = $data->model;
			$suffixFilter[] = $data->suffix;
			$colorFilter[]  = $data->color;
		}
		
		$rules = $this->preprocessFilter([
			'model'  => $modelFilter,
			'suffix' => $suffixFilter,
			'color'  => $colorFilter
		]);

		// 2. Ambil Plan WOS dan buat Mapping (Lookup Table) buat D55L
		$getPlanWos = $this->model->gds($table_plan_wos, "model_code, suffix, plan", "model_code = 'D55L'", "result");
		
		$planMapping = [];
		foreach ($getPlanWos as $p) {
			// Simpan ke array biar aksesnya cepet (O(1) complexity)
			$planMapping[$p->model_code][$p->suffix] = true;
		}

		// 3. Bersihkan data lama
		$this->model->delete("twotone_setting", "suffix_pdd !=");
		$this->model->delete("singletone_setting", "suffix_pdd !=");
		$this->model->delete($table_tabungan, "wos_material_description LIKE '% D55L %'");

		$temp_data = [];

		if (isset($_FILES["upload-file"]["name"])) {
			$path = $_FILES["upload-file"]["tmp_name"];
			$object = PHPExcel_IOFactory::load($path);
			
			foreach ($object->getWorksheetIterator() as $worksheet) {
				$highestRow = $worksheet->getHighestRow();
				
				for ($row = 2; $row <= $highestRow; $row++) {
					$sapnik = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
					if (empty($sapnik)) continue;

					$wos_material_description = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
					$exp_wmd = explode(" ", $wos_material_description);
					
					$curr_model  = isset($exp_wmd[2]) ? $exp_wmd[2] : null;
					$curr_suffix = isset($exp_wmd[1]) ? $exp_wmd[1] : null;
					$curr_color  = end($exp_wmd);

					// --- GATEKEEPER: Cek apakah Model & Suffix ini ada di Plan D55L ---
					if (!isset($planMapping[$curr_model][$curr_suffix])) {
						continue; 
					}

					$actData = [
						'model'  => $curr_model,
						'suffix' => $curr_suffix,
						'color'  => $curr_color
					];

					// Cek Judgement Warna
					if ($this->matchAct($rules, $actData) === false) {
						
						$temp_data[] = [
							'wos_material'             => $worksheet->getCellByColumnAndRow(1, $row)->getValue(),
							'wos_material_description' => $wos_material_description,
							'sapnik'                   => str_replace(" ", "", $sapnik),
							'sap_material'             => $worksheet->getCellByColumnAndRow(4, $row)->getValue(),
							'engine_model'             => $worksheet->getCellByColumnAndRow(5, $row)->getValue(),
							'engine_prefix'            => $worksheet->getCellByColumnAndRow(6, $row)->getValue(),
							'engine_number'            => $worksheet->getCellByColumnAndRow(7, $row)->getValue(),
							'plant'                    => $worksheet->getCellByColumnAndRow(8, $row)->getValue(),
							'chassis_number'           => $worksheet->getCellByColumnAndRow(9, $row)->getValue(),
							'lot_code'                 => $worksheet->getCellByColumnAndRow(10, $row)->getValue(),
							'lot_number'               => $worksheet->getCellByColumnAndRow(11, $row)->getValue(),
							'katashiki'                => $worksheet->getCellByColumnAndRow(12, $row)->getValue(),
							'katashiki_suffix'         => $curr_suffix,
							'adm_production_id'        => $worksheet->getCellByColumnAndRow(14, $row)->getValue(),
							'tam_production_id'        => $worksheet->getCellByColumnAndRow(15, $row)->getValue(),
							'plan_delivery_date'       => $this->_parse_pdd($worksheet->getCellByColumnAndRow(16, $row)->getValue()),
							'plan_jig_in_date'         => $this->_parse_pjid($worksheet->getCellByColumnAndRow(17, $row)->getValue()),
							'wos_release_date'         => $this->_parse_wrd($worksheet->getCellByColumnAndRow(18, $row)->getValue()),
							'sapwos_des'               => $worksheet->getCellByColumnAndRow(19, $row)->getValue(),
							'location'                 => $worksheet->getCellByColumnAndRow(20, $row)->getValue(),
							'color_code'               => $worksheet->getCellByColumnAndRow(21, $row)->getValue(),
							'ed'                       => $worksheet->getCellByColumnAndRow(22, $row)->getValue(),
							'order_column'             => $worksheet->getCellByColumnAndRow(23, $row)->getValue(),
							'destination'              => $worksheet->getCellByColumnAndRow(24, $row)->getValue(),
						];
					}
				}
			}

			// 4. Proses Simpan Batch
			if (!empty($temp_data)) {
				$insert = $this->model->insert_batch($table_tabungan, $temp_data);
				if ($insert) {
					
					// --- LOGIKA CEK STOK (DARI KODE LAMA LO) ---
					$get_plan_suffix = $this->model->gds($table_plan_wos, "suffix,SUM(plan) as plan,model_name", "plan IS NOT NULL OR plan != '' GROUP BY suffix ORDER BY model_name ASC", "result");
					
					$status = [];
					foreach ($get_plan_suffix as $gps) {
						$get_tabungan = $this->model->gds($table_tabungan, "COUNT(sapnik) AS tabungan", "katashiki_suffix = '" . $gps->suffix . "'", "row");
						$tabungan_count = (!empty($get_tabungan->tabungan)) ? $get_tabungan->tabungan : 0;

						if ($tabungan_count >= $gps->plan) {
							$status[] = ["status" => "OK"];
						} else {
							$status[] = [
								"status"   => "NOK",
								"unit"     => $gps->model_name,
								"suffix"   => $gps->suffix,
								"plan"     => $gps->plan,
								"tabungan" => $tabungan_count
							];
						}
					}

					$nok_data = array_filter($status, function ($var) {
						return ($var['status'] == 'NOK');
					});

					if (!empty($nok_data)) {
						$table_nok = "<table class='table table-sm table-hover table-bordered' style='font-size:10pt;'><tr><th>Model</th><th>Suffix</th><th>Plan</th><th>Tabungan</th></tr>";
						foreach ($nok_data as $value) {
							$table_nok .= '<tr><td>' . $value["unit"] . '</td><td>' . $value["suffix"] . '</td><td>' . $value["plan"] . '</td><td>' . $value["tabungan"] . '</td></tr>';
						}
						$table_nok .= '</table>';
						$this->swal_custom_icon("Maaf...", "Stock Tabungan Tidak Cukup...<br><h5 align='left' class='mt-2 mb-2'>List Tabungan Tidak Cukup</h5>" . $table_nok, base_url('assets/images/emot-sedih.jpg'), "", "true");
					} else {
						redirect("tabungan?p=kap2");
					}

				} else {
					$this->_response_error("Gagal import data tabungan ke database");
				}
			} else {
				$this->_response_error("Tidak ada data D55L yang cocok untuk di-import");
			}

		} else {
			$this->_response_error("Tidak ada file yang dipilih");
		}
	}

	// --- HELPER PARSING ---
	private function _parse_pdd($val) {
		if (empty($val)) return NULL;
		$p = explode(" ", $val);
		return (count($p) >= 3) ? "20" . $p[2] . "-" . $this->month_import($p[1]) . "-" . $p[0] : NULL;
	}

	private function _parse_pjid($val) {
		if (empty($val)) return NULL;
		return substr($val, 0, 4) . "-" . substr($val, 4, 2) . "-" . substr($val, 6, 2);
	}

	private function _parse_wrd($val) {
		if (empty($val)) return NULL;
		return substr($val, 4, 4) . "-" . substr($val, 2, 2) . "-" . substr($val, 0, 2);
	}

	private function _response_error($msg) {
		$this->voice("gagal.mp3");
		$this->swal_custom_icon("Gagal", $msg, base_url('assets/images/emot-sedih.jpg'), "rounded-circle", "true");
		redirect("tabungan");
	}
}
