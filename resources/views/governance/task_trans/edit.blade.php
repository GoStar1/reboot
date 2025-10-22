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
                    <form class="form main-form" method="POST" action="{{ route('task_trans.save') }}">
                        @csrf
                        <input type="hidden" value="{{$item['task_trans_id']}}" name="task_trans_id">
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right">Start Time</label>
                            <div class="col-md-8">
                                <div class="input-group date" id="startTime" data-target-input="nearest">
                                    <input type="text" class="form-control datetimepicker-input"
                                           data-target="#startTime" name="execute_time"
                                           value="{{date('m/d/Y H:i',$item['execute_time'])}}"/>
                                    <div class="input-group-append" data-target="#startTime"
                                         data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
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
        $('#startTime').datetimepicker({
            icons: {time: 'far fa-clock'},
            timeZone: 'UTC',
            showTimezone: true,
            minDate: moment.utc(),
            default: moment.utc(),
            format: 'MM/DD/YYYY HH:mm',
        });
        $(".main-form").ajaxForm({
            success: function (res) {
                if (res.code === 0) {
                    toastr.success('success');
                    setTimeout(function () {
                        window.location.href = "{{route('task_trans.index')}}";
                    }, 1000);
                } else {
                    toastr.error(res.msg);
                }
            }
        });
    </script>
@endsection
