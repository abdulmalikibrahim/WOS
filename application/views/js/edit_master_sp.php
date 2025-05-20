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

	function delete_row(id) {
		Swal.fire({
            title: 'Yakin hapus data ini?',
            html: 'Data akan di hapus secara permanen',
            showCancelButton: true,
            cancelButtonText: "Tidak",
            confirmButtonColor: "red",
            confirmButtonText: 'Ya, Hapus',
        }).then((result) => {
            /* Read more about isConfirmed, isDenied below */
            if (result.isConfirmed) {
				$("#data-row-"+id).hide();
				$("#keterangan-"+id).val("Delete This Data");
			}
		});
	}

	function add_row(no) {
		no = no + 1;
		params_delete = "'data-row-add-"+no+"'";
		$("#body-edit").append('<tr align="center" id="data-row-add-'+no+'"><th class="align-middle"><input type="text" required class="form-control" name="breakdown[]" value="" style="width:10rem;" onchange="add_row('+no+')"></th><th class="align-middle"><input type="text" required class="form-control" name="part_name[]" value="" style="width:25rem;"></th><th class="align-middle"><input type="text" required class="form-control" name="original_part[]" value="" style="width:7rem;"></th><th class="align-middle"><input type="text" required class="form-control" name="route[]" value=""></th><th class="align-middle"><input type="number" required class="form-control text-center" name="qty[]" value="" style="width:4rem;"></th><th class="align-middle"><input type="text" class="form-control" name="keterangan[]" value=""></th><th class="align-middle"><button class="btn btn-sm btn-danger" title="Delete" onclick="delete_row('+params_delete+')"><i class="fas fa-trash-alt m-0"></i></button></th></tr>');
		$("#btn-save").attr("data-id",no);
	}

	$("#btn-save").click(function() {
		$("#data-row-add-"+$(this).attr("data-id")).remove();
		$("#form-input").submit();		
	});

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
