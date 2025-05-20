<?php

class model extends CI_Model
{
	function __construct()
	{
		parent::__construct();
		$this->db_heijunka = $this->load->database('db_heijunka_wos', TRUE);
	}
	public function insert_batch_heijunka($table, $data)
	{
		$insert = $this->db_heijunka->insert_batch($table, $data);
		if ($insert) {
			return true;
		}
	}
	public function gds_heijunka($table, $select, $where, $status)
	{
		$this->db_heijunka->select($select);
		$this->db_heijunka->where($where);
		$this->db_heijunka->from($table);
		if ($status == 'result') {
			return $this->db_heijunka->get()->result();
		} else {
			return $this->db_heijunka->get()->row();
		}
	}
	public function delete_heijunka($table, $where)
	{
		$this->db_heijunka->where($where);
		$this->db_heijunka->delete($table);
	}
	function update_heijunka($table, $where, $data)
	{
		$this->db_heijunka->where($where);
		$this->db_heijunka->update($table, $data);
	}
	function update_batch_heijunka($table, $where, $data)
	{
		$this->db_heijunka->update_batch($table, $data, $where); //where is just set 1 column
	}
	function update_batch_heijunka_2where($table, $data, $where1, $where2)
	{
		$this->db_heijunka->where($where2);
		$this->db_heijunka->update_batch($table, $data, $where1); //where is just set 1 column
	}
	public function insert_heijunka($table, $data)
	{
		$this->db_heijunka->insert($table, $data);
	}
	public function join_data_heijunka($table, $table_join, $on_join, $select, $where, $status)
	{
		$this->db_heijunka->select($select);
		$this->db_heijunka->where($where);
		$this->db_heijunka->from($table);
		$this->db_heijunka->join($table_join, $on_join);
		if ($status == 'result') {
			return $this->db_heijunka->get()->result();
		} else {
			return $this->db_heijunka->get()->row();
		}
	}

	public function union_heijunka($order)
	{
		$query = "SELECT * FROM `master` UNION SELECT * FROM `master_td_link` WHERE SAPNIK != '' $order";
		$db = $this->db_heijunka->query($query);
		return $db->result();
	}

	public function union_heijunka_print($order)
	{
		$query = "SELECT * FROM `master_print` UNION SELECT * FROM `master_td_link` WHERE SAPNIK != '' $order";
		$db = $this->db_heijunka->query($query);
		return $db->result();
	}

	public function union_heijunka_kap2($order)
	{
		$query = "SELECT * FROM `master_kap2` UNION SELECT * FROM `master_td_link_kap2` WHERE SAPNIK != '' $order";
		$db = $this->db_heijunka->query($query);
		return $db->result();
	}

	public function union_heijunka_limit($start,$end,$order)
	{
		$query = "SELECT * FROM `master` UNION SELECT * FROM `master_td_link` WHERE SAPNIK != '' $order LIMIT $start,$end";
		$db = $this->db_heijunka->query($query);
		return $db->result();
	}

	public function union_heijunka_limit_kap2($start,$end,$order)
	{
		$query = "SELECT * FROM `master_kap2` UNION SELECT * FROM `master_td_link_kap2` WHERE SAPNIK != '' $order LIMIT $start,$end";
		$db = $this->db_heijunka->query($query);
		return $db->result();
	}

	public function data_wos($select,$where)
	{
		$query = "SELECT $select FROM `master` WHERE $where UNION SELECT $select FROM `master_td_link` WHERE $where";
		$db = $this->db_heijunka->query($query);
		return $db->row();
	}


	public function insert_batch($table, $data)
	{
		$insert = $this->db->insert_batch($table, $data);
		if ($insert) {
			return true;
		}
	}
	public function gds($table, $select, $where, $status)
	{
		$this->db->select($select);
		$this->db->where($where);
		$this->db->from($table);
		if ($status == 'result') {
			return $this->db->get()->result();
		} else if($status == "result_array") {
			return $this->db->get()->result_array();
		}else{
			return $this->db->get()->row();
		}
	}
	public function join_data($table, $table_join, $on_join, $select, $where, $status)
	{
		$this->db->select($select);
		$this->db->where($where);
		$this->db->from($table);
		$this->db->join($table_join, $on_join);
		if ($status == 'result') {
			return $this->db->get()->result();
		} else {
			return $this->db->get()->row();
		}
	}
	public function join3table($table, $table1, $table2, $join1, $join2, $select, $where, $status)
	{
		$this->db->select($select);
		$this->db->from($table);
		$this->db->join($table1, $join1, 'left');
		$this->db->join($table2, $join2, 'left');
		$this->db->where($where);
		if ($status == 'result') {
			return $this->db->get()->result();
		} else {
			return $this->db->get()->row();
		}
	}
	public function delete($table, $where)
	{
		$this->db->where($where);
		$this->db->delete($table);
	}
	function update($table, $where, $data)
	{
		$this->db->where($where);
		$this->db->update($table, $data);
	}
	public function insert($table, $data)
	{
		$this->db->insert($table, $data);
	}
	public function insert_multiple($table, $data)
	{
		$this->db->insert_batch($table, $data);
	}
	public function gdc($table, $select, $where, $return)
	{
		$this->db->select($select);
		$this->db->from($table);
		$this->db->where($where);
		if ($return == "result") {
			return $this->db->get()->result();
		} else {
			return $this->db->get()->row();
		}
	}
	public function truncate($table)
	{
		$this->db->truncate($table);
	}
}
