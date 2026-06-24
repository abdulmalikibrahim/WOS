<?php
defined('BASEPATH') OR exit('No direct script access allowed');
date_default_timezone_set('Asia/Jakarta');
include_once(dirname(__FILE__) . "/Construct.php");

class Bank_VLT extends Construct
{
    public function index()
    {
        $data["title"]      = "Bank VLT";
        $data["javascript"] = "bank_vlt";
        $data["content"]    = "view/bank_vlt";
        $this->load->view('layout/index', $data);
    }

    public function import_bank_vlt()
    {
        if (!isset($_FILES["upload-file"]["name"]) || empty($_FILES["upload-file"]["name"])) {
            $this->voice("gagal.mp3");
            $this->swal("Gagal", "Tidak ada file yang diupload", "error");
            redirect("bank_vlt");
            return;
        }

        $path = $_FILES["upload-file"]["tmp_name"];

        try {
            $object = PHPExcel_IOFactory::load($path);
        } catch (Exception $e) {
            $this->voice("gagal.mp3");
            $this->swal("Gagal", "Format file tidak valid: " . $e->getMessage(), "error");
            redirect("bank_vlt");
            return;
        }

        $temp_data = [];

        foreach ($object->getWorksheetIterator() as $worksheet) {
            $highestRow = $worksheet->getHighestRow();

            for ($row = 2; $row <= $highestRow; $row++) {
                $sapnik = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
                if (empty($sapnik)) continue;

                $wos_material             = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
                $wos_material_description = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
                $sap_material             = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
                $engine_model             = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
                $engine_prefix            = $worksheet->getCellByColumnAndRow(6, $row)->getValue();
                $engine_number            = $worksheet->getCellByColumnAndRow(7, $row)->getValue();
                $plant_excel              = $worksheet->getCellByColumnAndRow(8, $row)->getValue();
                $chassis_number           = $worksheet->getCellByColumnAndRow(9, $row)->getValue();
                $lot_code                 = $worksheet->getCellByColumnAndRow(10, $row)->getValue();
                $lot_number               = $worksheet->getCellByColumnAndRow(11, $row)->getValue();
                $katashiki                = $worksheet->getCellByColumnAndRow(12, $row)->getValue();

                // Kolom N (katashiki_suffix) diisi sama dengan kolom K (lot_code)
                $katashiki_suffix = $lot_code;

                $adm_production_id = $worksheet->getCellByColumnAndRow(14, $row)->getValue();
                $tam_production_id = $worksheet->getCellByColumnAndRow(15, $row)->getValue();

                // Plan Delivery Date (kolom Q): format "22 MAY 26"
                $pdd_raw = $worksheet->getCellByColumnAndRow(16, $row)->getValue();
                if (!empty($pdd_raw)) {
                    $pdd_parts = explode(" ", trim($pdd_raw));
                    if (!empty($pdd_parts[2])) {
                        $year_pdd          = "20" . $pdd_parts[2];
                        $month_pdd         = $this->month_import($pdd_parts[1]);
                        $day_pdd           = str_pad($pdd_parts[0], 2, '0', STR_PAD_LEFT);
                        $plan_delivery_date = $year_pdd . "-" . $month_pdd . "-" . $day_pdd;
                    } else {
                        $plan_delivery_date = NULL;
                    }
                } else {
                    $plan_delivery_date = NULL;
                }

                // Plan Jig In Date (kolom R): format YYYYMMDD
                $pjid_raw = $worksheet->getCellByColumnAndRow(17, $row)->getValue();
                if (!empty($pjid_raw)) {
                    $year_pjid        = substr($pjid_raw, 0, 4);
                    $month_pjid       = substr($pjid_raw, 4, 2);
                    $day_pjid         = substr($pjid_raw, 6, 2);
                    $plan_jig_in_date = $year_pjid . "-" . $month_pjid . "-" . $day_pjid;
                } else {
                    $plan_jig_in_date = NULL;
                }

                // WOS Release Date (kolom S): format YYYYMMDD
                $vrd_raw = $worksheet->getCellByColumnAndRow(18, $row)->getValue();
                if (!empty($vrd_raw)) {
                    $day_vrd          = substr($vrd_raw, 0, 2);
                    $month_vrd        = substr($vrd_raw, 2, 2);
                    $year_vrd         = substr($vrd_raw, 4, 6);
                    $wos_release_date = $year_vrd . "-" . $month_vrd . "-" . $day_vrd;
                } else {
                    $wos_release_date = NULL;
                }

                $sapwos_des   = $worksheet->getCellByColumnAndRow(19, $row)->getValue();
                $location     = $worksheet->getCellByColumnAndRow(20, $row)->getValue();
                $color_code   = $worksheet->getCellByColumnAndRow(21, $row)->getValue();
                $ed           = $worksheet->getCellByColumnAndRow(22, $row)->getValue();
                $order_column = $worksheet->getCellByColumnAndRow(23, $row)->getValue();
                $destination  = $worksheet->getCellByColumnAndRow(24, $row)->getValue();

                // Kolom Y: jika mengandung TMMIN, ubah jadi DOM
                if (!empty($destination) && strpos($destination, 'TMMIN') !== false) {
                    $destination = 'DOM';
                }

                // Ekstrak model dari kolom C: explode by space, ambil index ke-2
                $exp_wmd = explode(" ", trim($wos_material_description));
                $model   = isset($exp_wmd[2]) ? $exp_wmd[2] : NULL;

                $temp_data[] = [
                    'wos_material'             => $wos_material,
                    'wos_material_description' => $wos_material_description,
                    'sapnik'                   => str_replace(" ", "", $sapnik),
                    'sap_material'             => $sap_material,
                    'engine_model'             => $engine_model,
                    'engine_prefix'            => $engine_prefix,
                    'engine_number'            => $engine_number,
                    'plant_excel'              => $plant_excel,
                    'chassis_number'           => $chassis_number,
                    'lot_code'                 => $lot_code,
                    'lot_number'               => $lot_number,
                    'katashiki'                => $katashiki,
                    'katashiki_suffix'         => $katashiki_suffix,
                    'adm_production_id'        => $adm_production_id,
                    'tam_production_id'        => $tam_production_id,
                    'plan_delivery_date'       => $plan_delivery_date,
                    'plan_jig_in_date'         => $plan_jig_in_date,
                    'wos_release_date'         => $wos_release_date,
                    'sapwos_des'               => $sapwos_des,
                    'location'                 => $location,
                    'color_code'               => $color_code,
                    'ed'                       => $ed,
                    'order_column'             => $order_column,
                    'destination'              => $destination,
                    'model'                    => $model
                ];
            }
        }

        if (!empty($temp_data)) {
            // Fetch existing SAPNIKs from DB
            $existing_rows   = $this->db->select('sapnik')->get('bank_vlt')->result_array();
            $existing_sapnik = array_column($existing_rows, 'sapnik');

            $new_data        = [];
            $duplicate_sapnik = [];

            foreach ($temp_data as $row) {
                if (!empty($row['sapnik']) && in_array($row['sapnik'], $existing_sapnik)) {
                    $duplicate_sapnik[] = $row['sapnik'];
                } else {
                    $new_data[] = $row;
                }
            }

            $dup_info = '';
            if (!empty($duplicate_sapnik)) {
                $dup_list = "<ol style='margin:0' class='text-left m-2'><li>". implode('<li>', $duplicate_sapnik) . '</li></ol>';
                $dup_info = "<br><br><b>" . count($duplicate_sapnik) . " SAPNIK duplikat ditolak:</b><br><small style='word-break:break-all;'>" . $dup_list . "</small>";
            }

            if (!empty($new_data)) {
                $insert = $this->model->insert_batch("bank_vlt", $new_data);
                if(!empty($dup_info)){
                    $dup_info .= "<br><br>Total VIN New Insert : ".count($new_data);
                }
            } else {
                $insert = false;
            }

            if (!empty($new_data) && $insert) {
                $this->voice("sukses.mp3");
                $this->swal_custom_icon(
                    "Sukses",
                    "Upload Bank VLT berhasil!<br><b>" . count($new_data) . " data</b> berhasil diimport." . $dup_info,
                    base_url('assets/images/happy.png'),
                    "",
                    "true"
                );
            } elseif (empty($new_data) && !empty($duplicate_sapnik)) {
                $this->voice("gagal.mp3");
                $this->swal_custom_icon(
                    "Peringatan",
                    "Semua data ditolak karena SAPNIK sudah ada di database." . $dup_info,
                    base_url('assets/images/emot-sedih.jpg'),
                    "rounded-circle",
                    "true"
                );
            } else {
                $this->voice("gagal.mp3");
                $this->swal_custom_icon("Gagal", "Gagal menyimpan data ke database" . $dup_info, base_url('assets/images/emot-sedih.jpg'), "rounded-circle", "true");
            }
        } else {
            $this->voice("gagal.mp3");
            $this->swal_custom_icon("Gagal", "Tidak ada data yang dapat diimport dari file tersebut", base_url('assets/images/emot-sedih.jpg'), "rounded-circle", "true");
        }

        redirect("bank_vlt");
    }

