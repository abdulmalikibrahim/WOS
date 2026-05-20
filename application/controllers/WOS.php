<?php
defined('BASEPATH') OR exit('No direct script access allowed');
date_default_timezone_set('Asia/Jakarta');
include_once (dirname(__FILE__) . "/Construct.php");

class WOS extends Construct {
	public function err404()
	{
		$this->load->view("errors/html/error_404");
	}
	public function index()
	{
		$data = [
			"open" => "Open",
		];
		$this->session->set_userdata($data);
		if(!empty($this->session->userdata("open"))){
			$data["open_first"] = "Silent";
		}else{
			$data["open_first"] = "Voice";
		}
		$data["title"] = "WOS Creation System";
		$data["content"] = "view/index";
		$this->load->view('layout/index',$data);
	}
	public function plan_wos()
	{
		$data["title"] = "Plan WOS";
		$data["javascript"] = "plan_wos";
		$data["content"] = "view/plan_wos";
		$this->load->view('layout/index',$data);
	}
	public function upload_vlt()
	{
		$data["title"] = "Upload VLT";
		$data["javascript"] = "upload_vlt";
		$data["content"] = "view/upload_vlt";
		$this->load->view('layout/index',$data);
	}
	public function show_nik()
	{
		$data["title"] = "NIK DOMESTIK";
		$data["javascript"] = "show_nik";
		$data["content"] = "view/show_nik";
		$this->load->view('layout/index',$data);
	}
	public function pkb_service_part()
	{
		$data["title"] = "PKB Service Part";
		$data["javascript"] = "pkb_service_part";
		$data["content"] = "view/pkb_service_part";
		$this->load->view('layout/index',$data);
	}
	public function create_pkb()
	{
		$data["title"] = "Create PKB";
		$data["javascript"] = "create_pkb";
		$data["content"] = "view/create_pkb";
		$this->load->view('layout/index',$data);
	}
	public function adjust_twotone()
	{
		// header("Content-Type: application/json");
		$docking = $this->input->get("docking");
		if($docking == "yes"){
			//CHECK APAKAH TABUNGAN DAN PLAN SEIMBANG
			$tabungan = $this->model->gds("tabungan_vlt","lot_code,COUNT(sapnik) AS total","lot_code != '' GROUP BY lot_code","result_array");
			$pis_kap = $this->model->gds("pis_kap1","Lot_Code,COUNT(Lot_Code) as qty","Lot_Code != '' GROUP BY Lot_Code","result_array");

			// Mapping Tabungan ke Key-Value
			$map_tabungan = [];
			foreach ($tabungan as $t) {
				$map_tabungan[$t['lot_code']] = (int)$t['total'];
			}

			$list_kurang = [];
			foreach ($pis_kap as $p) {
				$lot = $p['Lot_Code'];
				$qty_pis = (int)$p['qty'];
				$qty_tabungan = isset($map_tabungan[$lot]) ? $map_tabungan[$lot] : 0;

				// FILTER: Munculin cuma jika Tabungan KURANG dari PIS
				if ($qty_tabungan < $qty_pis) {
					$kurangnya = $qty_pis - $qty_tabungan;
					$list_kurang[] = [
						'lot_code' => $lot,
						'tabungan' => $qty_tabungan,
						'pis_kap'  => $qty_pis,
						'kurang'   => $kurangnya
					];
				}
			}

			if(!empty($list_kurang)){
				$data['list_kurang'] = $list_kurang;
				$data['title'] = "TABUNGAN KURANG";
				$data['content'] = "view/lot_shortage"; 
				$this->load->view("layout/index", $data);
				return;
			}
			
			$plan_wos_base = $this->model->gds("plan_wos_base","*","suffix !=","result_array");
			
			// Gabung berdasarkan Lot_Code = suffix
			$result = [];
			foreach ($pis_kap as $item1) {
				$found = false;
				foreach ($plan_wos_base as $item2) {
					if ($item1["Lot_Code"] == $item2["suffix"]) {
						$found = true;
						$merged = $item2;
						$merged["plan"] = $item1["qty"]; // ubah plan jadi qty
						$merged["suffix_batch"] = $item2["suffix"] . "-1"; // tambah suffix_batch
						$merged["batch"] = "1";
						$result[] = $merged;
						break;
					}
				}

				// kalau nggak ketemu, tetap masukin minimal qty-nya biar aman
				if (!$found) {
					$result[] = [
						"model_code" => null,
						"model_name" => null,
						"brand" => null,
						"katashiki" => null,
						"suffix" => $item1["Lot_Code"],
						"plan" => $item1["qty"],
						"suffix_batch" => $item1["Lot_Code"] . "-1",
						"batch" => "1",
					];
				}
			}
			$this->model->delete("plan_wos","batch !=");
			$this->model->insert_batch("plan_wos", $result);
		}
		$data["title"] = "Adjust TwoTone";
		$data["javascript"] = "adjust_twotone";
		$data["content"] = "view/adjust_twotone";
		$this->load->view('layout/index',$data);
	}
	public function heijunka_wos()
	{
		$this->load->view('content/view/heijunka_wos');
	}
	public function heijunka_wos_kap2()
	{
		$this->load->view('content/view/heijunka_wos_kap2');
	}
	public function heijunka_wos_print()
	{
		$this->load->view('content/view/heijunka_wos_print');
	}
	public function download_docking()
	{
		$this->load->view('content/view/download_docking');
	}
	public function tabungan()
	{
		$data["title"] = "Tabungan VLT";
		$data["javascript"] = "tabungan";
		$data["content"] = "view/tabungan";
		$this->load->view('layout/index',$data);
	}
	
