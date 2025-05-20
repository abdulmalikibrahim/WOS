<style>
    .tableFixHead { overflow: auto; height: 100px; }
    .tableFixHead thead th { position: sticky; top: -1px; z-index: 1; }

    /* Just common table stuff. Really. */
    table  { border-collapse: collapse; width: 100%; }
    th, td { padding: 8px 16px; }
    th     { background:#eee; }
    
    #calendar {
        max-width: 900px;
        margin: 40px auto;
    }
</style>
<div class="row justify-content-center">
    <div class="col-12" align="center">
        <h1 class="m-0 text-white" style="font-size:3rem;"><?=$title?></h1>
    </div>
	<a href="<?=base_url("")?>" class="btn btn-sm btn-danger" title="Main Menu" style="height:35px; position:absolute; top:20px; right:20px;">Main Menu</a>
</div>

<div class="card card-body mt-4">
    <div class="row">
        <div class="col-8">
            <form action="<?= base_url("upload_wos_duplicate_checking") ?>" method="post" enctype="multipart/form-data" id="form-input">
                <div class="row">
                    <div class="col-2">
                        <label class="text-danger mb-2" style="font-size:15px; font-weight:bold;">Line</i></label>
                        <select class="form-control checkingUploadActive" id="plant" name="plant" required>
                            <option value="">PILIH LINE</option>
                            <option value="1">KAP 1</option>
                            <option value="2">KAP 2</option>
                        </select>
                    </div>
                    <div class="col-3">
                        <label class="text-danger mb-2" style="font-size:15px; font-weight:bold;">PDD</i></label>
                        <input type="date" name="pdd" id="pdd" class="form-control checkingUploadActive" required>
                        <input type="number" name="reupload" id="reupload" class="form-control checkingUploadActive" value="0" hidden>
                        <button class="btn btn-sm btn-primary" style="height:40px;" id="btn-upload-form" hidden>Upload</button>
                    </div>
                    <div class="col-7">
                        <label class="text-danger mb-2" style="font-size:15px; font-weight:bold;">Upload WOS</i></label>
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input checkingUploadActive" id="upload-file" name="upload-file" accept=".xls" required>
                                <label class="custom-file-label" for="customFile-upload" id="customFile-upload">Pilih File WOS</label>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-2 d-flex align-items-end">
            <button class="btn btn-sm btn-primary" style="height:40px;" id="btn-upload" onclick="checkUpload()" disabled>Upload</button>
        </div>
        <div class="col-lg-2 d-flex align-items-end justify-content-end">
            <button class="btn btn-sm btn-primary" style="height:40px;" data-toggle="modal" data-target="#modal-vin-search">VIN Search</button>
        </div>
    </div>
    <div class="row">
        <?php
        $plant = [1,2];
        foreach ($plant as $key => $value) {
            ?>
            <div class="col-4 pt-3" style="position:relative;">
                <center><h4 class="mb-1 mt-2">DATA VIN KAP <?= $value ?></h4></center>
                <a href="javascript:void(0)" onclick="openModalDownload(<?= $value ?>)" class="btn btn-sm btn-success" style="position:absolute; top:20px; right:15px;">Excel</a>
                <div id="calendar-<?= $value; ?>"></div>
                <div class="table-responsive mt-2" style="height: 250px;">
                    <table class="table table-bordered table-hover table-sm w-100 tableFixHead" style="font-size:10pt;">
                        <thead class="thead-light">
                            <tr align="center">
                                <th class="align-middle">No.</th>
                                <th class="align-middle">Model</th>
                                <th class="align-middle">SAPNIK</th>
                                <th class="align-middle">Suffix</th>
                                <th class="align-middle">PDD</th>
                            </tr>
                        </thead>
                        <tbody id="data_vin_kap<?= $value ?>">
                        </tbody>
                    </table>
                </div>
            </div>
            <?php
        }
        ?>
        <div class="col-4 pt-3" style="position:relative;">
            <center><h4 class="mb-1 mt-2" id="title-vin-duplicate"><?= !empty($this->session->userdata("search_vin")) ? "RESULT VIN SEARCH" : "DATA VIN DUPLICATE"; ?></h4></center>
            <div style="position:absolute; top:20px; right:15px;">
                <a href="<?= base_url("downloadDataDuplicate"); ?>" class="btn btn-sm btn-success" target="_blank">Excel</a>
                <button onclick="clearDataDouble()" class="btn btn-sm btn-danger">Clear</a>
            </div>
            <div class="table-responsive mt-2" style="height: 250px;">
                <table class="table table-bordered table-hover table-sm w-100 tableFixHead" style="font-size:10pt;">
                    <thead class="thead-light">
                        <tr align="center">
                            <th class="align-middle">No.</th>
                            <th class="align-middle">Line</th>
                            <th class="align-middle">SAPNIK</th>
                            <th class="align-middle">Suffix</th>
                            <th class="align-middle">Model</th>
                            <th class="align-middle" id="label-pdd-duplicate"><?= !empty($this->session->userdata("search_vin")) ? "PDD" : "PDD Duplicate"; ?></th>
                        </tr>
                    </thead>
                    <tbody id="data-vin-double">
                        <?php
                        $doubleData = $this->session->userdata('duplicate_data');
                        if(!empty($doubleData)){
                            foreach ($doubleData as $key => $value) {
                                ?>
                                <tr>
                                    <td class="text-center align-middle bg-warning"><?= $key+1 ?></td>
                                    <td class="text-center align-middle bg-warning"><?= $value['kap'] ?></td>
                                    <td class="text-center align-middle bg-warning"><?= $value['vin'] ?></td>
                                    <td class="text-center align-middle bg-warning"><?= $value['suffix'] ?></td>
                                    <td class="text-center align-middle bg-warning"><?= $value['model'] ?></td>
                                    <td class="text-center align-middle bg-warning"><?= $value['pdd_duplicate'] ?></td>
                                </tr>
                                <?php
                            }
                        }else{
                            ?>
                            <tr>
                                <td colspan="6" class="text-center align-middle bg-success text-light font-weight-bold">No Duplicate Data</td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="modal" id="modal-vin-search" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form action="<?= base_url("search_vin") ?>" method="post">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">VIN Search</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-danger mb-1" style="font-size:9pt;">*Note : Gunakan enter untuk mencari VIN lebih dari 1</p>
                    <textarea type="text" id="vin_search" name="vin_search" class="form-control" rows="10" placeholder="Masukkan VIN disini"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="btn-search-vin">Search Now</button>
                    <a href="javascript:void(0)" type="button" class="btn btn-secondary" data-dismiss="modal">Close</a>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="modal" id="modal-download-vin" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form action="<?= base_url("downloadVIN") ?>" method="post">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title-download">Donwload VIN</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-12 mb-2">
                            <p class="mb-1">Line</p>
                            <input type="text" class="form-control" name="line-download" id="line-download" readonly>
                        </div>
                        <div class="col-lg-6">
                            <p class="mb-1">Start PDD</p>
                            <input type="date" name="start" id="start" class="form-control" value="<?= date("Y-m-d"); ?>">
                        </div>
                        <div class="col-lg-6">
                            <p class="mb-1">End PDD</p>
                            <input type="date" name="end" id="end" class="form-control" value="<?= date("Y-m-d"); ?>">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="btn-download-vin">Download Now</button>
                    <a href="javascript:void(0)" type="button" class="btn btn-secondary" data-dismiss="modal">Close</a>
                </div>
            </div>
        </form>
    </div>
</div>
