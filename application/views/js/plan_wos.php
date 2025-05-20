<script>
    function loading(title,html) {
        Swal.fire({
            title: title,
            html: html,
            timerProgressBar: true,
            didOpen: () => {
                Swal.showLoading()
                const b = Swal.getHtmlContainer().querySelector('b')
            },
            allowOutsideClick: false
        });
    }
    $("#upload-excel").change(function() {
        loading("Sedang Upload...","");
        fileupload = document.getElementById("upload-excel");
        file = fileupload.files[0];
        $("#customFile").html(file.name);
        $("#form_export").submit();
    });
    $("#upload-excel-kap2").change(function() {
        loading("Sedang Upload...","");
        fileupload = document.getElementById("upload-excel-kap2");
        file = fileupload.files[0];
        $("#customFile-kap2").html(file.name);
        $("#form_export_kap2").submit();
    });
</script>