	// public function download_u_pis()
	// {
	// 	//   $this->load->view("content/view/download_u_pis");
	// 	$this->load->library('excel');
	// 	$excel = new PHPExcel();
	// 	$now = date('d-m-Y');
	// 	$pdd = $this->input->post("pdd");
	// 	$plan_jig_in = $this->input->post("plan_jig_in");
	// 	$dummy = $this->input->post("dummy");
	// 	//Header File Excel
	// 	$excel->setActiveSheetIndex(0)->setCellValue('A1', "No.");
	// 	$excel->setActiveSheetIndex(0)->setCellValue('B1', "WOS Material");
	// 	$excel->setActiveSheetIndex(0)->setCellValue('C1', "WOS Material Description");
	// 	$excel->setActiveSheetIndex(0)->setCellValue('D1', "SAPNIK");
	// 	$excel->setActiveSheetIndex(0)->setCellValue('E1', "SAP Material");
	// 	$excel->setActiveSheetIndex(0)->setCellValue('F1', "Engine Model");
	// 	$excel->setActiveSheetIndex(0)->setCellValue('G1', "Engine Prefix");
	// 	$excel->setActiveSheetIndex(0)->setCellValue('H1', "Engine Number");
	// 	$excel->setActiveSheetIndex(0)->setCellValue('I1', "Plant");
	// 	$excel->setActiveSheetIndex(0)->setCellValue('J1', "Chassis Number");
	// 	$excel->setActiveSheetIndex(0)->setCellValue('K1', "Lot Code");
	// 	$excel->setActiveSheetIndex(0)->setCellValue('L1', "Lot Number");
	// 	$excel->setActiveSheetIndex(0)->setCellValue('M1', "Katashiki");
	// 	$excel->setActiveSheetIndex(0)->setCellValue('N1', "Katashiki Sfx");
	// 	$excel->setActiveSheetIndex(0)->setCellValue('O1', "ADM Production ID");
	// 	$excel->setActiveSheetIndex(0)->setCellValue('P1', "TAM Production ID");
	// 	$excel->setActiveSheetIndex(0)->setCellValue('Q1', "Plan Delivery Date");
	// 	$excel->setActiveSheetIndex(0)->setCellValue('R1', "Plan Jig In Date");
	// 	$excel->setActiveSheetIndex(0)->setCellValue('S1', "WOS Release Date");
	// 	$excel->setActiveSheetIndex(0)->setCellValue('T1', "SAPWOS DES");
	// 	$excel->setActiveSheetIndex(0)->setCellValue('U1', "Location");
	// 	$excel->setActiveSheetIndex(0)->setCellValue('V1', "Color Code");
	// 	$excel->setActiveSheetIndex(0)->setCellValue('W1', "Model");
	// 	$excel->setActiveSheetIndex(0)->setCellValue('X1', "ED");
	// 	$excel->setActiveSheetIndex(0)->setCellValue('Y1', "Order");
	// 	$excel->setActiveSheetIndex(0)->setCellValue('Z1', "Dest");
	// 	$number = 1;
	// 	$i = 1;
	// 	$key = 0;
	// 	$numrow = 2;
	// 	$data_wos_arr = $this->model->gds_heijunka("master","*","No != '' ORDER BY batch,No DESC","result");
	// 	$data_wos = $this->model->union_heijunka("ORDER BY batch,No DESC");
	// 	$count_wos = count($data_wos);
	// 	foreach ($data_wos as $data_wos) {
	// 		$pid = "3Z".date("md",strtotime($pdd)).sprintf("%04d",$number);
	// 		if($data_wos->heijunka_tone == "TD-Link"){
	// 			$color_row = "#FFFFFF";
	// 			$color_font = "#000000";
	// 			$status = "TD-Link";
	// 			$wos_release_date = "";
	// 		}else if($data_wos->heijunka_tone == "D74A-LINK"){
	// 			$color_row = "#FFFFFF";
	// 			$color_font = "#000000";
	// 			$status = "D74A-LINK";
	// 			$wos_release_date = "";
	// 		}else{
	// 			$explode = explode(',', $data_wos->Color);
	// 			$color_row = $explode[0];
	// 			if(!empty($explode[1])){
	// 				$color_font = $explode[1];
	// 			}else{
	// 				$color_font = "#FFFFFF";
	// 			}
	// 			$tone = $data_wos->tone;
				
	// 			if($key <= 0){
	// 				$no = 1;
	// 				$bc_bef = $data_wos_arr[$key]->Bot_Color;
	// 			}else{
	// 				if(!empty($data_wos_arr[$key-1]->Bot_Color)){
	// 					$bc_bef = $data_wos_arr[$key-1]->Bot_Color;
	// 					if($data_wos_arr[$key-1]->Bot_Color == $data_wos->Bot_Color){
	// 						$no = $no;
	// 					}else{
	// 						$no = 1;
	// 					}
	// 				}
	// 			}
	
	// 			if($data_wos->Bot_Color == "A"){
	// 				if($no > 2){
	// 				$status = "NG";
	// 				}else{
	// 				$status = "OK";
	// 				}
	// 				$no++;
	// 			}else if($data_wos->Bot_Color == "B"){
	// 				if($no > 2){
	// 				$status = "NG";
	// 				}else{
	// 				$status = "OK";
	// 				}
	// 				$no++;
	// 			}else{
	// 				$status = "OK";
	// 			}
	// 			if(!empty($data_wos->WOS_Release_Date)){
	// 				$wos_release_date = date("dmY",strtotime($data_wos->WOS_Release_Date));
	// 			}else{
	// 				$wos_release_date = "";
	// 			}
	// 		}

	// 		$pdd = $dummy == "yes" ? $pdd : $data_wos->Plan_Delivery_Date;
	// 		$excel->setActiveSheetIndex(0)->setCellValue('A' . $numrow, $i);
	// 		$excel->setActiveSheetIndex(0)->setCellValue('B' . $numrow, $data_wos->WOS_Material);
	// 		$excel->setActiveSheetIndex(0)->setCellValue('C' . $numrow, $data_wos->WOS_Material_Description);
	// 		$excel->setActiveSheetIndex(0)->setCellValue('D' . $numrow, $data_wos->SAPNIK);
	// 		$excel->setActiveSheetIndex(0)->setCellValueExplicit('E' . $numrow, $data_wos->SAP_Material, PHPExcel_Cell_DataType::TYPE_STRING);
	// 		$excel->setActiveSheetIndex(0)->setCellValue('F' . $numrow, $data_wos->Engine_Model);
	// 		$excel->setActiveSheetIndex(0)->setCellValue('G' . $numrow, $data_wos->Engine_Prefix);
	// 		$excel->setActiveSheetIndex(0)->setCellValue('H' . $numrow, $data_wos->Engine_Number);
	// 		$excel->setActiveSheetIndex(0)->setCellValue('I' . $numrow, $data_wos->Plant);
	// 		$excel->setActiveSheetIndex(0)->setCellValue('J' . $numrow, $data_wos->Chassis_Number);
	// 		$excel->setActiveSheetIndex(0)->setCellValue('K' . $numrow, $data_wos->Lot_Code);
	// 		$excel->setActiveSheetIndex(0)->setCellValue('L' . $numrow, $data_wos->Lot_Number);
	// 		$excel->setActiveSheetIndex(0)->setCellValue('M' . $numrow, $data_wos->Katashiki);
	// 		$excel->setActiveSheetIndex(0)->setCellValue('N' . $numrow, $data_wos->Katashiki_Sfx);
	// 		$excel->setActiveSheetIndex(0)->setCellValue('O' . $numrow, $pid);
	// 		$excel->setActiveSheetIndex(0)->setCellValue('P' . $numrow, $data_wos->TAM_Production_Id);
	// 		$excel->setActiveSheetIndex(0)->setCellValue('Q' . $numrow, strtoupper(date("d M Y",strtotime($pdd))));
	// 		$excel->setActiveSheetIndex(0)->setCellValue('R' . $numrow, date("Ymd",strtotime($plan_jig_in)));
	// 		$excel->setActiveSheetIndex(0)->setCellValue('S' . $numrow, $wos_release_date);
	// 		$excel->setActiveSheetIndex(0)->setCellValue('T' . $numrow, $data_wos->Model == "X02X" ? $data_wos->WOS_Material."".date("Ymd",strtotime($plan_jig_in)) : $data_wos->SAPWOS_DES);
	// 		$excel->setActiveSheetIndex(0)->setCellValue('U' . $numrow, $data_wos->Location);
	// 		$excel->setActiveSheetIndex(0)->setCellValue('V' . $numrow, $data_wos->Color_Code);
	// 		$excel->setActiveSheetIndex(0)->setCellValue('W' . $numrow, $data_wos->Model);
	// 		$excel->setActiveSheetIndex(0)->setCellValue('X' . $numrow, $data_wos->ED);
	// 		$excel->setActiveSheetIndex(0)->setCellValue('Y' . $numrow, $data_wos->Order);
	// 		$excel->setActiveSheetIndex(0)->setCellValue('Z' . $numrow, $data_wos->Dest);
			
