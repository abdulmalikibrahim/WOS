<?php
defined('BASEPATH') OR exit('No direct script access allowed');
date_default_timezone_set('Asia/Jakarta');
include_once (dirname(__FILE__) . "/Construct.php");

class Filter extends Construct {
	public function index($method)
	{
		if($method == "page"){
			$this->page();
		}else if($method == "add"){
			$this->add();
		}else if($method == "delete"){
			$this->delete();
		}else{
			redirect(base_url());
		}
	}

	private function page(){
		$data["title"] = "Setting Filter";
		$data["content"] = "view/filter";
		$data["plant"] = ["1","2"];
		$data["suffix"] = $this->model->gds("plan_wos_base","suffix","suffix != '' GROUP BY suffix","result_array");
		$data["model"] = $this->model->gds("plan_wos_base","model_code as model","model_code != '' GROUP BY model_code","result_array");
		$this->load->view('layout/index',$data);
	}

	private function add()
	{
		$this->form_validation
			->set_rules('model', 'Model', 'trim')
			->set_rules('suffix', 'Suffix', 'trim')
			->set_rules('color', 'Color', 'trim');

		if ($this->form_validation->run() == FALSE) {
			$this->session->set_flashdata('error', validation_errors());
			$this->swal("Gagal", validation_errors(), "error");
			redirect($_SERVER['HTTP_REFERER']);
		} else {
			$kap = $this->input->get("p");
			$model = !empty($this->input->post("model")) ? $this->input->post("model") : "all";
			$suffix = !empty($this->input->post("suffix")) ? $this->input->post("suffix") : "all";
			if($model == "all" && $suffix != "all"){
				$model = $this->model->gds("plan_wos_base","model_code as model","suffix = '$suffix' GROUP BY model_code","row");
				$model = $model->model;
			}
			$color = !empty($this->input->post("color")) ? strtoupper($this->input->post("color")) : "all";
			$check = $this->model->gds("filter","*","kap = '$kap' AND model = '$model' AND suffix = '$suffix' AND color = '$color'","row_array");
			if(empty($check)){
				$insert = [
					"kap" => $kap,
					"model" => $model,
					"suffix" => $suffix,
					"color" => $color
				];
				$this->model->insert("filter",$insert);
				$this->swal("Sukses","Data filer berhasil di tambahkan","success");
				redirect($_SERVER['HTTP_REFERER']);
			}else{
				$this->swal("Gagal","Data filter sudah ada","error");
				redirect($_SERVER['HTTP_REFERER']);
			}
		}	
	}

	private function delete()
	{
		$id = $this->input->get("i");
		$check = $this->model->gds("filter","*","id = '$id'","row_array");
		if(!empty($check)){
			$this->model->delete("filter","id = '$id'");
			$this->swal("Sukses","Data filter berhasil di hapus","success");
			redirect($_SERVER['HTTP_REFERER']);
		}else{
			$this->swal("Gagal","Data filter tidak ditemukan","error");
			redirect($_SERVER['HTTP_REFERER']);
		}
		
	}
}
