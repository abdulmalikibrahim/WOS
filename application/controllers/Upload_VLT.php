<?php
defined('BASEPATH') or exit('No direct script access allowed');
date_default_timezone_set('Asia/Jakarta');
include_once(dirname(__FILE__) . "/Construct.php");

class Upload_VLT extends Construct
{
	public function import_vlt() //FINISH
	{
		header("Content-Type:text/event-stream");
		$clear_data = $this->model->delete("table_vlt", "sapnik !=");
		if (isset($_FILES["upload-file"])) {
			$upload_files = $_FILES["upload-file"];
			foreach ($upload_files["name"] as $index => $filename) {
				$path = $upload_files["tmp_name"][$index];
				$object = PHPExcel_IOFactory::load($path);
				foreach ($object->getWorksheetIterator() as $worksheet) {
					$highestRow = $worksheet->getHighestRow();
					$highestColumn = $worksheet->getHighestColumn();
					for ($row = 2; $row <= $highestRow; $row++) {
						$sapnik = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
						if (!empty($sapnik)) {
							$sap_material = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
							$engine_model = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
							$engine_prefix = $worksheet->getCellByColumnAndRow(6, $row)->getValue();
							$engine_number = $worksheet->getCellByColumnAndRow(7, $row)->getValue();
							$plant = $worksheet->getCellByColumnAndRow(8, $row)->getValue();
							$chassis_number = $worksheet->getCellByColumnAndRow(9, $row)->getValue();
							$lot_code = $worksheet->getCellByColumnAndRow(10, $row)->getValue();
							$lot_number = $worksheet->getCellByColumnAndRow(11, $row)->getValue();
							$katashiki = $worksheet->getCellByColumnAndRow(12, $row)->getValue();
							$katashiki_suffix = $worksheet->getCellByColumnAndRow(10, $row)->getValue();
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
							$ed = $worksheet->getCellByColumnAndRow(22, $row)->getValue();
							$order_column = $worksheet->getCellByColumnAndRow(23, $row)->getValue();
							$destination = $worksheet->getCellByColumnAndRow(24, $row)->getValue();
							if(substr_count($destination,"TMMIN") > 0){
								$destination = "DOM";
							}
							$rrn_number = $worksheet->getCellByColumnAndRow(26, $row)->getValue();
							$destination_sequence_number = $worksheet->getCellByColumnAndRow(27, $row)->getValue();
							$material_description = $worksheet->getCellByColumnAndRow(30, $row)->getValue();
							$destination_code = $worksheet->getCellByColumnAndRow(31, $row)->getValue();
							$destination_description = $worksheet->getCellByColumnAndRow(32, $row)->getValue();
							$equipment_number = $worksheet->getCellByColumnAndRow(34, $row)->getValue();
							$rdd = $worksheet->getCellByColumnAndRow(36, $row)->getValue();
							if (!empty($rdd)) {
								$year_rdd = substr($rdd, 0, 4);
								$month_rdd = substr($rdd, 4, 2);
								$day_rdd = substr($rdd, 6, 2);
								$revise_del_date = $year_rdd . "-" . $month_rdd . "-" . $day_rdd;
							} else {
								$revise_del_date = NULL;
							}
							if (!empty($sapnik)) {
								$temp_data[$sapnik] = array(
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
									'rrn_number' => $rrn_number,
									'destination_sequence_number' => $destination_sequence_number,
									'material_description' => $material_description,
									'destination_code' => $destination_code,
									'destination_description' => $destination_description,
									'equipment_number' => $equipment_number,
									'revise_del_date' => $revise_del_date,
								);
							}
						}
					}
				}
			}
			// print_r($temp_data);
			// echo count($temp_data);
			// die();
			$insert = $this->model->insert_batch("table_vlt", $temp_data);
			if ($insert) {
				$this->voice("sukses.mp3");
				$this->swal_custom_icon("Sukses", "Data VLT berhasil di upload", base_url('assets/images/happy.png'), "", true);
			} else {
				$this->voice("gagal.mp3");
				$this->swal_custom_icon("Gagal", "Data VLT gagal di upload", base_url('assets/images/emot-sedih.jpg'), "rounded-circle", true);
			}
		} else {
			$this->voice("gagal.mp3");
			$this->swal_custom_icon("Gagal", "Tidak ada file yang masuk", base_url('assets/images/emot-sedih.jpg'), "rounded-circle");
		}
		redirect("upload_vlt");
	}

