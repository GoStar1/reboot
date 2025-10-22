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
                                        <label for="chain">chain</label>
                                        <select id="chain" name="chain" class="select2" style="width: 100%;">
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
                                        <label>status</label>
                                        <select name="status" class="select2" style="width: 100%;">
                                            <option value="">select</option>
                                            @foreach($status_dict as $key=>$value)
                                                <option value="{{$key}}"
                                                    {{(string)$key===$status?'selected':''}} >{{$value}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-3">
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
                            <div class="card-header">
                                <div class="card-tools">
                                    <div class="input-group input-group-sm">
                                        <a class="btn btn-default" href="{{route("rpc.add_view")}}">
                                            <i class="fa fa-plus"></i> create
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Chain</th>
                                        <th>url</th>
                                        <th>block</th>
                                        <th>Gas Price</th>
                                        <th>time(s)</th>
                                        <th>heartbeat</th>
                                        <th>Status</th>
                                        <th>Priority</th>
                                        <th>update</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($list['data'] as $item)
                                        <tr>
                                            <td>{{$item['id']}}</td>
                                            <td>{{$item['chain']->name}}</td>
                                            <td style="width:20%;word-break: break-all;">{{$item['url']}}</td>
                                            <td>{{$item['block_number']}}</td>
                                            <th class="gasPrice">-</th>
                                            <td>{{$item['resp_time']}}</td>
                                            <td>{{$bool_dict[$item['heartbeat']]??'-'}}</td>
                                            <td>{{$status_dict[$item['status']] ??'-'}}</td>
                                            <td>
                                                <div class="input-group">
                                                    <input type="text" class="form-control priority-input"
                                                           style="width:50px" value="{{$item['priority']}}">
                                                    <span class="input-group-append data-order-input"
                                                          data-id="{{$item['id']}}">
                                                        <span class="input-group-text">
                                                            <i class="fa fa-save"></i></span>
                                                        </span>
                                                </div>
                                            </td>
                                            <td>{{$item['updated_at']}}</td>
                                            <td>
                                                <a href="{{route('rpc.edit_view')}}?id={{$item['id']}}"
                                                   class="btn-block btn">
                                                    <i class="fa fa-edit"></i>
                                                    edit
                                                </a>
                                                <a href="javascript:;"
                                                   class="btn-gas-price btn" data-url="{{$item['url']}}">
                                                    gasPrice
                                                </a>
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
@endsection
@section('script_plus')
    <script>
        $('.btn-gas-price').click(function () {
            var obj = $(this);
            window.obj = obj;
            var url = obj.data('url');
            $.get('{{route('rpc.gasPrice')}}?url=' + url, function (data) {
                if (data.code == 0) {
                    obj.parents('tr').find('.gasPrice').html(data.data);
                }
            });
        });
        $('.data-order-input').click(function () {
            var $this = $(this);
            var id = $this.data('id');
            var input = $this.closest('div').find('input');
            var order = input.val();
            $.ajax({
                type: 'POST',
                url: '{{route('rpc.set-order')}}',
                data: {
                    'id': id,
                    'order': order,
                    '_token':'{{csrf_token()}}'
                },
                success: function (response) {
                    if (response['code'] == 0) {
                        window.location.reload();
                    } else {
                        toastr.error(response.msg);
                    }
                }
            });
        });
    </script>
@endsection
