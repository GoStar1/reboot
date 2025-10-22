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
                <form action="" id="searchForm">
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
                                        <label for="task_data_id">TaskData</label>
                                        <select id="task_data_id" name="task_data_id" class="select2"
                                                style="width: 100%;">
                                            <option value="">select</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="form-group form-group-sm">
                                        <label for="token_id">Asset</label>
                                        <select id="token_id" name="token_id" class="select2" style="width: 100%;">
                                            <option value="">select</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-3"></div>
                                <div class="col-3">
                                    <div class="form-group form-group-sm">
                                        <label for="balance">Balance</label>
                                        <input id="balance" name="balance" type="search" class="form-control"
                                               placeholder="Min balance" value="{{$balance}}">
                                        <div class="input-group-append">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="balance_type"
                                                       value="0" {{$balance_type==1?'':'checked'}}>
                                                <label class="form-check-label">Min</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="balance_type"
                                                       value="1" {{$balance_type==1?'checked':''}}>
                                                <label class="form-check-label">Max</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="form-group form-group-sm">
                                        <label for="asset_min">Asset Min</label>
                                        <input id="asset_min" name="asset_min" type="search" class="form-control"
                                               placeholder="Min balance" value="{{$asset_min}}">
                                        <div class="input-group-append">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="asset_type" value="0"
                                                    {{$asset_type=='1'?'':'checked'}}>
                                                <label class="form-check-label">Min</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="asset_type"
                                                       value="1" {{$asset_type=='1'?'checked':''}}>
                                                <label class="form-check-label">Max</label>
                                            </div>
                                        </div>
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
                                    <div class="row">
                                        <form id="CreateTaskDataForm" class="form-inline"
                                              action="{{route('task_data.create_view')}}"
                                              method="GET">
                                            <input type="hidden" name="accountArgs" value="{{http_build_query($_GET)}}">
                                            <button {{!$chain?'disabled':''}}  type="submit"
                                                    class="btn btn-default btn-primary">
                                                <i class="fa fa-plus"></i>
                                                CreateTaskData
                                            </button>
                                        </form>
                                        &nbsp;&nbsp;&nbsp;&nbsp;
                                        <button class="btn btn-default btn-primary" data-target="#modal-create"
                                                data-toggle="modal">
                                            <i class="fa fa-plus"></i> create
                                        </button>
                                        &nbsp;&nbsp;&nbsp;&nbsp;
                                        <form class="form-inline"
                                              action="{{route('account.export')}}?{{http_build_query($_GET)}}"
                                              method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-default btn-primary">
                                                CSV
                                            </button>
                                        </form>
                                        &nbsp;&nbsp;&nbsp;&nbsp;
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Chain</th>
                                        <th>Address</th>
                                        <th>ETH&nbsp;@if($chain)
                                                <button data-token="{{csrf_token()}}"
                                                        class="btn btn-secondary btn-sm btn-update-asset"
                                                        data-href="{{route('account.update-eth')}}?{{http_build_query($_GET)}}"
                                                        data-loading-text='<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>update'
                                                >
                                                    update
                                                </button>
                                            @endif</th>
                                        @if($token_id)
                                            <th>{{$token_name}}&nbsp;
                                                <button class="btn btn-secondary btn-sm btn-update-asset"
                                                        data-token="{{csrf_token()}}"
                                                        data-href="{{route('account.update-erc20')}}?token_id={{$token_id}}&{{http_build_query($_GET)}}"
                                                        data-loading-text='<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>update'>
                                                    update
                                                </button>
                                            </th>
                                        @endif
                                        <th>Tags</th>
                                        <th>Pending</th>
                                        <th>TransId</th>
                                        <th>Updated</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($list['data'] as $item)
                                        <tr>
                                            <td>{{$item['account_id']}}</td>
                                            <td>{{$item['chain']->name}}</td>
                                            <td>
                                                <a target="_blank"
                                                   href="{{ $item['chain']->explorer() }}/address/{{$item['address']}}">
                                                    {{substr($item['address'],0,5).'..'.substr($item['address'],38)}}
                                                </a>
                                                &nbsp;&nbsp;
                                                <i style="cursor: pointer" data-address="{{$item['address']}}"
                                                   class="fa fa-copy copy-btn"/>
                                            </td>
                                            <td>{{round($item['balance'],4)}}</td>
                                            @if($token_id)
                                                <td>
                                                    {{round(Arr::get($item,'asset_balance') ,4)}}
                                                </td>
                                            @endif
                                            <td>{{$item['tags']}}</td>
                                            <td>{{$bool_dict[$item['pending']]??'-'}}</td>
                                            <td>{{$item['task_trans_id']}}</td>
                                            <td>{{$item['updated_at']}}</td>
                                            <td>
                                                <a href="{{route('account.edit_view')}}?id={{$item['account_id']}}"
                                                   class="btn-block btn">
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


    <div class="modal fade" id="modal-create">
        <div class="modal-dialog modal-sm">
            <form id="create-form" action="{{route('account.add')}}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Add Account</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="chain">Chain</label>
                            <div class="form-group">
                                @foreach(\App\Enums\Chain::cases() as $_chain)
                                    {{--                                    <option value="{{$_chain->value}}">{{$_chain->name}}</option>--}}
                                    <div class="form-check">
                                        <input class="form-check-input" value="{{$_chain->value}}" type="checkbox"
                                               name="chain[]">
                                        <label class="form-check-label">{{$_chain->name}}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="count">Account Count</label>
                            <input id="count" type="number" name="count" class="form-control" style="width: 150px;"/>
                        </div>
                        <div class="form-group">
                            <label for="tags">Tags</label>
                            <input id="tags" name="tags" class="form-control" style="width: 150px;"/>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="save">Save</button>
                    </div>
                </div>
            </form>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
