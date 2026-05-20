<?php
defined('BASEPATH') OR exit('No direct script access allowed');
date_default_timezone_set('Asia/Jakarta');
include_once (dirname(__FILE__) . "/Construct.php");

class Plan_WOS extends Construct {
	public function import_plan_wos(){
		if(empty($this->input->get("t"))){
			$type = "";
			$plant = "KAP1";
		}else{
			$type = $this->input->get("t");
			$plant = strtoupper($this->input->get("t"));
		}
		//CHECK SUFFIX NOT INCLUDE
		$takeout_suffix = $this->model->gds("filtering_suffix","nilai","plant = '$plant'","result_array");
		$info_takeout_suffix = [];
		$pokayoke_d7a_multiples5 = $this->model->gds("setting_pokayoke","status","name = 'D74A Multiples of 5'","row");
		$pokayoke_d55l_multiples5 = $this->model->gds("setting_pokayoke","status","name = 'D55L Multiples of 5'","row");
		$d74a_count_b1 = 0;
		$d74a_count_b2 = 0;

		$d55l_count_b1 = 0;
		$d55l_count_b2 = 0;
		$temp_data = [];
		if (isset($_FILES["upload-file"]["name"])) {
			$path = $_FILES["upload-file"]["tmp_name"];
			$object = PHPExcel_IOFactory::load($path);
			$batch = 1;
			foreach($object->getWorksheetIterator() as $worksheet)
			{
				$highestRow = $worksheet->getHighestRow();
				$highestColumn = $worksheet->getHighestColumn();
				for($row=2; $row<=$highestRow; $row++)
				{
					$model_code = $worksheet->getCellByColumnAndRow(0, $row)->getValue();
					$model_name = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
					$brand = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
					$katashiki = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
					$suffix = strtoupper($worksheet->getCellByColumnAndRow(4, $row)->getValue());
					$plan = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
					if($plan > 0){
						$plan = $plan;
						if($plant == "KAP2"){
							if($model_code == "D74A"){
								if($batch == "1"){
									$d74a_count_b1 += $plan;
								}
								
								if($batch == "2"){
									$d74a_count_b2 += $plan;
								}
							}
							
							if($model_code == "D55L"){
								if($batch == "1"){
									$d55l_count_b1 += $plan;
								}
								
								if($batch == "2"){
									$d55l_count_b2 += $plan;
								}
							}
						}
					}else{
						$plan = NULL;
					}
					$check_array = in_array($suffix,array_column($takeout_suffix,"nilai"));
					if(empty($check_array)){ //SUFFIX DI PERBOLEHKAN MASUK
						if(!empty($model_code) && !empty($model_name) && !empty($brand) && !empty($katashiki) && !empty($suffix) && !empty($plan)){
							$temp_data[] = array(
								'suffix_batch' => $suffix."-".$batch,
								'model_code' => $model_code,
								'model_name' => $model_name,
								'brand' => $brand,
								'katashiki' => $katashiki,
								'suffix' => $suffix,
								'plan' => $plan,
								'batch' => $batch,
							);
						}
					}else{
						if($plan > 0){
							$info_takeout_suffix[$suffix] = $suffix;
						}
					}
				}
				$batch++;
			}

			if(($pokayoke_d7a_multiples5->status > 0 || $pokayoke_d55l_multiples5->status > 0) && $plant == "KAP2"){
				$pokayoke_ng = "";
				foreach (['Batch 1 D74A' => $d74a_count_b1, 'Batch 2 D74A' => $d74a_count_b2, 'Batch 1 D55L' => $d55l_count_b1, 'Batch 2 D55L' => $d55l_count_b2] as $label => $count) {
					if($count > 0){
						if ($count % 5 !== 0) {
							$pokayoke_ng .= "$label bukan kelipatan 5, Total = $count data<br>";
						}
					}
				}

				if(!empty($pokayoke_ng)){
					$this->voice("gagal.mp3");
					$this->swal_custom_icon("Gagal", $pokayoke_ng, base_url('assets/images/emot-sedih.jpg'), "rounded-circle", "true");
					redirect("plan_wos");
				}
			}

			if($plant == "KAP1"){
				//KAP 1
				$clear_plan_wos = $this->model->delete("plan_wos","suffix !=");
				$clear_docking = $this->model->delete("t_docking","sapnik !=");
				$clear_twotone_setting = $this->model->delete("twotone_setting","suffix_pdd !=");
				if(!empty($temp_data)){
					$insert = $this->model->insert_batch("plan_wos",$temp_data);
				} else {
					$insert = false;
				}
			}else{
				//KAP 2
				$clear_plan_wos = $this->model->delete("plan_wos_kap2","suffix !=");
				$clear_docking = $this->model->delete("t_docking_kap2","sapnik !=");
				$clear_twotone_setting = $this->model->delete("twotone_setting_kap2","suffix_pdd !=");
				if(!empty($temp_data)){
					$insert = $this->model->insert_batch("plan_wos_kap2",$temp_data);
				} else {
					$insert = false;
				}
			}
			if($insert){
				$this->voice("sukses.mp3");
				$url_tabungan_dummy = $plant == "KAP1" ? base_url("process_tabungan_dummy_kap1") : base_url("process_tabungan_dummy_kap2");
				if(empty($info_takeout_suffix)){
					$this->swal_custom_icon("Sukses","OK, Lanjutkan untuk upload tabungan, dan klik tetap disini untuk upload Plan WOS yang lain<br><a href='".base_url("tabungan")."' class='btn btn-sm btn-info mt-4'>Tabungan VLT</a><a href='".base_url("tabungan?plant=$plant&dummy=yes")."' class='btn btn-sm btn-info mt-4 ml-3'>Proses Tabungan Dummy</a><a href='javascript:void(0)' onclick='swal_close()' class='btn btn-sm btn-secondary mt-4 ml-3'>Tetap Disini</a>",base_url('assets/images/happy.png'),"","false");
				}else{
					$suffix_takeout = implode(",",$info_takeout_suffix); 
					$this->swal_custom_icon("Warning","Plan WOS berhasil upload.<br>Sistem takeout Suffix : ".$suffix_takeout."<br><a href='".base_url("tabungan")."' class='btn btn-sm btn-info mt-4'>Tabungan VLT</a><a href='".base_url("tabungan?plant=$plant&dummy=yes")."' class='btn btn-sm btn-info mt-4 ml-3'>Proses Tabungan Dummy</a><a href='javascript:void(0)' onclick='swal_close()' class='btn btn-sm btn-secondary mt-4 ml-3'>Tetap Disini</a>",base_url('assets/images/emot-sedih.jpg'),"","false");
				}
			}else{
				$this->voice("gagal.mp3");
				$this->swal_custom_icon("Gagal","Gagal import data",base_url('assets/images/emot-sedih.jpg'),"rounded-circle","true");
			}
		}else{
			$this->voice("gagal.mp3");
			$this->swal_custom_icon("Gagal","Tidak ada file yang masuk",base_url('assets/images/emot-sedih.jpg'),"rounded-circle","true");
		}
		redirect("plan_wos");
	}

	public function clear_plan_wos()
	{
		if(empty($this->input->get("t"))){
			//KAP 1
			$clear_plan_wos = $this->model->delete("plan_wos","suffix !=");
			$clear_docking = $this->model->delete("t_docking","sapnik !=");
			$clear_twotone_setting = $this->model->delete("twotone_setting","suffix_pdd !=");
			$clear_data_heijunka = $this->model->delete_heijunka("master","SAPNIK !=");
		}else{
			//KAP 2
			$clear_plan_wos = $this->model->delete("plan_wos_kap2","suffix !=");
			$clear_docking = $this->model->delete("t_docking_kap2","sapnik !=");
			$clear_twotone_setting = $this->model->delete("twotone_setting_kap2","suffix_pdd !=");
			$clear_data_heijunka = $this->model->delete_heijunka("master_kap2","SAPNIK !=");
		}
		$this->swal_custom_icon("Sukses","OK, Silahkan upload Plan WOS",base_url('assets/images/happy.png'),"","true");
		$this->voice("sukses.mp3");
		redirect("plan_wos");
	}
}
