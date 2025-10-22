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
                    <form class="form" id="main-form" method="POST" action="{{ route('task_data.create_data') }}">
                        @csrf
                        <div class="form-group row">
                            <label for="address"
                                   class="col-md-4 col-form-label text-md-right">Accounts</label>
                            <div class="col-md-8">
                                <iframe class="col-md-12 col-lg-12 col-sm-12" style="height: 400px;"
                                        src="{{route('task_data.iframe')}}?{{$accountArgs}}"></iframe>
                            </div>
                        </div>
                        <input type="hidden" name="accountArgs" value="{{$accountArgs}}">
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right">Chain</label>
                            <div class="col-md-8">
                                <select name="chain" readonly class="form-control">
                                    <option value="{{$chain->value}}">{{$chain->name}}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right">Name</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="name" value=""
                                       placeholder="taskData name" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right">ETH Amount</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="amount" value=""
                                       placeholder="0.00">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="call_data" class="col-md-4 col-form-label text-md-right">Call Data</label>
                            <div class="col-md-8">
                                <input type="text" id="call_data" class="form-control" name="call_data" value=""
                                       placeholder="data:,...">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="call_data" class="col-md-4 col-form-label text-md-right">Make Order</label>
                            <div class="col-md-8">
                                <input type="checkbox" id="make_order" class="custom-control-input" name="make_order">
                                &nbsp;&nbsp;&nbsp;&nbsp;
                                <label for="make_order" class="custom-control-label" style="vertical-align: middle;">&nbsp;</label>
                            </div>
                        </div>
                        <div id="make_order_list" style="display: none;">
                            <div class="form-group row">
                                <label for="sale_price" class="col-md-4 col-form-label text-md-right">Sale Price</label>
                                <div class="col-md-8">
                                    <input type="text" id="sale_price" class="form-control" name="sale_price" value=""
                                           placeholder="0.01">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="sale_amount" class="col-md-4 col-form-label text-md-right">Sale
                                    Amount</label>
                                <div class="col-md-8">
                                    <input type="text" id="sale_amount" class="form-control" name="sale_amount" value=""
                                           placeholder="100">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="sale_token" class="col-md-4 col-form-label text-md-right">Sale Token</label>
                                <div class="col-md-8">
                                    <input type="text" id="sale_token" class="form-control" name="sale_token" value=""
                                           placeholder="PCLUB">
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="take_order" class="col-md-4 col-form-label text-md-right">Take Order</label>
                            <div class="col-md-8">
                                <input type="checkbox" id="take_order" class="custom-control-input" name="take_order">
                                &nbsp;&nbsp;&nbsp;&nbsp;
                                <label for="take_order" class="custom-control-label" style="vertical-align: middle;">&nbsp;</label>
                            </div>
                        </div>
                        <div id="take_order_list" style="display: none;">
                            <div class="form-group row">
                                <label for="take_count" class="col-md-4 col-form-label text-md-right">Take Count</label>
                                <div class="col-md-8">
                                    <input type="text" id="take_count" class="form-control" name="take_count" value=""
                                           placeholder="10">
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="call_data" class="col-md-4 col-form-label text-md-right">Max Gas Price</label>
                            <div class="col-md-8">
                                <input type="text" id="call_data" class="form-control" name="min_gas_price" value=""
                                       placeholder="min_gas_price">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right">Start Time</label>
                            <div class="col-md-8">
                                <div class="input-group date" id="startTime" data-target-input="nearest">
                                    <input type="text" class="form-control datetimepicker-input"
                                           data-target="#startTime" name="startTime"/>
                                    <div class="input-group-append" data-target="#startTime"
                                         data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right">In Range</label>
                            <div class="col-md-8">
                                <div class="input-group">
                                    <input type="number" class="form-control" name="inRange">
                                    <input type="hidden" id="inRangeType" name="inRangeType" value="Days">
                                    <div class="input-group-append">
                                        <button id="inRangeTypeShow" type="button"
                                                class="btn btn-warning dropdown-toggle" data-toggle="dropdown">Days
                                        </button>
                                        <ul class="dropdown-menu" id="inRangeTypeList">
                                            <li class="dropdown-item btn">Days</li>
                                            <li class="dropdown-item btn">Hours</li>
                                            <li class="dropdown-item btn">Minutes</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="address"
                                   class="col-md-4 col-form-label text-md-right">Templates:</label>
                        </div>
                        <div id="templates">
                        </div>
                        <div class="form-group row">
                            <label
                                class="col-md-4 col-form-label text-md-right"></label>
                            <div class="col-md-8">
                                <div class="text-center">
                                    <a class="btn btn-default btn-primary" data-target="#modal-add-template"
                                       data-toggle="modal">
                                        <i class="fa fa-plus"></i>AddTemplate
                                    </a>
                                    &nbsp;&nbsp;&nbsp;
                                    <button type="submit" class="btn btn-primary">
                                        submit
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @include("governance.component.add_template")
@endsection