@endsection
@section('script_plus')
    <script>
        const task_data_dict = JSON.parse('{!!json_encode($task_data_dict) !!}');
        const token_dict = JSON.parse('{!! json_encode($token_dict) !!}');
        const token_id_ele = $('#token_id');
        const chain_ele = $('#filter_chain');
        const task_data_id_ele = $('#task_data_id');
        chain_ele.change(function () {
            let token_data = token_dict[$(this).val()];
            if (!token_data) {
                token_data = [];
            }
            let html = '<option value="">select</option>';
            for (let i = 0; i < token_data.length; i++) {
                html += '<option value="' + token_data[i].value + '">' + token_data[i].key + '</option>';
            }
            token_id_ele.html(html);
            setTimeout(function () {
                token_id_ele.change();
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
        @if($token_id)
        token_id_ele.val({{$token_id}});
        @endif
    </script>
    @if($invalid_keyword)
        <script>
            setTimeout(function () {
                toastr.error("Invalid Keyword");
                var keyword = $("#keyword");
                var val = keyword.val();
                keyword.val('').attr('placeholder', val);
            }, 1000);
        </script>
    @endif
    <script>
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

        function checkSearchForm(event) {
            const formData = $(event.target).serializeObject();
            // The array of form data takes the following form:
            // [ { name: 'username', value: 'jresig' }, { name: 'password', value: 'secret' } ]
            // return false to cancel submit
            $keyword = formData['keyword'];
            if ($keyword && $keyword.substr(0, 2) !== '0x' && !(/^[0-9a-zA-Z +-]+$/.test($keyword))) {

                toastr.error("Invalid Keywords!!!Only spaces alphanumeric and +- are allowed,Please refer to mysql fulltext");
                event.preventDefault();
                return false;
            }
            return true;
        }

        function checkCreateData(event) {
            console.log($(event.target).attr('action'));
            const formData = $(event.target).serializeObject();
            // The array of form data takes the following form:
            // [ { name: 'username', value: 'jresig' }, { name: 'password', value: 'secret' } ]
            // return false to cancel submit
            $keyword = formData['keyword'];
            if ($keyword && $keyword.substr(0, 2) !== '0x' && !(/^[0-9a-zA-Z +-]+$/.test($keyword))) {

                toastr.error("Invalid Keywords!!!Only spaces alphanumeric and +- are allowed,Please refer to mysql fulltext");
                event.preventDefault();
                return false;
            }
            return true;
        }

        document.getElementById("searchForm")
            .addEventListener("submit", checkSearchForm);
        document.getElementById("CreateTaskDataForm")
            .addEventListener("submit", checkCreateData);
        $('#create-form').ajaxForm({
            success: function (data) {
                console.log(data);
                if (data.code === 0) {
                    toastr.success('success');
                    setTimeout(function () {
                        window.location.href = "{{route('account.index')}}"
                    }, 1000);
                } else {
                    toastr.error(data.msg);
                }
            },
            beforeSubmit: function (arr, $form, options) {
                const formData = $form.serializeObject();
                const chain = formData['chain[]'];
                console.log({chain})
                if (!chain || chain.length === 0) {
                    toastr.error("Choose at least one public chain");
                    return false;
                }
                // The array of form data takes the following form:
                // [ { name: 'username', value: 'jresig' }, { name: 'password', value: 'secret' } ]
                // return false to cancel submit
                if (formData['count'] === '' || parseInt(formData['count']) === 0) {
                    toastr.error("Input account count");
                    return false;
                }
            },
            error: function (res) {
                if (res.responseJSON) {
                    toastr.error(res.responseJSON.message);
                    return;
                }
                if (res.responseText) {
                    toastr.error(res.responseText);
                }
            },
        });


    </script>
@endsection
