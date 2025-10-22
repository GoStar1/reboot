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
                                <input type="hidden" name="task_data_id" value="{{$task_data_id}}">
                                <div class="col-3">
                                    <div class="form-group form-group-sm">
                                        <label for="token_id">Asset</label>
                                        <select id="token_id" name="token_id" class="select2" style="width: 100%;">
                                            <option value="">select</option>
                                            @foreach($token_dict as $key=>$value)
                                                <option value="{{$key}}"
                                                    {{$key==$chain?'selected':''}} >{{$value}}</option>
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
                                    <form class="form-inline"
                                          action="{{route('account.export')}}?task_data_id={{$task_data_id}}"
                                          method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-default btn-primary">
                                            CSV
                                        </button>
                                    </form>
                                    &nbsp;&nbsp;&nbsp;&nbsp;
                                </div>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Address</th>
                                        <th>Balance
                                            <button data-token="{{csrf_token()}}"
                                                    class="btn btn-secondary btn-sm btn-update-asset"
                                                    data-href="{{route('account.update-eth')}}?chain={{$chain->value}}&task_data_id={{$task_data_id}}"
                                                    data-loading-text='<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>update'
                                            >
                                                update
                                            </button>
                                        </th>
                                        @if($token_id)
                                            <th>{{$token_dict[$token_id]}}&nbsp;
                                                <button class="btn btn-secondary btn-sm btn-update-asset"
                                                        data-token="{{csrf_token()}}"
                                                        data-href="{{route('account.update-erc20')}}?chain={{$chain->value}}&token_id={{$token_id}}&task_data_id={{$task_data_id}}"
                                                        data-loading-text='<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>update'>
                                                    update
                                                </button>
                                            </th>
                                        @endif
                                        <th>Data</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($list['data'] as $item)
                                        <tr>
                                            <td>{{$item['task_account_id']}}</td>
                                            <td><a target="_blank"
                                                   href="{{ $chain->explorer() }}/address/{{$item['address']}}">
                                                    {{substr($item['address'],0,5).'..'.substr($item['address'],38)}}
                                                </a>
                                                &nbsp;&nbsp;
                                                <i style="cursor: pointer" data-address="{{$item['address']}}"
                                                   class="fa fa-copy copy-btn"/></td>
                                            <td>{{$item['balance']}}</td>
                                            @if($token_id)
                                                <td>
                                                    {{$item['asset_balance']}}
                                                </td>
                                            @endif
                                            <td>{{json_encode($item['data'])}}</td>
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
        $('.btn-update-asset').click(function () {
            let btn = $(this);
            let btn_content = btn.html();
            btn.html(btn.data('loading-text'));
            $.ajax({
                type: 'POST',
                url: btn.data('href'),
                data: "_token=" + btn.data('token'),
                success: function ({code, data, msg}) {
                    btn.html(btn_content);
                    if (code === 0) {
                        toastr.success('success');
                        setTimeout(function () {
                            window.location.reload();
                        }, 2000);
                    } else {
                        toastr.error(msg);
                    }
                },
                error: function (res) {
                    btn.html(btn_content);
                    console.log(res);
                    if (res.responseJSON) {
                        toastr.error(res.responseJSON.message);
                        return;
                    }
                    if (res.responseText) {
                        toastr.error(res.responseText);
                    }
                },
            });
        })
    </script>
@endsection
