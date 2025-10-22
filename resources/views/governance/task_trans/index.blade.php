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
                        <div class="col-12">
                            <div class="row">
                                <div class="col-2">
                                    <div class="form-group form-group-sm">
                                        <label>Chain</label>
                                        <select id="chain" name="chain" class="select2" style="width: 100%;">
                                            <option value="">select</option>
                                            @foreach(\App\Enums\Chain::cases() as $_chain)
                                                <option value="{{$_chain->value}}"
                                                    {{$_chain==$chain?'selected':''}} >{{$_chain->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-2">
                                    <div class="form-group form-group-sm">
                                        <label>TaskData</label>
                                        <select id="task_data_id" name="task_data_id" class="select2"
                                                style="width: 100%;">
                                            <option value="">select</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-2">
                                    <div class="form-group form-group-sm">
                                        <label>Template</label>
                                        <select name="template_id" id="template_id" class="select2"
                                                style="width: 100%;">
                                            <option value="">select</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-2">
                                    <div class="form-group form-group-sm">
                                        <label>Task ID</label>
                                        <div class="input-group">
                                            <input name="task_id" type="search" class="form-control"
                                                   placeholder="Task ID" value="{{$task_id}}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-2">
                                    <div class="form-group form-group-sm">
                                        <label for="status">Status</label>
                                        <select id="status" name="status" class="select2" style="width: 100%;">
                                            <option value="">select</option>
                                            @foreach($status_dict as $k=>$v)
                                                <option value="{{$k}}"
                                                    {{(string)$status===(string)$k?'selected':''}} >{{$v}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-2">
                                    <div class="form-group form-group-sm">
                                        <label>keyword</label>
                                        <div class="input-group">
                                            <input name="keyword" type="search" class="form-control"
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
                            <div class="card-body">
                                <table class="table table-bordered card-body table-responsive p-0">
                                    <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Chain</th>
                                        <th>Account</th>
                                        <th>TaskData</th>
                                        <th>Template</th>
                                        <th>Method</th>
                                        <th>ExecuteTime(GMT)</th>
                                        <th>Args</th>
                                        <th>Status</th>
                                        <th>TransHash</th>
                                        <th>Retry</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($list['data'] as $item)
                                        <tr>
                                            <td>{{$item['task_trans_id']}}</td>
                                            <td>{{$item['chain']->name}}</td>
                                            <th>
                                                <a target="_blank"
                                                   href="{{$item['chain']->explorer()}}/address/{{$item['from']}}">
                                                    {{substr($item['from'],0,5).'..'.substr($item['from'],38)}}
                                                </a>
                                                <i style="cursor: pointer" data-address="{{$item['from']}}"
                                                   class="fa fa-copy copy-btn"/>
                                            </th>
                                            <th>{{$task_data_list_dict[$item['task_data_id']]??'-'}}</th>
                                            <th>
                                                @if(isset($template_list_dict[$item['template_id']]))
                                                    {{$template_list_dict[$item['template_id']]}}
                                                @else
                                                    <a target="_blank"
                                                       href="{{$item['chain']->explorer()}}/address/{{$item['to']}}">
                                                        {{substr($item['to'],0,5).'..'.substr($item['to'],38)}}
                                                    </a>
                                                    <i style="cursor: pointer" data-address="{{$item['to']}}"
                                                       class="fa fa-copy copy-btn"/>
                                                @endif
                                            </th>
                                            <th>{{$item['method']}}</th>
                                            <th>{{date('Y-m-d H:i:s',$item['execute_time'])}}</th>
                                            <td style="word-break: break-all;min-width: 400px;max-width: 400px;">
                                                @if($item['call_data']&&$item['method']!='executeOrder')
                                                    {{$item['call_data']}}
                                                @else
                                                    {{json_encode($item['args'])}}
                                                @endif
                                            </td>
                                            <td>{{$status_dict[$item['status']]??'-'}}</td>
                                            <td>
                                                @if($item['trans_hash'])
                                                    <a target="_blank"
                                                       href="{{$item['chain']->explorer()}}/tx/{{$item['trans_hash']}}">
                                                        {{substr($item['trans_hash'],0,5).'..'.substr($item['trans_hash'],61)}}
                                                    </a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{$item['retry']}}</td>
                                            <td>
                                                <a class="link-dark trigger-modal" data-target="#modal-detail"
                                                   data-toggle="modal"
                                                   data-href="{{route('task_trans.detail')}}?task_trans_id={{$item['task_trans_id']}}"
                                                   href="javascript:;">
                                                    Detail
                                                </a>
                                                @if($item['status']==0 && !$item['trans_hash'])
                                                    <a class="link-dark btn-cancel"
                                                       data-id="{{$item['task_trans_id']}}"
                                                       href="javascript:;">
                                                        Cancel
                                                    </a>
                                                    <a class="link-dark"
                                                       href="{{route('task_trans.edit')}}?task_trans_id={{$item['task_trans_id']}}">
                                                        Edit
                                                    </a>
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
        function copyToBoard(text) {
            try {
                const input = document.createElement('textarea')
                input.value = text
                document.body.appendChild(input)
                input.focus()
                input.select()
                document.execCommand('copy')
                document.body.removeChild(input)
            } catch (err) {
                // ignore
            }
        }

        $('.copy-btn').click(function () {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText($(this).data('address'));
            } else {
                copyToBoard($(this).data('address'));
            }
            toastr.success("Copied");
        })
        const task_data_dict = JSON.parse('{!!json_encode($task_data_dict) !!}');
        const template_dict = JSON.parse('{!! json_encode($template_dict) !!}');
        const template_ele = $('#template_id');
        const chain_ele = $('#chain');
        const task_data_id_ele = $('#task_data_id');
        chain_ele.change(function () {
            let template_data = template_dict[$(this).val()];
            if (!template_data) {
                template_data = [];
            }
            let html = '<option value="">select</option>';
            for (let i = 0; i < template_data.length; i++) {
                html += '<option value="' + template_data[i].value + '">' + template_data[i].key + '</option>';
            }
            template_ele.html(html);
            setTimeout(function () {
                template_ele.change();
            }, 1000);
            let task_data = task_data_dict[$(this).val()];
            if (!task_data) {
                task_data = [];
            }
            html = '<option value="">select</option>';
            for (let i = 0; i < task_data.length; i++) {
                html += '<option value="' + task_data[i].value + '">' + task_data[i].key + '</option>';
            }
            task_data_id_ele.html(html);
            setTimeout(function () {
                task_data_id_ele.change();
            }, 1000);
        });
        chain_ele.change();
        @if($task_data_id)
        task_data_id_ele.val({{$task_data_id}});
        @endif
        @if($template_id)
        template_ele.val({{$template_id}});
        @endif
    </script>
    <script>
        $('.trigger-modal').on('click', function () {
            let modal = $($(this).data('target'));
            let body = modal.find('.modal-body');
            body.html('');
            body.load($(this).data('href'), function () {
                modal.modal({show: true});
            });
        });
        $('.btn-cancel').click(function () {
            var id = $(this).data('id');
            var ret = confirm('Are you sure you want to cancel the transaction: ' + id + '?');
            if (!ret) {
                return;
            }
            $.ajax({
                url: '{{route('task_trans.cancel')}}?task_trans_id=' + id + "&_token={{csrf_token()}}",
                type: 'POST',
                success: function (data) {
                    if (data.code == 0) {
                        toastr.success('success');
                        setTimeout(function () {
                            window.location.href = "{{route('task_trans.index')}}";
                        }, 1000);
                    } else {
                        toastr.error(data.msg);
                    }
                }
            });
        });
    </script>
@endsection
