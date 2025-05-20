<style>
    .tableFixHead { overflow: auto; height: 100px; }
    .tableFixHead thead th { position: sticky; top: -1px; z-index: 1; }

    /* Just common table stuff. Really. */
    table  { border-collapse: collapse; width: 100%; }
    th, td { padding: 8px 16px; }
    th     { background:#eee; }

    .GREY{
        background:#A9A9A9;
    }
    .BLACK{
        background:#000000;
        color:#FFFFFF;
    }
    .WHITE{
        background:#FFFFFF;
    }
</style>
<div class="row justify-content-center">
    <div class="col-12" align="center">
        <h1 class="m-0 text-white" style="font-size:3rem;"><?=$title?></h1>
    </div>
</div>
<div class="row justify-content-center">
    <div class="col-lg-12 align-items-center mt-3">
        <div class="card card-body">
            <div class="row">
                <div class="col-lg-12 pl-0" align="right">
                    <a href="<?=base_url("")?>" class="btn btn-sm btn-danger" title="Main Menu" style="height:35px;">Main Menu</a>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="table-responsive" style="height: calc(100vh - 170px);">
                        <table class="table table-bordered table-hover table-sm w-100 tableFixHead" style="font-size:8pt;">
                            <thead class="thead-light">
                                <tr align="center">
                                    <th class="align-middle">No.</th>
                                    <th class="align-middle">Suffix</th>
                                    <th class="align-middle">PID</th>
                                    <th class="align-middle">VARIANT</th>
                                    <th class="align-middle">VIN</th>
                                    <th class="align-middle">TYPE</th>
                                    <th class="align-middle">PDD</th>
                                    <th class="align-middle">Zero Defect</th>
                                    <th class="align-middle">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $data_std = $this->model->gds("hc_standard","*","suffix !=","result");
                                if(!empty($data_std)){
                                    $no = 1;
                                    foreach ($data_std as $data_std) {
                                        ?>
                                        <tr align="center">
                                            <td class="<?=$data_std->pid;?> align-middle"><?=$no?></td>
                                            <td class="<?=$data_std->pid;?> align-middle"><?=$data_std->suffix;?></td>
                                            <td class="<?=$data_std->pid;?> align-middle">3Z11102001</td>
                                            <td class="<?=$data_std->variant;?> align-middle">B400RS-GMDEJ</td>
                                            <td class="<?=$data_std->vin;?> align-middle">MHKAA1BA0MJ019988</td>
                                            <td class="<?=$data_std->type;?> align-middle">TYPE</td>
                                            <td class="<?=$data_std->pdd;?> align-middle">01 NOV 21</td>
                                            <td class="<?=$data_std->zero_defect;?> align-middle"><?=$data_std->val_zero_defect;?></td>
                                            <td>
                                                <a href="javascript:void(0)" onclick="delete_data(<?=$data_std->id?>)" class="btn btn-sm btn-danger"><i class="fas fa-trash-alt m-0"></i></a>
                                                <a href="<?=base_url("hard_copy_std?id=".$data_std->id)?>" class="btn btn-sm btn-info"><i class="fas fa-pencil-alt m-0"></i></a>
                                            </td>
                                        </tr>
                                        <?php
                                        $no++;
                                    }
                                }else{
                                    ?>
                                    <tr>
                                        <td colspan="25" align="center"><i>Data kosong</i></td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php
                $id_edit = $this->input->get("id");
                if(!empty($id_edit)){
                    $title = "EDIT HARD COPY";
                    $edit_format = $this->model->gds("hc_standard","*","id = '$id_edit'","row");
                    if(!empty($edit_format)){
                        $suffix = $edit_format->suffix;
                        $w_pid = $edit_format->pid;
                        $w_variant = $edit_format->variant;
                        $w_vin = $edit_format->vin;
                        $w_type = $edit_format->type;
                        $w_pdd = $edit_format->pdd;
                        $w_zero_defect = $edit_format->zero_defect;
                        $v_zero_defect = $edit_format->val_zero_defect;
                    }else{
                        $suffix = "Data tidak ditemukan";
                        $v_zero_defect = "";
                    }
                }else{
                    $title = "INPUT HARD COPY";
                    $suffix = "";
                    $v_zero_defect = "";
                }
                $warna = ["WHITE" => "WHITE","GREY" => "GRAY","BLACK" => "BLACK"];
                ?>
                <div class="col-lg-6">
                    <h4><?=$title?></h4>
                    <form action="<?=base_url("ihcs?id=".$id_edit)?>" method="post">
                        <div class="row">
                            <div class="col-6">
                                <p class="mb-1">Masukkan Suffix</p>
                                <input type="text" name="suffix" class="form-control" placeholder="Masukkan Suffix" maxlength="5" value="<?=$suffix?>" required>
                                <p class="mb-1 mt-1">PID</p>
                                <select name="pid" class="form-control">
                                    <?php
                                    foreach ($warna as $key => $value) {
                                        if(!empty($id_edit)){
                                            if($w_pid == $key){
                                                $s = "selected";
                                            }else{
                                                $s = "";
                                            }
                                        }else{
                                            $s = "";
                                        }
                                        ?>
                                        <option value="<?=$key?>" <?=$s?>><?=$value?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                                <p class="mb-1 mt-1">Variant</p>
                                <select name="variant" class="form-control">
                                    <?php
                                    foreach ($warna as $key => $value) {
                                        if(!empty($id_edit)){
                                            if($w_variant == $key){
                                                $s = "selected";
                                            }else{
                                                $s = "";
                                            }
                                        }else{
                                            $s = "";
                                        }
                                        ?>
                                        <option value="<?=$key?>" <?=$s?>><?=$value?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                                <p class="mb-1 mt-1">VIN</p>
                                <select name="vin" class="form-control">
                                    <?php
                                    foreach ($warna as $key => $value) {
                                        if(!empty($id_edit)){
                                            if($w_vin == $key){
                                                $s = "selected";
                                            }else{
                                                $s = "";
                                            }
                                        }else{
                                            $s = "";
                                        }
                                        ?>
                                        <option value="<?=$key?>" <?=$s?>><?=$value?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-6">
                                <p class="mb-1 mt-1">Type</p>
                                <select name="type" class="form-control">
                                    <?php
                                    foreach ($warna as $key => $value) {
                                        if(!empty($id_edit)){
                                            if($w_type == $key){
                                                $s = "selected";
                                            }else{
                                                $s = "";
                                            }
                                        }else{
                                            $s = "";
                                        }
                                        ?>
                                        <option value="<?=$key?>" <?=$s?>><?=$value?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                                <p class="mb-1 mt-1">PDD</p>
                                <select name="pdd" class="form-control">
                                    <?php
                                    foreach ($warna as $key => $value) {
                                        if(!empty($id_edit)){
                                            if($w_pdd == $key){
                                                $s = "selected";
                                            }else{
                                                $s = "";
                                            }
                                        }else{
                                            $s = "";
                                        }
                                        ?>
                                        <option value="<?=$key?>" <?=$s?>><?=$value?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                                <p class="mb-1 mt-1">Zero Defect</p>
                                <select name="zero_defect" class="form-control">
                                    <?php
                                    foreach ($warna as $key => $value) {
                                        if(!empty($id_edit)){
                                            if($w_zero_defect == $key){
                                                $s = "selected";
                                            }else{
                                                $s = "";
                                            }
                                        }else{
                                            $s = "";
                                        }
                                        ?>
                                        <option value="<?=$key?>" <?=$s?>><?=$value?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                                <p class="mb-1 mt-1">Value Zero Defect</p>
                                <input type="text" name="val_zero_defect" class="form-control" placeholder="Masukkan Value Zero Defect" maxlength="5" value="<?=$v_zero_defect?>">
                            </div>
                            <div class="col-12 mt-3" align="right">
                                <?php
                                if(!empty($id_edit)){
                                    echo '<a href="'.base_url("hard_copy_std").'" class="btn btn-danger">Batal</a>';
                                }
                                ?>
                                <button class="btn btn-info">Simpan</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>