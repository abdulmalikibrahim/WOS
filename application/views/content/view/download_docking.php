<?php
header ("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment;filename = Docking WOS ".date("d-M-Y").".xls");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Docking</title>
</head>
<body>
    <table style="width:100%" border="1">
        <tr align="center">
            <th>No</th>
            <th>WOS Material</th>
            <th>WOS Material Description</th>
            <th>SAPNIK</th>
            <th>SAP Material</th>
            <th>Engine Model</th>
            <th>Engine Prefix</th>
            <th>Engine Number</th>
            <th>Plant</th>
            <th>Chassis Number</th>
            <th>Lot Code</th>
            <th>Lot Number</th>
            <th>Katashiki</th>
            <th>Katashiki Suffix</th>
            <th>ADM Prod. ID</th>
            <th>TAM Prod. ID</th>
            <th>Plan Delivery Date</th>
            <th>Plan Jig In Date</th>
            <th>WOS Release Date</th>
            <th>SAPWOS DES</th>
            <th>Location</th>
            <th>Color Code</th>
            <th>Model</th>
            <th>ED</th>
            <th>Order</th>
            <th>Destination</th>
            <th>Brand</th>
        </tr>
        <?php 
        $data_docking = $this->model->join_data("t_docking d","tabungan_vlt t","sapnik","*,t.sapnik","t.sapnik !=","result");
        if(!empty($data_docking)){
            $no = 1;
            foreach ($data_docking as $data_docking) {
                $model = $this->model->gds("plan_wos","model_name,model_code","suffix = '".$data_docking->katashiki_suffix."'","row");
                if(!empty($model)){
                    $model_code = $model->model_code;
                    $brand = $model->model_name;
                }else{
                    $model_code = "";
                    $brand = "";
                }
                ?>
                <tr align="center">
                    <td><?= $no++; ?></td>
                    <td><?= $data_docking->wos_material; ?></td>
                    <td><?= $data_docking->wos_material_description; ?></td>
                    <td><?= $data_docking->sapnik; ?></td>
                    <td><?= $data_docking->sap_material; ?></td>
                    <td><?= $data_docking->engine_model; ?></td>
                    <td><?= $data_docking->engine_prefix; ?></td>
                    <td><?= $data_docking->engine_number; ?></td>
                    <td><?= $data_docking->plant; ?></td>
                    <td>&nbsp;&nbsp;<?= $data_docking->sapnik; ?>&nbsp;&nbsp;</td>
                    <td><?= $data_docking->lot_code; ?></td>
                    <td><?= $data_docking->lot_number; ?></td>
                    <td><?= $data_docking->katashiki; ?></td>
                    <td><?= $data_docking->katashiki_suffix; ?></td>
                    <td><?= $data_docking->adm_production_id; ?></td>
                    <td><?= $data_docking->tam_production_id; ?></td>
                    <td><?= $data_docking->plan_delivery_date; ?></td>
                    <td><?= $data_docking->plan_jig_in_date; ?></td>
                    <td><?= $data_docking->wos_release_date; ?></td>
                    <td><?= $data_docking->sapwos_des; ?></td>
                    <td>NO</td>
                    <td><?= $data_docking->color_code; ?></td>
                    <td><?= $model_code; ?></td>
                    <td><?= $data_docking->ed; ?></td>
                    <td><?= $data_docking->order_column; ?></td>
                    <td><?= $data_docking->destination; ?></td>
                    <td><?= $brand; ?></td>
                </tr>
                <?php
            }
        }
        ?>
    </table>
</body>
</html>