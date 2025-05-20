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
	public function download_u_pis()
	{
		//   $this->load->view("content/view/download_u_pis");
		$this->load->library('excel');
		$excel = new PHPExcel();
		$now = date('d-m-Y');
		$pdd = $this->input->post("pdd");
		$plan_jig_in = $this->input->post("plan_jig_in");
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
		$number = 1;
		$i = 1;
		$key = 0;
		$numrow = 2;
		$data_wos_arr = $this->model->gds_heijunka("master","*","No != '' ORDER BY batch,No DESC","result");
		$data_wos = $this->model->union_heijunka("ORDER BY batch,No DESC");
		$count_wos = count($data_wos);
		foreach ($data_wos as $data_wos) {
			$pid = "3Z".date("md",strtotime($pdd)).sprintf("%04d",$number);
			if($data_wos->heijunka_tone == "TD-Link"){
				$color_row = "#FFFFFF";
				$color_font = "#000000";
				$status = "TD-Link";
				$wos_release_date = "";
			}else if($data_wos->heijunka_tone == "D74A-LINK"){
				$color_row = "#FFFFFF";
				$color_font = "#000000";
				$status = "D74A-LINK";
				$wos_release_date = "";
			}else{
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
			}
			$excel->setActiveSheetIndex(0)->setCellValue('A' . $numrow, $i);
			$excel->setActiveSheetIndex(0)->setCellValue('B' . $numrow, $data_wos->WOS_Material);
			$excel->setActiveSheetIndex(0)->setCellValue('C' . $numrow, $data_wos->WOS_Material_Description);
			$excel->setActiveSheetIndex(0)->setCellValue('D' . $numrow, $data_wos->SAPNIK);
			$excel->setActiveSheetIndex(0)->setCellValue('E' . $numrow, $data_wos->SAP_Material);
			$excel->setActiveSheetIndex(0)->setCellValue('F' . $numrow, $data_wos->Engine_Model);
			$excel->setActiveSheetIndex(0)->setCellValue('G' . $numrow, $data_wos->Engine_Prefix);
			$excel->setActiveSheetIndex(0)->setCellValue('H' . $numrow, $data_wos->Engine_Number);
			$excel->setActiveSheetIndex(0)->setCellValue('I' . $numrow, $data_wos->Plant);
			$excel->setActiveSheetIndex(0)->setCellValue('J' . $numrow, $data_wos->Chassis_Number);
			$excel->setActiveSheetIndex(0)->setCellValue('K' . $numrow, $data_wos->Lot_Code);
			$excel->setActiveSheetIndex(0)->setCellValue('L' . $numrow, $data_wos->Lot_Number);
			$excel->setActiveSheetIndex(0)->setCellValue('M' . $numrow, $data_wos->Katashiki);
			$excel->setActiveSheetIndex(0)->setCellValue('N' . $numrow, $data_wos->Katashiki_Sfx);
			$excel->setActiveSheetIndex(0)->setCellValue('O' . $numrow, $pid);
			$excel->setActiveSheetIndex(0)->setCellValue('P' . $numrow, $data_wos->TAM_Production_Id);
			$excel->setActiveSheetIndex(0)->setCellValue('Q' . $numrow, strtoupper(date("d M Y",strtotime($data_wos->Plan_Delivery_Date))));
			$excel->setActiveSheetIndex(0)->setCellValue('R' . $numrow, date("Ymd",strtotime($plan_jig_in)));
			$excel->setActiveSheetIndex(0)->setCellValue('S' . $numrow, $wos_release_date);
			$excel->setActiveSheetIndex(0)->setCellValue('T' . $numrow, $data_wos->SAPWOS_DES);
			$excel->setActiveSheetIndex(0)->setCellValue('U' . $numrow, $data_wos->Location);
			$excel->setActiveSheetIndex(0)->setCellValue('V' . $numrow, $data_wos->Color_Code);
			$excel->setActiveSheetIndex(0)->setCellValue('W' . $numrow, $data_wos->Model);
			$excel->setActiveSheetIndex(0)->setCellValue('X' . $numrow, $data_wos->ED);
			$excel->setActiveSheetIndex(0)->setCellValue('Y' . $numrow, $data_wos->Order);
			$excel->setActiveSheetIndex(0)->setCellValue('Z' . $numrow, $data_wos->Dest);
			// if($data_wos->heijunka_tone != "TD-Link"){
			// 	$excel->setActiveSheetIndex(0)->setCellValue('A' . $numrow, $data_wos->Bot_Color);
			// }else{
			// 	$excel->getActiveSheet()->mergeCells('AA'.$numrow.':AC'.$numrow);
			// 	$excel->setActiveSheetIndex(0)->setCellValue('A' . $numrow, 'TD-Link');
			// }
			// $excel->setActiveSheetIndex(0)->setCellValue('A' . $vtone, $i);
			// $excel->setActiveSheetIndex(0)->setCellValue('A' . $data_wos, $i);
			// $excel->setActiveSheetIndex(0)->setCellValue('A' . $data_wos, $i);
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
			}else if($data_wos->heijunka_tone != "D74A-LINK"){
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
		$excel->getActiveSheet()->getStyle('A:AC')->getAlignment()->setWrapText(true);
		$excel->setActiveSheetIndex(0);
		ob_end_clean();
		$filename = "UPLOAD WOS KAP1 PDD ".date("d-m-Y",strtotime($pdd));
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
			$pid = "5X".date("md",strtotime($pdd)).sprintf("%04d",$number);
			$pid_d74a = "5X".date("md",strtotime($pdd)).sprintf("%04d",$number_d74a);
			$pid_d26a = "5X".date("md",strtotime($pdd)).sprintf("%04d",$number_d26a);
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
			$SAPNIK = $data_wos->SAPNIK;
			$Chassis_Number = $data_wos->Chassis_Number;
			$Plan_Delivery_Date = strtoupper(date("d M Y",strtotime($data_wos->Plan_Delivery_Date)));
			$Plan_Jig_In_Date = date("Ymd",strtotime($plan_jig_in));
			if($dummy == "YES"){
				$WosNumber = $data_wos->No;
				$SAPNIK = $data_wos->Model == "D74A" ? "D74LINK".$pid_d74a : "D26ADUM".$pid_d26a;
				$Chassis_Number = $data_wos->Model == "D74A" ? " D74LINK".$pid_d74a." " : " D26ADUM".$pid_d26a." ";
				$Plan_Delivery_Date = strtoupper(date("d M Y",strtotime($pdd)));
				$data_update = [
					"SAPNIK" => $SAPNIK,
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
			$excel->setActiveSheetIndex(0)->setCellValue('E' . $numrow, $data_wos->SAP_Material);
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
			if($data_wos->heijunka_tone != "TD-Link"){
				$key++;
			}
			$data_wos->Model == "D74A" ? $number_d74a++ : $number_d26a++;
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
		$excel->getActiveSheet()->getStyle('A:AC')->getAlignment()->setWrapText(true);
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
		$data["content"] = "view/download_master_service_part";
		$this->load->view('layout/index',$data);
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
		$data["title"] = "Create WOS Service Part";
		$data["javascript"] = "create_wos";
		$data["content"] = "view/create_wos";
		$this->load->view('layout/index',$data);
	}
	public function create_wos_sp_download()
	{
		$this->load->view('content/view/create_wos_sp_download');
	}
	public function pro_number()
	{
		$data["title"] = "PRO Number";
		$data["javascript"] = "pro_number";
		$data["content"] = "view/pro_number";
		$this->load->view('layout/index',$data);
	}
}
