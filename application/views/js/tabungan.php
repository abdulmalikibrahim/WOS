<?php
// === FIX BUG 1: Hapus GROUP BY agar total plan menghitung global, bukan per suffix ===

//KAP 1
$array_suffix = $this->model->gds("plan_wos","suffix,plan,batch","plan IS NOT NULL AND suffix !=''","result");
// HAPUS 'GROUP BY suffix' DISINI
$total_plan = $this->model->gds("plan_wos","SUM(plan) as total_plan","plan IS NOT NULL AND suffix !=''","row"); 
$total_suffix = count($array_suffix);
$suffix_final = '';
foreach ($array_suffix as $array_suffix) {
    $suffix_final .= "'".$array_suffix->suffix."-".$array_suffix->batch."':".$array_suffix->plan.", ";
}

//KAP 2
$array_suffix_kap2 = $this->model->gds("plan_wos_kap2","suffix,plan,batch","plan IS NOT NULL AND suffix !=''","result");
// HAPUS 'GROUP BY suffix' DISINI
$total_plan_kap2 = $this->model->gds("plan_wos_kap2","SUM(plan) as total_plan","plan IS NOT NULL AND suffix !=''","row");
$total_suffix_kap2 = count($array_suffix_kap2);
$suffix_final_kap2 = '';
foreach ($array_suffix_kap2 as $array_suffix_kap2) {
    $suffix_final_kap2 .= "'".$array_suffix_kap2->suffix."-".$array_suffix_kap2->batch."':".$array_suffix_kap2->plan.", ";
}
?>
<script>
    const p = '<?= $this->input->get("p"); ?>';
    const plant = '<?= $this->input->get("plant") ?? "KAP1"; ?>';
    const dummy = '<?= $this->input->get("dummy"); ?>';

    if(dummy == "yes"){
        swal.close();
        setTimeout(() => {
            if(plant == "KAP1"){
                $("#docking-kap1").trigger("click");
            }else{
                $("#create_tabungan_dummy_kap2").trigger("click");
            }
        }, 2000);
    }

    $(document).ready(function() {
        load_data();
        load_data_kap2();
    });

    function load_data() {
        $.ajax({
            url:"<?=base_url("load_tabungan")?>",
            beforeSend:function() {
                $("#data_tabungan").html('<tr><td align="center" colspan="25" class="align-middle"><i>Sedang Memuat...</i></td></tr>');
            },
            success:function(r) {
                $("#data_tabungan").html(r);
            },
            error:function(xhr,status,error) {
                voice("gagal.mp3");
                swal.fire("Error",xhr.responseText,"error");
            }
        })
    }

    function load_data_kap2() {
        $.ajax({
            url:"<?=base_url("load_tabungan?t=kap2")?>",
            beforeSend:function() {
                $("#data_tabungan_kap2").html('<tr><td align="center" colspan="25" class="align-middle"><i>Sedang Memuat...</i></td></tr>');
            },
            success:function(r) {
                $("#data_tabungan_kap2").html(r);
            },
            error:function(xhr,status,error) {
                voice("gagal.mp3");
                swal.fire("Error",xhr.responseText,"error");
            }
        })
    }

    
    function create_tabungan_dummy() {
        $.ajax({
            url: "<?=base_url('process_tabungan_dummy_kap2')?>",
            type: "POST", // Sesuaikan kalau lo pake GET atau POST
            dataType: "json", // WAJIB, biar jQuery otomatis dapet Object, bukan String
            beforeSend: function() {
                $("#create_tabungan_dummy_kap2").html('Creating....');
                $("#create_tabungan_dummy_kap2").attr('disabled', true); // Opsional: disable tombol biar gak double click
            },
            success: function(r) {
                console.log(r)
                $("#create_tabungan_dummy_kap2").html('Create Tabungan Dummy');
                $("#create_tabungan_dummy_kap2").attr('disabled', false);

                // Munculin konfirmasi
                swal.fire({
                    title: "Sukses!",
                    text: "Data dummy berhasil dibuat. Apakah kamu ingin upload tabungan D55L?",
                    icon: "success",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Ya, Upload!",
                    cancelButtonText: "Tidak"
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Kalau klik "Ya", langsung ke menu Bank VLT (ambil data tabungan D55L)
                        // redirect=tabungan -> setelah data dipakai, balik ke menu Tabungan
                        window.location.href = "<?=base_url('pick_bank_vlt?kap=kap2&redirect=tabungan')?>";
                    }
                });
            },
            error: function(xhr, status, error) {
                console.log(xhr,status,error)
                voice("gagal.mp3");
                
                // Default pesan kalau gagal total
                let pesanError = "Terjadi kesalahan pada server.";

                // Coba bongkar isi responseText-nya
                try {
                    let response = JSON.parse(xhr.responseText);
                    // Kalau ada key 'res' di JSON lo, ambil itu
                    if (response.res) {
                        pesanError = response.res;
                    } else {
                        // Kalau nggak ada key res, tampilin string JSON-nya aja (biar gak object)
                        pesanError = JSON.stringify(response);
                    }
                } catch (e) {
                    // Kalau ternyata response-nya bukan JSON (misal error 500 mentah dari PHP)
                    pesanError = xhr.responseText || error;
                }

                // Pastikan pakai format object biar lebih aman di SweetAlert2
                swal.fire({
                    title: "Error",
                    text: pesanError, // Ini harus STRING
                    icon: "error"
                });
            }
        });
    }

    // $(document).on('submit', '#formUploadExcel', function(e) {
    //     e.preventDefault();
    //     let formData = new FormData(this);

    //     $.ajax({
    //         url: "<?=base_url('import_tabungan_d55l')?>", // Ganti ke controller upload lo
    //         type: "POST",
    //         data: formData,
    //         contentType: false,
    //         processData: false,
    //         beforeSend: function() {
    //             swal.fire({
    //                 title: 'Tunggu bentar...',
    //                 text: 'Lagi upload file...',
    //                 allowOutsideClick: false,
    //                 didOpen: () => { swal.showLoading() }
    //             });
    //         },
    //         success: function(res) {
    //             $('#modalUploadExcel').modal('hide');
    //             swal.fire({
    //                 title: "Berhasil!",
    //                 html: "File Excel udah masuk sistem.",
    //                 icon: "success"
    //             }).then((result) => {
    //                 if (result.isConfirmed) {
    //                     window.location.href = "<?= base_url("heijunka_wos_kap2") ?>"
    //                 }
    //             });
    //             swal.fire("Berhasil!", "File Excel udah masuk sistem.", "success");
    //         },
    //         error: function() {
    //             swal.fire("Gagal!", "Ada masalah pas upload file.", "error");
    //         }
    //     });
    // });

    // --- LOGIC DOCKING FIX ---
    function docking(data = null) {
        var tipe = (data) ? data.dataset.tipe : "kap1";
        var url_truncate = "";
        var array_suffix = {};

        window.docking_vars = {
            total_plan: 0,
            total_suffix: 0,
            tipe: tipe,
            processed_count: 0 // Counter manual untuk tracking progress
        };

        if(tipe == "kap1"){
            url_truncate = "<?=base_url("docking_truncate")?>";
            array_suffix = {<?= $suffix_final ?>};
            window.docking_vars.total_plan = <?=!empty($total_plan->total_plan) ? ($total_plan->total_plan*1) : 0?>;
            window.docking_vars.total_suffix = <?=!empty($total_suffix) ? $total_suffix : 0?>;
        }else{
            url_truncate = "<?=base_url("docking_truncate?t=kap2")?>";
            array_suffix = {<?= $suffix_final_kap2 ?>};
            window.docking_vars.total_plan = <?= !empty($total_plan_kap2) ? ($total_plan_kap2->total_plan*1) : "0"?>;
            window.docking_vars.total_suffix = <?=$total_suffix_kap2?>;
        }

        var keys = Object.keys(array_suffix);
        var last_suffix = keys[keys.length - 1];

        loading_docking("Sedang Proses Docking...","Mohon tunggu, proses dilakukan berurutan agar data akurat...");

        $.ajax({
            url: url_truncate,
            beforeSend: function() {
                if(tipe == "kap1"){
                    $("#docking-kap1").html("Sedang Docking...");
                }else{
                    $("#docking").html("Sedang Docking...");
                }
            },
            success: function(r) {
                if(r == "sukses"){
                    process_docking_sequence(keys, array_suffix, last_suffix);
                }else{
                    swal.fire("Error","Terjadi kesalahan saat membersihkan data lama","error");
                }
            },
            error: function(xhr,status,error) {
                swal.fire({icon:"error",title:"Gagal Truncate",html:xhr.responseText});
            }
        });
    }

    async function process_docking_sequence(keys, array_suffix, last_suffix) {
        // Reset counter
        window.docking_vars.processed_count = 0;
        
        for (const key of keys) {
            await do_docking_ajax(key, array_suffix[key], last_suffix);
            
            // Increment counter manual setiap kali ajax selesai (sukses/gagal)
            window.docking_vars.processed_count++;
            
            // === FIX BUG 2: Cek Selesai Berdasarkan Loop Count, Bukan Data DB ===
            // Jika counter sudah sama dengan jumlah antrian, berarti selesai
            if(window.docking_vars.processed_count >= keys.length){
                finish_docking(window.docking_vars.tipe);
            }
        }
    }

    function do_docking_ajax(suffix, plan, last_suffix) {
        return new Promise((resolve, reject) => {
            var vars = window.docking_vars;
            var url_docking = (vars.tipe == "kap1") ? "<?=base_url("docking")?>" : "<?=base_url("docking_kap2")?>";
            var split_suffix = suffix.split("-");
            var new_suffix = split_suffix[0];
            var batch = split_suffix[1];
            
            $.ajax({
                type: "get",
                url: url_docking,
                data: {
                    suffix: new_suffix,
                    plan: plan,
                    batch: batch,
                },
                dataType: "JSON",
                success: function(r) {
					d = JSON.parse(JSON.stringify(r));
					let actual = d.actual;
					let total_docking = d.total_docking;
					
					// Hitung persentase progress bar
					let percentage_docking = 0;
					if(vars.total_plan > 0){
						percentage_docking = Math.round((parseInt(total_docking) / parseInt(vars.total_plan)) * 100);
					}
					
					// --- UPDATE TAMPILAN DISINI ---
					
					// 1. Update Kotak Detail (Info Tech)
					$("#tech-details").removeClass("d-none").html(`
						<div><strong>SUFFIX :</strong> ${new_suffix} [Batch ${batch}]</div>
						<div><strong>TARGET :</strong> ${plan} Units</div>
						<div><strong>CAPTURED:</strong> ${actual} Units</div>
					`);

					// 2. Update Progress Bar
					$("#tech-progress-bar")
						.css("width", percentage_docking + "%")
						.text(percentage_docking + "%");
					
					// 3. Update Text Bawah (Total Keseluruhan)
					$("#tech-status-text").html(`
						Processing Data Stream... <b>${total_docking} / ${vars.total_plan}</b> Records
					`);

					resolve(r);
				},
                error: function(xhr, status, error) {
                    console.error("Error pada suffix: " + suffix);
                    resolve(null); // Tetap resolve biar loop lanjut
                }
            });
        });
    }

    function finish_docking(tipe){
        voice("sukses.mp3");
        
        const buttonHeijunka = tipe == "kap1" 
                ? `<a href="<?=base_url("heijunka_wos?proses=yes&docking=".$this->input->get("docking")."&dummy=".$this->input->get("dummy"))?>" class="btn btn-info mt-4">Heijunka KAP 1</a>` 
                : `<a href="<?=base_url("heijunka_wos_kap2?proses=yes")?>" class="btn btn-info mt-4 ml-2">Heijunka KAP 2</a>`;

        swal.fire({
            iconHtml: '<img src="<?=base_url('assets/images/happy.png')?>" width="100%">',
            customClass: { icon: 'border-0' },
            title: "Docking Selesai!",
            html: 'Semua proses selesai.<br>Silahkan lanjut ke Heijunka.<br>'+buttonHeijunka+'<a href="javascript:void(0)" onclick="swal_close()" class="btn btn-secondary mt-4 ml-2">Tetap Disini</a>',
            showConfirmButton: false,
        });
        
        if(tipe == "kap1") $("#docking-kap1").html("Docking");
        else $("#docking").html("Docking");
    }

    function loading(title, html) {
        Swal.fire({
            title: title,
            html: html,
            timerProgressBar: true,
            didOpen: () => {
                Swal.showLoading()
            },
            allowOutsideClick: false
        })
    }
    
    function loading_docking(title, html) {
		Swal.fire({
			// Kita kosongin title bawaan biar bisa custom HTML full
			title: '', 
			html: `
				<div class="tech-loader"></div>
				<div class="tech-title">${title}</div>
				
				<div id="tech-details" class="info-tech d-none">
					Preparing Data Stream...
				</div>

				<div class="progress-container">
					<div id="tech-progress-bar" class="progress-bar-tech" role="progressbar" style="width: 0%">0%</div>
				</div>
				
				<div id="tech-status-text" class="mt-2 text-muted small" style="font-style:italic;">
					${html}
				</div>
			`,
			showConfirmButton: false,
			allowOutsideClick: false,
			customClass: {
				popup: 'glass-popup' // Panggil class CSS tadi
			}
		});
	}

    $("#upload-excel").change(function() {
        loading("Sedang Upload...", "");
        $("#form_export").submit();
    });
    
    $("#upload-excel-kap2").change(function() {
        loading("Sedang Upload...", "");
        $("#form_export_kap2").submit();
    });

    if(p){
        if(p == "kap2"){
            $("#docking").click();
        }else{
            docking();
        }
    }
</script>
