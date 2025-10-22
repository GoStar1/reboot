@extends('layouts.app')

@section('content')
    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <form action="" id="searchForm">
                <div class="row">
                    <div class="col-md-10 offset-md-1">
                        <div class="row">
                            @if($task_data_id)
                                <div class="col-3">
                                    <div class="form-group form-group-sm">
                                        <label>TaskData</label>
                                        <select readonly class="select2"
                                                style="width: 100%;">
                                            <option>{{$task_data_name}}</option>
                                        </select>
                                    </div>
                                </div>
                            @else
                                <div class="col-3">
                                    <div class="form-group form-group-sm">
                                        <label for="filter_chain">chain</label>
                                        <select disabled id="filter_chain" name="chain" class="select2"
                                                style="width: 100%;">
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
                                        <label for="balance">Balance Min</label>
                                        <input disabled id="balance" name="balance" type="search" class="form-control"
                                               placeholder="Min balance" value="{{$balance}}">
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="form-group form-group-sm">
                                        <label for="keyword">keyword</label>
                                        <div class="input-group">
                                            <input disabled id="keyword" name="keyword" type="search"
                                                   class="form-control"
                                                   placeholder="Type your keywords here" value="{{$keyword}}">
                                            <div class="input-group-append">
                                                <button type="submit" class="btn btn-sm btn-default">
                                                    <i class="fa fa-search"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Address</th>
                                    <th>balance</th>
                                    <th>Updated</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($list['data'] as $item)
                                    <tr>
                                        <td>{{$item['account_id']}}</td>
                                        <td>
                                            <a target="_blank"
                                               href="{{ $chain->explorer() }}/address/{{$item['address']}}">
                                                {{substr($item['address'],0,5).'..'.substr($item['address'],38)}}
                                            </a>
                                            &nbsp;&nbsp;
                                            <i style="cursor: pointer" data-address="{{$item['address']}}"
                                               class="fa fa-copy copy-btn"/>
                                        </td>
                                        <td>{{round($item['balance'],4)}}</td>
                                        <td>{{$item['updated_at']}}</td>
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
@endsection
@section('script_plus')
    <script>
        $('.copy-btn').click(function () {
            // Copy the text inside the text field
            navigator.clipboard.writeText($(this).data('address'));
            // Alert the copied text
            toastr.success("Copied");
        })
    </script>
@endsection