	// 		$sheet = $excel->getActiveSheet(0);
	// 		$row_style = array(
	// 			'fill' => array(
	// 				'type' => PHPExcel_Style_Fill::FILL_SOLID,
	// 				'color' => array('rgb' => str_replace('#','',$color_row)),
	// 			),
	// 			'font' => array(
	// 				'color' => array('rgb' => str_replace('#','',$color_font)),
	// 				'name' => 'Trebuchet MS',
	// 			),
	// 			'borders' => array(
	// 				'allborders' => array(
	// 					'style' => PHPExcel_Style_Border::BORDER_THIN,
	// 					'color' => array('rgb' => '000000'),
	// 				)
	// 			)
	// 		);
	// 		$sheet->getStyle('A'.$numrow.':Z'.$numrow)->applyFromArray($row_style);
	// 		$i++;
	// 		if($data_wos->heijunka_tone != "TD-Link"){
	// 			$key++;
	// 		}else if($data_wos->heijunka_tone != "D74A-LINK"){
	// 			$key++;
	// 		}
	// 		$number++;
	// 		$numrow++;
	// 	}
	// 	$excel->getActiveSheet()->getStyle('A1:Z1500')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	// 	$excel->getActiveSheet()->getStyle('A1:Z1500')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	// 	$header_style = array(
	// 		'fill' => array(
	// 			'type' => PHPExcel_Style_Fill::FILL_SOLID,
	// 			'color' => array('rgb' => '00FF00'),
	// 		),
	// 		'font' => array(
	// 			'color' => array('rgb' => '000000'),
	// 			'name' => 'Trebuchet MS',
	// 			'bold' => true,
	// 		),
	// 		'borders' => array(
	// 			'allborders' => array(
	// 				'style' => PHPExcel_Style_Border::BORDER_THIN,
	// 				'color' => array('rgb' => '000000'),
	// 			)
	// 		)
	// 	);
	// 	$sheet->getStyle('A1:Z1')->applyFromArray($header_style);
	// 	$sheet->getRowDimension('1')->setRowHeight(55);
	// 	$sheet->getColumnDimension('A')->setWidth(5);
	// 	$sheet->getColumnDimension('B')->setWidth(24);
	// 	$sheet->getColumnDimension('C')->setWidth(48);
	// 	$sheet->getColumnDimension('D')->setWidth(22);
	// 	$sheet->getColumnDimension('E')->setWidth(22);
	// 	$sheet->getColumnDimension('F')->setWidth(18);
	// 	$sheet->getColumnDimension('G')->setWidth(19);
	// 	$sheet->getColumnDimension('H')->setWidth(19);
	// 	$sheet->getColumnDimension('I')->setWidth(10);
	// 	$sheet->getColumnDimension('J')->setWidth(27);
	// 	$sheet->getColumnDimension('K')->setWidth(12);
	// 	$sheet->getColumnDimension('L')->setWidth(15);
	// 	$sheet->getColumnDimension('M')->setWidth(17);
	// 	$sheet->getColumnDimension('N')->setWidth(15);
	// 	$sheet->getColumnDimension('O')->setWidth(20);
	// 	$sheet->getColumnDimension('P')->setWidth(20);
	// 	$sheet->getColumnDimension('Q')->setWidth(20);
	// 	$sheet->getColumnDimension('R')->setWidth(20);
	// 	$sheet->getColumnDimension('S')->setWidth(20);
	// 	$sheet->getColumnDimension('T')->setWidth(31);
	// 	$sheet->getColumnDimension('U')->setWidth(12);
	// 	$sheet->getColumnDimension('V')->setWidth(14);
	// 	$sheet->getColumnDimension('W')->setWidth(11);
	// 	$sheet->getColumnDimension('X')->setWidth(12);
	// 	$sheet->getColumnDimension('Y')->setWidth(11);
	// 	$sheet->getColumnDimension('Z')->setWidth(11);
	// 	$excel->getActiveSheet()->getStyle('A1:AC'.$numrow)->getAlignment()->setWrapText(true);
	// 	$excel->setActiveSheetIndex(0);
	// 	ob_end_clean();
	// 	$filename = "UPLOAD WOS KAP1 PDD ".date("d-m-Y",strtotime($pdd));
	// 	header('Content-Tyep: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	// 	header('Content-Disposition: attachment; filename=' . $filename . '.xls');
	// 	header('Cache-Control: max-age=0');
	// 	$write = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
	// 	$write->save('php://output');
	// 	error_reporting(E_ALL);
	// 	exit();
				
	// }
	
