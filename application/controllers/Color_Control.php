<?php
defined('BASEPATH') OR exit('No direct script access allowed');
date_default_timezone_set('Asia/Jakarta');
include_once(dirname(__FILE__) . "/Construct.php");

class Color_Control extends Construct
{
    public function index()
    {
        $data["title"]      = "Color Control";
        $data["javascript"] = "color_control";
        $data["content"]    = "view/color_control";
        $this->load->view('layout/index', $data);
    }

    public function upload()
    {
        header('Content-Type: application/json');

        $month = (int)$this->input->post('month');
        $year  = (int)$this->input->post('year');
        $model = trim($this->input->post('model'));

        if (empty($month) || empty($year)) {
            echo json_encode(['status' => 'error', 'message' => 'Periode bulan dan tahun wajib diisi']);
            die();
        }

        if (empty($model)) {
            echo json_encode(['status' => 'error', 'message' => 'Model wajib dipilih']);
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

        $insert_data = [];
        $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $m = str_pad($month, 2, '0', STR_PAD_LEFT);

        foreach ($object->getWorksheetIterator() as $worksheet) {
            $highestRow = $worksheet->getHighestRow();

            for ($row = 2; $row <= $highestRow; $row++) {
                $color = trim($worksheet->getCellByColumnAndRow(0, $row)->getValue());
                if (empty($color)) continue;

                for ($day = 1; $day <= 31; $day++) {
                    if ($day > $days_in_month) break;
                    $col_idx = $day; // col 0 = color, col 1..31 = day 1..31
                    $plan    = (int)$worksheet->getCellByColumnAndRow($col_idx, $row)->getValue();
                    if ($plan <= 0) continue;

                    $d = str_pad($day, 2, '0', STR_PAD_LEFT);
                    $insert_data[] = [
                        'tanggal' => "{$year}-{$m}-{$d}",
                        'model'   => $model,
                        'color'   => $color,
                        'plan'    => $plan,
                    ];
                }
            }
        }

        if (empty($insert_data)) {
            echo json_encode(['status' => 'error', 'message' => 'Tidak ada data plan yang ditemukan di file']);
            die();
        }

        // Hapus data lama untuk periode & model ini lalu insert baru
        $this->db->where('YEAR(tanggal)', $year)->where('MONTH(tanggal)', $month)->where('model', $model)->delete('color_control');
        $this->db->insert_batch('color_control', $insert_data);

        echo json_encode([
            'status'  => 'ok',
            'message' => count($insert_data) . ' record berhasil diimport untuk model ' . $model . ' periode ' . $m . '/' . $year,
        ]);
        die();
    }

    public function get_plan()
    {
        header('Content-Type: application/json');

        $month = (int)$this->input->get('month');
        $year  = (int)$this->input->get('year');
        $model = $this->input->get('model');

        if (empty($month) || empty($year)) {
            echo json_encode(['status' => 'error', 'data' => []]);
            die();
        }

        $model_filter = '';
        if (!empty($model)) {
            $model_safe   = $this->db->escape($model);
            $model_filter = "AND model = {$model_safe}";
        }

        $rows = $this->db->query(
            "SELECT color, DAY(tanggal) AS day, SUM(plan) AS plan
             FROM color_control
             WHERE YEAR(tanggal) = {$year} AND MONTH(tanggal) = {$month}
               {$model_filter}
             GROUP BY color, DAY(tanggal)
             ORDER BY color ASC, DAY(tanggal) ASC"
        )->result_array();

        // Pivot: color => [day => plan]
        $pivot = [];
        foreach ($rows as $r) {
            $pivot[$r['color']][(int)$r['day']] = (int)$r['plan'];
        }

        echo json_encode(['status' => 'ok', 'data' => $pivot]);
        die();
    }

    public function get_models()
    {
        header('Content-Type: application/json');

        $month = (int)$this->input->get('month');
        $year  = (int)$this->input->get('year');

        if (empty($month) || empty($year)) {
            echo json_encode(['status' => 'ok', 'data' => []]);
            die();
        }

        $rows = $this->db->query(
            "SELECT DISTINCT model
             FROM bank_vlt
             WHERE YEAR(plan_delivery_date) = {$year} AND MONTH(plan_delivery_date) = {$month}
               AND model IS NOT NULL AND model != ''
             ORDER BY model ASC"
        )->result_array();

        $models = array_column($rows, 'model');
        echo json_encode(['status' => 'ok', 'data' => $models]);
        die();
    }

    public function get_actual()
    {
        header('Content-Type: application/json');

        $month = (int)$this->input->get('month');
        $year  = (int)$this->input->get('year');
        $model = $this->input->get('model');

        if (empty($month) || empty($year)) {
            echo json_encode(['status' => 'error', 'data' => []]);
            die();
        }

        $model_filter = '';
        if (!empty($model)) {
            $model_safe   = $this->db->escape($model);
            $model_filter = "AND model = {$model_safe}";
        }

        $rows = $this->db->query(
            "SELECT color_code as color, DAY(plan_delivery_date) AS day, COUNT(*) AS actual
             FROM bank_vlt
             WHERE YEAR(plan_delivery_date) = {$year} AND MONTH(plan_delivery_date) = {$month}
               AND color_code IS NOT NULL AND color_code != ''
               AND plan_delivery_date IS NOT NULL
               {$model_filter}
             GROUP BY color_code, DAY(plan_delivery_date)
             ORDER BY color_code ASC, DAY(plan_delivery_date) ASC"
        )->result_array();

        $pivot = [];
        foreach ($rows as $r) {
            $pivot[$r['color']][(int)$r['day']] = (int)$r['actual'];
        }

        echo json_encode(['status' => 'ok', 'data' => $pivot]);
        die();
    }

    public function download_template()
    {
        $file = FCPATH . 'uploads/template_uploads/template_control_color.xlsx';
        if (!file_exists($file)) {
            show_error('File template tidak ditemukan.', 404);
            return;
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="template_control_color.xlsx"');
        header('Content-Length: ' . filesize($file));
        header('Cache-Control: no-cache');
        readfile($file);
        exit;
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

        $this->db->where('YEAR(tanggal)', $year)->where('MONTH(tanggal)', $month)->delete('color_control');
        echo json_encode(['status' => 'ok', 'message' => 'Data periode ' . str_pad($month,2,'0',STR_PAD_LEFT) . '/' . $year . ' berhasil dihapus']);
        die();
    }
}
