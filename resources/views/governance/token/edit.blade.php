@extends('layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">

                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <div class="content">
            <div class="row justify-content-left">
                <div class="col-md-8">
                    <form class="form main-form" method="POST" action="{{ route('token.update') }}">
                        @csrf
                        <input type="hidden" value="{{$token_id}}" name="token_id">
                        <div class="form-group row">
                            <label for="chain"
                                   class="col-md-4 col-form-label text-md-right">Chain</label>
                            <div class="col-md-6">
                                <select id="chain" {{$token_id?'readonly':''}} name="chain" class="select2" style="width: 100%;">
                                    @foreach(\App\Enums\Chain::cases() as $_chain)
                                        <option value="{{$_chain->value}}"
                                            {{$_chain==$data['chain']?'selected':''}} >{{$_chain->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">Name</label>
                            <div class="col-md-6">
                                <input id="name" placeholder="name" type="text" class="form-control"
                                       name="name" value="{{$data['name']??''}}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="contract" class="col-md-4 col-form-label text-md-right">Contract</label>
                            <div class="col-md-6">
                                <input id="contract" placeholder="contract" type="text"
                                       {{$token_id?'readonly':''}} class="form-control"
                                       name="contract" value="{{$data['contract']??''}}">
                            </div>
                        </div>
                        @if($token_id)
                            <div class="form-group row">
                                <label for="decimals" class="col-md-4 col-form-label text-md-right">decimals</label>
                                <div class="col-md-6">
                                    <input id="decimals" placeholder="0-255" type="text" readonly class="form-control"
                                           name="decimals" value="{{$data['decimals']??''}}">
                                </div>
                            </div>
                        @endif
                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    submit
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script_plus')
    <script>
        $(".main-form").ajaxForm({
            success: function (res) {
                if (res.code === 0) {
                    alert("success");
                    location.href = "{{route('token.index')}}";
                } else {
                    alert(res.msg);
                }
            }
        });
    </script>
@endsection
