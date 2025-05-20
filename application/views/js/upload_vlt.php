

<?php
$array_suffix = $this->model->gds("plan_wos","suffix,plan","plan IS NOT NULL AND suffix !=''","result");
$total_plan = $this->model->gds("plan_wos","SUM(plan) as total_plan","plan IS NOT NULL AND suffix !=''","row");
$total_suffix = count($array_suffix);
$suffix_final = '';
foreach ($array_suffix as $array_suffix) {
    $suffix_final .= "'".$array_suffix->suffix."':".$array_suffix->plan.", ";
}
?>
<script>
    $(document).ready(function() {
        load_data();
    });

    function load_data() {
        $.ajax({
            url:"<?=base_url("load_vlt")?>",
            beforeSend:function() {
                $("#data_tabungan").html('<tr><td align="center" colspan="25"><i>Sedang Memuat...</i></td></tr>');
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

    $("#upload-excel").change(function() {
        loading("Sedang Upload...", "");
        fileupload = document.getElementById("upload-excel");
        file = fileupload.files[0];
        $("#customFile").html(file.name);
        $("#form_export").submit();
    });

    <?php
    $p = $this->input->get("p");
    if(!empty($p)){
        if($p == "docking"){
            echo 'docking();';
        }
    }
    ?>
</script>
