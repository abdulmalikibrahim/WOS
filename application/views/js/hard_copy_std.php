<script>
    function delete_data(id) {
        swal.fire({
            title: 'Hapus Data ini?',
            confirmButtonText: 'Yes, Hapus',
            showCancelButton: true,
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "<?=base_url("delete_hcstd?id=")?>"+id;
            }
        });
    }
</script>