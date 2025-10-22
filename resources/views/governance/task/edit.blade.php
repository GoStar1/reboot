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
                    <form class="form main-form" method="POST" action="{{ route('task.save') }}">
                        @csrf
                        <input type="hidden" value="{{$item['task_id']}}" name="task_id">
                        <div class="form-group row">
                            <label for="min_gas_price"
                                   class="col-md-4 col-form-label text-md-right">Max Gas Price</label>
                            <div class="col-md-6">
                                <input id="min_gas_price" type="text" class="form-control"
                                       name="min_gas_price" required value="{{$item['min_gas_price']}}">
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
                        window.location.href = "{{route('task.index')}}";
                    }, 1000);
                } else {
                    toastr.error(res.msg);
                }
            }
        });
    </script>
@endsection
