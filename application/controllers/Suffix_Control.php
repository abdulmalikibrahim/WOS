<?php
defined('BASEPATH') OR exit('No direct script access allowed');
date_default_timezone_set('Asia/Jakarta');
include_once(dirname(__FILE__) . "/Construct.php");

class Suffix_Control extends Construct
{
    public function index()
    {
        $data["title"]      = "Suffix Control";
        $data["javascript"] = "suffix_control";
        $data["content"]    = "view/suffix_control";
        $this->load->view('layout/index', $data);
    }

    public function upload()
    {
        header('Content-Type: application/json');

        $month = (int)$this->input->post('month');
        $year  = (int)$this->input->post('year');

        if (empty($month) || empty($year)) {
            echo json_encode(['status' => 'error', 'message' => 'Periode bulan dan tahun wajib diisi']);
            die();
        }

        if (!isset($_FILES['upload-file']['name']) || empty($_FILES['upload-file']['name'])) {
            echo json_encode(['status' => 'error', 'message' => 'Tidak ada file yang diupload']);
            die();
        }

        try {
            $object = PHPExcel_IOFactory::load($_FILES['upload-file']['tmp_name']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Format file tidak valid: ' . $e->getMessage()]);
            die();
        }

        $insert_data   = [];
        $m             = str_pad($month, 2, '0', STR_PAD_LEFT);
        $periode       = "{$year}-{$m}-01";

        foreach ($object->getWorksheetIterator() as $worksheet) {
            $highestRow = $worksheet->getHighestRow();

            for ($row = 2; $row <= $highestRow; $row++) {
                $katashiki = trim($worksheet->getCellByColumnAndRow(0, $row)->getValue());
                if (empty($katashiki)) continue;

                $suffix     = trim($worksheet->getCellByColumnAndRow(1, $row)->getValue());
                $model_name = trim($worksheet->getCellByColumnAndRow(2, $row)->getValue());
                $model_code = trim($worksheet->getCellByColumnAndRow(3, $row)->getValue());
                $plan_mdp   = (int)$worksheet->getCellByColumnAndRow(4, $row)->getValue();

                $insert_data[] = [
                    'periode'    => $periode,
                    'katashiki'  => $katashiki,
                    'suffix'     => $suffix,
                    'model_name' => $model_name,
                    'model_code' => $model_code,
                    'plan_mdp'   => $plan_mdp,
                    'add_mdp'    => 0,
                ];
            }
        }

        if (empty($insert_data)) {
            echo json_encode(['status' => 'error', 'message' => 'Tidak ada data yang ditemukan di file']);
            die();
        }

        $this->db->where('YEAR(periode)', $year)->where('MONTH(periode)', $month)->delete('suffix_control');
        $this->db->insert_batch('suffix_control', $insert_data);

        echo json_encode([
            'status'  => 'ok',
            'message' => count($insert_data) . ' record berhasil diimport untuk periode ' . $m . '/' . $year,
        ]);
        die();
    }

    public function get_data()
    {
        header('Content-Type: application/json');

        $month = (int)$this->input->get('month');
        $year  = (int)$this->input->get('year');

        if (empty($month) || empty($year)) {
            echo json_encode(['status' => 'error', 'data' => []]);
            die();
        }

        $rows = $this->db->query(
            "SELECT id, katashiki, suffix, model_name, model_code, plan_mdp, add_mdp
             FROM suffix_control
             WHERE YEAR(periode) = {$year} AND MONTH(periode) = {$month}
             ORDER BY model_code ASC, model_name ASC, suffix ASC"
        )->result_array();

        if (empty($rows)) {
            echo json_encode(['status' => 'ok', 'data' => []]);
            die();
        }

        // Act. Receive: COUNT from bank_vlt per katashiki_suffix (= suffix) for period
        $act_receive_map = [];
        $act_model = [];
        $ar = $this->db->query(
            "SELECT katashiki_suffix, model, COUNT(*) AS cnt
             FROM bank_vlt
             WHERE YEAR(plan_delivery_date) = {$year} AND MONTH(plan_delivery_date) = {$month}
               AND katashiki_suffix IS NOT NULL AND katashiki_suffix != ''
             GROUP BY katashiki_suffix"
        )->result_array();
        foreach ($ar as $r) {
            $act_receive_map[$r['katashiki_suffix']] = (int)$r['cnt'];
            $act_model[$r['model']] = $r['model'];
        }

        $implode_model = "'".implode("','",$act_model)."'";
        // Act. WOS: COUNT from checking_wos per suffix for period
        $act_wos_map = [];
        $aw = $this->db->query(
            "SELECT suffix, COUNT(*) AS cnt
             FROM checking_wos
             WHERE YEAR(pdd) = {$year} AND MONTH(pdd) = {$month}
               AND suffix IS NOT NULL AND suffix != ''
               AND model IN($implode_model)
             GROUP BY suffix"
        )->result_array();
        foreach ($aw as $r) {
            $act_wos_map[$r['suffix']] = (int)$r['cnt'];
        }

        // Group: model_code → model_name → rows
        $grouped = [];
        foreach ($rows as $r) {
            $mc = $r['model_code'];
            $mn = $r['model_name'];

            if (!isset($grouped[$mc])) {
                $grouped[$mc] = [
                    'model_code' => $mc,
                    'models'     => [],
                    'totals'     => ['plan_mdp'=>0,'add_mdp'=>0,'total_plan'=>0,'act_receive'=>0,'remain'=>0,'act_wos'=>0,'available'=>0],
                ];
            }
            if (!isset($grouped[$mc]['models'][$mn])) {
                $grouped[$mc]['models'][$mn] = [
                    'model_name' => $mn,
                    'rows'       => [],
                    'subtotals'  => ['plan_mdp'=>0,'add_mdp'=>0,'total_plan'=>0,'act_receive'=>0,'remain'=>0,'act_wos'=>0,'available'=>0],
                ];
            }

            $plan_mdp    = (int)$r['plan_mdp'];
            $add_mdp     = (int)$r['add_mdp'];
            $total_plan  = $plan_mdp + $add_mdp;
            $act_receive = $act_receive_map[$r['suffix']] ?? 0;
            $remain      = $act_receive - $total_plan;
            $act_wos     = $act_wos_map[$r['suffix']] ?? 0;
            $available   = $act_receive - $act_wos;

            $row_data = [
                'id'          => (int)$r['id'],
                'katashiki'   => $r['katashiki'],
                'suffix'      => $r['suffix'],
                'plan_mdp'    => $plan_mdp,
                'add_mdp'     => $add_mdp,
                'total_plan'  => $total_plan,
                'act_receive' => $act_receive,
                'remain'      => $remain,
                'act_wos'     => $act_wos,
                'available'   => $available,
            ];

            $grouped[$mc]['models'][$mn]['rows'][] = $row_data;

            $s = &$grouped[$mc]['models'][$mn]['subtotals'];
            $s['plan_mdp']    += $plan_mdp;
            $s['add_mdp']     += $add_mdp;
            $s['total_plan']  += $total_plan;
            $s['act_receive'] += $act_receive;
            $s['remain']      += $remain;
            $s['act_wos']     += $act_wos;
            $s['available']   += $available;

            $t = &$grouped[$mc]['totals'];
            $t['plan_mdp']    += $plan_mdp;
            $t['add_mdp']     += $add_mdp;
            $t['total_plan']  += $total_plan;
            $t['act_receive'] += $act_receive;
            $t['remain']      += $remain;
            $t['act_wos']     += $act_wos;
            $t['available']   += $available;
        }

        // Re-index models as array for JSON
        foreach ($grouped as &$mc_data) {
            $mc_data['models'] = array_values($mc_data['models']);
        }

        echo json_encode(['status' => 'ok', 'data' => $grouped]);
        die();
    }

    public function update_add_mdp()
    {
        header('Content-Type: application/json');

        $id      = (int)$this->input->post('id');
        $add_mdp = (int)$this->input->post('add_mdp');

        if (empty($id)) {
            echo json_encode(['status' => 'error', 'message' => 'ID tidak valid']);
            die();
        }

        $this->db->where('id', $id)->update('suffix_control', ['add_mdp' => $add_mdp]);
        echo json_encode(['status' => 'ok', 'message' => 'Add MDP berhasil diupdate']);
        die();
    }

    public function clear()
    {
        header('Content-Type: application/json');

        $month = (int)$this->input->post('month');
        $year  = (int)$this->input->post('year');

        if (empty($month) || empty($year)) {
            echo json_encode(['status' => 'error', 'message' => 'Periode tidak valid']);
            die();
        }

        $this->db->where('YEAR(periode)', $year)->where('MONTH(periode)', $month)->delete('suffix_control');
        echo json_encode(['status' => 'ok', 'message' => 'Data periode ' . str_pad($month,2,'0',STR_PAD_LEFT) . '/' . $year . ' berhasil dihapus']);
        die();
    }

    public function download_template()
    {
        $file = FCPATH . 'uploads/template_uploads/template_control_suffix.xlsx';
        if (!file_exists($file)) {
            show_error('File template tidak ditemukan.', 404);
            return;
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="template_control_suffix.xlsx"');
        header('Content-Length: ' . filesize($file));
        header('Cache-Control: no-cache');
        readfile($file);
        exit;
    }
}
