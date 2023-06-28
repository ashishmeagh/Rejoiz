@extends('admin.layout.master') @section('main_content')
<!-- Page Content -->
<div id="page-wrapper">
   <div class="container-fluid">
      <div class="row bg-title">
         <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">{{$page_title or ''}}</h4>
         </div>
         <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
               <li><a href="{{ url(config('app.project.admin_panel_slug').'/dashboard') }}">Dashboard</a></li>
               <li><a href="{{ url('/admin/commission_reports') }}">{{$module_title or ''}}</a></li>
               <li class="active">{{$page_title or ''}}</li>
            </ol>
         </div>
         <!-- /.col-lg-12 -->
      </div>
      <!-- .row -->
      <div class="row">
         <div class="col-sm-12">
            <div class="white-box">
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
               <div class="form-group row">
                <div class="col-md-2"></div>
               <div class="col-sm-8 offset-md-2">
                  <div class="row">
                     <div class="col-sm-12">
                        <div class="row">
                           <label class="col-sm-4">Order No</label>
                           
                           <div class="col-sm-8">
                              <span>
                                 <?php 
                                 if(isset($transaction_details['order_no']) && $transaction_details['order_no'] != '')
                                 {
                                    echo '<span class="label label-warning">'.$transaction_details['order_no'].'</span>';
                                 }
                                 else
                                 {
                                  echo 'N/A';
                                 }

                                 ?>
                              </span>
                           </div>
                        </div>

                        <div class="row">
                           <label class="col-sm-4">Transaction ID</label>
                           
                           <div class="col-sm-8">
                              <span>
                                 <?php 
                                    if(isset($transaction_details['transfer_id']) && $transaction_details['transfer_id'] != 'N/A')
                                    {
                                       echo '<span class="label label-info">'.$transaction_details['transfer_id'].'</span>';
                                    }
                                    else
                                    {
                                     echo 'N/A';
                                    }

                                 ?>
                              </span>
                           </div>
                        </div>

                        <div class="row">
                           <label class="col-sm-4">Commission Amount</label>
                           
                           <div class="col-sm-8">
                              <span>
                                 ${{ isset($transaction_details['commission_amount'])?num_format($transaction_details['commission_amount']):'0.00' }}
                              </span>
                           </div>
                        </div>

                        <div class="row">
                           <label class="col-sm-4">Order Amount</label>
                           
                           <div class="col-sm-8">
                              <span>
                                 ${{ isset($transaction_details['order_amount'])?num_format($transaction_details['order_amount']):'0.00' }}
                              </span>
                           </div>
                        </div>

                        <div class="row">
                           <label class="col-sm-4">Vendor</label>
                           
                           <div class="col-sm-8">
                              <span>
                                 {{isset($transaction_details['vendor_name'])?$transaction_details['vendor_name']:'N/A'}}
                              </span>
                           </div>
                        </div>

                        @if(isset($transaction_details['rep_name']) && $transaction_details['rep_name'] !=" ")
                           <div class="row">
                              <label class="col-sm-4">Representative</label>
                              
                              <div class="col-sm-8">
                                 <span>
                                    {{isset($transaction_details['rep_name'])?$transaction_details['rep_name']:''}}
                                 </span>
                              </div>
                           </div>
                        @endif

                        @if(isset($transaction_details['sales_man_name']) && $transaction_details['sales_man_name'] !=" ")
                           <div class="row">
                              <label class="col-sm-4">Sales Manager</label>
                              
                              <div class="col-sm-8">
                                 <span>
                                    {{isset($transaction_details['sales_man_name'])?$transaction_details['sales_man_name']:''}}
                                 </span>
                              </div>
                           </div>
                        @endif

                        <div class="row">
                           <label class="col-sm-4">Retailer</label>
                           
                           <div class="col-sm-8">
                              <span>
                                 {{isset($transaction_details['retailer_name'])?$transaction_details['retailer_name']:''}}
                              </span>
                           </div>
                        </div>

                        <div class="row">
                           <label class="col-sm-4">Payment Status</label>
                           
                           <div class="col-sm-8">
                              <span>
                                 <?php
                                    $txn_status = isset($transaction_details['status'])?$transaction_details['status']:'';

                                    if($txn_status == '2')
                                    {
                                      echo '<span class="label label-success">Paid</span>';
                                    }
                                    if($txn_status == '3')
                                    {
                                      echo '<span class="label label-info">Failed</span>';
                                    }
                                    if($txn_status == '1' || $txn_status == '')
                                    {
                                      echo '<span class="label label-warning">Pending</span>';
                                    }
                                 ?>
                              </span>
                           </div>
                        </div>

                     </div>
                  </div>
               </div>
             </div>
               <div class="form-group row">
                  <div class="col-sm-8 offset-md-2 text-center">
                     <a class="btn btn-inverse waves-effect waves-light" href="{{ url('/admin/commission_reports') }}"> <i class="fa fa-arrow-left"></i> Back</a>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<!-- END Main Content -->
@stop


