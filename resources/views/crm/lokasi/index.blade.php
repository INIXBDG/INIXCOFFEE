@extends('layouts_crm.app')

@section('crm_contents')
    <div class="container">
        <h1 class="mb-4">Manage Locations</h1>

        <!-- Form to Create a New Location -->
        <div class="card mb-4">
            <div class="card-header">Add New Location</div>
            <div class="card-body">
                <form action="{{ route('crm.lokasi.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="lokasi" class="form-label">Location Name</label>
                        <input type="text" class="form-control @error('lokasi') is-invalid @enderror" id="lokasi"
                            name="lokasi" value="{{ old('lokasi') }}">
                        @error('lokasi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="latitude" class="form-label">Latitude</label>
                        <input type="number" step="any" class="form-control @error('latitude') is-invalid @enderror"
                            id="latitude" name="latitude" value="{{ old('latitude') }}">
                        @error('latitude')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="longitude" class="form-label">Longitude</label>
                        <input type="number" step="any" class="form-control @error('longitude') is-invalid @enderror"
                            id="longitude" name="longitude" value="{{ old('longitude') }}">
                        @error('longitude')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary">Save Location</button>
                </form>
            </div>
        </div>

        <!-- Table to Display Locations -->
        <div class="card">
            <div class="card-header">Location List</div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Latitude</th>
                            <th>Longitude</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lokasis as $lokasi)
                            <tr>
                                <td>{{ $lokasi->lokasi }}</td>
                                <td>{{ $lokasi->latitude }}</td>
                                <td>{{ $lokasi->longitude }}</td>
                                <td>
                                    <!-- Edit Form -->
                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                        data-bs-target="#editModal{{ $lokasi->id }}">Edit</button>

                                    <!-- Delete Form -->
                                    <form action="{{ route('crm.lokasi.delete', $lokasi->id) }}" method="POST"
                                        style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Are you sure you want to delete this location?')">Delete</button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Edit Modal -->
                            <div class="modal fade" id="editModal{{ $lokasi->id }}" tabindex="-1"
                                aria-labelledby="editModalLabel{{ $lokasi->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editModalLabel{{ $lokasi->id }}">Edit Location
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('crm.lokasi.update') }}" method="POST">
                                            @csrf
                                            @method('put')
                                            <div class="modal-body">
                                                <input type="hidden" name="id" value="{{ $lokasi->id }}">
                                                <div class="mb-3">
                                                    <label for="lokasi{{ $lokasi->id }}" class="form-label">Location
                                                        Name</label>
                                                    <input type="text"
                                                        class="form-control @error('lokasi') is-invalid @enderror"
                                                        id="lokasi{{ $lokasi->id }}" name="lokasi"
                                                        value="{{ old('lokasi', $lokasi->lokasi) }}">
                                                    @error('lokasi')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="mb-3">
                                                    <label for="latitude{{ $lokasi->id }}"
                                                        class="form-label">Latitude</label>
                                                    <input type="number" step="any"
                                                        class="form-control @error('latitude') is-invalid @enderror"
                                                        id="latitude{{ $lokasi->id }}" name="latitude"
                                                        value="{{ old('latitude', $lokasi->latitude) }}">
                                                    @error('latitude')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="mb-3">
                                                    <label for="longitude{{ $lokasi->id }}"
                                                        class="form-label">Longitude</label>
                                                    <input type="number" step="any"
                                                        class="form-control @error('longitude') is-invalid @enderror"
                                                        id="longitude{{ $lokasi->id }}" name="longitude"
                                                        value="{{ old('longitude', $lokasi->longitude) }}">
                                                    @error('longitude')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss
                                                    Mod="modal">Close</button>
                                                <button type="submit" class="btn btn-primary">Update Location</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