    public function load_bank_vlt()
    {
        header("Content-Type: application/json");

        $start_date      = $this->input->get("start_date");
        $end_date        = $this->input->get("end_date");
        $already_process = $this->input->get("already_process");
        $exclude_master  = $this->input->get("exclude_master");

        $conditions = ["b.id > 0"];

        // Saat dipakai dari menu pick (ambil data untuk Tabungan), jangan munculkan
        // SAPNIK yang sudah ada di master / master_kap2 agar tidak terjadi double data.
        // Kedua tabel itu berada di database heijunka (db_heijunka_wos), bukan database
        // default, jadi ambil nama database-nya secara dinamis lalu join lintas-database.
        $master_join = "";
        if ($exclude_master == 1) {
            $hj_db = $this->model->db_heijunka->database;
            $master_join = "
             LEFT JOIN (SELECT DISTINCT SAPNIK FROM `{$hj_db}`.master      WHERE SAPNIK IS NOT NULL AND SAPNIK != '') m1 ON m1.SAPNIK = b.sapnik
             LEFT JOIN (SELECT DISTINCT SAPNIK FROM `{$hj_db}`.master_kap2 WHERE SAPNIK IS NOT NULL AND SAPNIK != '') m2 ON m2.SAPNIK = b.sapnik";
            $conditions[] = "m1.SAPNIK IS NULL";
            $conditions[] = "m2.SAPNIK IS NULL";
        }

        if (!empty($start_date)) {
            $conditions[] = "b.plan_delivery_date >= '" . $this->db->escape_str($start_date) . "'";
        }
        if (!empty($end_date)) {
            $conditions[] = "b.plan_delivery_date <= '" . $this->db->escape_str($end_date) . "'";
        }
        if ($already_process !== '' && $already_process !== NULL && $already_process !== false) {
            if (intval($already_process) === 0) {
                $conditions[] = "c.vin IS NULL";
            } else {
                $conditions[] = "c.vin IS NOT NULL";
            }
        }

        $where_str = implode(' AND ', $conditions);
        $rows      = $this->db->query(
            "SELECT b.*, IF(c.vin IS NOT NULL, 1, 0) AS already_process,
                    c.kap AS wos_plant, c.pdd_input AS wos_pdd_input
             FROM bank_vlt b
             LEFT JOIN (
                 SELECT vin, kap, pdd_input
                 FROM checking_wos
                 WHERE id IN (SELECT MAX(id) FROM checking_wos GROUP BY vin)
             ) c ON c.vin = b.sapnik
             {$master_join}
             WHERE {$where_str}
             ORDER BY b.plan_delivery_date ASC, b.id ASC"
        )->result();

        $result = [];

        foreach ($rows as $d) {
            $result[] = [
                'id'                       => $d->id,
                'wos_material'             => $d->wos_material,
                'wos_material_description' => $d->wos_material_description,
                'sapnik'                   => $d->sapnik,
                'sap_material'             => $d->sap_material,
                'engine_model'             => $d->engine_model,
                'engine_prefix'            => $d->engine_prefix,
                'engine_number'            => $d->engine_number,
                'plant_excel'              => $d->plant_excel,
                'chassis_number'           => $d->chassis_number,
                'lot_code'                 => $d->lot_code,
                'lot_number'               => $d->lot_number,
                'katashiki'                => $d->katashiki,
                'katashiki_suffix'         => $d->katashiki_suffix,
                'adm_production_id'        => $d->adm_production_id,
                'tam_production_id'        => $d->tam_production_id,
                'plan_delivery_date'       => $d->plan_delivery_date,
                'plan_jig_in_date'         => $d->plan_jig_in_date,
                'wos_release_date'         => $d->wos_release_date,
                'sapwos_des'               => $d->sapwos_des,
                'location'                 => $d->location,
                'color_code'               => $d->color_code,
                'ed'                       => $d->ed,
                'order_column'             => $d->order_column,
                'destination'              => $d->destination,
                'model'                    => $d->model,
                'already_process'          => (int)$d->already_process,
                'plant'                    => $d->wos_plant ?? '',
                'pdd_proses'               => !empty($d->wos_pdd_input) ? date("d-m-Y", strtotime($d->wos_pdd_input)) : ""
            ];
        }

        echo json_encode(['status' => 'success', 'data' => $result, 'total' => count($result)]);
        die();
    }

