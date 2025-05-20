<?php
header ("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment;filename = Master Service Part.xls");
?>
<!DOCTYPE html>
<html>
<head>
    <title></title>
</head>
<body>
    <table border="1" style="font-size: 10pt;">
        <tr style="font-weight: bold;" align="center">
            <td>No.</td>
            <td>Mother Part Number</td>
            <td>Child Part Number</td>
            <td>Part Name</td>
            <td>Job Number</td>
            <td>Route</td>
            <td>Qty</td>
            <td>Keterangan</td>
        </tr>
		<?php
		$data_contoh = array(
			"53301-BZ380-00" => array(
				"53301-BZ380-00" => array(
					"name" => "HOOD SUB-ASSY",
					"route" => "PL5 (WELD)-ED-SPD",
					"qty" => "1",
					"job_num" => "-",
				),
				"53307-BZ040-00" => array(
					"name" => "HOOK SUB-ASSY, HOOD LOCK",
					"route" => "SP-PL5 (WELD)",
					"qty" => "1",
					"job_num" => "AA-0256",
				),
				"53311-BZ280-00" => array(
					"name" => "PANEL, HOOD",
					"route" => "PL5 (STAMP)-PL5 (WELD)",
					"qty" => "1",
					"job_num" => "P4051",
				),
				"53321-BZ270-00" => array(
					"name" => "PANEL, HOOD, INNER",
					"route" => "PL5 (STAMP)-PL5 (WELD)",
					"qty" => "1",
					"job_num" => "P4052",
				),
				"53331-BZ120-00" => array(
					"name" => "PLATE, HOOD HINGE MOUNTING",
					"route" => "SP-PL5 (WELD)",
					"qty" => "2",
					"job_num" => "AA-0257",
				),
			)
		);
		$no = 1;
		foreach ($data_contoh["53301-BZ380-00"] as $key => $value) {
			?>
			<tr align="center">
				<td><?= $no++; ?></td>
				<td>53301-BZ380-00</td>
				<td><?= $key; ?></td>
				<td><?= $value["name"]; ?></td>
				<td><?= $value["job_num"]; ?></td>
				<td><?= $value["route"]; ?></td>
				<td><?= $value["qty"]; ?></td>
				<td>Hanya Contoh</td>
			</tr>
			<?php
		}
		?>
    </table>
</body>
</html>
