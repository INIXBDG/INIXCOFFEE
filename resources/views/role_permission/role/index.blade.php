@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="modal fade" id="loadingModal" tabindex="-1" aria-labelledby="spinnerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="cube">
                <div class="cube_item cube_x"></div>
                <div class="cube_item cube_y"></div>
                <div class="cube_item cube_x"></div>
                <div class="cube_item cube_z"></div>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4>Roles</h4>
                    <div class="d-flex justify-content-end">
                        {{-- @if ( auth()->user()->jabatan == 'HRD' || auth()->user()->jabatan == 'Koordinator Office' || auth()->user()->jabatan == 'Office Manager') --}}
                            <a href="{{ route('roles.create') }}" class="btn click-primary mx-4"><img src="{{ asset('icon/plus.svg') }}" class=""> Roles</a>
                        {{-- @endif --}}
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                         <table class="table table-bordered" id="tableRoles">
                            <thead>
                                <tr>
                                    <th>No</th>    
                                    <th>Roles</th>    
                                    <th>Aksi</th>    
                                </tr>    
                            </thead> 
                            <tbody>
                                @foreach ($data as $item)
                                <tr>
                                    <td>{{$loop->iteration}}</td>
                                    <td>
                                        {{$item->name}}
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>
                                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                <a class="dropdown-item" href="{{ route('roles.edit', $item->id) }}"><img src="{{ asset('icon/edit-warning.svg') }}" class=""> Edit</a>
                                                <a class="dropdown-item" href="{{ route('givePermissionToRole', $item->id) }}"><img src="{{ asset('icon/edit-warning.svg') }}" class=""> Add or Change Permissions</a>
                                                <form onsubmit="return confirm('Apakah Anda Yakin?');" action="{{ route('roles.destroy', $item->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item"><img src="{{ asset('icon/trash-danger.svg') }}" class=""> Hapus</button>
                                                </form>
                                            </div>
                                        </div>                                        
                                    </td>   
                                </tr> 
                                @endforeach   
                            </tbody>   
                        </table>   
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script>
    $(document).ready(function(){
        var userRole = '{{ auth()->user()->jabatan}}';
        $('#tableRoles').DataTable();
    
    });
</script>
@endsection