    public function get_summary_bank_vlt()
    {
        header("Content-Type: application/json");

        $month = $this->input->get("month");
        $year  = $this->input->get("year");

        $where_filter = "";
        if (!empty($month) && !empty($year)) {
            $m = str_pad(intval($month), 2, '0', STR_PAD_LEFT);
            $y = intval($year);
            $where_filter = "WHERE b.plan_delivery_date >= '{$y}-{$m}-01'
                               AND b.plan_delivery_date < DATE_ADD('{$y}-{$m}-01', INTERVAL 1 MONTH)";
        } elseif (!empty($year)) {
            $y = intval($year);
            $where_filter = "WHERE b.plan_delivery_date >= '{$y}-01-01'
                               AND b.plan_delivery_date < '" . ($y + 1) . "-01-01'";
        }

        $summary = $this->db->query(
            "SELECT COUNT(b.id) as total,
                    SUM(IF(c.vin IS NOT NULL, 1, 0)) as sudah_proses,
                    SUM(IF(c.vin IS NULL, 1, 0)) as belum_proses
             FROM bank_vlt b
             LEFT JOIN (SELECT DISTINCT vin FROM checking_wos) c ON c.vin = b.sapnik
             {$where_filter}"
        )->row();

        $model_where = !empty($where_filter)
            ? $where_filter . " AND b.model IS NOT NULL AND b.model != ''"
            : "WHERE b.model IS NOT NULL AND b.model != ''";

        $per_model_rows = $this->db->query(
            "SELECT b.model,
                    COUNT(b.id) as total,
                    SUM(IF(c.vin IS NOT NULL, 1, 0)) as sudah_proses
             FROM bank_vlt b
             LEFT JOIN (SELECT DISTINCT vin FROM checking_wos) c ON c.vin = b.sapnik
             {$model_where}
             GROUP BY b.model
             ORDER BY b.model ASC"
        )->result();

        // Plan/Add/Total MDP dari suffix_control per model_code (periode = bulan/tahun terpilih)
        $mdp_map = [];
        if (!empty($month) && !empty($year)) {
            $mm = intval($month);
            $yy = intval($year);
            $mdp_rows = $this->db->query(
                "SELECT model_code,
                        SUM(plan_mdp) AS plan_mdp,
                        SUM(add_mdp)  AS add_mdp
                 FROM suffix_control
                 WHERE YEAR(periode) = {$yy} AND MONTH(periode) = {$mm}
                 GROUP BY model_code"
            )->result();
            foreach ($mdp_rows as $mr) {
                $mdp_map[$mr->model_code] = [
                    'plan_mdp' => (int)$mr->plan_mdp,
                    'add_mdp'  => (int)$mr->add_mdp,
                ];
            }
        }

        $per_model = [];
        foreach ($per_model_rows as $m) {
            $mdp = $mdp_map[$m->model] ?? ['plan_mdp' => 0, 'add_mdp' => 0];
            $per_model[] = [
                'model'       => $m->model,
                'total'       => (int)$m->total,
                'sudah_proses'=> (int)$m->sudah_proses,
                'belum_proses'=> (int)$m->total - (int)$m->sudah_proses,
                'plan_mdp'    => $mdp['plan_mdp'],
                'add_mdp'     => $mdp['add_mdp'],
                'total_mdp'   => $mdp['plan_mdp'] + $mdp['add_mdp'],
            ];
        }

        echo json_encode([
            'total'       => (int)($summary->total ?? 0),
            'belum_proses'=> (int)($summary->belum_proses ?? 0),
            'sudah_proses'=> (int)($summary->sudah_proses ?? 0),
            'per_model'   => $per_model,
        ]);
        die();
    }

