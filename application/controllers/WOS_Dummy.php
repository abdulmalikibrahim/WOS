<?php
defined('BASEPATH') OR exit('No direct script access allowed');
date_default_timezone_set('Asia/Jakarta');
include_once (dirname(__FILE__) . "/Construct.php");

class WOS_Dummy extends Construct {
	public function index()
	{
		$data["title"] = "Docking WOS Dummy to WOS With VIN KAP2";
		$data["content"] = "view/docking_wos_dummy";
		$data["javascript"] = "docking_wos_dummy";
		$this->load->view('layout/index',$data);
	}

	public function import_pis_kap2()
	{
		$clear_data = $this->model->delete("pis_kap2", "No !=");
		if (isset($_FILES["upload-file"]["name"])) {
			$path = $_FILES["upload-file"]["tmp_name"];
			$object = PHPExcel_IOFactory::load($path);
			foreach ($object->getWorksheetIterator() as $worksheet) {
				$highestRow = $worksheet->getHighestRow();
				$highestColumn = $worksheet->getHighestColumn();
				for ($row = $highestRow; $row >= 2; $row--) {
					$sapnik = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
					if (!empty($sapnik)) {
						$no = $worksheet->getCellByColumnAndRow(0, $row)->getValue();
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
							$year_pdd = $pdd[2];
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
								
							$color_row = $this->model->gds_heijunka("color_model_kap2", "Background,Font", "Model_Name = '".$model_name."'", "row");
							if (!empty($color_row)) {
								$color = $color_row->Background . "," . $color_row->Font;
							} else {
								$color = "";
							}

							$model_color = $model_code . $color_code;
							$get_bot_color = $this->model->gds_heijunka("color_both_kap2", "Bot", "Model_Warna = '$model_color'", "row");
							if (!empty($get_bot_color)) {
								$bot_color = $get_bot_color->Bot;
							} else {
								$bot_color = "";
							}
							
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
			$insert = $this->model->insert_batch("pis_kap2", $temp_data);
			if ($insert) {
				$this->session->set_userdata(array("pis_dummy" => "YES"));
				$this->voice("sukses.mp3");
				$this->swal_custom_icon("Sukses", "Data PIS berhasil di upload", base_url('assets/images/happy.png'), "","true");
			} else {
				$this->voice("gagal.mp3");
				$this->swal_custom_icon("Gagal", 'Data PIS gagal di upload', base_url('assets/images/emot-sedih.jpg'), "rounded-circle","false");
			}
		} else {
            $this->voice("gagal.mp3");
			$this->swal_custom_icon("Gagal", 'Tidak ada file yang masuk', base_url('assets/images/emot-sedih.jpg'), "rounded-circle","false");
		}
		redirect("docking_wos_dummy");
	}

	public function load_pis_kap2()
	{
		$data_tabungan = $this->model->gds("pis_kap2","*","sapnik !=","result");
		$load = '';
		if(!empty($data_tabungan)){
			$no = 1;
			foreach ($data_tabungan as $dt) {
				$load .= '
				<tr>
					<td align="center">'.$no.'</td>
					<td>'.$dt->WOS_Material.'</td>
					<td>'.$dt->WOS_Material_Description.'</td>
					<td>'.$dt->SAPNIK.'</td>
					<td>'.$dt->SAP_Material.'</td>
					<td align="center">'.$dt->Engine_Model.'</td>
					<td align="center">'.$dt->Engine_Prefix.'</td>
					<td>'.$dt->Engine_Number.'</td>
					<td align="center">'.$dt->Plant.'</td>
					<td>'.$dt->Chassis_Number.'</td>
					<td align="center">'.$dt->Lot_Code.'</td>
					<td align="center">'.$dt->Lot_Number.'</td>
					<td align="center">'.$dt->Katashiki.'</td>
					<td align="center">'.$dt->Katashiki_Sfx.'</td>
					<td align="center">'.$dt->ADM_Production_Id.'</td>
					<td align="center">'.$dt->TAM_Production_Id.'</td>
					<td align="center">'.$dt->Plan_Delivery_Date.'</td>
					<td align="center">'.$dt->Plan_Jig_In_Date.'</td>
					<td align="center">'.$dt->WOS_Release_Date.'</td>
					<td align="center">'.$dt->SAPWOS_DES.'</td>
					<td align="center">'.$dt->Location.'</td>
					<td align="center">'.$dt->Color_Code.'</td>
					<td align="center">'.$dt->ED.'</td>
					<td align="center">'.$dt->Order.'</td>
					<td align="center">'.$dt->Dest.'</td>
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

	public function docking_dummy()
	{
		header('Content-Type: application/json');
		//CLEAR DATA USE VLT
		$update_data = ["use_vlt" => "0"];
		$this->model->update("tabungan_vlt_kap2","sapnik !=",$update_data);

		$dataPisDummy = $this->model->gds("pis_kap2","Katashiki_Sfx,Transmisi,Color,Bot_Color,Model,Model_Name","No != '' ORDER BY No ASC","result");
		if(empty($dataPisDummy)){
			$this->voice("gagal.mp3");
			$this->swal_custom_icon("Gagal", 'Sepertinya anda belum upload data PIS', base_url('assets/images/emot-sedih.jpg'), "rounded-circle","false");
			redirect("docking_wos_dummy");
		}

		$data_input = [];
		$suffix_not_docking = [];
		$count_pis_kap2 = $this->model->gds("pis_kap2","COUNT(No) as counting","No !=","row");
		$no = $count_pis_kap2->counting;
		foreach ($dataPisDummy as $dpd) {
			$suffix = $dpd->Katashiki_Sfx;
			//GET DATA TABUNGAN
			$data_tabungan = $this->model->gds("tabungan_vlt_kap2","*","katashiki_suffix = '$suffix' AND color_code != 'R75' AND use_vlt = '0' ORDER BY plan_delivery_date ASC","row");
			
			if(!empty($data_tabungan)){
				$transmisi = $dpd->Transmisi;
				$color = $dpd->Color;
				$bot_color = $dpd->Bot_Color;
				$model_code = $dpd->Model;
				$model_name = $dpd->Model_Name;
	
				$data_input[] = array(
					'No' => $no,
					'WOS_Material' => $data_tabungan->wos_material,
					'WOS_Material_Description' => $data_tabungan->wos_material_description,
					'SAPNIK' => $data_tabungan->sapnik,
					'SAP_Material' => $data_tabungan->sap_material,
					'Engine_Model' => $data_tabungan->engine_model,
					'Engine_Prefix' => $data_tabungan->engine_prefix,
					'Engine_Number' => $data_tabungan->engine_number,
					'Plant' => $data_tabungan->plant,
					'Chassis_Number' => $data_tabungan->chassis_number,
					'Lot_Code' => $data_tabungan->lot_code,
					'Lot_Number' => $data_tabungan->lot_number,
					'Katashiki' => $data_tabungan->katashiki,
					'Katashiki_Sfx' => $data_tabungan->katashiki_suffix,
					'ADM_Production_Id' => $data_tabungan->adm_production_id,
					'TAM_Production_Id' => $data_tabungan->tam_production_id,
					'Plan_Delivery_Date' => $data_tabungan->plan_delivery_date,
					'Plan_Jig_In_Date' => $data_tabungan->plan_jig_in_date,
					'WOS_Release_Date' => $data_tabungan->wos_release_date,
					'SAPWOS_DES' => $data_tabungan->sapwos_des,
					'Location' => $data_tabungan->location,
					'Color_Code' => $data_tabungan->color_code,
					'ED' => $data_tabungan->ed,
					'Model' => $model_code,
					"Model_Name" => $model_name,
					'Order' => $data_tabungan->order_column,
					'Dest' => $data_tabungan->destination,
					"Transmisi" => $transmisi,
					"Color" => $color,
					"Bot_Color" => $bot_color,
				);
				$no--;

				//UPDATE USE VLT
				$update_data = ["use_vlt" => "1"];
				$this->model->update("tabungan_vlt_kap2","sapnik = '".$data_tabungan->sapnik."'",$update_data);
			}else{
				$suffix_not_docking[] = $dpd->Katashiki_Sfx;
			}
		}

		if(!empty($suffix_not_docking)){
			$SND = array_count_values($suffix_not_docking);
			$htmlSuffixNotDocking = "<ul style='width: 170px;' class='mt-2'>";
			foreach ($SND as $key => $value) {
				$htmlSuffixNotDocking .= "<li style='text-align:left;'>".$key." (".$value." Data)</li>";
			}
			$htmlSuffixNotDocking .= '</ul>';
			$this->voice("gagal.mp3");
			$this->swal_custom_icon("Gagal docking WOS", 'Tabungan tidak mencukupi data docking,<br>berikut informasi suffix yang kekurangan data tabungan : <br>'.$htmlSuffixNotDocking, base_url('assets/images/emot-sedih.jpg'), "rounded-circle","false");
			redirect("docking_wos_dummy");
		}

		// print_r($data_input);
		// die();
		//CLEAR DATA
		$this->model->delete_heijunka("master_kap2","No !=");
		//UPDATE DATA KAP2
		$this->model->insert_batch_heijunka("master_kap2",$data_input);
		$this->swal_custom_icon("Sukses", 'Docking WOS Dummy to WOS VIN KAP 2 Berhasil, Silahkan lanjut proses download PIS dan Hardcopy', base_url('assets/images/happy.png'), "","true");
		$this->voice("sukses.mp3");
		redirect("heijunka_wos_kap2?no_dummy=yes");
	}
}
