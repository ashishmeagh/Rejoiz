@extends('admin.layout.master')                

@section('main_content')
 <div id="page-wrapper">
<div class="container-fluid">
<div class="row bg-title">
   <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">{{ isset($page_title)?$page_title:"" }}</h4>
   </div>
   <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
         <li><a href="{{URL::previous()}}">Update Commission</a></li>
         <li class="active">{{ isset($page_title)?$page_title:"" }}</li>
      </ol>
   </div>
   <!-- /.col-lg-12 -->
</div>

<!-- BEGIN Main Content -->

<div class="row">
<div class="col-md-12">
<div class="white-box">
   @include('admin.layout._operation_status')
   <div class="row">
      <div class="col-sm-12 col-xs-12">
         <div class="box-title">
            <h3><i class="fa fa-user-plus"></i> {{ isset($page_title)?$page_title:"" }}</h3>
            <div class="box-tool">
            </div>
         </div>
        
         {{-- {!! Form::open([ 'url' => $module_url_path.'/save_shop_story/'.base64_encode($shop_data['maker_id']),
         'method'=>'POST',   
         'class'=>'form-horizontal', 
         'id'=>'validation-form',
         'enctype' =>'multipart/form-data'
         ]) !!} --}}

          <form class="form-horizontal" id="validation-form">
             {{ csrf_field() }}
             <input type="hidden" name="tran_id" id="tran_id" value="{{isset($arr_data['id'])?$arr_data['id']:""}}">

            <input type="hidden" name="maker_id" id="maker_id" value="{{isset($arr_data['maker_id'])?$arr_data['maker_id']:""}}">  
            <input type="hidden" name="rep_id" id="rep_id" value="{{isset($arr_data['representative_id'])?$arr_data['representative_id']:""}}"> 

            <div class="form-group row">
                <label for="description" class="col-2 col-form-label">Representative Name</label>
                <div class="col-10">
                    <label for="description" class="col-2 col-form-label">{{isset($arr_data['representative_details']['first_name'])?ucfirst($arr_data['representative_details']['first_name']).' '.$arr_data['representative_details']['last_name']:""}}</label>
                </div>
                   <span class='red'></span>
            </div>
            <div class="form-group row">
                <label for="description" class="col-2 col-form-label">Commission Status</label>
                <div class="col-10">
                  @if($arr_data['is_lock']==1 && $arr_data['is_confirm']==1)
                    <label for="description" class="col-2 col-form-label">Confirmed</label>    
                  @elseif($arr_data['is_lock']==0 && $arr_data['is_confirm']==1)
                    <label for="description" class="col-2 col-form-label">Request Pending</label>
                  @elseif($arr_data['is_confirm']==0)
                    <label for="description" class="col-4 col-form-label">Commission not confirmed by maker</label>
                  @else  
                    <label for="description" class="col-2 col-form-label">Request pending by admin</label>  
                  @endif    
                </div>
                   <span class='red'></span>
            </div>
             @if($arr_data['is_confirm']!=0)
            <div class="form-group row">
                <label for="description" class="col-2 col-form-label">Commission%</label>
                <div class="col-10">
                    <label for="description" class="col-2 col-form-label">
                        <input class="form-control valid" data-parsley-required="true" type="number" name="commission" id="commission" step="0.01" value="{{isset($arr_data['commission'])?$arr_data['commission']:""}}">
                      
                    </label>
                </div>
                   <span class='red'></span>
            </div>
              @endif

          @if($arr_data['is_confirm']!=0)
          <div class="form-group row">
            <div class="col-10">
              <button type="button" class="btn btn-success waves-effect waves-light m-r-10" value="Update" id = btn_update>Update</button>
            </div>
         </div>
         @endif

         </form>
      </div>
   </div>
</div>
</div>
</div>

<script type="text/javascript">
  const module_url_path = "{{ $module_url_path }}";
 
   $(document).ready(function(){
      $('#btn_update').click(function(){

       if($('#validation-form').parsley().validate()==false)return;
    
        swal({
               title: 'Need Confirmation',
               text: "Are you sure? Do you want to update commission and after this maker will not be able to update commission.",
               type: 'warning',
               showCancelButton: true,
               confirmButtonText: "OK",
               closeOnConfirm: false
              },
             function(isConfirm,tmp)
             {
                if(isConfirm==true)
                {
                  var formdata = new FormData($("#validation-form")[0]);
                  $.ajax({
                    url: module_url_path+'/set_commission',
                    type:"POST",
                    data: formdata,
                    contentType:false,
                    processData:false,
                    dataType:'json',
                    success:function(data)
                    { 
                       if(data.status=="SUCCESS")
                       {
                          swal({
                              title:'Success',
                              type: 'success',
                              text: "Commission has been updated.",
                              confirmButtonText: "OK",
                              closeOnConfirm: false
                            },
                           function(isConfirm,tmp)
                           {
                             if(isConfirm==true)
                             {
                                location.reload();
                             }
                           });
                        }
                        else
                        {
                           swal({
                                   title:'Error',
                                   text: "Something went wrong,please try again.",
                                   type: 'error',
                                   confirmButtonText: "OK",
                                   closeOnConfirm: false
                                },
                               function(isConfirm,tmp)
                               {
                                 if(isConfirm==true)
                                 {
                                     location.reload();
                                 }
                               });
                        }

                    } 
                    
                  });   
                }
             });
                    
       
      });
   });


</script>

@endsection