    public function edit_bank_vlt()
    {
        header("Content-Type: application/json");
        $id = (int)$this->input->get("id");

        if (empty($id)) {
            echo json_encode(['status' => 'error', 'msg' => 'ID tidak ditemukan']);
            die();
        }

        $data = $this->model->gds("bank_vlt", "*", "id = '$id'", "row");

        if (empty($data)) {
            echo json_encode(['status' => 'error', 'msg' => 'Data tidak ditemukan']);
            die();
        }

        echo json_encode(['status' => 'success', 'data' => $data]);
        die();
    }

    public function get_reason_bank_vlt()
    {
        header("Content-Type: application/json");
        $id = (int)$this->input->get("id");

        if (empty($id)) {
            echo json_encode(['status' => 'error', 'msg' => 'ID tidak ditemukan']);
            die();
        }

        $data = $this->model->gds("bank_vlt", "reason, pic, last_updated", "id = '$id'", "row");

        if (empty($data)) {
            echo json_encode(['status' => 'error', 'msg' => 'Data tidak ditemukan']);
            die();
        }

        if (!empty($data->last_updated) && $data->last_updated !== '0000-00-00 00:00:00') {
            $bulan = [
                1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];
            $timestamp = strtotime($data->last_updated);
            $data->last_updated = date('d', $timestamp) . ' ' . $bulan[(int)date('m', $timestamp)] . ' ' . date('Y H:i:s', $timestamp);
        } else {
            $data->last_updated = '-';
        }

        echo json_encode(['status' => 'success', 'data' => $data]);
        die();
    }

