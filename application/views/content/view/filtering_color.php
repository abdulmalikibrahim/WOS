<div class="row justify-content-center">
    <div class="col-12" align="center">
        <h1 class="m-0 text-white" style="font-size:3rem;"><?=$title?></h1>
    </div>
	<a href="<?=base_url("")?>" class="btn btn-sm btn-danger" title="Kembali" style="height:35px; position:absolute; top:15px; right:20px;">Main Menu</a>
</div>
<div class="row pl-3 pr-3 mt-5 justify-content-center">
    <div class="col-6">
        <div class="card">
            <div class="card-header text-center"><h3 class="mb-0">KAP 1</h3></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 mb-3">
                        <form action="<?= base_url("filtering_color/add?p=kap1") ?>" method="post">
                            <p class="mb-1">Input Color</p>
                            <div class="input-group">
                                <input type="text" name="color" class="form-control" placeholder="Masukkan color disini">
                                <button class="button btn btn-info"><i class="fas fa-save mr-1"></i>Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr class="text-center">
                            <th>No</th>
                            <th>Color</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if(!empty($dataKap1)){
                            $no = 1;
                            foreach ($dataKap1 as $dataKap1) {
                                ?>
                                <tr class="text-center">
                                    <td class="align-middle"><?= $no++ ?></td>
                                    <td class="align-middle"><?= $dataKap1->nilai; ?></td>
                                    <td class="align-middle">
                                        <a href="<?= base_url("filtering_color/delete?i=".$dataKap1->id); ?>" class="btn btn-danger" title="Hapus" ><i class="fas fa-trash-alt"></i> Hapus</a>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="card">
            <div class="card-header text-center"><h3 class="mb-0">KAP 2</h3></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 mb-3">
                        <form action="<?= base_url("filtering_color/add?p=kap2") ?>" method="post">
                            <p class="mb-1">Input Color</p>
                            <div class="input-group">
                                <input type="text" name="color" class="form-control" placeholder="Masukkan color disini">
                                <button class="button btn btn-info"><i class="fas fa-save mr-1"></i>Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr class="text-center">
                            <th>No</th>
                            <th>Color</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if(!empty($dataKap2)){
                            $no = 1;
                            foreach ($dataKap2 as $dataKap2) {
                                ?>
                                <tr class="text-center">
                                    <td class="align-middle"><?= $no++ ?></td>
                                    <td class="align-middle"><?= $dataKap2->nilai; ?></td>
                                    <td class="align-middle">
                                        <a href="<?= base_url("filtering_color/delete?i=".$dataKap2->id); ?>" class="btn btn-danger" title="Hapus" ><i class="fas fa-trash-alt"></i> Hapus</a>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>