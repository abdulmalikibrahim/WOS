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
		$filter = $this->model->gds("checking_wos","vin,pdd","vin IN('".str_replace(",","','",$unit)."')","result_array");
		echo json_encode(["status" => 200, "res" => $filter]);
		die();
	}
}
