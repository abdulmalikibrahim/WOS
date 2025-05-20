<script>
    function loading(title, html) {
        Swal.fire({
            title: title,
            html: html,
            timerProgressBar: true,
            didOpen: () => {
                Swal.showLoading()
                const b = Swal.getHtmlContainer().querySelector('b')
            },
            allowOutsideClick: false
        })
    }
    $(".checkingUploadActive").change(function() {
        activeUpload();
    });
    function activeUpload(){
        const plant = $("#plant").val();
        const pdd = $("#pdd").val();
        const file = $("#upload-file").val();
        if(plant != "" && pdd != "" && file != ""){
            $("#btn-upload").removeAttr("disabled");
        }else{
            $("#btn-upload").attr("disabled", "disabled");
        }
    }
    $("#upload-file").change(function() {
        fileupload = document.getElementById("upload-file");
        file = fileupload.files[0];
        $("#customFile-upload").html(file.name);
    });
    function getDataVINChecking(kap) {
        $.ajax({
            url: '<?= base_url('getDataVINChecking') ?>/'+kap,
            beforeSend: function() {
                $('#data_vin_kap'+kap).html('<tr><td colspan="5" class="text-center"><h6 class="m-0 mt-3">Loading...</h6></td></tr>');
            },
            success: function(data) {
                var result = JSON.parse(data);
                var data = result.data;
                if (data.length > 0) {
                    var html = '';
                    for (var i = 0; i < data.length; i++) {
                        html += '<tr>';
                        html += '<td class="text-center">' + (i + 1) + '</td>';
                        html += '<td class="text-center">' + data[i].model + '</td>';
                        html += '<td class="text-center">' + data[i].vin + '</td>';
                        html += '<td class="text-center">' + data[i].suffix + '</td>';
                        html += '<td class="text-center">' + data[i].pdd_input + '</td>';
                        html += '</tr>';
                    }
                    $('#data_vin_kap'+kap).html(html);
                } else {
                    $('#data_vin_kap'+kap).html('<tr><td colspan="5" class="text-center"><h6 class="m-0 mt-3">No Data Entry</h6></td></tr>');
                }
            }
        })
    }
    getDataVINChecking("1");
    getDataVINChecking("2");

    function checkUpload() {
        const pdd = $('#pdd').val();
        const plant = $('#plant').val();
        $.ajax({
            url:'<?= base_url("checkUpload"); ?>',
            type:'GET',
            data: {pdd: pdd, plant: plant},
            dataType:"JSON",
            beforeSend: function() {
                $("#btn-upload").html('<i class="fas fa-spin fa-spinner"></i> Uploading...');
            },
            success:function(r) {
                console.log(r);
                d = JSON.parse(JSON.stringify(r));
                if(d.statusCode == "200") {
                    $("#btn-upload-form").click();
                    loading("Sedang Upload...", "");
                } else {
                    swal.fire({
                        title: "Informasi",
                        html: d.res,
                        confirmButtonText: "Ya, Lanjutkan",
                        cancelButtonText: "Cancel",
                        showCancelButton: true,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $("#reupload").val("1");
                            $("#btn-upload-form").click();
                            loading("Sedang Upload...", "");
                        }else{
                            $("#btn-upload").html('Upload');
                        }
                    });
                }
            }
        })
    }

    function clearDataDouble() {
        $.ajax({
            url:'<?= base_url("clearDataDouble"); ?>',
            dataType:"JSON",
            beforeSend: function() {
                $("#data-vin-double").html('<tr><td colspan="6" class="text-center align-middle text-dark font-weight-bold">Deleting Data...</td></tr>');
            }
        }).always(function () {
            $("#title-vin-duplicate").html("DATA VIN DUPLICATE");
            $("#label-pdd-duplicate").html("PDD Duplicate");
            $("#data-vin-double").html('<tr><td colspan="6" class="text-center align-middle bg-success text-light font-weight-bold">No Duplicate Data</td></tr>');
        });
    }
    $("#btn-search-vin").click(function() {
        $(this).html("Searching...");
    })

    function openModalDownload(plant) {
        $("#modal-download-vin").modal("show");
        $("#line-download").val("KAP "+plant);
        $("#modal-title-download").html("Download Data VIN KAP "+plant);
    }
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.15/index.global.min.js" ...></script>
<script>
    var kap2 = <?= $pddinputkap2; ?>;
    var kap1 = <?= $pddinputkap1; ?>;

    document.addEventListener('DOMContentLoaded', function () {
        var calendarEl = document.getElementById('calendar-1');

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: '', //prev,next today
                center: 'title',
                right: ''
            },
            dayCellDidMount: function (info) {
                var year = info.date.getFullYear();
                var month = String(info.date.getMonth() + 1).padStart(2, '0');
                var day = String(info.date.getDate()).padStart(2, '0');
                var cellDate = `${year}-${month}-${day}`; // Format YYYY-MM-DD

                if (kap1.includes(cellDate)) {
                    info.el.classList.add('bg-success'); // Tambahkan class CSS
                    info.el.classList.add('text-light'); // Tambahkan class CSS
                    $(".fc-daygrid-day-top").addClass("justify-content-center font-weight-bold")
                    $(".fc-daygrid-day-top").attr("style","position: absolute;top: 50%;left: 50%; transform: translate(-50%,-50%); font-size: 18pt;")
                }
            }
        });

        calendar.render();
    });

    document.addEventListener('DOMContentLoaded', function () {
        var calendarEl = document.getElementById('calendar-2');

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: '',
                center: 'title',
                right: ''
            },
            dayCellDidMount: function (info) {
                var year = info.date.getFullYear();
                var month = String(info.date.getMonth() + 1).padStart(2, '0');
                var day = String(info.date.getDate()).padStart(2, '0');
                var cellDate = `${year}-${month}-${day}`; // Format YYYY-MM-DD

                if (kap2.includes(cellDate)) {
                    info.el.classList.add('bg-success'); // Tambahkan class CSS
                    info.el.classList.add('text-light'); // Tambahkan class CSS
                    $(".fc-daygrid-day-top").addClass("justify-content-center font-weight-bold")
                    $(".fc-daygrid-day-top").attr("style","position: absolute;top: 50%;left: 50%; transform: translate(-50%,-50%); font-size: 18pt;")
                }
            }
        });

        calendar.render();
    });
</script>