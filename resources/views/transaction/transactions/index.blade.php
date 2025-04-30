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
            <th>Kode</th>
            <th>Tipe</th>
            <th>Karyawan</th>
            <th>Supplier</th>
            <th>Produk</th>
            <th>Kuantitas</th>
            <th>Harga</th>
            <th>Subtotal</th>
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
                <h5 class="modal-title" id="modalLabel">Tambah {{ ucfirst($title) }}</h5> 
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="frm" class="row">
                    @csrf
                    <!-- Fields for Transaction Information -->
                    <div class="mb-3 col-md-4">
                        <label for="transaction_type" class="form-label">Tipe</label>
                        <select type="text" class="form-control form-control-sm" id="transaction_type" name="transaction_type" required>
                            <option></option>
                            <option value="purchase">Pembelian</option>
                            <option value="sale">Penjualan</option>
                        </select>
                    </div>
                    <div class="mb-3 col-md-4">
                        <label for="supplier_id" class="form-label">Supplier</label>
                        <select type="text" class="form-control form-control-sm" id="supplier_id" name="supplier_id" required>
                            <option></option>
                        </select>
                    </div>
                    <div class="mb-3 col-md-4">
                        <label for="employee_id" class="form-label">Karyawan</label>
                        <select type="text" class="form-control form-control-sm" id="employee_id" name="employee_id" required>
                            <option></option>
                        </select>
                    </div>
                    <div class="mb-3 col-md-4">
                        <label for="total_amount" class="form-label">Total</label>
                        <input type="number" class="form-control form-control-sm" id="total_amount" name="total_amount" value="0" required readonly="">
                    </div>
                    
                    <div class="col-12">
                        <!-- Table for Transaction Details -->
                        <h5>Detail Transaksi</h5>
                        <table id="details_table" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Jumlah</th>
                                    <th>Harga Satuan</th>
                                    <th>Subtotal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Baris detail transaksi akan ditambahkan di sini -->
                            </tbody>
                        </table>
                        <button type="button" id="add_row" class="btn btn-sm btn-success float-right">Tambah Baris</button>
                    </div>

                    <!-- Hidden fields for editing -->
                    <input type="hidden" id="id"> <!-- Hidden field for edit -->
                    <div class="col-12">
                        <hr/>
                    </div>
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
        var rowIndex = 0;  // To keep track of row index for editing

    // Add a new row to the details table
    $('#add_row').on('click', function() {
        var newRow = `
        <tr id="row_${rowIndex}">
        <td>
        <input type='hidden' name='detail_id[]' value="">
        <select name="product_id[]" id="product_id`+rowIndex+`" class="form-control form-control-sm product_select product_id" rowIndex="`+rowIndex+`" required>
        <option></option>
        <!-- Populate with products via AJAX or pre-defined options -->
        </select>
        </td>
        <td><input type="number" id="quantity`+rowIndex+`" name="quantity[]" class="form-control form-control-sm quantity" required></td>
        <td><input type="number" id="unit_price`+rowIndex+`" name="unit_price[]" class="form-control form-control-sm unit_price" required></td>
        <td><input type="number" id="subtotal`+rowIndex+`" name="subtotal[]" class="form-control form-control-sm subtotal" readonly></td>
        <td>
        <button type="button" class="btn btn-sm btn-danger delete_row" data-index="${rowIndex}">Hapus</button>
        </td>
        </tr>
        `;
        $('#details_table tbody').append(newRow);

        $("#product_id"+rowIndex).select2({
            placeholder:'--- Pilih Produk ---',
            width:'100%',
            allowClear:true,
            cache:false,
            dropdownParent: $('#modal'), // Menyimpan dropdown keluar dari modal

    // Menggunakan AJAX untuk mengambil data kategori dari server
    ajax: {
        url: '/products/options',  // URL yang mengembalikan data kategori dalam format JSON
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
        rowIndex++;
    });

    $(document).on('change','.product_id',function(e){
        var rowIndexnya = $(this).attr("rowIndex");
        var data = $(this).select2('data')[0];

        console.log(data);

        if(data){
            $("#unit_price"+rowIndexnya).val(parseInt(data.price)).trigger('change');
        }
    })

    // Delete row functionality
    $(document).on('click', '.delete_row', function() {
        var index = $(this).data('index');

        var index = $(this).data('index');
    var detailId = $(`#row_${index}`).find('input[name="detail_id[]"]').val(); // Ambil ID detail produk yang dihapus
    if (detailId) {
        // Tandai bahwa baris ini dihapus dengan menambahkan hidden input deleted[]
        $(`#details_table tbody`).append(`<input type="hidden" name="deleted[]" value="${detailId}">`);
    }
    $(`#row_${index}`).remove(); // Remove the row

        $(`#row_${index}`).remove(); // Remove the row
        recalculateTotal();  // Recalculate total
    });

    // Calculate the subtotal whenever quantity or unit price is changed
    $(document).on('input', '.quantity, .unit_price', function() {
        var row = $(this).closest('tr');
        updateSubtotal(row);
    });

    // Update the subtotal for the row
    function updateSubtotal(row) {
        var quantity = row.find('.quantity').val();
        var unitPrice = row.find('.unit_price').val();
        var subtotal = row.find('.subtotal');
        if (quantity && unitPrice) {
            var total = quantity * unitPrice;
            subtotal.val(total);
        }
        recalculateTotal();
    }

    // Recalculate the total amount based on subtotals of all rows
    function recalculateTotal() {
        var total = 0;
        $('#details_table tbody tr').each(function() {
            var subtotal = $(this).find('.subtotal').val();
            total += parseFloat(subtotal) || 0;
        });
        $('#total_amount').val(total);  // Set the total amount in the form
    }


    $("#transaction_type").select2({
        placeholder:'--- Pilih Tipe Transaksi ---',
        width:'100%',
        allowClear:true,
        cache:false,
            dropdownParent: $('#modal'), // Menyimpan dropdown keluar dari modal

        });

    $("#supplier_id").select2({
        placeholder:'--- Pilih Supplier ---',
        width:'100%',
        allowClear:true,
        cache:false,
            dropdownParent: $('#modal'), // Menyimpan dropdown keluar dari modal

    // Menggunakan AJAX untuk mengambil data kategori dari server
    ajax: {
        url: '/suppliers/options',  // URL yang mengembalikan data kategori dalam format JSON
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
    $("#employee_id").select2({
        placeholder:'--- Pilih Karyawan ---',
        width:'100%',
        allowClear:true,
        cache:false,
            dropdownParent: $('#modal'), // Menyimpan dropdown keluar dari modal

    // Menggunakan AJAX untuk mengambil data kategori dari server
    ajax: {
        url: '/employees/options',  // URL yang mengembalikan data kategori dalam format JSON
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
            { data: 'code', name: 'code' },
            { data: 'transaction_type', name: 'transaction_type' },
        { data: 'employee_name', name: 'employee_name' },  // Kolom untuk kategori
        { data: 'supplier_name', name: 'supplier_name' },
        { data: 'product_name', name: 'product_name' }, 
        {
            data: 'quantity', 
            name: 'quantity',
            render: function(data, type, row) {
                // Menambahkan pemisah ribuan dan menghilangkan angka di belakang koma
                return parseFloat(data).toFixed(0).toLocaleString('id-ID'); // Menambahkan pemisah ribuan
            }
        },
        {
            data: 'unit_price', 
            name: 'unit_price',
            render: function(data, type, row) {
                // Menambahkan simbol mata uang dan pemisah ribuan
                return 'Rp ' + parseFloat(data).toFixed(0).toLocaleString('id-ID'); // Menambahkan simbol "Rp" dan pemisah ribuan
            }
        },
        {
            data: 'subtotal', 
            name: 'subtotal',
            render: function(data, type, row) {
                // Menambahkan simbol mata uang dan pemisah ribuan
                return 'Rp ' + parseFloat(data).toFixed(0).toLocaleString('id-ID'); // Menambahkan simbol "Rp" dan pemisah ribuan
            }
        },
        { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[1, 'asc']]  // Set default urutan berdasarkan kolom kedua (title)
    });

            // Open Create Post Modal
            $('#btnAdd').on('click', function() {
                $("#category_id").html('');
                $('#frm')[0].reset(); // Reset form
                $('#id').val(''); // Clear hidden ID
                $('#transaction_type').val('').trigger('change'); // Clear hidden ID
                $("#supplier_id").html('');
                $("#employee_id").html('');
                $("#details_table tbody").html('');
                $('#modalLabel').text('Tambah'); // Change modal title
                $('#saveBtn').text('Simpan'); // Change button text
                $('#modal').modal('show'); // Show the modal
            });

            // Open Edit Post Modal
            $(document).on('click', '.editBtn', function() {
                $("#category_id").html('');
                 var id = $(this).data('id'); // Mendapatkan ID transaksi detail
                 $.get('{{ url($title) }}/' + id + '/edit', function(data) {
                    // Mengisi data transaksi ke dalam form modal
                    $('#id').val(data.id); // Set ID pada field hidden

                    // Mengisi transaksi utama
                    $('#transaction_type').val(data.transaction_type).trigger('change');
                    $('#total_amount').val(data.total_amount);
                    $("#supplier_id").select2('trigger','select',{data:{id:data.supplier.id,text:data.supplier.name}})
                    $("#employee_id").select2('trigger','select',{data:{id:data.employee.id,text:data.employee.name}})

                    // Mengisi detail transaksi
                    $("#details_table tbody").html(''); // Hapus detail sebelumnya
                    data.transaction_detail.forEach(function(detail, index) {
                        // Menambahkan baris detail ke dalam tabel modal
                        var row = `
                        <tr id="row_${rowIndex}">
                        <td>
                        <input type='hidden' name='detail_id[]' value="${detail.id}">
                        <select name="product_id[]" id="product_id${rowIndex}" class="form-control form-control-sm product_select product_id" rowIndex="${rowIndex}" required>
                        <option value="${detail.product.id}" selected>${detail.product.name}</option>
                        </select>
                        </td>
                        <td><input type="number" id="quantity${rowIndex}" name="quantity[]" class="form-control form-control-sm quantity" value="${detail.quantity}" required></td>
                        <td><input type="number" id="unit_price${rowIndex}" name="unit_price[]" class="form-control form-control-sm unit_price" value="${detail.unit_price}" required></td>
                        <td><input type="number" id="subtotal${rowIndex}" name="subtotal[]" class="form-control form-control-sm subtotal" value="${detail.subtotal}" readonly></td>
                        <td><button type="button" class="btn btn-sm btn-danger delete_row" data-index="${rowIndex}">Hapus</button></td>
                        </tr>
                        `;
                        $('#details_table tbody').append(row);

                        $("#product_id"+rowIndex).select2({
                            placeholder:'--- Pilih Produk ---',
                            width:'100%',
                            allowClear:true,
                            cache:false,
            dropdownParent: $('#modal'), // Menyimpan dropdown keluar dari modal

    // Menggunakan AJAX untuk mengambil data kategori dari server
    ajax: {
        url: '/products/options',  // URL yang mengembalikan data kategori dalam format JSON
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
                        rowIndex++;
                    });
                    recalculateTotal();

                    $('#modalLabel').text('Edit Transaksi'); // Ubah title modal menjadi "Edit Transaksi"
                    $('#saveBtn').text('Perbaharui'); // Ubah tombol simpan menjadi "Perbaharui"
                    $('#modal').modal('show'); // Tampilkan modal
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