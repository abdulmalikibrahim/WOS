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
</div>
<div class="row justify-content-center">
    <div class="col-lg-12 align-items-center mt-3">
        <div class="card card-body">
            <div class="row">
                <div class="col-12">
                    <span class="text-danger" style="font-size:15px; font-weight:bold;">Silahkan Upload Tabungan VLT...</i></span>
                </div>
                <div class="col-lg-4">
                    <form action="<?=base_url("import_vlt")?>" method="post" id="form_export" enctype="multipart/form-data" >
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="upload-excel" name="upload-file[]" accept=".xls, .xlsx" multiple>
                                <label class="custom-file-label" for="customFile" id="customFile">Pilih File Excel</label>
                            </div>
                            <a href="<?=base_url("clear_vlt")?>" class="btn btn-sm btn-secondary ml-2" title="Bersihkan Plan WOS" style="height:35px;">Clear</a>
                        </div>
                    </form>
                </div>
                <div class="col-lg-8 pl-0" align="right">
                    <a href="<?=base_url("download_txt?type=DOM")?>" target="_blank" class="btn btn-sm btn-info" title="Download Data VLT to TXT" style="height:35px; padding-top:6px !important;"><i class="fas fa-clipboard pr-1"></i> Download TXT DOM</a>
                    <a href="<?=base_url("download_txt?type=EXP")?>" target="_blank" class="btn btn-sm btn-info" title="Download Data VLT to TXT" style="height:35px; padding-top:6px !important;"><i class="fas fa-clipboard pr-1"></i> Download TXT EXP</a>
                    <a href="<?=base_url("show_nik")?>" class="btn btn-sm btn-success" title="Show NIK DOM" style="height:35px; padding-top:6px !important;"><i class="fas fa-eye pr-1"></i> Show NIK</a>
                    <a href="<?=base_url("")?>" class="btn btn-sm btn-danger" title="Main Menu" style="height:35px; padding-top:6px !important;">Main Menu</a>
                </div>
            </div>
            <div class="table-responsive mt-3" style="height: calc(100vh - 170px);">
                <table class="table table-bordered table-hover table-sm w-100 tableFixHead" style="font-size:8pt;">
                    <thead class="thead-light">
                        <tr align="center">
                            <th class="align-middle">Type</th>
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
                            <th class="align-middle">RRN Number</th>
                            <th class="align-middle">Destination Sequnce Number</th>
                            <th class="align-middle"><div style="width:260px;">Material description</div></th>
                            <th class="align-middle">Destination Code</th>
                            <th class="align-middle">Destination Description</th>
                            <th class="align-middle">Equipment number</th>
                            <th class="align-middle"><div style="width:60px;">Revise Del. Date</div></th>
                        </tr>
                    </thead>
                    <tbody id="data_tabungan">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
