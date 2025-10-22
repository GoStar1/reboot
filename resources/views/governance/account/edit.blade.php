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
                    <form class="form main-form" method="POST" action="{{ route('account.update_data') }}">
                        @csrf
                        <input type="hidden" value="{{$id}}" name="id">
                        <div class="form-group row">
                            <label for="address"
                                   class="col-md-4 col-form-label text-md-right">address</label>

                            <div class="col-md-6">
                                <input id="address" type="text" class="form-control"
                                       name="address" readonly required value="{{$data['address']}}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="tags"
                                   class="col-md-4 col-form-label text-md-right">Tags</label>
                            <div class="col-md-6">
                                <input id="tags" type="text" class="form-control"
                                       name="tags" required value="{{$data['tags']}}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="pending"
                                   class="col-md-4 col-form-label text-md-right">pending</label>
                            <div class="col-md-6">
                                <select name="pending" class="select2" style="width: 100%;">
                                    @foreach($bool_dict as $key=>$value)
                                        <option value="{{$key}}"
                                            {{$key==$data['pending']?'selected':''}} >{{$value}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="chain"
                                   class="col-md-4 col-form-label text-md-right">chain</label>
                            <div class="col-md-6">
                                <select id="chain" name="chain" readonly disabled class="select2" style="width: 100%;">
                                    @foreach(\App\Enums\Chain::cases() as $_chain)
                                        <option value="{{$_chain->value}}"
                                            {{$_chain==$data['chain']?'selected':''}} >{{$_chain->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
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
                    location.href = "{{route('account.index')}}";
                } else {
                    toastr.error(res.msg);
                }
            },
            error: function (res, data) {
                console.log(res, data);
                if (res.responseJSON) {
                    toastr.error(res.responseJSON.message);
                    return;
                }
                if (res.responseText) {
                    toastr.error(res.responseText);
                    return;
                }
            },
        });
    </script>
@endsection
