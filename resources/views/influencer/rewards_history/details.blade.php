@extends('influencer.layout.master')                
@section('main_content')
<style>
.row{
     padding-bottom: 20px;
  }
.input-break{
  word-break: break-all;
}
</style> 

<!-- Page Content -->
<link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/data-tables/latest/dataTables.bootstrap.min.css">
  <div id="page-wrapper">
    <div class="container-fluid">
      <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
          <h4 class="page-title">{{$page_title or ''}}</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
          <ol class="breadcrumb">
            <li>
              <a href="{{ url(config('app.project.influencer_panel_slug').'/dashboard') }}">Dashboard</a>
            </li>
            <li>
              <a href="{{$module_url_path}}">{{$module_title or ''}}</a>
            </li>
            <li class="active">{{$page_title or ''}}</li>
          </ol>
        </div>
        <!-- /.col-lg-12 -->
      </div>
      <!-- .row -->
      <div class="row">
        <div class="col-sm-12">
          <div class="">
            @include('influencer.layout._operation_status')
            <div class="row">
              <div class="col-sm-12 col-xs-12">
                <h3>
                  <span class="text-" ondblclick="scrollToButtom()" style="cursor: default;" title="Double click to Take Action" ></span>
                </h3>
              </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                  <div class=" white-box">
                    <div class="col-sm-12 admin_profile">
                      <div class="row">
                        <div class="col-sm-12">
                          <div class="row">
                            <div class="col-sm-12 col-md-4 col-lg-3">
                              <div class="input-break">
                                <label><b>Influencer Name</b></label>
                                  @php
                                    $first_name = isset($arr_data['influencer_details']['first_name']) && $arr_data['influencer_details']['first_name'] !=""  ?$arr_data['influencer_details']['first_name']:'--';
                                    $last_name  = isset($arr_data['influencer_details']['last_name']) && $arr_data['influencer_details']['last_name'] !=""  ?$arr_data['influencer_details']['last_name']:'--';
                                  @endphp
                                  <div class="form-group">
                                    <span>{{ $first_name.' '.$last_name }}</span>
                                  </div>
                              </div>
                            </div>

                            <div class="col-sm-12 col-md-4 col-lg-3">
                              <label><b>Admin Settled Sales Target</b></label>
                                @php
                                  $admin_settled_sales_target  = isset($arr_data['admin_settled_sales_target']) && $arr_data['admin_settled_sales_target'] !=""  ?num_format($arr_data['admin_settled_sales_target']):'--';
                                @endphp
                              <div class="form-group">
                                <span>${{ $admin_settled_sales_target or '--' }}</span>
                              </div>
                            </div>

                            <div class="col-sm-12 col-md-4 col-lg-3">
                              <label><b>Admin Settled Reward Amount</b></label>
                                @php
                                  $admin_settled_reward_amount  = isset($arr_data['admin_settled_reward_amount']) && $arr_data['admin_settled_reward_amount'] !=""  ?num_format($arr_data['admin_settled_reward_amount']):'--';
                                @endphp
                              <div class="form-group">
                                <span>${{ $admin_settled_reward_amount or '--' }}</span>
                              </div>
                            </div>

                            <div class="col-sm-12 col-md-4 col-lg-3">
                              <label><b>Influencer Reward Amount</b></label>
                                @php
                                  $reward_amount  = isset($arr_data['reward_amount']) && $arr_data['reward_amount'] !=""  ?num_format($arr_data['reward_amount']):'--';
                                @endphp
                              <div class="form-group">
                                <span>${{ $reward_amount or '--' }}</span>
                              </div>
                            </div>

                            <div class="col-sm-12 col-md-4 col-lg-3">
                              <label><b>Current Order Amount</b></label>
                              @php
                                $current_order_amount = isset($arr_data['current_order_amount']) && $arr_data['current_order_amount'] !=""  ?num_format($arr_data['current_order_amount']):'--';
                              @endphp
                              <div class="form-group">
                                <span>${{ $current_order_amount or '--'}}</span>
                              </div>
                            </div>

                            <div class="col-sm-12 col-md-4 col-lg-3">
                              <label><b>Previous Carry Forward Amount</b></label>
                              @php
                                $previous_carry_forward_amount = isset($arr_data['previous_carry_forward_amount']) && $arr_data['previous_carry_forward_amount'] !=""  ?num_format($arr_data['previous_carry_forward_amount']):'--';
                              @endphp
                              <div class="form-group">
                                <span>${{ $previous_carry_forward_amount or '--'}}</span>
                              </div>
                            </div>

                            <div class="col-sm-12 col-md-4 col-lg-3">
                              <label><b>Total Order Amount</b></label>
                              @php
                                $total_order_amount = isset($arr_data['total_order_amount']) && $arr_data['total_order_amount'] !=""  ?num_format($arr_data['total_order_amount']):'--';
                              @endphp
                              <div class="form-group">
                                <span>${{ $total_order_amount or '--'}}</span>
                              </div>
                            </div>

                          
                            <div class="col-sm-12 col-md-4 col-lg-3">
                              <label><b>Used Order Amount</b></label>
                              @php
                                $used_order_amount = isset($arr_data['used_order_amount']) && $arr_data['used_order_amount'] !=""  ?num_format($arr_data['used_order_amount']):'--';
                              @endphp
                              <div class="form-group">
                                <span>${{ $used_order_amount or '--' }} </span>
                              </div>
                            </div>

                            <div class="col-sm-12 col-md-4 col-lg-3">
                              <label><b>Carry Forward Amount</b></label>
                               @php
                                  $carry_forward_amount = isset($arr_data['carry_forward_amount']) && $arr_data['carry_forward_amount'] !=""  ?num_format($arr_data['carry_forward_amount']):'--'; 
                                @endphp
                              <div class="form-group">
                                <span>${{ $carry_forward_amount or '--' }}</span>
                              </div>
                            </div>

                            <div class="col-sm-12 col-md-4 col-lg-3">
                              <label><b>Status</b></label>
                              @php
                                $status_label = '';
                                $status = isset($arr_data['status'])?$arr_data['status']:'';
                                if($status != '' && $status == '1')
                                {
                                  $status_label = '<span class="label label-warning">Pending</span>';
                                }
                                else if($status != ''&& $status == '2')
                                {
                                  $status_label = '<span class="label label-success">Success</span>';
                                }
                                else if($status != ''&& $status == '3')
                                {
                                  $status_label = '<span class="label label-danger">Failed</span>';
                                }
                              @endphp

                              <div class="form-group">
                                <span>{!! $status_label !!}</span>
                              </div>
                            </div>

                            <div class="col-sm-12">
                                <label><b>Description</b></label>
                                <div class="form-group">
                                  <span>{{ isset($arr_data['description'])?$arr_data['description']:'--' }}</span>
                                </div>
                            </div>

                            <!-- Status = 2 (Success)-->
                            @if($status == '2')
                              <div class="col-sm-12 col-md-4 col-lg-3">
                                  <label><b>Transfer Id</b></label>
                                <div class="form-group">
                                  <span>{{ isset($arr_data['transfer_id'])?$arr_data['transfer_id']:'--' }}</span>
                                </div>
                              </div>

                              <div class="col-sm-12 col-md-4 col-lg-3">
                                  <label><b>Transaction Id</b></label>
                                <div class="form-group">
                                  <span>{{ isset($arr_data['transaction_id'])?$arr_data['transaction_id']:'--' }}</span>
                                </div>
                              </div>

                              <div class="col-sm-12 col-md-4 col-lg-3">
                                <label><b>Destination Payment</b></label>
                                <div class="form-group">
                                  <span>{{ isset($arr_data['destination_payment'])?$arr_data['destination_payment']:'--' }}</span>
                                </div>
                              </div>
                            @endif

                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-md-12">
                  <div class="white-box">

                    @php
                      $current_total_order_amount = 0;
                    @endphp

                    <div class="table-responsive">
                      <input type="hidden" name="multi_action" value="" />
                      <table class="table table-striped"  id="table_module" >
                        <thead>
                          <tr>
                            <th>Order No</th>
                            <th>Order Date</th>
                            <th>Total Amount</th>
                            <th>Promo Code</th>
                          </tr>
                        </thead>
                        <tbody>
                          @foreach($arr_order_data as $data)
                            <tr>
                              @php
                                $order_id = isset($data['id'])?base64_encode($data['id']):0;
                                $total_retail_price = isset($data['total_retail_price'])?num_format($data['total_retail_price']):'0';
                                $current_total_order_amount += $total_retail_price;
                              @endphp
                              <td> 
                                <a target="_blank" href="{{$customer_orders_path}}/view/{{$order_id}}">
                                  {{ $data['order_no'] or '--' }}  
                                </a>
                              </td>
                              <td> 
                                {{ isset($data['created_at'])?us_date_format($data['created_at']):'--' }}
                              </td>
                              <td> 
                                ${{ $total_retail_price or '--' }} 
                              </td>
                              <td> {{ $data['promo_code'] or '--' }}  </td>
                            </tr>
                          @endforeach
                        </tbody>
                      </table>
                    </div>

                    <div class="col-sm-6 pull-right viewsummaryamtbox">
                      <div class="row totalrow">
                        <div class="left">
                          <h3>Current Order Total Amount :</h3>
                        </div>
                        <div class="right">
                          <span><i class="fa fa-usd" aria-hidden="true"></i>{{  isset($current_total_order_amount)?num_format($current_total_order_amount):'0' }}</span>
                        </div>
                      </div>
                    </div>

                  </div>
                </div>
            </div>      
            <div class="form-group row">
              <div class="col-md-12 text-left">
                <a class="btn btn-success waves-effect waves-light" href="{{$module_url_path or ''}}">
                  <i class="fa fa-arrow-left"></i> Back
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
    <!-- END Main Content -->
  

<script type="text/javascript">

  var module_url_path = '{{$module_url_path}}';

  $(document).ready(function() 
  {
      var table =  $('#table_module').DataTable({language: {
            'loadingRecords': '&nbsp;',
            'processing': '<div class="spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>'
      },  
          stateSave: true
      });

      $("#table_module").on('page.dt', function (){
        var info = table.page.info();
      });
  });

</script>
@stop