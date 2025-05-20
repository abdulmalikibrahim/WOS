<?php
header ("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment;filename = Master PRO Number.xls");
?>
<!DOCTYPE html>
<html>
<head>
    <title></title>
</head>
<body>
    <table border="1" style="font-size: 10pt;">
        <tr style="font-weight: bold;" align="center">
            <td>Part Number</td>
            <td>Qty</td>
            <td>PRO Number</td>
        </tr>
		<tr align="center">
			<td>53301-BZ380-00</td>
			<td>15</td>
			<td>12345678</td>
		</tr>
    </table>
</body>
</html>