    public function save_edit_bank_vlt()
    {
        header("Content-Type: application/json");
        $id = (int)$this->input->post("id");

        if (empty($id)) {
            echo json_encode(['status' => 'error', 'msg' => 'ID tidak ditemukan']);
            die();
        }

        $plan_delivery_date = $this->input->post("plan_delivery_date");
        $plan_jig_in_date   = $this->input->post("plan_jig_in_date");
        $wos_release_date   = $this->input->post("wos_release_date");

        $reason = $this->input->post("reason");
        $pic    = $this->input->post("pic");

        if (empty($reason) || empty($pic)) {
            echo json_encode(['status' => 'error', 'msg' => 'Reason dan PIC wajib diisi']);
            die();
        }

        $update_data = [
            'wos_material'      => $this->input->post("wos_material"),
            'sapnik'            => $this->input->post("sapnik"),
            'sap_material'      => $this->input->post("sap_material"),
            'engine_model'      => $this->input->post("engine_model"),
            'engine_prefix'     => $this->input->post("engine_prefix"),
            'engine_number'     => $this->input->post("engine_number"),
            'plant_excel'       => $this->input->post("plant_excel"),
            'chassis_number'    => $this->input->post("chassis_number"),
            'lot_code'          => $this->input->post("lot_code"),
            'lot_number'        => $this->input->post("lot_number"),
            'katashiki'         => $this->input->post("katashiki"),
            'katashiki_suffix'  => $this->input->post("katashiki_suffix"),
            'adm_production_id' => $this->input->post("adm_production_id"),
            'tam_production_id' => $this->input->post("tam_production_id"),
            'plan_delivery_date'=> !empty($plan_delivery_date) ? $plan_delivery_date : NULL,
            'plan_jig_in_date'  => !empty($plan_jig_in_date)   ? $plan_jig_in_date   : NULL,
            'wos_release_date'  => !empty($wos_release_date)   ? $wos_release_date   : NULL,
            'sapwos_des'        => $this->input->post("sapwos_des"),
            'location'          => $this->input->post("location"),
            'color_code'        => $this->input->post("color_code"),
            'ed'                => $this->input->post("ed"),
            'order_column'      => $this->input->post("order_column"),
            'destination'       => $this->input->post("destination"),
            'model'             => $this->input->post("model"),
            'reason'            => $reason,
            'pic'               => $pic,
            'last_updated'      => date("Y-m-d H:i:s"),
        ];

        $this->model->update("bank_vlt", "id = '$id'", $update_data);
        echo json_encode(['status' => 'success', 'msg' => 'Data berhasil disimpan']);
        die();
    }

