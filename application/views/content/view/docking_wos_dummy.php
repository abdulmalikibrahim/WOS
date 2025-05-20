<style>
    .tableFixHead { overflow: auto; height: 100px; }
    .tableFixHead thead th { position: sticky; top: -1px; z-index: 1; }

    /* Just common table stuff. Really. */
    table  { border-collapse: collapse; width: 100%; }
    th, td { padding: 8px 16px; }
    th     { background:#eee; }
</style>
<div class="row justify-content-center">
    <div class="col-12" align="center">
        <h1 class="m-0 text-white" style="font-size:3rem;"><?=$title?></h1>
    </div>
	<a href="<?=base_url("")?>" class="btn btn-sm btn-danger" title="Main Menu" style="height:35px; position:absolute; top:20px; right:20px;">Main Menu</a>
</div>

<div class="card card-body mt-4">
    <div class="row">
        <div class="col-4">
            <label class="text-danger mb-2" style="font-size:15px; font-weight:bold;">Upload Tabungan VLT</i></label>
            <form action="<?=base_url("import_tabungan?t=kap2&p=docking_wos_dummy")?>" method="post" id="form_upload_kap2" enctype="multipart/form-data">
                <div class="input-group">
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="upload-excel-kap2" name="upload-file" accept=".xls">
                        <label class="custom-file-label" for="customFile-kap2" id="customFile-kap2">Pilih File Excel</label>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-4">
            <label class="text-danger mb-2" style="font-size:15px; font-weight:bold;">Upload PIS Dummy</i></label>
            <form action="<?=base_url("import_pis_kap2?t=kap2&p=docking_wos_dummy")?>" method="post" id="form_upload_pis_kap2" enctype="multipart/form-data">
                <div class="input-group">
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="upload-excel-pis-kap2" name="upload-file" accept=".xls">
                        <label class="custom-file-label" for="customFile-pis-kap2" id="customFile-pis-kap2">Pilih File Excel</label>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-4 d-flex align-items-end">
            <a href="<?= base_url("docking_dummy"); ?>" class="btn btn-sm btn-secondary" style="height:35px; font-size:10pt;" id="docking" title="Docking" data-tipe="kap2" onclick="docking(this)">Docking</a>
        </div>
        <div class="col-4">
            <?php
            if(!empty($this->session->userdata("tabungan_actual"))){
                echo '<div class="card card-body bg-success p-1 text-center mt-2 text-light font-weight-bold">UPLOAD SUKSES</div>';
            }
            ?>
        </div>
        <div class="col-4">
            <?php
            if(!empty($this->session->userdata("pis_dummy"))){
                echo '<div class="card card-body bg-success p-1 text-center mt-2 text-light font-weight-bold">UPLOAD SUKSES</div>';
            }
            ?>
        </div>
        <div class="col-4">
            <?php
            if(!empty($this->session->userdata("pis_dummy")) || !empty($this->session->userdata("tabungan_actual"))){
                echo '<div class="card card-body bg-light border-0 p-1 text-center mt-2 text-light font-weight-bold">UPLOAD SUKSES</div>';
            }
            ?>
        </div>
    </div>
    <div class="row">
        <div class="col-6">
            <h6 class="mb-1 mt-2">Data Tabungan VLT</h6>
            <div class="table-responsive mt-2" style="height: calc(100vh - 170px);">
                <table class="table table-bordered table-hover table-sm w-100 tableFixHead" style="font-size:4.5pt;">
                    <thead class="thead-light">
                        <tr align="center">
                            <th class="align-middle">No.</th>
                            <th class="align-middle">WOS Material</th>
                            <th class="align-middle"><div style="width:150px;">WOS Material Description</div></th>
                            <th class="align-middle">SAPNIK</th>
                            <th class="align-middle">SAP Material</th>
                            <th class="align-middle"><div style="width:30px;">Engine Model</div></th>
                            <th class="align-middle">Engine Prefix</th>
                            <th class="align-middle">Engine Number</th>
                            <th class="align-middle">Plant</th>
                            <th class="align-middle">ChassisNumber</th>
                            <th class="align-middle">Lot Code</th>
                            <th class="align-middle">Lot Number</th>
                            <th class="align-middle"><div style="width:50px;">Katashiki</div></th>
                            <th class="align-middle">Katashiki Sfx</th>
                            <th class="align-middle">ADM Production ID</th>
                            <th class="align-middle">TAM Production ID</th>
                            <th class="align-middle"><div style="width:40px;">Plan Delivery Date</div></th>
                            <th class="align-middle"><div style="width:40px;">Plan Jig In Date</div></th>
                            <th class="align-middle"><div style="width:40px;">WOS Release Date</div></th>
                            <th class="align-middle">SAPWOS-DES</th>
                            <th class="align-middle">LOCATION</th>
                            <th class="align-middle">COLORCODE</th>
                            <th class="align-middle">ED</th>
                            <th class="align-middle">ORDER</th>
                            <th class="align-middle">DESTINATION</th>
                        </tr>
                    </thead>
                    <tbody id="data_tabungan_kap2">
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-6">
            <h6 class="mb-1 mt-2">Data PIS Dummy</h6>
            <div class="table-responsive mt-2" style="height: calc(100vh - 170px);">
                <table class="table table-bordered table-hover table-sm w-100 tableFixHead" style="font-size:4.5pt;">
                    <thead class="thead-light">
                        <tr align="center">
                            <th class="align-middle">No.</th>
                            <th class="align-middle">WOS Material</th>
                            <th class="align-middle"><div style="width:150px;">WOS Material Description</div></th>
                            <th class="align-middle">SAPNIK</th>
                            <th class="align-middle">SAP Material</th>
                            <th class="align-middle"><div style="width:30px;">Engine Model</div></th>
                            <th class="align-middle">Engine Prefix</th>
                            <th class="align-middle">Engine Number</th>
                            <th class="align-middle">Plant</th>
                            <th class="align-middle">ChassisNumber</th>
                            <th class="align-middle">Lot Code</th>
                            <th class="align-middle">Lot Number</th>
                            <th class="align-middle"><div style="width:50px;">Katashiki</div></th>
                            <th class="align-middle">Katashiki Sfx</th>
                            <th class="align-middle">ADM Production ID</th>
                            <th class="align-middle">TAM Production ID</th>
                            <th class="align-middle"><div style="width:40px;">Plan Delivery Date</div></th>
                            <th class="align-middle"><div style="width:40px;">Plan Jig In Date</div></th>
                            <th class="align-middle"><div style="width:40px;">WOS Release Date</div></th>
                            <th class="align-middle">SAPWOS-DES</th>
                            <th class="align-middle">LOCATION</th>
                            <th class="align-middle">COLORCODE</th>
                            <th class="align-middle">ED</th>
                            <th class="align-middle">ORDER</th>
                            <th class="align-middle">DESTINATION</th>
                        </tr>
                    </thead>
                    <tbody id="data_pis_kap2">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
