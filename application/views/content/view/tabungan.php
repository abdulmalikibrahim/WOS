<style>
    .tableFixHead { overflow: auto; height: 100px; }
    .tableFixHead thead th { position: sticky; top: -1px; z-index: 1; }

    /* Just common table stuff. Really. */
    table  { border-collapse: collapse; width: 100%; }
    th, td { padding: 8px 16px; }
    th     { background:#eee; }
</style>
<style>
    /* Container Alert biar transparan & modern (Glassmorphism) */
    .glass-popup {
        background: rgba(255, 255, 255, 0.95) !important;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-radius: 20px !important;
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        border: 1px solid rgba(255, 255, 255, 0.18);
        padding-bottom: 2rem !important;
    }

    /* Judul dengan Gradient Text */
    .tech-title {
        font-family: 'Segoe UI', sans-serif;
        font-weight: 800;
        font-size: 1.8rem;
        background: -webkit-linear-gradient(45deg, #0984e3, #6c5ce7);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        letter-spacing: 1px;
        margin-bottom: 1rem;
    }

    /* Spinner Keren (Dual Ring Rotate) */
    .tech-loader {
        display: inline-block;
        width: 70px;
        height: 70px;
        margin-bottom: 15px;
    }
    .tech-loader:after {
        content: " ";
        display: block;
        width: 50px;
        height: 50px;
        margin: 8px;
        border-radius: 50%;
        border: 5px solid #6c5ce7;
        border-color: #6c5ce7 transparent #6c5ce7 transparent;
        animation: tech-spin 1.2s linear infinite;
        box-shadow: 0 0 15px rgba(108, 92, 231, 0.4);
    }
    @keyframes tech-spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Box Informasi Detail (Ala Console) */
    .info-tech {
        font-family: 'Consolas', 'Monaco', monospace;
        color: #2d3436;
        background: #f1f2f6;
        padding: 12px;
        border-radius: 8px;
        border-left: 5px solid #6c5ce7;
        text-align: left;
        font-size: 0.85rem;
        margin: 10px 0;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    .info-tech strong { color: #6c5ce7; }

    /* Progress Bar Modern dengan Animasi */
    .progress-container {
        background-color: #dfe6e9;
        border-radius: 15px;
        height: 25px;
        width: 100%;
        overflow: hidden;
        margin-top: 15px;
        box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
        position: relative;
    }
    
    .progress-bar-tech {
        background: linear-gradient(90deg, #0984e3, #6c5ce7, #00cec9);
        background-size: 200% 200%;
        height: 100%;
        line-height: 25px;
        color: white;
        font-weight: bold;
        font-size: 0.8rem;
        text-align: center;
        width: 0%;
        transition: width 0.4s ease;
        animation: gradient-move 2s ease infinite;
        box-shadow: 0 0 10px rgba(108, 92, 231, 0.7);
    }

    @keyframes gradient-move {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }
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
                    <button class="btn btn-sm btn-secondary" style="height:35px; font-size:10pt;" id="docking-kap1" title="Docking" data-tipe="kap1" onclick="docking(this)">Docking</button>
                    <a href="<?=base_url("adjust_twotone")?>" class="btn btn-sm btn-secondary" title="Masuk Menu Twotone" style="height:35px;">Adjust TwoTone</a>
                    <a href="<?=base_url("process_tabungan_dummy_kap1")?>" class="btn btn-sm btn-secondary" id="create_tabungan_dummy_kap1" title="Create Tabungan Dummy" style="height:35px;">Create Tabungan Dummy</a>
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
                    <button class="btn btn-sm btn-secondary" id="create_tabungan_dummy_kap2" onclick="create_tabungan_dummy()" title="Create Tabungan Dummy" style="height:35px;">Create Tabungan Dummy</button>
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

<div class="modal fade" id="modalUploadExcel" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Excel Tabungan D55L</h5>
            </div>
            <form id="formUploadExcel" method="post" action="<?= base_url("import_tabungan_d55l") ?>" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="inputGroupFileAddon01">Upload</span>
                        </div>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="inputGroupFile01" name="upload-file" aria-describedby="inputGroupFileAddon01">
                            <label class="custom-file-label" for="inputGroupFile01">Pilih File Excel (.xlsx / .xls)</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Mulai Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>