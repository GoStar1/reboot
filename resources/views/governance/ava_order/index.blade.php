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
                                <table class="table table-bordered card-body">
                                    <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Seller</th>
                                        <th>Make Hash</th>
                                        <th>Amount</th>
                                        <th>Price</th>
                                        <th>Taker</th>
                                        <th>Take Hash</th>
                                        <th>Status</th>
                                        <th>Time</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($list['data'] as $item)
                                        <tr>
                                            <td>{{$item['id']}}</td>
                                            <td>
                                                <a target="_blank"
                                                   href="{{ \App\Enums\Chain::Avalanche->explorer() }}/address/{{$item['seller']}}">
                                                    {{substr($item['seller'],0,5).'..'.substr($item['seller'],38)}}
                                                </a>
                                                <i style="cursor: pointer" data-address="{{$item['seller']}}"
                                                   class="fa fa-copy copy-btn"/>
                                            </td>
                                            <td>
                                                <a target="_blank"
                                                   href="{{ \App\Enums\Chain::Avalanche->explorer() }}/tx/{{$item['list_id']}}">
                                                    {{substr($item['list_id'],0,5).'..'.substr($item['list_id'],61)}}
                                                </a>
                                            </td>
                                            <td>{{base_convert($item['amount'],16,10)}}</td>
                                            <td>{{bcdiv(base_convert($item['price'],16,10),bcpow(10,18),8)}}</td>
                                            <td>
                                                @if($item['taker'])
                                                    <a target="_blank"
                                                       href="{{ \App\Enums\Chain::Avalanche->explorer() }}/address/{{$item['taker']}}">
                                                        {{substr($item['taker'],0,5).'..'.substr($item['taker'],38)}}
                                                    </a>
                                                    <i style="cursor: pointer" data-address="{{$item['taker']}}"
                                                       class="fa fa-copy copy-btn"/>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if($item['confirm_trans_hash'])
                                                    <a target="_blank"
                                                       href="{{ \App\Enums\Chain::Avalanche->explorer() }}/tx/{{$item['confirm_trans_hash']}}">
                                                        {{substr($item['confirm_trans_hash'],0,5).'..'.substr($item['confirm_trans_hash'],61)}}
                                                    </a>
                                                @endif
                                            </td>
                                            <td>{{$status_dict[$item['status']] ??'-'}}</td>
                                            <td>{{$item['listing_time']}}</td>
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
    </script>
@endsection
