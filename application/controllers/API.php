<?php
defined('BASEPATH') or exit('No direct script access allowed');
date_default_timezone_set('Asia/Jakarta');
include_once(dirname(__FILE__) . "/Construct.php");
// header("Content-Type: text/plain");

class API extends Construct
{
	function checking_wos()
	{
		$unit = $this->input->get_post("unit");
		//CHECK PDD
		$filter = $this->model->join_data("checking_wos","plan_wos_base","checking_wos.suffix=plan_wos_base.suffix","checking_wos.vin,checking_wos.pdd,plan_wos_base.brand","vin IN('".str_replace(",","','",$unit)."')","result");
		echo json_encode(["status" => 200, "res" => $filter]);
		die();
	}
}
