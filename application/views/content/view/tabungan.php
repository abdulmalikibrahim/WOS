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
<div class="row pl-3 pr-3 justify-content-center">
    <div class="col-lg-6 pr-1 align-items-center mt-3">
        <div class="card card-body">
            <div class="row">
                <div class="col-12">
                    <span class="text-danger" style="font-size:15px; font-weight:bold;">Silahkan Upload Tabungan VLT KAP 1</i></span>
                </div>
                <div class="col-lg-8">
                    <form action="<?=base_url("import_tabungan")?>" method="post" id="form_export" enctype="multipart/form-data">
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="upload-excel" name="upload-file" accept=".xls">
                                <label class="custom-file-label" for="customFile" id="customFile">Pilih File Excel</label>
                            </div>
                            <a href="<?=base_url("clear_tabungan")?>" class="btn btn-sm btn-secondary ml-2" title="Bersihkan Plan WOS" style="height:35px;">Clear</a>
                        </div>
                    </form>
                </div>
                <div class="col-lg-12 mt-2">
                    <a href="<?=base_url("download_docking")?>" target="_blank" class="btn btn-sm btn-success" title="Download Hasil Docking" style="height:35px;"><i class="fas fa-file-excel pr-1"></i> Download Docking</a>
                    <button class="btn btn-sm btn-secondary" style="height:35px; font-size:10pt;" id="docking" title="Docking" data-tipe="kap1" onclick="docking(this)">Docking</button>
                    <a href="<?=base_url("adjust_twotone")?>" class="btn btn-sm btn-secondary" title="Masuk Menu Twotone" style="height:35px;">Adjust TwoTone</a>
                </div>
            </div>
            <div class="table-responsive mt-3" style="height: calc(100vh - 170px);">
                <table class="table table-bordered table-hover table-sm w-100 tableFixHead" style="font-size:6pt;">
                    <thead class="thead-light">
                        <tr align="center">
                            <th class="align-middle">No.</th>
                            <th class="align-middle">WOS Material</th>
                            <th class="align-middle"><div style="width:260px;">WOS Material Description</div></th>
                            <th class="align-middle">SAPNIK</th>
                            <th class="align-middle">SAP Material</th>
                            <th class="align-middle"><div style="width:50px;">Engine Model</div></th>
                            <th class="align-middle">Engine Prefix</th>
                            <th class="align-middle">Engine Number</th>
                            <th class="align-middle">Plant</th>
                            <th class="align-middle">ChassisNumber</th>
                            <th class="align-middle">Lot Code</th>
                            <th class="align-middle">Lot Number</th>
                            <th class="align-middle"><div style="width:80px;">Katashiki</div></th>
                            <th class="align-middle">Katashiki Sfx</th>
                            <th class="align-middle">ADM Production ID</th>
                            <th class="align-middle">TAM Production ID</th>
                            <th class="align-middle"><div style="width:60px;">Plan Delivery Date</div></th>
                            <th class="align-middle"><div style="width:60px;">Plan Jig In Date</div></th>
                            <th class="align-middle"><div style="width:60px;">WOS Release Date</div></th>
                            <th class="align-middle">SAPWOS-DES</th>
                            <th class="align-middle">LOCATION</th>
                            <th class="align-middle">COLORCODE</th>
                            <th class="align-middle">ED</th>
                            <th class="align-middle">ORDER</th>
                            <th class="align-middle">DESTINATION</th>
                        </tr>
                    </thead>
                    <tbody id="data_tabungan">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6 pl-1 align-items-center mt-3">
        <div class="card card-body">
            <div class="row">
                <div class="col-12">
                    <span class="text-danger" style="font-size:15px; font-weight:bold;">Silahkan Upload Tabungan VLT KAP 2</i></span>
                </div>
                <div class="col-lg-8">
                    <form action="<?=base_url("import_tabungan?t=kap2")?>" method="post" id="form_export_kap2" enctype="multipart/form-data">
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="upload-excel-kap2" name="upload-file" accept=".xls">
                                <label class="custom-file-label" for="customFile-kap2" id="customFile-kap2">Pilih File Excel</label>
                            </div>
                            <a href="<?=base_url("clear_tabungan?t=kap2")?>" class="btn btn-sm btn-secondary ml-2" title="Bersihkan Plan WOS" style="height:35px;">Clear</a>
                        </div>
                    </form>
                </div>
                <div class="col-lg-12 mt-2">
                    <a href="<?=base_url("download_docking?t=kap2")?>" target="_blank" class="btn btn-sm btn-success" title="Download Hasil Docking" style="height:35px;"><i class="fas fa-file-excel pr-1"></i> Download Docking</a>
                    <button class="btn btn-sm btn-secondary" style="height:35px; font-size:10pt;" id="docking" title="Docking" data-tipe="kap2" onclick="docking(this)">Docking</button>
                    <!-- <a href="<?=base_url("adjust_twotone?t=kap2")?>" class="btn btn-sm btn-secondary" title="Masuk Menu Twotone" style="height:35px;">Adjust TwoTone</a> -->
                </div>
            </div>
            <div class="table-responsive mt-3" style="height: calc(100vh - 170px);">
                <table class="table table-bordered table-hover table-sm w-100 tableFixHead" style="font-size:6pt;">
                    <thead class="thead-light">
                        <tr align="center">
                            <th class="align-middle">No.</th>
                            <th class="align-middle">WOS Material</th>
                            <th class="align-middle"><div style="width:260px;">WOS Material Description</div></th>
                            <th class="align-middle">SAPNIK</th>
                            <th class="align-middle">SAP Material</th>
                            <th class="align-middle"><div style="width:50px;">Engine Model</div></th>
                            <th class="align-middle">Engine Prefix</th>
                            <th class="align-middle">Engine Number</th>
                            <th class="align-middle">Plant</th>
                            <th class="align-middle">ChassisNumber</th>
                            <th class="align-middle">Lot Code</th>
                            <th class="align-middle">Lot Number</th>
                            <th class="align-middle"><div style="width:80px;">Katashiki</div></th>
                            <th class="align-middle">Katashiki Sfx</th>
                            <th class="align-middle">ADM Production ID</th>
                            <th class="align-middle">TAM Production ID</th>
                            <th class="align-middle"><div style="width:60px;">Plan Delivery Date</div></th>
                            <th class="align-middle"><div style="width:60px;">Plan Jig In Date</div></th>
                            <th class="align-middle"><div style="width:60px;">WOS Release Date</div></th>
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
    </div>
</div>
