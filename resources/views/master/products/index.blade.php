@extends('adminlte::page')

@section('title', ucfirst($title))

@section('content_header')

<h1>{{ ucfirst($titleShow) }}</h1>
@stop

@section('content')
@can("create $title")
<button class="btn btn-sm btn-primary mb-2" id="btnAdd">Tambah Data</button>
@endcan
<table id="table" class="table table-hover table-sm" style="width: 100%">
    <thead>
        <tr>
            <th width="3%">No</th>
            <th>Nama</th>
            <th>Kategori</th>
            <th>Harga</th>
            <th>Deskripsi</th>
            <th>Stock</th>
            <th width="15%">Aksi</th>
        </tr>
    </thead>
    <tbody>
    </tbody>
</table>


<!-- Modal untuk Create dan Edit -->
<div class="modal fade" id="modal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabel">Tambah {{ ucfirst($title) }}</h5> <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="frm" class="row">
                    @csrf
                    <div class="mb-3 col-md-4">
                        <label for="name" class="form-label">Nama</label>
                        <input type="text" class="form-control form-control-sm" id="name" name="name" required>
                    </div>
                    <div class="mb-3 col-md-4">
                        <label for="category_id" class="form-label">Kategori</label>
                        <select type="text" class="form-control form-control-sm" id="category_id" name="category_id" required>
                            <option></option>
                        </select>
                    </div>
                    <div class="mb-3 col-md-4">
                        <label for="price" class="form-label">Harga</label>
                        <input type="number" class="form-control form-control-sm" id="price" name="price" required>
                    </div>
                    <div class="mb-3 col-md-4">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea class="form-control form-control-sm" id="description" name="description"></textarea>
                    </div>
                    <input type="hidden" id="id"> <!-- Hidden field for edit -->
                    <div class="col-12">
                        <button type="submit" class="btn btn-sm btn-primary float-right" id="saveBtn">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    $(document).ready(function() {
        $("#category_id").select2({
            placeholder:'--- Pilih Kategori ---',
            width:'100%',
            allowClear:true,
            cache:false,
            dropdownParent: $('#modal'), // Menyimpan dropdown keluar dari modal

    // Menggunakan AJAX untuk mengambil data kategori dari server
    ajax: {
        url: '/categories/options',  // URL yang mengembalikan data kategori dalam format JSON
        dataType: 'json',
        processResults: function (data) {
            // Menyusun hasil data untuk digunakan oleh Select2
            return {
                results: data  // Data yang diterima akan dimasukkan ke dalam select2
            };
        },
        delay: 250,  // Memberikan delay 250ms sebelum melakukan request baru
        cache: true
    }
});

        var table = $("#table").DataTable({
            processing: true,
            serverSide: true,
            responsive:true,
                ajax: '{{ route($title.'.index') }}', // Route untuk ambil data
                columns: [
            // Kolom untuk nomor urut (akan ditambahkan otomatis)
            {
                data: null,
                render: function(data, type, row, meta) {
                    return meta.row + 1; // Menambahkan nomor urut
                },
                orderable: false, // Tidak bisa diurutkan
                searchable: false // Tidak bisa dicari
            },
            { data: 'name', name: 'name' },
        { data: 'category_name', name: 'category_name' },  // Kolom untuk kategori
        { data: 'price', name: 'price' },
        { data: 'description', name: 'description' },
        { data: 'quantity_in_stock', name: 'quantity_in_stock' },
        { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[1, 'asc']]  // Set default urutan berdasarkan kolom kedua (title)
    });

            // Open Create Post Modal
            $('#btnAdd').on('click', function() {
                    $("#category_id").html('');
                $('#frm')[0].reset(); // Reset form
                $('#id').val(''); // Clear hidden ID
                $('#modalLabel').text('Tambah'); // Change modal title
                $('#saveBtn').text('Simpan'); // Change button text
                $('#modal').modal('show'); // Show the modal
            });

            // Open Edit Post Modal
            $(document).on('click', '.editBtn', function() {
                    $("#category_id").html('');
                var id = $(this).data('id');
                $.get('{{ url($title) }}/' + id + '/edit', function(data) {
                    console.log(data.category);
                    $('#id').val(data.id); // Set ID in hidden input
                    $('#name').val(data.name); // Set name in input
                    $('#description').val(data.description); // Set description in textarea
                    $('#modalLabel').text('Edit'); // Change modal title
                    $('#savePostBtn').text('Perbaharui'); // Change button text
                    if(data.category){
                        $("#category_id").select2('trigger','select',{data:{id:data.category.id,text:data.category.name}});
                    }
                    $('#modal').modal('show'); // Show the modal
                });
            });

            // Save (Create or Update) Post
            $('#frm').on('submit', function(e) {
                e.preventDefault();

                var formData = $(this).serialize();
                var id = $('#id').val();
                var method = id ? 'PUT' : 'POST';
                var url = id ? '{{ url($title) }}/' + id : '{{ route($title.".store") }}';

                $.ajax({
                    url: url,
                    method: method,
                    data: formData,
                    success: function(response) {

                        if(response.icon=="success"){
                        $('#modal').modal('hide'); // Hide the modal
                        table.ajax.reload(); // Reload DataTable
                    }

                // Show SweetAlert2 notification
                Swal.fire({

                    icon: response.icon,
                    title: response.title,
                    text: response.message,
                    showConfirmButton: true,
                    timer: 1500
                });
            },
            error: function(xhr) {
                   // Pastikan server mengirimkan JSON error message dengan properti 'message'
                   let errorMessage = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Terjadi kesalahan yang tidak diketahui.';
                   
                   Swal.fire({
                    icon: 'error',
                    title: 'Aduhh...',
                    text: errorMessage,
                    showConfirmButton: true,
                    timer: 1500
                });
               }
           });5
            });

            // Delete Post (Confirmation Modal)
            $(document).on('click', '.deleteBtn', function() {
                var id = $(this).data('id');
                 // SweetAlert2 Confirmation
                 Swal.fire({
                    title: 'Apa kamu yakin?',
                    text: "Anda tidak akan dapat mengembalikan ini!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus sekarang!',
                    cancelButtonText: 'Tidak',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
            // Jika tombol "Yes, delete it!" diklik
            $.ajax({
                url: '{{ url($title) }}/' + id,
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}' // Kirim CSRF Token
                },
                success: function(response) {
                    // Reload DataTable
                    table.ajax.reload();

                    // Show success notification with SweetAlert2
                    Swal.fire({
                        icon: response.icon,
                        title: response.title,
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    });
                },
                error: function(xhr) {

                   // Pastikan server mengirimkan JSON error message dengan properti 'message'
                   let errorMessage = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Terjadi kesalahan yang tidak diketahui.';

                    // Show error notification
                    Swal.fire({
                        icon: 'error',
                        title: 'Aduhh...',
                        text: errorMessage,
                        showConfirmButton: true
                    });
                }
            });
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            // Jika tombol "No, keep it" diklik atau modal ditutup
            Swal.fire(
                'Dibatalkan',
                'Data anda aman!',
                'info'
                );
        }
    });
            });
        });
    </script>
    @endpush