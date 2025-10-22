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
                    <form class="form main-form" method="POST" action="{{route('rpc.update_data')}}">
                        @csrf
                        <div class="form-group row">
                            <label for="url"
                                   class="col-md-4 col-form-label text-md-right">url</label>

                            <div class="col-md-6">
                                <input id="url" type="text" class="form-control"
                                       name="url" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="chain"
                                   class="col-md-4 col-form-label text-md-right">Chain</label>
                            <div class="col-md-6">
                                <select id="chain" name="chain" class="select2" style="width: 100%;">
                                    @foreach(\App\Enums\Chain::cases() as $_chain)
                                        <option value="{{$_chain->value}}">{{$_chain->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="status"
                                   class="col-md-4 col-form-label text-md-right">status</label>
                            <div class="col-md-6">
                                <select name="status" class="select2" style="width: 100%;">
                                    @foreach($status_dict as $key=>$value)
                                        <option value="{{$key}}"
                                        >{{$value}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="heartbeat"
                                   class="col-md-4 col-form-label text-md-right">heartbeat</label>
                            <div class="col-md-6">
                                <select name="heartbeat" class="select2" style="width: 100%;">
                                    @foreach($bool_dict as $key=>$value)
                                        <option value="{{$key}}"
                                        >{{$value}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="priority"
                                   class="col-md-4 col-form-label text-md-right">priority</label>

                            <div class="col-md-6">
                                <input id="priority" placeholder="0-255" type="text" class="form-control"
                                       name="priority" value="255">
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
                    toastr.success('success');
                    setTimeout(function () {
                        window.location.href = "{{route('rpc.index')}}";
                    }, 2000);
                } else {
                    toastr.error(res.msg);
                }
            }
        });
    </script>
@endsection
