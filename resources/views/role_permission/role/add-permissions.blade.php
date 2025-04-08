@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body" id="card">
                <a href="/roles" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                <h5 class="card-title text-center mb-2">Role yang Diijinkan untuk {{$data->name}}</h5>
                    <form method="POST" action="{{ route('givePermissionToRole', $data->id) }}">
                        @csrf
                        @method('PUT')
                        {{-- <label for="permissions">Permissions</label> --}}
                        <div class="row mb-3">
                            @foreach ($groupedPermissions as $key => $permissions)
                            <div class="col-md-4">
                                <div class="cardLive">
                                    <div class="toolsie">
                                      <div class="circles">
                                        <span class="red box"></span>
                                      </div>
                                      <div class="circles">
                                        <span class="yellow box"></span>
                                      </div>
                                      <div class="circles">
                                        <span class="green box"></span>
                                      </div>
                                    </div>
                                    <div class="card__content">
                                        <h4 style="text-transform:uppercase">{{ $key }}</h4>
                                        @foreach ($permissions as $item)
                                            <label for="permission_{{ $item->id }}" class="cl-checkbox">
                                                <input 
                                                    type="checkbox" 
                                                    name="permissions[]" 
                                                    id="permission_{{ $item->id }}" 
                                                    value="{{ $item->name }}" 
                                                    {{ in_array($item->id, $rolePermissions) ? 'checked' : '' }}
                                                /> 
                                                <span>{{ $item->name }}</span>
                                            </label>
                                            <br>
                                        @endforeach
                                    </div>
                                </div>
                                
                            </div>
                            @endforeach
                        </div>
                                              
                        <div class="row mb-4">
                            <div class="col-md-6 offset-md-5">
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
<style>
    /* From Uiverse.io by EmmaxPlay */ 
    .cardLive {
        width: auto;
        height: 350px;
        margin: 10px auto;
        background-color: #011522;
        border-radius: 8px;
        z-index: 1;
        color: white;
    }

    .card__content{
        margin: 10px;
        /* overflow-y: auto; */
    }

    .toolsie {
        display: flex;
        align-items: center;
        padding: 9px;
    }

    .circles {
        padding: 0 4px;
    }

    .box {
        display: inline-block;
        align-items: center;
        width: 10px;
        height: 10px;
        padding: 1px;
        border-radius: 50%;
    }

    .red {
        background-color: #ff605c;
    }

    .yellow {
        background-color: #ffbd44;
    }

    .green {
        background-color: #00ca4e;
    }

    .cl-checkbox {
        position: relative;
        display: inline-block;
    }
    .cl-checkbox > input {
        appearance: none;
        -moz-appearance: none;
        -webkit-appearance: none;
        z-index: -1;
        position: absolute;
        left: -10px;
        top: -8px;
        display: block;
        margin: 0;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        background-color: rgba(0, 0, 0, 0.6);
        box-shadow: none;
        outline: none;
        opacity: 0;
        transform: scale(1);
        pointer-events: none;
        transition: opacity 0.3s, transform 0.2s;
    }

        /* Span */
    .cl-checkbox > span {
        display: inline-block;
        width: 100%;
        cursor: pointer;
    }

        /* Box */
    .cl-checkbox > span::before {
        content: "";
        display: inline-block;
        box-sizing: border-box;
        margin: 3px 11px 3px 1px;
        border: solid 2px;
        /* Safari */
        border-color: rgba(255, 255, 255, 0.6);
        border-radius: 2px;
        width: 18px;
        height: 18px;
        vertical-align: top;
        transition: border-color 0.2s, background-color 0.2s;
    }

        /* Checkmark */
    .cl-checkbox > span::after {
        content: "";
        display: block;
        position: absolute;
        top: 3px;
        left: 1px;
        width: 10px;
        height: 5px;
        border: solid 2px transparent;
        border-right: none;
        border-top: none;
        transform: translate(3px, 4px) rotate(-45deg);
    }

        /* Checked, Indeterminate */
    .cl-checkbox > input:checked,
    .cl-checkbox > input:indeterminate {
        background-color: #018786;
    }

    .cl-checkbox > input:checked + span::before,
    .cl-checkbox > input:indeterminate + span::before {
        border-color: #018786;
        background-color: #018786;
    }

    .cl-checkbox > input:checked + span::after,
    .cl-checkbox > input:indeterminate + span::after {
        border-color: #fff;
    }

    .cl-checkbox > input:indeterminate + span::after {
        border-left: none;
        transform: translate(4px, 3px);
    }

        /* Hover, Focus */
    .cl-checkbox:hover > input {
        opacity: 0.04;
    }

    .cl-checkbox > input:focus {
        opacity: 0.12;
    }

    .cl-checkbox:hover > input:focus {
        opacity: 0.16;
    }

        /* Active */
    .cl-checkbox > input:active {
        opacity: 1;
        transform: scale(0);
        transition: transform 0s, opacity 0s;
    }

    .cl-checkbox > input:active + span::before {
        border-color: #85b8b7;
    }

    .cl-checkbox > input:checked:active + span::before {
        border-color: transparent;
        background-color: rgba(0, 0, 0, 0.6);
    }

        /* Disabled */
    .cl-checkbox > input:disabled {
        opacity: 0;
    }

    .cl-checkbox > input:disabled + span {
        color: rgba(0, 0, 0, 0.38);
        cursor: initial;
    }

    .cl-checkbox > input:disabled + span::before {
        border-color: currentColor;
    }

    .cl-checkbox > input:checked:disabled + span::before,
    .cl-checkbox > input:indeterminate:disabled + span::before {
        border-color: transparent;
        background-color: currentColor;
    }

    @media (max-width: 768px) {
        .cardLive {
            width: auto;
            height: auto;
            padding: 10px; /* Mengurangi padding untuk tampilan mobile */
        }
        .btn {
            width: 100%; /* Tombol mengambil lebar penuh */
        }
    }
</style>
@endsection
