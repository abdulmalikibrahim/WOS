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