	public function download_u_pis()
	{
		header("Content-Type:text/plain");
		//   $this->load->view("content/view/download_u_pis");
		$this->load->library('excel');
		$excel = new PHPExcel();
		$now = date('d-m-Y');
		$dummy = $this->input->post("dummy");
		$pdd = $this->input->post("pdd");
		$plan_jig_in = $this->input->post("plan_jig_in");
		$start_vin = $this->input->post("start_vin");
		//Header File Excel
		$excel->setActiveSheetIndex(0)->setCellValue('A1', "No.");
		$excel->setActiveSheetIndex(0)->setCellValue('B1', "WOS Material");
		$excel->setActiveSheetIndex(0)->setCellValue('C1', "WOS Material Description");
		$excel->setActiveSheetIndex(0)->setCellValue('D1', "SAPNIK");
		$excel->setActiveSheetIndex(0)->setCellValue('E1', "SAP Material");
		$excel->setActiveSheetIndex(0)->setCellValue('F1', "Engine Model");
		$excel->setActiveSheetIndex(0)->setCellValue('G1', "Engine Prefix");
		$excel->setActiveSheetIndex(0)->setCellValue('H1', "Engine Number");
		$excel->setActiveSheetIndex(0)->setCellValue('I1', "Plant");
		$excel->setActiveSheetIndex(0)->setCellValue('J1', "Chassis Number");
		$excel->setActiveSheetIndex(0)->setCellValue('K1', "Lot Code");
		$excel->setActiveSheetIndex(0)->setCellValue('L1', "Lot Number");
		$excel->setActiveSheetIndex(0)->setCellValue('M1', "Katashiki");
		$excel->setActiveSheetIndex(0)->setCellValue('N1', "Katashiki Sfx");
		$excel->setActiveSheetIndex(0)->setCellValue('O1', "ADM Production ID");
		$excel->setActiveSheetIndex(0)->setCellValue('P1', "TAM Production ID");
		$excel->setActiveSheetIndex(0)->setCellValue('Q1', "Plan Delivery Date");
		$excel->setActiveSheetIndex(0)->setCellValue('R1', "Plan Jig In Date");
		$excel->setActiveSheetIndex(0)->setCellValue('S1', "WOS Release Date");
		$excel->setActiveSheetIndex(0)->setCellValue('T1', "SAPWOS DES");
		$excel->setActiveSheetIndex(0)->setCellValue('U1', "Location");
		$excel->setActiveSheetIndex(0)->setCellValue('V1', "Color Code");
		$excel->setActiveSheetIndex(0)->setCellValue('W1', "Model");
		$excel->setActiveSheetIndex(0)->setCellValue('X1', "ED");
		$excel->setActiveSheetIndex(0)->setCellValue('Y1', "Order");
		$excel->setActiveSheetIndex(0)->setCellValue('Z1', "Dest");
		$number = empty($start_vin) ? 1 : $start_vin;
		$i = 1;
		$key = 0;
		$numrow = 2;
		$data_sess = ["pdd_pis_kap1" => $pdd];
		$this->session->set_userdata($data_sess);
		$data_wos_arr = $this->model->gds_heijunka("master","*","No != '' ORDER BY batch,No DESC","result");
		$data_wos = $this->model->union_heijunka("ORDER BY batch,No DESC");
		$count_wos = count($data_wos);
		foreach ($data_wos as $data_wos) {
			$pid = "3Z".substr(date("ymd", strtotime($pdd)), 1).sprintf("%04d",$number);
			if($data_wos->heijunka_tone != "TD-Link"){
				$explode = explode(',', $data_wos->Color);
				$color_row = $explode[0];
				if(!empty($explode[1])){
					$color_font = $explode[1];
				}else{
					$color_font = "#FFFFFF";
				}
				$tone = $data_wos->tone;
				
				if($key <= 0){
					$no = 1;
					$bc_bef = $data_wos_arr[$key]->Bot_Color;
				}else{
					if(!empty($data_wos_arr[$key-1]->Bot_Color)){
						$bc_bef = $data_wos_arr[$key-1]->Bot_Color;
						if($data_wos_arr[$key-1]->Bot_Color == $data_wos->Bot_Color){
							$no = $no;
						}else{
							$no = 1;
						}
					}
				}
	
				if($data_wos->Bot_Color == "A"){
					if($no > 2){
					$status = "NG";
					}else{
					$status = "OK";
					}
					$no++;
				}else if($data_wos->Bot_Color == "B"){
					if($no > 2){
					$status = "NG";
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
			}else{
				$color_row = "#FFFFFF";
				$color_font = "#000000";
				$status = "TD-Link";
				$wos_release_date = "";
			}
			$Plan_Delivery_Date = strtoupper(date("d M Y",strtotime($data_wos->Plan_Delivery_Date)));
			$Plan_Jig_In_Date = date("Ymd",strtotime($plan_jig_in));
			$data_update = [];
			if($dummy == "YES"){
				$WosNumber = $data_wos->No;
				$newSAPNIK = "{$data_wos->Model}DUM3Z".date("md",strtotime($pdd)).sprintf("%04d",$number);
				$Plan_Delivery_Date = strtoupper(date("d M Y",strtotime($pdd)));
				$data_update = [
					"SAPNIK" => $newSAPNIK,
					"Chassis_Number" => " $newSAPNIK ",
					"ADM_Production_Id" => $pid,
					"Plan_Delivery_Date" => $Plan_Delivery_Date,
					"Plan_Jig_In_Date" => $Plan_Jig_In_Date,
					"SAPWOS_DES" => $data_wos->WOS_Material."".$Plan_Jig_In_Date,
				];
				$this->model->update_heijunka("master",["No" => $WosNumber, "batch" => $data_wos->batch],$data_update);
			}
			$excel->setActiveSheetIndex(0)->setCellValue('A' . $numrow, $i);
			$excel->setActiveSheetIndex(0)->setCellValue('B' . $numrow, $data_wos->WOS_Material);
			$excel->setActiveSheetIndex(0)->setCellValue('C' . $numrow, $data_wos->WOS_Material_Description);
			$excel->setActiveSheetIndex(0)->setCellValue('D' . $numrow, !empty($data_update) ? $data_update["SAPNIK"] : $data_wos->SAPNIK);
			$excel->setActiveSheetIndex(0)->setCellValueExplicit('E' . $numrow, $data_wos->SAP_Material, PHPExcel_Cell_DataType::TYPE_STRING);
			$excel->setActiveSheetIndex(0)->setCellValue('F' . $numrow, $data_wos->Engine_Model);
			$excel->setActiveSheetIndex(0)->setCellValue('G' . $numrow, $data_wos->Engine_Prefix);
			$excel->setActiveSheetIndex(0)->setCellValue('H' . $numrow, $data_wos->Engine_Number);
			$excel->setActiveSheetIndex(0)->setCellValue('I' . $numrow, $data_wos->Plant);
			$excel->setActiveSheetIndex(0)->setCellValue('J' . $numrow, !empty($data_update) ? $data_update["Chassis_Number"] : $data_wos->Chassis_Number);
			$excel->setActiveSheetIndex(0)->setCellValue('K' . $numrow, $data_wos->Lot_Code);
			$excel->setActiveSheetIndex(0)->setCellValue('L' . $numrow, $data_wos->Lot_Number);
			$excel->setActiveSheetIndex(0)->setCellValue('M' . $numrow, $data_wos->Katashiki);
			$excel->setActiveSheetIndex(0)->setCellValue('N' . $numrow, $data_wos->Katashiki_Sfx);
			$excel->setActiveSheetIndex(0)->setCellValue('O' . $numrow, $pid);
			$excel->setActiveSheetIndex(0)->setCellValue('P' . $numrow, $data_wos->TAM_Production_Id);
			$excel->setActiveSheetIndex(0)->setCellValue('Q' . $numrow, $Plan_Delivery_Date);
			$excel->setActiveSheetIndex(0)->setCellValue('R' . $numrow, $Plan_Jig_In_Date);
			$excel->setActiveSheetIndex(0)->setCellValue('S' . $numrow, $wos_release_date);
			$excel->setActiveSheetIndex(0)->setCellValue('T' . $numrow, $data_wos->WOS_Material."".$Plan_Jig_In_Date);
			$excel->setActiveSheetIndex(0)->setCellValue('U' . $numrow, $data_wos->Location);
			$excel->setActiveSheetIndex(0)->setCellValue('V' . $numrow, $data_wos->Color_Code);
			$excel->setActiveSheetIndex(0)->setCellValue('W' . $numrow, $data_wos->Model);
			$excel->setActiveSheetIndex(0)->setCellValue('X' . $numrow, $data_wos->ED);
			$excel->setActiveSheetIndex(0)->setCellValue('Y' . $numrow, $data_wos->Order);
			$excel->setActiveSheetIndex(0)->setCellValue('Z' . $numrow, $data_wos->Dest);
			$sheet = $excel->getActiveSheet(0);
			$row_style = array(
				'fill' => array(
					'type' => PHPExcel_Style_Fill::FILL_SOLID,
					'color' => array('rgb' => str_replace('#','',$color_row)),
				),
				'font' => array(
					'color' => array('rgb' => str_replace('#','',$color_font)),
					'name' => 'Trebuchet MS',
				),
				'borders' => array(
					'allborders' => array(
						'style' => PHPExcel_Style_Border::BORDER_THIN,
						'color' => array('rgb' => '000000'),
					)
				)
			);
			$sheet->getStyle('A'.$numrow.':Z'.$numrow)->applyFromArray($row_style);
			$i++;
			if($data_wos->heijunka_tone != "TD-Link"){
				$key++;
			}
			$number++;
			$numrow++;
		}
		$excel->getActiveSheet()->getStyle('A1:Z1500')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$excel->getActiveSheet()->getStyle('A1:Z1500')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$header_style = array(
			'fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'color' => array('rgb' => '00FF00'),
			),
			'font' => array(
				'color' => array('rgb' => '000000'),
				'name' => 'Trebuchet MS',
				'bold' => true,
			),
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('rgb' => '000000'),
				)
			)
		);
		$sheet->getStyle('A1:Z1')->applyFromArray($header_style);
		$sheet->getRowDimension('1')->setRowHeight(55);
		$sheet->getColumnDimension('A')->setWidth(5);
		$sheet->getColumnDimension('B')->setWidth(24);
		$sheet->getColumnDimension('C')->setWidth(48);
		$sheet->getColumnDimension('D')->setWidth(22);
		$sheet->getColumnDimension('E')->setWidth(22);
		$sheet->getColumnDimension('F')->setWidth(18);
		$sheet->getColumnDimension('G')->setWidth(19);
		$sheet->getColumnDimension('H')->setWidth(19);
		$sheet->getColumnDimension('I')->setWidth(10);
		$sheet->getColumnDimension('J')->setWidth(27);
		$sheet->getColumnDimension('K')->setWidth(12);
		$sheet->getColumnDimension('L')->setWidth(15);
		$sheet->getColumnDimension('M')->setWidth(17);
		$sheet->getColumnDimension('N')->setWidth(15);
		$sheet->getColumnDimension('O')->setWidth(20);
		$sheet->getColumnDimension('P')->setWidth(20);
		$sheet->getColumnDimension('Q')->setWidth(20);
		$sheet->getColumnDimension('R')->setWidth(20);
		$sheet->getColumnDimension('S')->setWidth(20);
		$sheet->getColumnDimension('T')->setWidth(31);
		$sheet->getColumnDimension('U')->setWidth(12);
		$sheet->getColumnDimension('V')->setWidth(14);
		$sheet->getColumnDimension('W')->setWidth(11);
		$sheet->getColumnDimension('X')->setWidth(12);
		$sheet->getColumnDimension('Y')->setWidth(11);
		$sheet->getColumnDimension('Z')->setWidth(11);
		$excel->getActiveSheet()->getStyle('A1:AC'.$numrow)->getAlignment()->setWrapText(true);
		$excel->setActiveSheetIndex(0);
		ob_end_clean();
		if($this->input->post("dummy") == "YES"){
			$filename = "UPLOAD WOS DUMMY KAP1 PDD ".date("d-m-Y",strtotime($pdd));
		}else{
			$filename = "UPLOAD WOS KAP1 PDD ".date("d-m-Y",strtotime($pdd));
		}
		header('Content-Tyep: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename=' . $filename . '.xls');
		header('Cache-Control: max-age=0');
		$write = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
		$write->save('php://output');
		error_reporting(E_ALL);
		exit();
				
	}
	
	public function download_hardcopy()
	{
		$this->load->view("content/view/download_hardcopy");
	}

	// public function download_hardcopy()
	// {
	// 	set_time_limit(120); // Disable time limit untuk proses yang lama
	// 	// 1. Load Library & Helper
	// 	$this->load->library('excel');
		
	// 	// 2. Ambil Input & Konversi Tanggal
	// 	$pddInput = $this->input->post("pdd"); 
	// 	$plan_jig_in = $this->input->post("plan_jig_in");
		
	// 	// Format Tanggal sesuai format di Excel (misal: 2026-01-31)
	// 	$pddDate = date("Y-m-d", strtotime($pddInput));     
	// 	$jigInDate = date("Y-m-d", strtotime($plan_jig_in)); 
	// 	$filename = "WOS VEHICLE KAP-1 PDD " . date("d M Y", strtotime($pddInput));

	// 	// 3. Load Template Excel
	// 	$templatePath = FCPATH . 'assets/excel/template_wos_kap1.xlsx'; // PASTIKAN PATH INI BENAR
		
	// 	if (!file_exists($templatePath)) {
	// 		die("File template tidak ditemukan di: " . $templatePath);
	// 	}

	// 	// Gunakan Excel2007 karena template extension .xlsx
	// 	$objReader = PHPExcel_IOFactory::createReader('Excel2007'); 
	// 	$excel = $objReader->load($templatePath);

	// 	// ==========================================
	// 	// STEP 1: UPDATE SHEET 3 (SUMBER DATA HEADER)
	// 	// ==========================================
	// 	$excel->setActiveSheetIndexByName('Sheet3');
	// 	$sheet3 = $excel->getActiveSheet();

	// 	// !!! PENTING: SESUAIKAN KOORDINAT CELL INI DENGAN FILE ASLI KAMU !!!
	// 	// Berdasarkan CSV Sheet 3 kamu, sepertinya tanggal ada di baris 4 kolom N dan O
	// 	// Cek file asli, misal cell N4 itu "PLAN JIG IN", O4 itu "WOS PDD"
	// 	$sheet3->setCellValue('N4', $jigInDate); 
	// 	$sheet3->setCellValue('O4', $pddDate);

	// 	// ==========================================
	// 	// STEP 2: UPDATE SHEET 2 (NAMA APPROVAL - OPSIONAL)
	// 	// ==========================================
	// 	// Kalau mau ganti nama approval via koding, buka komen di bawah:
	// 	/*
	// 	$excel->setActiveSheetIndexByName('Sheet2');
	// 	$sheet2 = $excel->getActiveSheet();
	// 	$sheet2->setCellValue('C6', 'Nama Baru Prepared'); // Sesuaikan koordinat
	// 	*/

	// 	// ==========================================
	// 	// STEP 3: ISI DATA DI WOS (PRINT)
	// 	// ==========================================
	// 	$excel->setActiveSheetIndexByName('WOS (Print)');
	// 	$sheet = $excel->getActiveSheet();

	// 	// Load Data dari Database
	// 	$data_wos = $this->model->union_heijunka("ORDER BY batch,No ASC");
	// 	$total_all = count($data_wos);

	// 	// Config Baris Awal & Style
	// 	$rowBase = 8; // Mulai tulis data di baris ke-8 (Asumsi Header baris 1-7)
	// 	$itemsPerPage = 50; // 50 Kiri, 50 Kanan
		
	// 	// Style Dasar (Border & Font) - Kita apply manual ke cell baru biar rapi
	// 	$styleData = array(
	// 		'borders' => array(
	// 			'allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)
	// 		),
	// 		'font' => array('name' => 'Calibri', 'size' => 9, 'bold' => true), // Bold sesuai template
	// 		'alignment' => array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER, 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
	// 	);
	// 	$styleLeft = array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT));

	// 	// Array Warna
	// 	$styles = array(
	// 		'BLACK' => array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => '000000')), 'font' => array('color' => array('rgb' => 'FFFFFF'))),
	// 		'WHITE' => array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => 'FFFFFF')), 'font' => array('color' => array('rgb' => '000000'))),
	// 		'GREY'  => array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => 'A9A9A9')), 'font' => array('color' => array('rgb' => '000000'))),
	// 		'RED'   => array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => 'FF0000')), 'font' => array('color' => array('rgb' => 'FFFFFF')))
	// 	);
		
	// 	$suffixExport = ["ZH" => 1,"XK" => 1,"VZ" => 1,"67" => 1,"64" => 1,"62" => 1,"VW" => 1,"ZB" => 1,"VV" => 1,"ZC" => 1,"65" => 1,"U2" => 1,"ZE" => 1,"WA" => 1,"5H" => 1,"9F" => 1,"9G" => 1,"ZF" => 1,"ZG" => 1,"WC" => 1,"66" => 1,"VX" => 1,"VY" => 1,"5J" => 1];

	// 	for ($i = 0; $i < $total_all; $i++) {
	// 		$rowObj = $data_wos[$i];
			
	// 		// Logic Kiri-Kanan
	// 		$pageIndex = floor($i / ($itemsPerPage * 2)); 
	// 		$indexInPage = $i % ($itemsPerPage * 2);      
			
	// 		if ($indexInPage < $itemsPerPage) {
	// 			$currRow = $rowBase + ($pageIndex * $itemsPerPage) + $indexInPage;
	// 			$colStart = 0; // Kolom A
	// 		} else {
	// 			$currRow = $rowBase + ($pageIndex * $itemsPerPage) + ($indexInPage - $itemsPerPage);
	// 			$colStart = 9; // Kolom J (Kolom ke-9 indexnya)
	// 		}

	// 		// --- LOGIC WARNA ---
	// 		$suffix = $rowObj->Katashiki_Sfx;
	// 		$get_std = $this->model->gds("hc_standard","*","suffix = '$suffix'","row");
	// 		$colorPID = $colorVar = $colorVIN = $colorType = $colorPDD = $colorZero = $styles['RED']; 
			
	// 		$isTDLink = ($rowObj->Model_Name == "TD-Link");
	// 		$isD74Link = (strpos($rowObj->SAPNIK, "D74LINK") !== false);
			
	// 		if ($isTDLink) {
	// 			$colorPID = $colorVar = $colorVIN = $colorType = $colorPDD = $colorZero = $styles['GREY'];
	// 		} elseif ($isD74Link) {
	// 			$colorPID = $colorVIN = $colorType = $colorPDD = $styles['BLACK'];
	// 			$colorVar = $styles['BLACK'];
	// 			$colorZero = $styles['WHITE'];
	// 			if (!empty($suffixExport[$suffix])) {
	// 				$colorPID = $colorVar = $colorVIN = $colorType = $colorPDD = $styles['BLACK'];
	// 				$colorZero = $styles['WHITE'];
	// 			}
	// 		} else {
	// 			if (!empty($get_std)) {
	// 				$colorPID   = isset($styles[$get_std->pid]) ? $styles[$get_std->pid] : $styles['RED'];
	// 				$colorVar   = isset($styles[$get_std->variant]) ? $styles[$get_std->variant] : $styles['RED'];
	// 				$colorVIN   = isset($styles[$get_std->vin]) ? $styles[$get_std->vin] : $styles['RED'];
	// 				$colorType  = isset($styles[$get_std->type]) ? $styles[$get_std->type] : $styles['RED'];
	// 				$colorPDD   = isset($styles[$get_std->pdd]) ? $styles[$get_std->pdd] : $styles['RED'];
	// 				$colorZero  = isset($styles[$get_std->zero_defect]) ? $styles[$get_std->zero_defect] : $styles['RED'];
	// 			}
	// 		}

	// 		// Values
	// 		$valNo = $i + 1;
	// 		$valPID = "3Z" . date("md", strtotime($pddInput)) . sprintf("%04d", $valNo);
	// 		$valVar = $rowObj->WOS_Material_Description; 
	// 		$valVIN = $rowObj->SAPNIK;
	// 		$valType = ($isD74Link) ? "X02X" : $rowObj->Model_Name;
	// 		$valPDD = strtoupper(date("d M Y", strtotime($rowObj->Plan_Delivery_Date)));
	// 		$valZero = ($isD74Link) ? "LINK" : (!empty($get_std->val_zero_defect) ? $get_std->val_zero_defect : "");

	// 		// --- WRITE CELLS ---
	// 		// 1. NO
	// 		$sheet->setCellValueByColumnAndRow($colStart + 0, $currRow, $valNo);
	// 		$sheet->getStyleByColumnAndRow($colStart + 0, $currRow)->applyFromArray(array_merge($styleData, $colorPID));

	// 		// 2. SFX
	// 		$sheet->setCellValueByColumnAndRow($colStart + 1, $currRow, $suffix);
	// 		$sheet->getStyleByColumnAndRow($colStart + 1, $currRow)->applyFromArray(array_merge($styleData, $colorPID));

	// 		// 3. PID
	// 		$sheet->setCellValueByColumnAndRow($colStart + 2, $currRow, $valPID);
	// 		$sheet->getStyleByColumnAndRow($colStart + 2, $currRow)->applyFromArray(array_merge($styleData, $colorPID));

	// 		// 4. VARIANT (Align Left + Wrap Text)
	// 		$sheet->setCellValueByColumnAndRow($colStart + 3, $currRow, $valVar);
	// 		$sheet->getStyleByColumnAndRow($colStart + 3, $currRow)->applyFromArray(array_merge($styleData, $styleLeft, $colorVar));
	// 		$sheet->getStyleByColumnAndRow($colStart + 3, $currRow)->getAlignment()->setWrapText(true);

	// 		// 5. VIN (Explicit String)
	// 		$sheet->setCellValueExplicitByColumnAndRow($colStart + 4, $currRow, $valVIN, PHPExcel_Cell_DataType::TYPE_STRING);
	// 		$sheet->getStyleByColumnAndRow($colStart + 4, $currRow)->applyFromArray(array_merge($styleData, $colorVIN));

	// 		// 6. TYPE
	// 		$sheet->setCellValueByColumnAndRow($colStart + 5, $currRow, $valType);
	// 		$sheet->getStyleByColumnAndRow($colStart + 5, $currRow)->applyFromArray(array_merge($styleData, $colorType));

	// 		// 7. PDD
	// 		$sheet->setCellValueByColumnAndRow($colStart + 6, $currRow, $valPDD);
	// 		$sheet->getStyleByColumnAndRow($colStart + 6, $currRow)->applyFromArray(array_merge($styleData, $colorPDD));

	// 		// 8. ZERO DEFECT
	// 		$sheet->setCellValueByColumnAndRow($colStart + 7, $currRow, $valZero);
	// 		$sheet->getStyleByColumnAndRow($colStart + 7, $currRow)->applyFromArray(array_merge($styleData, $colorZero));
	// 	}

	// 	// --- OUTPUT ---
	// 	ob_end_clean();
	// 	header('Content-Type: application/vnd.ms-excel');
	// 	header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
	// 	header('Cache-Control: max-age=0');

	// 	$objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
	// 	$objWriter->save('php://output');
	// 	exit;
	// }

	public function download_u_pis_kap2()
	{
		header("Content-Type:text/plain");
		//   $this->load->view("content/view/download_u_pis");
		$this->load->library('excel');
		$excel = new PHPExcel();
		$now = date('d-m-Y');
		$dummy = $this->input->post("dummy");
		$pdd = $this->input->post("pdd");
		$plan_jig_in = $this->input->post("plan_jig_in");
		$start_vin = $this->input->post("start_vin");
		//Header File Excel
		$excel->setActiveSheetIndex(0)->setCellValue('A1', "No.");
		$excel->setActiveSheetIndex(0)->setCellValue('B1', "WOS Material");
		$excel->setActiveSheetIndex(0)->setCellValue('C1', "WOS Material Description");
		$excel->setActiveSheetIndex(0)->setCellValue('D1', "SAPNIK");
		$excel->setActiveSheetIndex(0)->setCellValue('E1', "SAP Material");
		$excel->setActiveSheetIndex(0)->setCellValue('F1', "Engine Model");
		$excel->setActiveSheetIndex(0)->setCellValue('G1', "Engine Prefix");
		$excel->setActiveSheetIndex(0)->setCellValue('H1', "Engine Number");
		$excel->setActiveSheetIndex(0)->setCellValue('I1', "Plant");
		$excel->setActiveSheetIndex(0)->setCellValue('J1', "Chassis Number");
		$excel->setActiveSheetIndex(0)->setCellValue('K1', "Lot Code");
		$excel->setActiveSheetIndex(0)->setCellValue('L1', "Lot Number");
		$excel->setActiveSheetIndex(0)->setCellValue('M1', "Katashiki");
		$excel->setActiveSheetIndex(0)->setCellValue('N1', "Katashiki Sfx");
		$excel->setActiveSheetIndex(0)->setCellValue('O1', "ADM Production ID");
		$excel->setActiveSheetIndex(0)->setCellValue('P1', "TAM Production ID");
		$excel->setActiveSheetIndex(0)->setCellValue('Q1', "Plan Delivery Date");
		$excel->setActiveSheetIndex(0)->setCellValue('R1', "Plan Jig In Date");
		$excel->setActiveSheetIndex(0)->setCellValue('S1', "WOS Release Date");
		$excel->setActiveSheetIndex(0)->setCellValue('T1', "SAPWOS DES");
		$excel->setActiveSheetIndex(0)->setCellValue('U1', "Location");
		$excel->setActiveSheetIndex(0)->setCellValue('V1', "Color Code");
		$excel->setActiveSheetIndex(0)->setCellValue('W1', "Model");
		$excel->setActiveSheetIndex(0)->setCellValue('X1', "ED");
		$excel->setActiveSheetIndex(0)->setCellValue('Y1', "Order");
		$excel->setActiveSheetIndex(0)->setCellValue('Z1', "Dest");
		$number = empty($start_vin) ? 1 : $start_vin;
		$number_d74a = empty($start_vin) ? 1 : $start_vin;
		$number_d26a = empty($start_vin) ? 1 : $start_vin;
		$i = 1;
		$key = 0;
		$numrow = 2;
		$data_sess = ["pdd_pis_kap2" => $pdd];
		$this->session->set_userdata($data_sess);
		$data_wos_arr = $this->model->gds_heijunka("master_kap2","*","No != '' ORDER BY batch,No DESC","result");
		$data_wos = $this->model->union_heijunka_kap2("ORDER BY batch,No DESC");
		$count_wos = count($data_wos);
		foreach ($data_wos as $data_wos) {
			// $pid = "5X".date("md",strtotime($pdd)).sprintf("%04d",$number);
			// $pid_d74a = "5X".date("md",strtotime($pdd)).sprintf("%04d",$number_d74a);
			// $pid_d26a = "5X".date("md",strtotime($pdd)).sprintf("%04d",$number_d26a);
			$pid = "5X".date("md",strtotime($pdd)).sprintf("%04d",$number);
			$pid_d74a = "5X".substr(date("ymd", strtotime($pdd)), 1).sprintf("%03d",$number_d74a);
			$pid_d26a = "5X".substr(date("ymd", strtotime($pdd)), 1).sprintf("%03d",$number_d26a);
			$explode = explode(',', $data_wos->Color);
			$color_row = $explode[0];
			if(!empty($explode[1])){
				$color_font = $explode[1];
			}else{
				$color_font = "#FFFFFF";
			}
			$tone = $data_wos->tone;
			
			if($key <= 0){
				$no = 1;
				$bc_bef = $data_wos_arr[$key]->Bot_Color;
			}else{
				if(!empty($data_wos_arr[$key-1]->Bot_Color)){
					$bc_bef = $data_wos_arr[$key-1]->Bot_Color;
					if($data_wos_arr[$key-1]->Bot_Color == $data_wos->Bot_Color){
						$no = $no;
					}else{
						$no = 1;
					}
				}
			}

			if($data_wos->Bot_Color == "A"){
				if($no > 2){
				$status = "NG";
				}else{
				$status = "OK";
				}
				$no++;
			}else if($data_wos->Bot_Color == "B"){
				if($no > 2){
				$status = "NG";
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
			$SAPNIK = $data_wos->SAPNIK;
			$Chassis_Number = $data_wos->Chassis_Number;
			$Plan_Delivery_Date = strtoupper(date("d M Y",strtotime($data_wos->Plan_Delivery_Date)));
			$Plan_Jig_In_Date = date("Ymd",strtotime($plan_jig_in));
			if($dummy == "YES"){
				$WosNumber = $data_wos->No;
				if($data_wos->Model == "D74A"){
					$SAPNIK = "D74LINK".$pid_d74a;
					$Chassis_Number = " D74LINK".$pid_d74a." ";
				}
				if($data_wos->Model == "D26A"){
					$SAPNIK = "D26ADUM".$pid_d26a;
					$Chassis_Number = " D26ADUM".$pid_d26a." ";
				}
				$Plan_Delivery_Date = strtoupper(date("d M Y",strtotime($pdd)));
				$data_update = [
					// "SAPNIK" => $SAPNIK,
					"Chassis_Number" => $Chassis_Number,
					"ADM_Production_Id" => $pid,
					"Plan_Delivery_Date" => $Plan_Delivery_Date,
					"Plan_Jig_In_Date" => $Plan_Jig_In_Date,
					"SAPWOS_DES" => $data_wos->WOS_Material."".$Plan_Jig_In_Date,
				];
				$this->model->update_heijunka("master_kap2","No = '".$WosNumber."'",$data_update);
			}
			$excel->setActiveSheetIndex(0)->setCellValue('A' . $numrow, $i);
			$excel->setActiveSheetIndex(0)->setCellValue('B' . $numrow, $data_wos->WOS_Material);
			$excel->setActiveSheetIndex(0)->setCellValue('C' . $numrow, $data_wos->WOS_Material_Description);
			$excel->setActiveSheetIndex(0)->setCellValue('D' . $numrow, $SAPNIK);
			$excel->setActiveSheetIndex(0)->setCellValueExplicit('E' . $numrow, $data_wos->SAP_Material, PHPExcel_Cell_DataType::TYPE_STRING);
			$excel->setActiveSheetIndex(0)->setCellValue('F' . $numrow, $data_wos->Engine_Model);
			$excel->setActiveSheetIndex(0)->setCellValue('G' . $numrow, $data_wos->Engine_Prefix);
			$excel->setActiveSheetIndex(0)->setCellValue('H' . $numrow, $data_wos->Engine_Number);
			$excel->setActiveSheetIndex(0)->setCellValue('I' . $numrow, $data_wos->Plant);
			$excel->setActiveSheetIndex(0)->setCellValue('J' . $numrow, $Chassis_Number);
			$excel->setActiveSheetIndex(0)->setCellValue('K' . $numrow, $data_wos->Lot_Code);
			$excel->setActiveSheetIndex(0)->setCellValue('L' . $numrow, $data_wos->Lot_Number);
			$excel->setActiveSheetIndex(0)->setCellValue('M' . $numrow, $data_wos->Katashiki);
			$excel->setActiveSheetIndex(0)->setCellValue('N' . $numrow, $data_wos->Katashiki_Sfx);
			$excel->setActiveSheetIndex(0)->setCellValue('O' . $numrow, $pid);
			$excel->setActiveSheetIndex(0)->setCellValue('P' . $numrow, $data_wos->TAM_Production_Id);
			$excel->setActiveSheetIndex(0)->setCellValue('Q' . $numrow, $Plan_Delivery_Date);
			$excel->setActiveSheetIndex(0)->setCellValue('R' . $numrow, $Plan_Jig_In_Date);
			$excel->setActiveSheetIndex(0)->setCellValue('S' . $numrow, $wos_release_date);
			$excel->setActiveSheetIndex(0)->setCellValue('T' . $numrow, $data_wos->WOS_Material."".$Plan_Jig_In_Date);
			$excel->setActiveSheetIndex(0)->setCellValue('U' . $numrow, $data_wos->Location);
			$excel->setActiveSheetIndex(0)->setCellValue('V' . $numrow, $data_wos->Color_Code);
			$excel->setActiveSheetIndex(0)->setCellValue('W' . $numrow, $data_wos->Model);
			$excel->setActiveSheetIndex(0)->setCellValue('X' . $numrow, $data_wos->ED);
			$excel->setActiveSheetIndex(0)->setCellValue('Y' . $numrow, $data_wos->Order);
			$excel->setActiveSheetIndex(0)->setCellValue('Z' . $numrow, $data_wos->Dest);
			$sheet = $excel->getActiveSheet(0);
			$row_style = array(
				'fill' => array(
					'type' => PHPExcel_Style_Fill::FILL_SOLID,
					'color' => array('rgb' => str_replace('#','',$color_row)),
				),
				'font' => array(
					'color' => array('rgb' => str_replace('#','',$color_font)),
					'name' => 'Trebuchet MS',
				),
				'borders' => array(
					'allborders' => array(
						'style' => PHPExcel_Style_Border::BORDER_THIN,
						'color' => array('rgb' => '000000'),
					)
				)
			);
			$sheet->getStyle('A'.$numrow.':Z'.$numrow)->applyFromArray($row_style);
			$i++;
			$key++;
			if($data_wos->Model == "D74A"){
				$number_d74a++;
			}
			if($data_wos->Model == "D26A"){
				$number_d26a++;
			}
			$number++;
			$numrow++;
		}
		$excel->getActiveSheet()->getStyle('A1:Z1500')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$excel->getActiveSheet()->getStyle('A1:Z1500')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$header_style = array(
			'fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'color' => array('rgb' => '00FF00'),
			),
			'font' => array(
				'color' => array('rgb' => '000000'),
				'name' => 'Trebuchet MS',
				'bold' => true,
			),
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('rgb' => '000000'),
				)
			)
		);
		$sheet->getStyle('A1:Z1')->applyFromArray($header_style);
		$sheet->getRowDimension('1')->setRowHeight(55);
		$sheet->getColumnDimension('A')->setWidth(5);
		$sheet->getColumnDimension('B')->setWidth(24);
		$sheet->getColumnDimension('C')->setWidth(48);
		$sheet->getColumnDimension('D')->setWidth(22);
		$sheet->getColumnDimension('E')->setWidth(22);
		$sheet->getColumnDimension('F')->setWidth(18);
		$sheet->getColumnDimension('G')->setWidth(19);
		$sheet->getColumnDimension('H')->setWidth(19);
		$sheet->getColumnDimension('I')->setWidth(10);
		$sheet->getColumnDimension('J')->setWidth(27);
		$sheet->getColumnDimension('K')->setWidth(12);
		$sheet->getColumnDimension('L')->setWidth(15);
		$sheet->getColumnDimension('M')->setWidth(17);
		$sheet->getColumnDimension('N')->setWidth(15);
		$sheet->getColumnDimension('O')->setWidth(20);
		$sheet->getColumnDimension('P')->setWidth(20);
		$sheet->getColumnDimension('Q')->setWidth(20);
		$sheet->getColumnDimension('R')->setWidth(20);
		$sheet->getColumnDimension('S')->setWidth(20);
		$sheet->getColumnDimension('T')->setWidth(31);
		$sheet->getColumnDimension('U')->setWidth(12);
		$sheet->getColumnDimension('V')->setWidth(14);
		$sheet->getColumnDimension('W')->setWidth(11);
		$sheet->getColumnDimension('X')->setWidth(12);
		$sheet->getColumnDimension('Y')->setWidth(11);
		$sheet->getColumnDimension('Z')->setWidth(11);
		$excel->getActiveSheet()->getStyle('A1:AC'.$numrow)->getAlignment()->setWrapText(true);
		$excel->setActiveSheetIndex(0);
		ob_end_clean();
		if($this->input->post("dummy") == "YES"){
			$filename = "UPLOAD WOS DUMMY KAP2 PDD ".date("d-m-Y",strtotime($pdd));
		}else{
			$filename = "UPLOAD WOS KAP2 PDD ".date("d-m-Y",strtotime($pdd));
		}
		header('Content-Tyep: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename=' . $filename . '.xls');
		header('Cache-Control: max-age=0');
		$write = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
		$write->save('php://output');
		error_reporting(E_ALL);
		exit();
				
	}
	public function download_hardcopy_kap2()
	{
		$this->load->view("content/view/download_hardcopy_kap2");
	}
	public function hard_copy_std()
	{
		$data["title"] = "Hard Copy Standard";
		$data["content"] = "view/hard_copy_std";
		$data["javascript"] = "hard_copy_std";
		$this->load->view('layout/index',$data);
	}
	public function master_service_part()
	{
		$data["title"] = "Master Service Part";
		$data["javascript"] = "master_service_part";
		$data["content"] = "view/master_service_part";
		$this->load->view('layout/index',$data);
	}
	public function download_master_service_part()
	{
		$this->load->view('content/view/download_master_service_part');
	}
	public function download_pro_number()
	{
		$data["content"] = "view/download_pro_number";
		$this->load->view('layout/index',$data);
	}
	public function edit_master_sp()
	{
		$data["title"] = "Edit Service Part";
		$data["javascript"] = "edit_master_sp";
		$data["content"] = "view/edit_master_sp";
		$this->load->view('layout/index',$data);
	}
	public function create_wos()
	{
		$this->model->delete_heijunka("create_wos", "Part_Number = ''");
		$data["title"] = "Create WOS Service Part";
		$data["javascript"] = "create_wos";
		$data["content"] = "view/create_wos";
		$this->load->view('layout/index',$data);
	}
	public function create_wos_sp_download()
	{
		$kap = empty($this->input->get("kap")) ? "1" : $this->input->get("kap");
		$data["kap"] = $kap;
		$this->load->view('content/view/create_wos_sp_download', $data);
	}
	public function pro_number()
	{
		$data["title"] = "PRO Number";
		$data["javascript"] = "pro_number";
		$data["content"] = "view/pro_number";
		$this->load->view('layout/index',$data);
	}
}