    public function pick_bank_vlt()
    {
        $data["title"]      = "Ambil Data Bank VLT";
        $data["javascript"] = "pick_bank_vlt";
        $data["content"]    = "view/pick_bank_vlt";
        $this->load->view('layout/index', $data);
    }

    public function use_bank_vlt()
    {
        header('Content-Type: application/json');

        $ids = $this->input->post('ids');
        $kap = $this->input->post('kap');

        if (empty($ids) || !is_array($ids)) {
            echo json_encode(['status' => 'error', 'message' => 'Tidak ada data yang dipilih']);
            die();
        }

        $table_tabungan = ($kap === 'kap2') ? 'tabungan_vlt_kap2' : 'tabungan_vlt';
        $clean_ids      = array_map('intval', $ids);

        $rows = $this->db->select('*')->from('bank_vlt')->where_in('id', $clean_ids)->get()->result_array();

        if (empty($rows)) {
            echo json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan di database']);
            die();
        }

        // Jangan hapus tabungan yang sudah ada (mis. data dummy) — data D55L ditambahkan.
        // Lewati SAPNIK yang sudah ada di tabungan supaya tidak terjadi duplikat.
        $existing        = $this->db->select('sapnik')->from($table_tabungan)->get()->result_array();
        $existing_sapnik = array_column($existing, 'sapnik');

        $insert_data = [];
        foreach ($rows as $r) {
            if (in_array($r['sapnik'], $existing_sapnik)) {
                continue;
            }
            $insert_data[] = [
                'wos_material'             => $r['wos_material'],
                'wos_material_description' => $r['wos_material_description'],
                'sapnik'                   => $r['sapnik'],
                'sap_material'             => $r['sap_material'],
                'engine_model'             => $r['engine_model'],
                'engine_prefix'            => $r['engine_prefix'],
                'engine_number'            => $r['engine_number'],
                'plant'                    => $r['plant_excel'],
                'chassis_number'           => $r['chassis_number'],
                'lot_code'                 => $r['lot_code'],
                'lot_number'               => $r['lot_number'],
                'katashiki'                => $r['katashiki'],
                'katashiki_suffix'         => $r['katashiki_suffix'],
                'adm_production_id'        => $r['adm_production_id'],
                'tam_production_id'        => $r['tam_production_id'],
                'plan_delivery_date'       => $r['plan_delivery_date'],
                'plan_jig_in_date'         => $r['plan_jig_in_date'],
                'wos_release_date'         => $r['wos_release_date'],
                'sapwos_des'               => $r['sapwos_des'],
                'location'                 => $r['location'],
                'color_code'               => $r['color_code'],
                'ed'                       => $r['ed'],
                'order_column'             => $r['order_column'],
                'destination'              => $r['destination'],
            ];
        }

        if (empty($insert_data)) {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Semua SAPNIK terpilih sudah ada di Tabungan VLT ' . strtoupper($kap)
            ]);
            die();
        }

        $insert = $this->model->insert_batch($table_tabungan, $insert_data);

        if ($insert) {
            // Set session so docking page shows tabungan is ready
            $this->session->set_userdata(['tabungan_actual' => 'YES']);
            echo json_encode([
                'status'  => 'ok',
                'message' => count($insert_data) . ' data berhasil ditambahkan ke Tabungan VLT ' . strtoupper($kap)
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan data ke tabungan']);
        }
        die();
    }

    public function download_bank_vlt()
    {
        $start_date      = $this->input->get("start_date");
        $end_date        = $this->input->get("end_date");
        $already_process = $this->input->get("already_process");

        $where = "id > 0";

        if (!empty($start_date)) {
            $where .= " AND plan_delivery_date >= '" . $this->db->escape_str($start_date) . "'";
        }
        if (!empty($end_date)) {
            $where .= " AND plan_delivery_date <= '" . $this->db->escape_str($end_date) . "'";
        }
        if ($already_process !== '' && $already_process !== NULL) {
            $where .= " AND already_process = '" . intval($already_process) . "'";
        }

        $where .= " ORDER BY plan_delivery_date ASC, id ASC";

        $data = $this->model->gds("bank_vlt", "*", $where, "result");

        $this->load->library('excel');
        $excel  = new PHPExcel();
        $sheet  = $excel->setActiveSheetIndex(0);
        $sheet->setTitle("Bank VLT");

        $headers = [
            'No.', 'WOS Material', 'WOS Material Description', 'SAPNIK', 'SAP Material',
            'Engine Model', 'Engine Prefix', 'Engine Number', 'Plant (Excel)', 'ChassisNumber',
            'Lot Code', 'Lot Number', 'Katashiki', 'Katashiki Sfx', 'ADM Production ID',
            'TAM Production ID', 'Plan Delivery Date', 'Plan Jig In Date', 'WOS Release Date',
            'SAPWOS-DES', 'Location', 'Color Code', 'ED', 'ORDER', 'Destination',
            'Model', 'Status Proses', 'Plant Process', 'PDD Process'
        ];

        $col_map = array_merge(range('A', 'Z'), ['AA', 'AB', 'AC']);

        $header_style = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'type'       => PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => ['rgb' => '1565C0'],
            ],
            'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER],
        ];

        foreach ($headers as $i => $header) {
            $cell = $col_map[$i] . '1';
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->applyFromArray($header_style);
            $sheet->getColumnDimension($col_map[$i])->setAutoSize(true);
        }

        $row_num = 2;
        foreach ($data as $i => $d) {
            $sheet->setCellValue('A'  . $row_num, $i + 1);
            $sheet->setCellValue('B'  . $row_num, $d->wos_material);
            $sheet->setCellValue('C'  . $row_num, $d->wos_material_description);
            $sheet->setCellValue('D'  . $row_num, $d->sapnik);
            $sheet->setCellValueExplicit('E'  . $row_num, $d->sap_material, PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('F'  . $row_num, $d->engine_model);
            $sheet->setCellValue('G'  . $row_num, $d->engine_prefix);
            $sheet->setCellValue('H'  . $row_num, $d->engine_number);
            $sheet->setCellValue('I'  . $row_num, $d->plant_excel);
            $sheet->setCellValue('J'  . $row_num, $d->chassis_number);
            $sheet->setCellValue('K'  . $row_num, $d->lot_code);
            $sheet->setCellValue('L'  . $row_num, $d->lot_number);
            $sheet->setCellValue('M'  . $row_num, $d->katashiki);
            $sheet->setCellValue('N'  . $row_num, $d->katashiki_suffix);
            $sheet->setCellValue('O'  . $row_num, $d->adm_production_id);
            $sheet->setCellValue('P'  . $row_num, $d->tam_production_id);
            $sheet->setCellValue('Q'  . $row_num, strtoupper(date("d M Y",strtotime($d->plan_delivery_date))), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('R'  . $row_num, date("Ymd",strtotime($d->plan_jig_in_date)), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('S'  . $row_num, $d->wos_release_date);
            $sheet->setCellValue('T'  . $row_num, $d->sapwos_des);
            $sheet->setCellValue('U'  . $row_num, $d->location);
            $sheet->setCellValue('V'  . $row_num, $d->color_code);
            $sheet->setCellValue('W'  . $row_num, $d->ed);
            $sheet->setCellValue('X'  . $row_num, $d->order_column);
            $sheet->setCellValue('Y'  . $row_num, $d->destination);
            $sheet->setCellValue('Z'  . $row_num, $d->model);
            $sheet->setCellValue('AA' . $row_num, $d->already_process == 1 ? 'Already' : 'Not yet');
            $sheet->setCellValue('AB' . $row_num, $d->plant);
            $sheet->setCellValue('AC' . $row_num, $d->pdd_proses);

            if ($d->already_process == 1) {
                $sheet->getStyle('A' . $row_num . ':AC' . $row_num)->applyFromArray([
                    'fill' => [
                        'type'       => PHPExcel_Style_Fill::FILL_SOLID,
                        'startcolor' => ['rgb' => 'C8E6C9'],
                    ],
                ]);
            }
            $row_num++;
        }

        $filename = "Bank_VLT_" . date("d-m-Y_His") . ".xls";
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment;filename=\"$filename\"");
        header("Cache-Control: max-age=0");
        header("Pragma: public");

        $writer = PHPExcel_IOFactory::createWriter($excel, "Excel5");
        $writer->save("php://output");
        die();
    }
}
