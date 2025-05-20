<?php
defined('BASEPATH') OR exit('No direct script access allowed');
date_default_timezone_set('Asia/Jakarta');

class Construct extends CI_Controller {
	public function __construct()
	{
		parent::__construct();
		$this->user_id = $this->session->userdata("user_id");
		$this->npk = $this->session->userdata("npk");
		$this->nama = $this->session->userdata("nama");
		$this->level = $this->session->userdata("level");
		$this->shift = $this->session->userdata("shift");
		$this->leader = $this->session->userdata("leader");
		$this->foreman = $this->session->userdata("foreman");
		$this->snd = $this->session->userdata("snd");
		$this->tnd = $this->session->userdata("tnd");
		$this->mr = $this->month_romawi();
		$this->params1 = $this->uri->segment(1);
		$this->params2 = $this->uri->segment(2);
		$this->params3 = $this->uri->segment(3);
		$this->load->library(array('excel','session'));
		if(date("YmdHi") > 202410081045 && date("YmdHi") < 202410090725){
			redirect("err404");
		}
	}
	public function format_indo($date){
		// array hari dan bulan
		$Hari = array("Minggu","Senin","Selasa","Rabu","Kamis","Jumat","Sabtu");
		$Bulan = array("Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember");
		
		// pemisahan tahun, bulan, hari, dan waktu
		$tahun = substr($date,0,4);
		$bulan = substr($date,5,2);
		$tgl = substr($date,8,2);
		$waktu = substr($date,11,5);
		$hari = date("w",strtotime($date));
		$result = $Hari[$hari].", ".$tgl." ".$Bulan[(int)$bulan-1]." ".$tahun." ".$waktu;
		return $result;
	}
	public function vv($var)
	{
		$var = str_replace("'","&#39;",str_replace('"','&#34;',$var));
		return $var;
	}
	public function pv($var)
	{
		$var = str_replace("<p>","<br>",str_replace('</p>','',$var));
		return $var;
	}
	public function nlp($var)
	{
		$var = str_replace("<p>","",str_replace('</p>','',$var));
		return $var;
	}
	public function brv($var)
	{
		$var = str_replace("<p>","",str_replace('</p>','\n',$var));
		$var = str_replace("\n","<br>",$var);
		return $var;
	}
	public function do_upload($file_name,$files,$place)
	{
		$config['upload_path']          = $place;
		$config['allowed_types']        = 'gif|jpg|png';
		$config['max_size']             = 2048;
		$config['max_width']            = 1024000;
		$config['max_height']           = 768000;
		$config['file_name']            = $file_name;
		$this->load->library('upload', $config);
		if (!$this->upload->do_upload($files)){
			$error = array('error' => $this->upload->display_errors());
			$return = $error["error"];
		}else{
			$data = array('upload_data' => $this->upload->data());
			$return = "success";
		}
		return $return;
	}

	public function month_import($month)
	{
		switch ($month) {
			case 'JAN':
				$month = "01";
				break;
			case 'FEB':
				$month = "02";
				break;
			case 'MAR':
				$month = "03";
				break;
			case 'APR':
				$month = "04";
				break;
			case 'MAY':
				$month = "05";
				break;
			case 'JUN':
				$month = "06";
				break;
			case 'JUL':
				$month = "07";
				break;
			case 'AUG':
				$month = "08";
				break;
			case 'SEP':
				$month = "09";
				break;
			case 'OCT':
				$month = "10";
				break;
			case 'NOV':
				$month = "11";
				break;
			case 'DEC':
				$month = "12";
				break;
		}
		return $month;
	}

	public function month_romawi()
	{
		$month = date("m");
		switch ($month) {
			case '01':
				$month = "I";
				break;
			case '02':
				$month = "II";
				break;
			case '03':
				$month = "III";
				break;
			case '04':
				$month = "IV";
				break;
			case '05':
				$month = "V";
				break;
			case '06':
				$month = "VI";
				break;
			case '07':
				$month = "VII";
				break;
			case '08':
				$month = "VIII";
				break;
			case '09':
				$month = "IX";
				break;
			case '10':
				$month = "XI";
				break;
			case '12':
				$month = "XII";
				break;
		}
		return $month;
	}
    public function swal($title, $text, $icon)
	{
		$this->session->set_flashdata("swal",'
		<script>
			swal.fire({title:"'.$title.'",html:"'.$text.'",icon:"'.$icon.'"});
		</script>');
	}
	public function swal_custom_icon($title,$text,$img,$rounded,$btn_ok)
	{
		if(!empty($rounded)){
			$rounded = "'".$rounded."'";
		}else{
			$rounded = "''";
		}
		if(!empty($img)){
			$img = "'".$img."'";
		}else{
			$img = "''";
		}
		$width = "'100%'";
		$this->session->set_flashdata("swal",'
		<script>
			swal.fire({
				iconHtml: "<img class='.$rounded.' src='.$img.' width='.$width.'>",
				customClass: {
					icon: "border-0"
				},
				title:"'.$title.'",
				html:"'.$text.'",
				showConfirmButton:'.$btn_ok.',
			});
		</script>');
	}
	public function voice($file)
	{
		$this->session->set_flashdata("voice",'
		<script>
			voice("'.$file.'");
		</script>');
	}
}