<?php
defined('BASEPATH') OR exit('No direct script access allowed');
date_default_timezone_set('Asia/Jakarta');
include_once (dirname(__FILE__) . "/Construct.php");

class Hardcopy_std extends Construct {
	public function input_hard_copy_std()
	{
		$id_edit = $this->input->get("id");
		
		$suffix = $this->input->post("suffix");
		$pid = $this->input->post("pid");
		$variant = $this->input->post("variant");
		$vin = $this->input->post("vin");
		$type = $this->input->post("type");
		$pdd = $this->input->post("pdd");
		$zero_defect = $this->input->post("zero_defect");
		$val_zero_defect = $this->input->post("val_zero_defect");

		$data_input = [
			"suffix" => $suffix,
			"pid" => $pid,
			"variant" => $variant,
			"vin" => $vin,
			"type" => $type,
			"pdd" => $pdd,
			"zero_defect" => $zero_defect,
			"val_zero_defect" => $val_zero_defect,
		];

		if(!empty($id_edit)){
			$proses = $this->model->update("hc_standard","id = '$id_edit'",$data_input);
		}else{
			$proses = $this->model->insert("hc_standard",$data_input);
		}
		if(!$proses){
			$this->voice("sukses.mp3");
			$this->swal_custom_icon("Sukses","Data berhasil disimpan",base_url('assets/images/happy.png'),"","true");
		}else{
			$this->voice("gagal.mp3");
			$this->swal_custom_icon("Gagal","Data gagal disimpan",base_url('assets/images/emot-sedih.jpg'),"rounded-circle","true");
		}
		redirect("hard_copy_std");
	}

	public function delete_hcstd()
	{
		$id = $this->input->get("id");
		$proses = $this->model->delete("hc_standard","id = '$id'");
		if(!$proses){
			$this->voice("sukses.mp3");
			$this->swal_custom_icon("Sukses","Data berhasil dihapus",base_url('assets/images/happy.png'),"","true");
		}else{
			$this->voice("gagal.mp3");
			$this->swal_custom_icon("Gagal","Data gagal dihapus",base_url('assets/images/emot-sedih.jpg'),"rounded-circle","true");
		}
		redirect("hard_copy_std");
	}
}
