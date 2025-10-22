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
                                    <a class="link-dark" href="{{route('account.index')}}">
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
                                        <th>Name</th>
                                        <th>Accounts</th>
                                        <th>Tasks</th>
                                        <th>Created</th>
                                        <th>Updated</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($list['data'] as $item)
                                        <tr>
                                            <td>{{$item['task_data_id']}}</td>
                                            <td>{{$item['chain']->name}}</td>
                                            <td>{{$item['name']}}</td>
                                            <td>
                                                <a class="link-dark"
                                                   href="{{route('task_data.account')}}?task_data_id={{$item['task_data_id']}}">
                                                    {{$item['accounts']}}
                                                </a>
                                            </td>
                                            <td>
                                                <a class="link-dark"
                                                   href="{{route('task.index')}}?task_data_id={{$item['task_data_id']}}">
                                                    {{$item['tasks']}}
                                                </a>
                                            </td>
                                            <td>{{$item['created_at']}}</td>
                                            <td>{{$item['updated_at']}}</td>
                                            <td>
                                                    <a class="link-dark" href="{{route('task.create_view')}}?task_data_id={{$item['task_data_id']}}">
                                                        <i class="fa fa-plus"></i> create
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

        $('.openPopup').on('click', function () {
            var dataURL = $(this).attr('data-href');
            $('.modal-body').load(dataURL, function () {
                $('#myModal').modal({show: true});
            });
        });
    </script>
@endsection
