@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                    <a href="{{ url()->previous() }}" class="btn click-primary my-2">
                        <img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back
                    </a>
                    <h5 class="card-title text-center mb-4">{{ __('Tambah List Exam') }}</h5>
                    <form method="POST" action="{{ route('listexams.store') }}">
                        @csrf

                        <div class="row mb-3">
                            <label for="provider" class="col-md-4 col-form-label text-md-start">{{ __('Provider') }}</label>
                            <div class="col-md-6">
                                <select class="form-select" name="provider" id="provider">
                                    <option value="">Pilih Provider</option>
                                    <option value="Pearson Vue">Pearson Vue</option>
                                    <option value="On Vue">On Vue</option>
                                    <option value="PSI">PSI</option>
                                    <option value="Exam Shield">Exam Shield</option>
                                    <option value="Examity">Examity</option>
                                    @foreach ($provider as $p)
                                        <option value="{{ $p->nama }}">{{ $p->nama }}</option>
                                    @endforeach
                                </select>
                                @error('provider')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-md-2 ">
                                <a href="#" class="btn click-primary d-flex" id="addProvider" data-bs-toggle="modal" data-bs-target="#providerModal">
                                    <img src="{{ asset('icon/plus.svg') }}" class="img-responsive" width="20px"> <p style="margin-bottom: 0; margin-top:3px">Provider</p>
                                </a>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="nama_exam" class="col-md-4 col-form-label text-md-start">{{ __('Nama Exam') }}</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="nama_exam" id="nama_exam">
                                @error('nama_exam')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="kode_exam" class="col-md-4 col-form-label text-md-start">{{ __('Kode Exam') }}</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="kode_exam" id="kode_exam">
                                @error('kode_exam')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="vendor" class="col-md-4 col-form-label text-md-start">{{ __('Vendor') }}</label>
                            <div class="col-md-6">
                                <select class="form-select" name="vendor" id="vendor">
                                    <option value="">Pilih Vendor</option>
                                    <option value="AWS">AWS</option>
                                    <option value="Cisco">Cisco</option>
                                    <option value="EC-Council">EC-Council</option>
                                    <option value="EPI">EPI</option>
                                    <option value="Google">Google</option>
                                    <option value="ISACA">ISACA</option>
                                    <option value="LSP">LSP</option>
                                    <option value="Microsoft">Microsoft</option>
                                    <option value="Mikrotik">Mikrotik</option>
                                    <option value="CompTIA">CompTIA</option>
                                    <option value="BNSP">BNSP</option>
                                    <option value="Inixindo Certificate">Inixindo Certificate</option>
                                    @foreach ($vendor as $v)
                                        <option value="{{ $v->nama }}">{{ $v->nama }}</option>
                                    @endforeach
                                </select>
                                @error('vendor')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-md-2 ">
                                <a href="#" class="btn click-primary d-flex" id="addVendor" data-bs-toggle="modal" data-bs-target="#vendorModal">
                                    <img src="{{ asset('icon/plus.svg') }}" class="img-responsive" width="20px"> <p style="margin-bottom: 0; margin-top:3px">Vendor</p>
                                </a>
                            </div>
                        </div>

                         <div class="row mb-3">
                            <label for="harga_exam" class="col-md-4 col-form-label text-md-start">{{ __('Harga') }}</label>
                            <div class="col-md-6">
                                <input type="number" class="form-control" name="harga_exam" id="harga_exam">
                            </div>
                        </div>
                         
                        <div class="row mb-3">
                            <label for="estimasi_durasi_booking" class="col-md-4 col-form-label text-md-start">{{ __('Estimasi Durasi Booking') }}</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="estimasi_durasi_booking" id="estimasi_durasi_booking">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <label for="valid_until" class="col-md-4 col-form-label text-md-start">{{ __('Valid Until') }}</label>
                            <div class="col-md-6">
                                <input id="valid_until" type="date" class="form-control" name="valid_until">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="note" class="col-md-4 col-form-label text-md-start">{{ __('Note/Syarat Exam') }}</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="note" id="note">
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn click-primary">
                                    {{ __('Simpan') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Adding Provider -->
<div class="modal fade" id="providerModal" tabindex="-1" aria-labelledby="providerModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="providerModalLabel">Tambah Provider</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="providerForm">
          <div class="mb-3">
            <label for="providerName" class="form-label">Nama Provider</label>
            <input type="text" class="form-control" id="providerName" name="providerName">
          </div>
          <button type="button" class="btn btn-primary" id="saveProvider">Simpan</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal for Adding Vendor -->
<div class="modal fade" id="vendorModal" tabindex="-1" aria-labelledby="vendorModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="vendorModalLabel">Tambah Vendor</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="vendorForm">
          <div class="mb-3">
            <label for="vendorName" class="form-label">Nama Vendor</label>
            <input type="text" class="form-control" id="vendorName" name="vendorName">
          </div>
          <button type="button" class="btn btn-primary" id="saveVendor">Simpan</button>
        </form>
      </div>
    </div>
  </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    $('#saveProvider').on('click', function() {
        var providerName = $('#providerName').val();
        if (providerName) {
            $.ajax({
                url: "{{ route('providers.store') }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    nama: providerName
                },
                success: function(response) {
                    setTimeout(function(){// wait for 5 secs(2)
                        // console.log(response);
                        // $('#provider').append(new Option(response.name, response.name));
                        $('#providerModal').modal('hide');
                        $('#providerName').val('');
                        location.reload(); // then reload the page.(3)
                    }, 2000);
                },
                error: function(response) {
                    alert('Error adding provider');
                }
            });
        }
    });

    $('#saveVendor').on('click', function() {
        var vendorName = $('#vendorName').val();
        if (vendorName) {
            $.ajax({
                url: "{{ route('vendors.store') }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    nama: vendorName
                },
                success: function(response) {
                    setTimeout(function(){// wait for 5 secs(2)
                        $('#vendorModal').modal('hide');
                        $('#vendorName').val('');
                        location.reload(); // then reload the page.(3)
                        // console.log(response);
                    }, 2000);
                },
                error: function(response) {
                    alert('Error adding vendor');
                }
            });
        }
    });
});
</script>
@endsection
