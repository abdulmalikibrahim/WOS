<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>PRINT CARD</title>
	<style>
		.table-border {
			border-collapse: collapse; /* Menggabungkan border dari cell berdekatan */
			width: 100%; /* Opsional, membuat tabel lebar penuh */
		}

		.table-border th, .table-border td {
			border: 1px solid black; /* Mengatur border tabel, header, dan cell */
			padding:3px;
		}
		@media print {
			.print-table {
				page-break-before: always;
			}
		}
	</style>
</head>
<body onload="window.print()" style="margin:0;">
	<?php
	$model = $this->input->get("model");
	$data_wos = $this->model->gds_heijunka("master_print","*","Model = '".$model."' ORDER BY No ASC","result");
	$data_wos1 = $this->model->gds_heijunka("master_print","*","Model = '".$model."' ORDER BY No ASC","result");
	if(!empty($data_wos)){
		$count_wos = count($data_wos);
		foreach ($data_wos as $data_wos) {
			$noIdParsing = str_split($data_wos->ADM_Production_Id);
			if($count_wos >= 1000){
				$no_id = $noIdParsing[6].$noIdParsing[7].$noIdParsing[8].$noIdParsing[9];
			}else{
				$no_id = $noIdParsing[7].$noIdParsing[8].$noIdParsing[9];
			}

			$DescParsing = explode(" ",$data_wos->WOS_Material_Description);
			?>
			<table class="table-border" style="font-family: calibri; width: 326.4px; height:330px;">
				<tr>
					<td style="padding:5px 5px 0px 5px; width: 33px;"><img src="<?= base_url("assets/logo_daihatsu.png") ?>" width="30px"></td>
					<td colspan="3" align="right" style="width: 100px; padding-right: 10px; font-size: 25px;"><b><?= $model; ?></b></td>
				</tr>
				<tr>
					<td colspan="4">
						<table style="width:100%; height:36px;">
							<td style="vertical-align: top; border:0; width:50%;">PLAN DELIVERY DATE :</td>
							<td style="width:159px; text-align:right; line-height: 50%; font-weight: bold; font-size: 25px;letter-spacing: -1px; border:0;"><?= date("d F y",strtotime($data_wos->Plan_Delivery_Date)); ?></td>
						</table>
					</td>
				</tr>
				<tr>
					<td colspan="4">
						<table style="padding-top: 1px; padding-bottom: 1px; width: 100%; height:37px;">
							<td style="vertical-align: top; line-height: 50%; border:0;">MODEL CODE : </td>
							<td style="width:159px; line-height: 100%; border:0; text-align:right;">
								<label style="margin: 0; font-weight:bold; font-size:18pt;"><?= $data_wos->Katashiki; ?></label>
							</td>
						</table>
					</td>
				</tr>
				<tr>
					<td colspan="2" rowspan="3" style="padding-left:10px; padding-right:10px;">
						<div id="<?= $data_wos->SAPNIK; ?>"></div>
					</td>
					<td colspan="2" style="width: 100px; padding-left:5px;">
						PRODUCTION ID:
						<center><h3 style="margin: 0; font-size: 22px;"><?= $data_wos->ADM_Production_Id; ?></h3></center>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<div style="position: relative; height: 35.2px;">
							<div style="vertical-align: top; padding-left:3px;">NO. ID: </div>
							<div style="width:90px; position: absolute; font-weight:bold; font-size:30pt; top: -8px; right: 0;"><?= $no_id; ?></div>
						</div>
					</td>
				</tr>
				<tr>
					<td style="width: 86px; text-align: center;"><label style="margin:0; font-weight:bold; font-size:40pt;"><?= $data_wos->Katashiki_Sfx; ?></label></td>
					<td style="width: 86px; text-align: center;">
						<div style="margin: 0; font-weight:bold; font-size:28pt; line-height: 110%;"><?= $data_wos->Color_Code; ?></div>
						<div style="margin: 0; font-weight:bold; font-size:15pt; line-height: 80%;"><?= $DescParsing[6]; ?></div>
					</td>
				</tr>
				<tr>
					<td colspan="4">
						<div style="vertical-align: top; padding-left: 5px;">NO. ID: </div>
						<div style="width:100%; text-align: center; margin: 0; font-weight:bold; font-size:20pt;"><?= $data_wos->SAPNIK; ?></div>
					</td>
				</tr>
			</table>
			<?php
		}
	}
	?>
</body>
<script src="<?= base_url("assets/js/qrcodejs-master/qrcode.min.js"); ?>"></script>
<script type="text/javascript">
	<?php
	if(!empty($data_wos1)){
		foreach ($data_wos1 as $data_wos1) {
			$text = $data_wos1->SAPNIK.$data_wos1->Chassis_Number.$data_wos1->ADM_Production_Id.$data_wos1->Katashiki."        ".$data_wos1->Katashiki_Sfx.$data_wos1->Color_Code;
			if(strlen($text) < 71){
				$text = $data_wos1->SAPNIK.$data_wos1->Chassis_Number.$data_wos1->ADM_Production_Id.$data_wos1->Katashiki."         ".$data_wos1->Katashiki_Sfx.$data_wos1->Color_Code;
			}
			?>
			var <?= $data_wos1->SAPNIK; ?> = new QRCode(document.getElementById("<?= $data_wos1->SAPNIK; ?>"), {
				text: "<?= $text; ?>",
				width: 128,
				height: 128,
				colorDark : "#000000",
				colorLight : "#ffffff",
				correctLevel : QRCode.CorrectLevel.L
			});
			<?php
		}
	}
	?>
</script>
</html>
