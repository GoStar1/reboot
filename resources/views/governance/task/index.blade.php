@extends('layouts.app')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">

                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <div class="content">
            <div class="container-fluid">
                <form action="">
                    <div class="row">
                        <div class="col-md-10 offset-md-1">
                            <div class="row">
                                <div class="col-3">
                                    <div class="form-group form-group-sm">
                                        <label>Chain</label>
                                        <select name="chain" class="select2" style="width: 100%;">
                                            <option value="">select</option>
                                            @foreach(\App\Enums\Chain::cases() as $_chain)
                                                <option value="{{$_chain->value}}"
                                                    {{$_chain==$chain?'selected':''}} >{{$_chain->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="form-group form-group-sm">
                                        <label>TaskData</label>
                                        <select name="task_data_id" class="select2" style="width: 100%;">
                                            <option value="">select</option>
                                            @foreach($task_data_dict as $key=>$value)
                                                <option value="{{$key}}"
                                                    {{$key==$task_data_id?'selected':''}} >{{$value}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="form-group form-group-sm">
                                        <label for="template_id">Template</label>
                                        <select id="template_id" name="template_id" class="select2"
                                                style="width: 100%;">
                                            <option value="">select</option>
                                            @foreach($template_dict as $key=>$value)
                                                <option value="{{$key}}"
                                                    {{$key==$template_id?'selected':''}} >{{$value}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="form-group form-group-sm">
                                        <label for="keyword">keyword</label>
                                        <div class="input-group">
                                            <input id="keyword" name="keyword" type="search" class="form-control"
                                                   placeholder="Type your keywords here" value="{{$keyword}}">
                                            <div class="input-group-append">
                                                <button type="submit" class="btn btn-sm btn-default">
                                                    <i class="fa fa-search"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="card-tools">
                                    <a class="link-dark" href="{{route('task_data.index')}}">
                                        <i class="fa fa-plus"></i> create
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Chain</th>
                                        <th>Task Data</th>
                                        <th>Template</th>
                                        <th>Method</th>
                                        <th>Args</th>
                                        <th>Start Time</th>
                                        <th>In Range</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($list['data'] as $item)
                                        <tr>
                                            <td>{{$item['task_id']}}</td>
                                            <td>{{$item['chain']->name}}</td>
                                            <td>{{$item['task_name']}}</td>
                                            <td>{{$item['template_name']}}</td>
                                            <td>{{$item['method']}}</td>
                                            <td style="word-break: break-all;max-width: 150px;">
                                                {{$item['args']}}
                                            </td>
                                            <td>{{date('Y-m-d H:i:s',$item['start_time'])}}</td>
                                            <td>{{\App\Services\SystemUtils::secondsDiff($item['time_range'])}}</td>
                                            <td>
                                                Failed:{{$item['failed']}}
                                                Completed:{{$item['completed']}}
                                                Waiting:{{$item['waiting']}}
                                            </td>
                                            <td>
                                                <a class="link-dark"
                                                   href="{{route('task_trans.index')}}?task_id={{$item['task_id']}}">
                                                    Trans
                                                </a>
                                                <a class="link-dark trigger-modal" data-target="#modal-detail"
                                                   data-toggle="modal"
                                                   data-href="{{route('task.detail')}}?task_id={{$item['task_id']}}"
                                                   href="javascript:;">
                                                    Detail
                                                </a>
                                                <a class="link-dark"
                                                   href="{{route('task.edit')}}?task_id={{$item['task_id']}}">
                                                    Edit
                                                </a>
                                                @if($item['waiting']>0)
                                                    <button class="btn btn-danger btn-cancel"
                                                            data-id="{{$item['task_id']}}"
                                                            data-amount="{{$item['waiting']}}">
                                                        Cancel
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer">
                                @include('components/pagination', ['pagination' => $list])
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.container-fluid -->
        </div>
        <!-- /.content -->
    </div>
    <div class="modal fade" id="modal-detail">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
@endsection
@section('script_plus')
    <script>
        $('.trigger-modal').on('click', function () {
            let modal = $($(this).data('target'));
            let body = modal.find('.modal-body');
            body.html('');
            body.load($(this).data('href'), function () {
                modal.modal({show: true});
            });
        });
        $('.btn-cancel').on('click', function () {
            var id = $(this).data('id');
            var amount = $(this).data('amount');
            var ret = confirm('Are you sure to delete the ' + amount + ' transactions in task:' + id + '?');
            if (!ret) {
                return;
            }
            $.ajax({
                type: 'POST',
                url: '{{route('task.cancel')}}',
                data: {
                    'task_id': id,
                    '_token': '{{csrf_token()}}'
                },
                success: function (response) {
                    if (response['code'] == 0) {
                        toastr.success();
                        setTimeout(function () {
                            window.location.href = "{{route('task.index')}}";
                        }, 1000);
                    } else {
                        toastr.error(response.msg);
                    }
                }
            });
        })
    </script>
@endsection
