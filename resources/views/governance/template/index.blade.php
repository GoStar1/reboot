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
                                        <label for="filter_chain">chain</label>
                                        <select id="filter_chain" name="chain" class="select2" style="width: 100%;">
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
                                    <a class="link-dark" href="{{route('template.edit')}}">
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
                                        <th>Address</th>
                                        <th>Methods</th>
                                        <th>Updated</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($list['data'] as $item)
                                        <tr>
                                            <td>{{$item['template_id']}}</td>
                                            <td>{{$item['chain']->name}}</td>
                                            <td>{{$item['name']}}</td>
                                            <td>
                                                <a target="_blank"
                                                   href="{{ $item['chain']->explorer() }}/address/{{$item['contract']}}">
                                                    {{substr($item['contract'],0,5).'..'.substr($item['contract'],38)}}
                                                </a>
                                                &nbsp;&nbsp;
                                                <i style="cursor: pointer" data-address="{{$item['contract']}}"
                                                   class="fa fa-copy copy-btn"/>
                                            </td>
                                            <td style="max-width:300px;word-wrap:break-word;">
                                                {!!$item['methods']!!}
                                            </td>
                                            <td>{{$item['updated_at']}}</td>
                                            <td>
                                                <a data-href="{{route('template.log')}}?template_id={{$item['template_id']}}"
                                                   data-toggle="modal"
                                                   class="link-dark trigger-modal" data-target="#modal-detail"
                                                   href="javascript:;">
                                                    Logs
                                                </a>
                                                &nbsp;&nbsp;
                                                <a data-href="{{route('template.read')}}?template_id={{$item['template_id']}}"
                                                   data-toggle="modal"
                                                   class="link-dark trigger-modal" data-target="#modal-detail"
                                                   href="javascript:;">
                                                    Read
                                                </a>
                                                &nbsp;&nbsp;
                                                <a data-href="{{route('template.write')}}?template_id={{$item['template_id']}}"
                                                   data-toggle="modal"
                                                   class="link-dark trigger-modal" data-target="#modal-detail"
                                                   href="javascript:;">
                                                    Write
                                                </a>
                                                &nbsp;&nbsp;
                                                <a href="{{route('template.edit')}}?template_id={{$item['template_id']}}"
                                                   class="link-dark">
                                                    <i class="fa fa-edit"></i>
                                                    edit
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
        $('.trigger-modal').on('click', function () {
            let modal = $($(this).data('target'));
            let body = modal.find('.modal-body');
            body.html('');
            body.load($(this).data('href'), function () {
                modal.modal({show: true});
            });
        });
    </script>
@endsection
