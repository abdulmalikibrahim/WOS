<?php
	header ("Content-type: application/vnd-ms-excel");
	header("Content-Disposition: attachment;filename = Service Part.xls");
	$p = $this->input->get("p");
	$d = $this->input->get("d");
?>
<?php
	$sub = ["Weld","Press"];
	foreach ($sub as $key => $value) {
		?>
		<table border="1" style="width:100%; border:0;">
			<?php
			if($value == "Weld"){
				?>
				<tr align="center">
					<td style="text-align:center; font-family:stencil; background:black; color:white; font-size:18pt; border-right:solid #fff 1px; border-left:solid 1px; width:96px;" colspan="2"><div style="width:6rem;">PROD</div></td>
					<td style="text-align:center; font-family:stencil; background:black; color:white; font-size:18pt; border-right:solid 1px; border-left:solid #fff 1px; width:96px;"><div style="width:6rem;">PDD</div></td>
					<td style="text-align:center; font-family:stencil; font-size:36pt; border:0;" rowspan="2" colspan="5">WOS SERVICE PART</td>
					<td style="text-align:center; font-family:stencil; font-size:18pt; border-left:0; border-top:0; border-right:0;" rowspan="2" colspan="2"></td>
				</tr>
				<tr align="center">
					<td style="text-align:center; font-family:stencil; font-size:35pt; border:solid 1px;" colspan="2"><?= date("d",strtotime($p)); ?></td>
					<td style="text-align:center; font-family:stencil; font-size:35pt; border:solid 1px;"><?= date("d",strtotime($d)); ?></td>
				</tr>
				<tr>
					<td colspan="2" style="text-align:center; font-family:stencil; background:black; color:white; font-size:20pt;  border-right:solid #fff 1px; border-left:solid 1px;"><?= date("M",strtotime($p)); ?></td>
					<td style="text-align:center; font-family:stencil; background:black; color:white; font-size:20pt; border-right:solid 1px; border-left:solid #fff 1px;"><?= date("M",strtotime($d)); ?></td>
					<td style="text-align:center; font-family:stencil; font-size:18pt; border:0;" colspan="5">KARAWANG ASSY PLANT</td>
					<td style="text-align:center; font-family:stencil; font-size:18pt; background:yellow; border-top:0; border-right:solid 1px;" colspan="2">WELD PART</td>
				</tr>
				<tr>
					<td colspan="10" style="height:10px; border:0;"></td>
				</tr>
				<tr align="center">
					<th class="align-middle">No.</th>
					<th class="align-middle">Model</th>
					<th class="align-middle">Job Number</th>
					<th class="align-middle">Part Number</th>
					<th class="align-middle">Part Name</th>
					<th class="align-middle">Routing Part</th>
					<th class="align-middle">Pcs</th>
					<th class="align-middle">PRO</th>	
					<th class="align-middle">Plan PRO</th>
					<th class="align-middle" style="width:250px; border-right:solid 1px;">Remark</th>
				</tr>
				<?php
			}else{
				?>
				<tr>
					<td colspan="10" style="height:10px; border:0;"></td>
				</tr>
				<tr>
					<td style="text-align:center; font-family:stencil; font-size:18pt; border:0;" colspan="8"></td>
					<td style="text-align:center; font-family:stencil; font-size:18pt; background:yellow;" colspan="2">PRESS PART</td>
				</tr>
				<tr>
					<td colspan="10" style="height:10px; border:0;"></td>
				</tr>
				<tr align="center">
					<th class="align-middle">No.</th>
					<th class="align-middle">Model</th>
					<th class="align-middle">Job Number</th>
					<th class="align-middle">Part Number</th>
					<th class="align-middle">Part Name</th>
					<th class="align-middle">Routing Part</th>
					<th class="align-middle">Pcs</th>
					<th class="align-middle">PRO</th>	
					<th class="align-middle">Plan PRO</th>
					<th class="align-middle" style="width:250px; border-right:solid 1px;">Remark</th>
				</tr>
				<?php
			}
			?>
			<tbody>
				<?php
				$part_number = $this->model->gds_heijunka("create_wos","*,COUNT(No) as row_qty, SUM(Qty) as total","sub = '$value' GROUP BY Part_Number","result");
				$part_number_total = $this->model->gds_heijunka("create_wos","*,SUM(Qty) as total","sub = '$value' GROUP BY sub","row");
				if(!empty($part_number)){
					$no = 1;
					foreach ($part_number as $pn) {
						$detail_part = $this->model->gds_heijunka("breakdown_sp","Breakdown,Part_Number,Part_Name,Route,Original_Part,Qty","Part_Number = '".$pn->Part_Number."' GROUP BY Breakdown ORDER BY (CASE WHEN Part_Number = Breakdown THEN 0 ELSE 1 END), Part_Number, Breakdown","result");
						foreach ($detail_part as $detail_part) {
							if($detail_part->Part_Number == $detail_part->Breakdown){
								$row_bg = "background:black; color:white;";
								$row_bg_press = "background:white; color:black; border:solid 1px;";
								$row_no = '
								<td style="'.$row_bg.'">'.$no.'</td>
								<td style="'.$row_bg.'">'.$pn->type.'</td>';
								$row_no_press = '
								<td style="'.$row_bg_press.'">'.$no.'</td>
								<td style="'.$row_bg_press.'">'.$pn->type.'</td>';

								$get_pro_detail = $this->model->gds_heijunka("pro_number","*","part_number = '".$pn->Part_Number."'","row");
								if(!empty($get_pro_detail)){
									$pro = $get_pro_detail->pro_number;
									$qty = $get_pro_detail->qty;
								}else{
									$pro = "-";
									$qty = "-";
								}
							}else{
								$row_bg = 'background:white; border:solid 1px;';
								$row_bg_press = "background:white; border:solid 1px;";
								$row_no = '
								<td style="'.$row_bg.'"></td>
								<td style="'.$row_bg.'"></td>';
								$row_no_press = '
								<td style="'.$row_bg_press.'"></td>
								<td style="'.$row_bg_press.'"></td>';
								$pro = "";
								$qty = "";
							}

							if($value == "Weld"){
								?>
								<tr align="center">
									<?=$row_no?>
									<td style="<?=$row_bg?>"><?=$detail_part->Original_Part?></td>
									<td style="<?=$row_bg?>"><?=$detail_part->Breakdown?></td>
									<td style="<?=$row_bg?>" align="left"><div style="width:360px;"><?=$detail_part->Part_Name?></div></td>
									<td style="<?=$row_bg?>"><?=$detail_part->Route?></td>
									<td style="<?=$row_bg?>"><?=($detail_part->Qty*$pn->total)?></td>
									<td style="<?=$row_bg?>"><?= $pro; ?></td>	
									<td style="<?=$row_bg?>"><?= $qty; ?></td>
									<td style="<?=$row_bg?>"></td>
								</tr>
								<?php
							}else{
								if($row_bg_press != "background:white; border:solid 1px;"){	
									?>
									<tr align="center">
										<?=$row_no_press?>
										<td style="<?=$row_bg_press?>"><?=$detail_part->Original_Part?></td>
										<td style="<?=$row_bg_press?>"><?=$detail_part->Breakdown?></td>
										<td style="<?=$row_bg_press?>" align="left"><div style="width:360px;"><?=$detail_part->Part_Name?></div></td>
										<td style="<?=$row_bg_press?>"><?=$detail_part->Route?></td>
										<td style="<?=$row_bg_press?>"><?=($detail_part->Qty*$pn->total)?></td>
										<td style="<?=$row_bg_press?>"><?= $pro; ?></td>	
										<td style="<?=$row_bg_press?>"><?= $qty; ?></td>
										<td style="<?=$row_bg_press?>"></td>
									</tr>
									<?php
								}
							}
						}
						$no++;
					}
					?>
					<tr>
						<td colspan="6" style="background:black; text-align:right; color:white; border:solid 1px;">TOTAL&nbsp;&nbsp;</td>
						<td style="background:black; text-align:center; color:white; border:solid 1px;"><?= $part_number_total->total; ?></td>
						<td colspan="3" style="background:black; color:white; border:solid 1px;"></td>
					</tr>
					<?php
				}
				?>
			</tbody>
		</table>
		<?php
	}
?>