	public function save_adjust()
	{
		$data = $this->input->post("input-data");
		$input_data = array_filter($data);
		$unit_single = 0;
		$unit_two = 0;
		foreach ($input_data as $key => $value) {
			$explode = explode("|", $key);
			$suffix = $explode[0];
			$pdd = $explode[1];
			$tone = $explode[2];
			if($tone == "singletone"){
				$data_insert_single[] = [
					"suffix_pdd" => $suffix."|".$pdd,
					"suffix" => $suffix,
					"pdd" => $pdd,
					"qty" => $value,
				];
				$unit_single += $value;
			}else{
				$data_insert_two[] = [
					"suffix_pdd" => $suffix."|".$pdd,
					"suffix" => $suffix,
					"pdd" => $pdd,
					"qty" => $value,
				];
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
	public function download_txt()
	{
	    $type = $this->input->get("type");
	    if ($type != "DOM") {
		  $search_type = "destination != 'DOM'";
	    } else {
		  $search_type = "destination = 'DOM'";
	    }
	
	    $filename = "DOWNLOAD DATA " . $type;
	    $data = $this->model->gds("table_vlt", "*", "sapnik != '' AND " . $search_type, "result");
	
	    if (!empty($data)) {
		  ob_start();
		  foreach ($data as $row) {
			if (!empty($row->wos_release_date)) {
			    $wos_release_date = date("dmY", strtotime($row->wos_release_date));
			} else {
			    $wos_release_date = "";
			}
	
			$txtcontent .=
			    $row->sapnik . "\t" .
			    $row->sap_material . "\t" .
			    $row->engine_model . "\t" .
			    $row->engine_prefix . "\t" .
			    $row->engine_number . "\t" .
			    $row->plant . "\t" .
			    $row->chassis_number . "\t" .
			    $row->lot_code . "\t" .
			    $row->lot_number . "\t" .
			    $row->katashiki . "\t" .
			    $row->katashiki_suffix . "\t" .
			    $row->adm_production_id . "\t" .
			    $row->tam_production_id . "\t" .
			    date("d M y", strtotime($row->plan_delivery_date)) . "\t" .
			    date("Ymd", strtotime($row->plan_jig_in_date)) . "\t" .
			    $wos_release_date . "\t" .
			    $row->sapwos_des . "\t" .
			    $row->location . "\t" .
			    $row->color_code . "\t" .
			    $row->ed . "\t" .
			    $row->order_column . "\t" .
			    $row->destination . "\t" .
			    $row->rrn_number . "\t" .
			    $row->destination_sequence_number . "\t" .
			    $row->material_description . "\t" .
			    $row->destination_code . "\t" .
			    $row->destination_description . "\t" .
			    $row->equipment_number . "\t" .
			    date("Ymd", strtotime($row->revise_del_date)) . "\r\n";
		  }
		  ob_end_clean();
	
		  header('Content-Type: text/plain');
		  header('Content-Disposition: attachment; filename=' . $filename . '.txt');
		  header('Cache-Control: max-age=0');
	
		  echo $txtcontent;
	    }
	}
	
	// public function download_txt()
	// {
      //       $this->load->library('excel');
	// 	$excel = new PHPExcel();
	// 	header("Content-Type:text/event-stream");
	// 	$type = $this->input->get("type");
	// 	if($type != "DOM"){
	// 		$search_type = "destination != 'DOM'";
	// 	}else{
	// 		$search_type = "destination = 'DOM'";
	// 	}

	// 	$filename = "DOWNLOAD DATA ".$type;
	// 	// header ("Content-type: application/vnd-ms-excel");
	// 	// header("Content-Disposition: attachment;filename = ".$filename);
	// 	$sheet = $excel->getActiveSheet(0);
	// 	$style_col = array(
	// 		'alignment' => array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER),
	// 		'borders' => array(
	// 			'allborders' => array(
	// 				'style' => PHPExcel_Style_Border::BORDER_THIN
	// 			)
	// 		)
	// 	);

	// 	$data = $this->model->gds("table_vlt","*","sapnik != '' AND ".$search_type,"result");
	// 	if(!empty($data)){
	// 		$numrow = 1;
	// 		foreach ($data as $row) {
	// 			if(!empty($row->wos_release_date)){
	// 				$wos_release_date = date("dmY",strtotime($row->wos_release_date));
	// 			}else{
	// 				$wos_release_date = "";
	// 			}
	// 			$excel->setActiveSheetIndex(0)->setCellValue('A' . $numrow, $row->sapnik);
	// 			$excel->setActiveSheetIndex(0)->setCellValue('B' . $numrow, $row->sap_material);
	// 			$excel->setActiveSheetIndex(0)->setCellValue('C' . $numrow, $row->engine_model);
	// 			$excel->setActiveSheetIndex(0)->setCellValue('D' . $numrow, $row->engine_prefix);
	// 			$excel->setActiveSheetIndex(0)->setCellValue('E' . $numrow, $row->engine_number);
	// 			$excel->setActiveSheetIndex(0)->setCellValue('F' . $numrow, $row->plant);
	// 			$excel->setActiveSheetIndex(0)->setCellValue('G' . $numrow, $row->chassis_number);
	// 			$excel->setActiveSheetIndex(0)->setCellValue('H' . $numrow, $row->lot_code);
	// 			$excel->setActiveSheetIndex(0)->setCellValue('I' . $numrow, $row->lot_number);
	// 			$excel->setActiveSheetIndex(0)->setCellValue('J' . $numrow, $row->katashiki);
	// 			$excel->setActiveSheetIndex(0)->setCellValue('K' . $numrow, $row->katashiki_suffix);
	// 			$excel->setActiveSheetIndex(0)->setCellValue('L' . $numrow, $row->adm_production_id);
	// 			$excel->setActiveSheetIndex(0)->setCellValue('M' . $numrow, $row->tam_production_id);
	// 			$excel->setActiveSheetIndex(0)->setCellValue('N' . $numrow, date("d M y",strtotime($row->plan_delivery_date)));
	// 			$excel->setActiveSheetIndex(0)->setCellValue('O' . $numrow, date("Ymd",strtotime($row->plan_jig_in_date)));
	// 			$excel->setActiveSheetIndex(0)->setCellValue('P' . $numrow, $wos_release_date);
	// 			$excel->setActiveSheetIndex(0)->setCellValue('Q' . $numrow, $row->sapwos_des);
	// 			$excel->setActiveSheetIndex(0)->setCellValue('R' . $numrow, $row->location);
	// 			$excel->setActiveSheetIndex(0)->setCellValue('S' . $numrow, $row->color_code);
	// 			$excel->setActiveSheetIndex(0)->setCellValue('T' . $numrow, $row->ed);
	// 			$excel->setActiveSheetIndex(0)->setCellValue('U' . $numrow, $row->order_column);
	// 			$excel->setActiveSheetIndex(0)->setCellValue('V' . $numrow, $row->destination);
	// 			$excel->setActiveSheetIndex(0)->setCellValue('W' . $numrow, $row->rrn_number);
	// 			$excel->setActiveSheetIndex(0)->setCellValue('X' . $numrow, $row->destination_sequence_number);
	// 			$excel->setActiveSheetIndex(0)->setCellValue('Y' . $numrow, $row->material_description);
	// 			$excel->setActiveSheetIndex(0)->setCellValue('Z' . $numrow, $row->destination_code);
	// 			$excel->setActiveSheetIndex(0)->setCellValue('AA' . $numrow, $row->destination_description);
	// 			$excel->setActiveSheetIndex(0)->setCellValue('AB' . $numrow, $row->equipment_number);
	// 			$excel->setActiveSheetIndex(0)->setCellValue('AC' . $numrow, date("Ymd",strtotime($row->revise_del_date)));
	// 			// $txtcontent .= "<tr><td>".$row->sapnik."</td>";
	// 			// $txtcontent .= "<td>".$row->sap_material."</td>";
	// 			// $txtcontent .= "<td>".$row->engine_model."</td>";
	// 			// $txtcontent .= "<td>".$row->engine_prefix."</td>";
	// 			// $txtcontent .= "<td>".$row->engine_number."</td>";
	// 			// $txtcontent .= "<td>".$row->plant."</td>";
	// 			// $txtcontent .= "<td>".$row->chassis_number."</td>";
	// 			// $txtcontent .= "<td>".$row->lot_code."</td>";
	// 			// $txtcontent .= "<td>".$row->lot_number."</td>";
	// 			// $txtcontent .= "<td>".$row->katashiki."</td>";
	// 			// $txtcontent .= "<td>".$row->katashiki_suffix."</td>";
	// 			// $txtcontent .= "<td>".$row->adm_production_id."</td>";
	// 			// $txtcontent .= "<td>".$row->tam_production_id."</td>";
	// 			// $txtcontent .= "<td>".date("'d M y",strtotime($row->plan_delivery_date))."</td>";
	// 			// $txtcontent .= "<td>".date("Ymd",strtotime($row->plan_jig_in_date))."</td>";
	// 			// $txtcontent .= "<td>".$wos_release_date."</td>";
	// 			// $txtcontent .= "<td>".$row->sapwos_des."</td>";
	// 			// $txtcontent .= "<td>".$row->location."</td>";
	// 			// $txtcontent .= "<td>".$row->color_code."</td>";
	// 			// $txtcontent .= "<td>".$row->ed."</td>";
	// 			// $txtcontent .= "<td>".$row->order_column."</td>";
	// 			// $txtcontent .= "<td>".$row->destination."</td>";
	// 			// $txtcontent .= "<td>".$row->rrn_number."</td>";
	// 			// $txtcontent .= "<td>".$row->destination_sequence_number."</td>";
	// 			// $txtcontent .= "<td>".$row->material_description."</td>";
	// 			// $txtcontent .= "<td>".$row->destination_code."</td>";
	// 			// $txtcontent .= "<td>".$row->destination_description."</td>";
	// 			// $txtcontent .= "<td>".$row->equipment_number."</td>";
	// 			// $txtcontent .= "<td>".date("Ymd",strtotime($row->revise_del_date))."</td></tr>";
	// 			$numrow++;
	// 		}
	// 		$sheet->getColumnDimension('A')->setWidth(22.09);
	// 		$sheet->getColumnDimension('B')->setWidth(17.91);
	// 		$sheet->getColumnDimension('C')->setWidth(10.82);
	// 		$sheet->getColumnDimension('D')->setWidth(8.55);
	// 		$sheet->getColumnDimension('E')->setWidth(8.55);
	// 		$sheet->getColumnDimension('F')->setWidth(8.55);
	// 		$sheet->getColumnDimension('G')->setWidth(20.27);
	// 		$sheet->getColumnDimension('H')->setWidth(4.64);
	// 		$sheet->getColumnDimension('I')->setWidth(7.27);
	// 		$sheet->getColumnDimension('J')->setWidth(15.91);
	// 		$sheet->getColumnDimension('K')->setWidth(4.64);
	// 		$sheet->getColumnDimension('L')->setWidth(13.45);
	// 		$sheet->getColumnDimension('M')->setWidth(13.36);
	// 		$sheet->getColumnDimension('N')->setWidth(10.45);
	// 		$sheet->getColumnDimension('O')->setWidth(10.45);
	// 		$sheet->getColumnDimension('P')->setWidth(11.18);
	// 		$sheet->getColumnDimension('Q')->setWidth(30.91);
	// 		$sheet->getColumnDimension('R')->setWidth(8.55);
	// 		$sheet->getColumnDimension('S')->setWidth(5.64);
	// 		$sheet->getColumnDimension('T')->setWidth(8.82);
	// 		$sheet->getColumnDimension('U')->setWidth(4.82);
	// 		$sheet->getColumnDimension('V')->setWidth(6.73);
	// 		$sheet->getColumnDimension('W')->setWidth(17);
	// 		$sheet->getColumnDimension('X')->setWidth(29.91);
	// 		$sheet->getColumnDimension('Y')->setWidth(55);
	// 		$sheet->getColumnDimension('Z')->setWidth(7);
	// 		$sheet->getColumnDimension('AA')->setWidth(13.64);
	// 		$sheet->getColumnDimension('AB')->setWidth(25);
	// 		$sheet->getColumnDimension('AC')->setWidth(10);
	// 		$excel->getActiveSheet()->getStyle('A1:BB999')->getAlignment()->setWrapText(true);
	// 		// $txtcontent .= '</table>';
	// 	}
	// 	print_r($excel);
	// 	die();
	// 	ob_end_clean();
	// 	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	// 	header('Content-Disposition: attachment; filename=' . $filename . '.xlsx');
	// 	header('Cache-Control: max-age=0');
	// 	$write = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
	// 	$write->save('php://output');
	// 	error_reporting(E_ALL);
	// 	die();
	// }

	public function docking()
	{
		$suffix = $this->input->get("suffix");
		$snd = $this->snd;
		$tnd = $this->tnd + $this->input->get("plan");
		$db_twotone = $this->model->gds("twotone", "*", "twotone !=", "result");
		$in_tt = '';
		foreach ($db_twotone as $db_twotone) {
			$in_tt .= "'" . $db_twotone->twotone . "',";
		}
		$in_tt = rtrim($in_tt, ",");
		//Cari suffix ini ada di plan twotone atau tidak
		$validasi_twotone = $this->model->gds("twotone_setting", "suffix,pdd,qty", "suffix = '".$suffix."'", "row");
		$total_docking_all = $this->model->gds("t_docking", "COUNT(sapnik) as count", "suffix !=", "row");
		$no = $total_docking_all->count + 1;
		if (empty($validasi_twotone)) { //jika tidak ada settingan twotone
			//Get Data All Single Tone
			$plan = $this->input->get("plan");

			$validasi_singletone = $this->model->gds("singletone_setting", "suffix,pdd,qty", "suffix = '".$suffix."'", "row");
			if(empty($validasi_singletone)){ //jika tidak ada data setting single tone
				$get_from_tabungan = $this->model->gds("tabungan_vlt", "*", "katashiki_suffix = '$suffix' AND color_code NOT IN(".$in_tt .") AND sapnik != '' ORDER BY plan_delivery_date ASC LIMIT " . $plan . " OFFSET 0", "result");
				if (!empty($get_from_tabungan)) {
					foreach ($get_from_tabungan as $gft) {
						if(empty($gft->twotone)){
							$data_docking[] = [
								"sapnik" => $gft->sapnik,
								"suffix" => $gft->katashiki_suffix,
								"pdd" => $gft->plan_delivery_date,
								"status_tone" => "SingleTone",
							];

							//Data untuk HEIJUNKA WOS
							$model = $this->model->gds("plan_wos", "model_code,model_name", "suffix = '" . $gft->katashiki_suffix . "'", "row");
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
							];
						}
					}
				}
			}else{ //jika ada settingan singletone
				//insert docking twotone first
				$get_plan_singletone = $this->model->gds("singletone_setting", "pdd,qty", "suffix = '$suffix'", "result");
				if (!empty($get_plan_singletone)) {
					foreach ($get_plan_singletone as $gpt) {
						//get data from tabungan vlt
						$get_from_tabungan = $this->model->gds("tabungan_vlt", "*", "katashiki_suffix = '$suffix' AND color_code NOT IN(" . $in_tt . ") AND plan_delivery_date = '" . $gpt->pdd . "' AND sapnik != '' ORDER BY plan_delivery_date ASC LIMIT " . $gpt->qty . " OFFSET 0", "result");
						if (!empty($get_from_tabungan)) {
							foreach ($get_from_tabungan as $gft) {
								$data_docking[] = [
									"sapnik" => $gft->sapnik,
									"suffix" => $gft->katashiki_suffix,
									"pdd" => $gft->plan_delivery_date,
									"status_tone" => "SingleTone",
								];
								$data_sapnik[] = "'" . $gft->sapnik . "'";

								//Data untuk HEIJUNKA WOS
								$model = $this->model->gds("plan_wos", "model_code,model_name", "suffix = '" . $gft->katashiki_suffix . "'", "row");
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
								];
							}
						}
					}
				}
			}
		} else { //jika ada settingan twotone
			//insert docking twotone first
			$get_plan_twotone = $this->model->gds("twotone_setting", "pdd,qty", "suffix = '$suffix'", "result");
			if (!empty($get_plan_twotone)) {
				foreach ($get_plan_twotone as $gpt) {
					//get data from tabungan vlt
					$get_from_tabungan = $this->model->gds("tabungan_vlt", "*", "katashiki_suffix = '$suffix' AND color_code IN(" . $in_tt . ") AND plan_delivery_date = '" . $gpt->pdd . "' AND sapnik != '' ORDER BY plan_delivery_date ASC LIMIT " . $gpt->qty . " OFFSET 0", "result");
					if (!empty($get_from_tabungan)) {
						foreach ($get_from_tabungan as $gft) {
							$data_docking[] = [
								"sapnik" => $gft->sapnik,
								"suffix" => $gft->katashiki_suffix,
								"pdd" => $gft->plan_delivery_date,
								"status_tone" => "TwoTone",
							];
							$data_sapnik[] = "'" . $gft->sapnik . "'";

							//Data untuk HEIJUNKA WOS
							$model = $this->model->gds("plan_wos", "model_code,model_name", "suffix = '" . $gft->katashiki_suffix . "'", "row");
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
							];
						}
						$in_sapnik = implode(",", $data_sapnik);
					} else {
						$in_sapnik = 0;
					}
				}
				$sum_plan_twotone = $this->model->gds("twotone_setting", "SUM(qty) as total_twotone", "suffix = '$suffix'", "row");
				$total_twotone = $sum_plan_twotone->total_twotone;
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
					$get_from_tabungan = $this->model->gds("tabungan_vlt", "*", "katashiki_suffix = '$suffix' AND sapnik NOT IN(" . $in_sapnik . ") AND color_code NOT IN(".$in_tt .") AND sapnik != '' ORDER BY plan_delivery_date ASC LIMIT " . $plan_single_tone . " OFFSET 0", "result");
				}else{
					$get_from_tabungan = $this->model->gds("tabungan_vlt", "*", "katashiki_suffix = '$suffix' AND color_code NOT IN(".$in_tt .") AND sapnik != '' ORDER BY plan_delivery_date ASC LIMIT " . $plan_single_tone . " OFFSET 0", "result");
				}
				if (!empty($get_from_tabungan)) {
					foreach ($get_from_tabungan as $gft) {
						if(empty($gft->twotone)){
							$data_docking[] = [
								"sapnik" => $gft->sapnik,
								"suffix" => $gft->katashiki_suffix,
								"pdd" => $gft->plan_delivery_date,
								"status_tone" => "SingleTone",
							];

							//Data untuk HEIJUNKA WOS
							$model = $this->model->gds("plan_wos", "model_code,model_name", "suffix = '" . $gft->katashiki_suffix . "'", "row");
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
		$total_suffix_docking = $this->model->gds("t_docking", "COUNT(distinct suffix) as suffix", "suffix != ''", "row");
		$count_docking_success = $this->model->gds("t_docking", "COUNT(sapnik) as count", "suffix = '$suffix'", "row");
		$total_docking_success = $this->model->gds("t_docking", "COUNT(sapnik) as count", "suffix !=", "row");
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

	public function clear_vlt() //FINISH
	{
		$clear_vlt = $this->model->delete("table_vlt", "sapnik !=");
		$this->voice("sukses.mp3");
		$this->swal_custom_icon("Sukses", "OK, Silahkan upload Tabungan VLT", base_url('assets/images/happy.png'), "","true");
		redirect("upload_vlt");
	}

	public function load_vlt() //FINISH
	{
		$data_tabungan = $this->model->gds("table_vlt","*","sapnik !=","result");
		$load = '';
		if(!empty($data_tabungan)){
			$no = 1;
			foreach ($data_tabungan as $dt) {
				$load .= '
				<tr>
					<td align="center">'.$no.'</td>
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
					<td align="center">'.$dt->rrn_number.'</td>
					<td align="center">'.$dt->destination_sequence_number.'</td>
					<td align="center">'.$dt->material_description.'</td>
					<td align="center">'.$dt->destination_code.'</td>
					<td align="center">'.$dt->destination_description.'</td>
					<td align="center">'.$dt->equipment_number.'</td>
					<td align="center">'.$dt->revise_del_date.'</td>
				</tr>';
				$no++;
			}
		}else{
			$load .= '
			<tr>
				<td colspan="25" align="center"><i>Data kosong</i></td>
			</tr>';
		}
		echo $load;
		die();
	}
}
