@extends('maker.layout.master')                
@section('main_content')
<!-- Page Content -->
<style type="text/css">
    .row{
    padding-bottom: 20px;
    }
</style>
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">{{$page_title or ''}}</h4>
            </div>
            <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
                <ol class="breadcrumb">
                    <li><a href="{{ url(config('app.project.maker_panel_slug').'/dashboard') }}">Dashboard</a></li>
                    <li><a href="{{$module_url_path}}">{{$module_title or ''}}</a></li>
                    <li class="active">{{$page_title or ''}}</li>
                </ol>
            </div>
        </div>
        <!-- .row -->
        <div class="row">
            <div class="col-sm-12">
                <div class="white-box my-representative--profile-page">
                    @include('admin.layout._operation_status')
                    <div class="row">
                        <div class="col-sm-12 col-xs-12">
                            <h3>
                                <span 
                                    class="text-" ondblclick="scrollToButtom()" style="cursor: default;" title="Double click to Take Action" >
                                </span>
                            </h3>
                        </div>
                    </div>
                    <div class="row">
                    <div class="col-xs-12 col-sm-3 col-md-3 col-lg-2 left">
                        @php
                        $kyc_status = isset($arr_data['get_user_details']['kyc_status'])?$arr_data['get_user_details']['kyc_status']:0;
                        @endphp
                        <div class="main-adm-profile">
                            @php
                            $profile_image = getProfileImage($arr_data['get_user_details']['profile_image']);
                            @endphp
                            <div class="imgview-profile-adm"><img src="{{ $profile_image or ''}}"></div>
                            <div class="profile-txtnw-adm">Profile Image</div>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-10 right">
                        <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-3 box">
                                <label>Name</label>
                                <div>
                                    @php
                                    $first_name = isset($arr_data['get_user_details']['first_name']) && $arr_data['get_user_details']['first_name'] !=""  ?$arr_data['get_user_details']['first_name']:'NA';
                                    $last_name  = isset($arr_data['get_user_details']['last_name']) && $arr_data['get_user_details']['last_name'] !=""  ?$arr_data['get_user_details']['last_name']:'NA';
                                    @endphp
                                    <span>{{ $first_name.' '.$last_name }}</span>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-3 box">
                                <label>Zip/Postal code</label>
                                <div>
                                    <span>{{ isset($arr_data['get_user_details']['post_code']) && $arr_data['get_user_details']['post_code'] !=""  ?$arr_data['get_user_details']['post_code']:'NA' }}</span>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-3 box">
                                <label>Email Id</label>
                                <div>
                                    <span>{{ isset($arr_data['get_user_details']['email']) && $arr_data['get_user_details']['email'] !=""  ?$arr_data['get_user_details']['email']:'NA' }}</span>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 box">
                                <label>Description</label>
                                <div>
                                    <span>{{ isset($arr_data['description']) && $arr_data['description'] !=""  ?$arr_data['description']:'NA' }}</span>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-3 box">
                                <label>Commission</label>
                                <div>
                                    <span>{{ isset($arr_data['get_user_details']['commission']) && $arr_data['get_user_details']['commission'] !=""  ?$arr_data['get_user_details']['commission']:'NA' }}</span>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-3 box">
                                <label>Sales Manager Name</label>
                                <div>
                                    @php
                                    $first_name = isset($arr_data['sales_manager_details']['get_user_data']['first_name']) && $arr_data['sales_manager_details']['get_user_data']['first_name'] !=""  ?$arr_data['sales_manager_details']['get_user_data']['first_name']:'NA';
                                    $last_name  = isset($arr_data['sales_manager_details']['get_user_data']['last_name']) && $arr_data['sales_manager_details']['get_user_data']['last_name'] !=""  ?$arr_data['sales_manager_details']['get_user_data']['last_name']:'NA';
                                    @endphp
                                    <span>{{ $first_name.' '.$last_name }}</span>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-3 box">
                                <label>Area Name</label>
                                <div>
                                    <span>{{ isset($arr_data['get_area_details']['area_name']) && $arr_data['get_area_details']['area_name'] !=""  ?$arr_data['get_area_details']['area_name']:'NA' }}</span>
                                </div>
                            </div>
                        </div>
                        {{-- 
                        <table class="table table-striped table-bordered view-porifile-table">
                            <tr>
                                <th> Representative Name</th>
                                <td>
                                    @php
                                    $first_name = isset($arr_data['get_user_details']['first_name']) && $arr_data['get_user_details']['first_name'] !=""  ?$arr_data['get_user_details']['first_name']:'NA';
                                    $last_name  = isset($arr_data['get_user_details']['last_name']) && $arr_data['get_user_details']['last_name'] !=""  ?$arr_data['get_user_details']['last_name']:'NA';
                                    @endphp
                                    {{ $first_name.' '.$last_name }}
                                </td>
                            </tr>
                            <tr>
                                <th>Email Id</th>
                                <td>{{ isset($arr_data['get_user_details']['email']) && $arr_data['get_user_details']['email'] !=""  ?$arr_data['get_user_details']['email']:'NA' }}</td>
                            </tr>
                            <tr>
                                <th>Zip/Postal code</th>
                                <td>{{ isset($arr_data['get_user_details']['post_code']) && $arr_data['get_user_details']['post_code'] !=""  ?$arr_data['get_user_details']['post_code']:'NA' }}</td>
                            </tr>
                            <tr>
                                <th>Description</th>
                                <td>{{ isset($arr_data['description']) && $arr_data['description'] !=""  ?$arr_data['description']:'NA' }}</td>
                            </tr>
                            <tr>
                                <th>Commission</th>
                                <td>{{ isset($arr_data['get_user_details']['commission']) && $arr_data['get_user_details']['commission'] !=""  ?$arr_data['get_user_details']['commission']:'NA' }}</td>
                            </tr>
                            <tr>
                                <th>Sales Manager Name</th>
                                <td> @php
                                    $first_name = isset($arr_data['sales_manager_details']['get_user_data']['first_name']) && $arr_data['sales_manager_details']['get_user_data']['first_name'] !=""  ?$arr_data['sales_manager_details']['get_user_data']['first_name']:'NA';
                                    $last_name  = isset($arr_data['sales_manager_details']['get_user_data']['last_name']) && $arr_data['sales_manager_details']['get_user_data']['last_name'] !=""  ?$arr_data['sales_manager_details']['get_user_data']['last_name']:'NA';
                                    @endphp
                                    {{ $first_name.' '.$last_name }}
                                </td>
                            </tr>
                            <tr>
                                <th>Area Name</th>
                                <td>{{ isset($arr_data['get_area_details']['area_name']) && $arr_data['get_area_details']['area_name'] !=""  ?$arr_data['get_area_details']['area_name']:'NA' }}</td>
                            </tr>
                            </tr>
                            </tr>
                        </table>
                        --}}
                    </div>
                </div>
                    @if($kyc_status == 3)
                    <div class="form-group row">
                        <button class="btn btn-success" onclick="perform_kyc_action({{$arr_data['get_user_details']['id']}},1)" type="button">Approve</button>
                        &nbsp;&nbsp;&nbsp;&nbsp;
                        <button class="btn btn-danger" onclick="perform_kyc_action({{$arr_data['get_user_details']['id']}},4)" type="button">Reject</button>
                    </div>
                    @endif
                    <div class="clearfix"></div>
                    <div class="form-group">
                        <div class="col-sm-12 text-center">
                            <a class="btn btn-inverse waves-effect waves-light backbtnonly" href="{{$module_url_path or ''}}"><i class="fa fa-arrow-left"></i> Back</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop