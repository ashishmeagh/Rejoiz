@extends('admin.layout.master')    
@section('main_content')

<style type="text/css">
  
  .error{
    color: #D94020; font-size: 12px;
  }
  .form-control.error{color: #333; font-size: 13px;}
  .height-forms{height: 60px !important; vertical-align: top;}
  .control-label {padding-top:0px !important; margin-bottom:5px;}
</style>
  <!-- Page Content -->
  <div id="page-wrapper">
      <div class="container-fluid">
          <div class="row bg-title">
                <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                  <h4 class="page-title">{{$module_title or ''}}</h4> 
                </div>
                <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12"> 
                  <ol class="breadcrumb">
                      <li><a href="{{url('/')}}/admin/dashboard">Dashboard</a></li>
                      <li class="active">{{$module_title or ''}}</li>
                  </ol>
                </div>
              <!-- /.col-lg-12 -->
          </div>

    <!-- BEGIN Main Content -->
    
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box">
             @include('admin.layout._operation_status')
                <div class="row admin_settings-site-setting-new">
                    <div class="col-sm-12 col-xs-12">
                       {{--   <div class="box-title">
                            <h3><i class="ti-settings"></i> {{ isset($page_title)?$page_title:"" }}</h3>
                            <div class="box-tool">
                            </div>
                        </div> --}}
                            
                            {!! Form::open([ 'url' => $module_url_path.'/update/'.base64_encode($arr_data['id']),
                                         'method'=>'POST',   
                                         'class'=>'form-horizontal', 
                                         'id'=>'validation-form',
                                         'enctype' =>'multipart/form-data'
                                        ]) !!}


                            <!-- hidden field -->
                            <input type="hidden" name="lat"  required="" id="lat" value="55.755825" class="form-control" > 
                            <input type="hidden" name="lng"  required="" id="lng" value="37.617298" class="form-control" >

                            <div class="row">
                              <div class="col-sm-12 col-md-12 col-lg-6">
                                   <div class="form-group">
                                <label class="control-label" for="state">Website Name <i class="red">*</i></label>
                                  
                                  <div>
                                    {!! Form::text('site_name',isset($arr_data['site_name'])?$arr_data['site_name']:'',['class'=>'form-control','placeholder'=>'Enter Website Name','data-parsley-required'=>'true','data-parsley-required-message'=>'Please enter website name','data-parsley-maxlength'=>'255']) !!}
                                 
                                 </div>
                            </div>
                                <span class='red'>{{ $errors->first('site_name') }}</span>
                                 
                              
                                <div class="form-group">
                                    <input type="hidden" name="old_logo" value="{{isset($arr_data['site_logo'])?$arr_data['site_logo']:''}}">
                                     <label class="control-label" for="ad_image">Site Logo<i class="red">*</i> </label>
                                      <div>
                                        <input type="file" name="image" id="ad_image" class="dropify" data-allowed-file-extensions="png jpg JPG jpeg JPEG" data-default-file="{{ url('/storage/app/'.$arr_data['site_logo'])}}"/>
                                      </div>
                                </div>


                                <div class="form-group">
                                    <input type="hidden" name="old_login_site_logo" value="{{isset($arr_data['login_site_logo'])?$arr_data['login_site_logo']:''}}">

                                     <label class="control-label" for="ad_image">Login Site Logo<i class="red">*</i> </label>
                                      <div>
                                        <input type="file" name="login_site_logo" id="login_site_logo" class="dropify" data-allowed-file-extensions="png jpg JPG jpeg JPEG" data-default-file="{{ url('/storage/app/'.$arr_data['login_site_logo'])}}"/>
                                      </div>
                                </div> 

                                <div class="form-group">
                                    <input type="hidden" name="old_favicon" value="{{isset($arr_data['favicon'])?$arr_data['favicon']:''}}">

                                     <label class="control-label" for="ad_image">Favicon<i class="red">*</i> </label>&nbsp &nbsp
                                     @if(isset($site_setting_arr['favicon']))
                                      <a target="_blank" href="{{url('/')}}/storage/app/{{$site_setting_arr['favicon'] or ''}}" class="underline" id = "favicon_change_link"><u><b>Favicon Preview</u></b></a>
                                     @endif
                                      <div>
                                        <input type="file" name="favicon" id="favicon" class="dropify" data-allowed-file-extensions="png jpg JPG jpeg JPEG ICO ico" data-default-file="{{ url('/storage/app/'.$arr_data['favicon'])}}"/>
                                      </div>
                                </div>
                                

                                <div class="form-group">
                                    <label class="control-label" for="category_name">Address<i class="red">*</i></label>
                                       <div>
                                     
                                      {{-- <textarea class="form-control" name="site_address" id="site_address" data-parsley-required="true" data-parsley-maxlength="255">{{$arr_data['site_address'] or ''}}</textarea> --}}

                                       <input type="text" class="form-control" placeholder="Enter Address" name="site_address" id="site_address" data-parsley-required="true" data-parsley-required-message="Please enter site address" data-parsley-maxlength="255" value="{{$arr_data['site_address'] or ''}}">
                                         
                                        <span class='red'>{{ $errors->first('site_address') }}</span>
                                       </div>
                                </div>
                       
                                <div class="form-group">
                                    <label class="control-label" for="lattitude">Lattitude<i class="red">*</i></label>
                                      <div>
                                       <input type="text" name="lattitude" id="lattitude" placeholder="Enter Lattitude" data-parsley-required="true" data-parsley-required-message="Please enter lattitude" class="form-control"  value="{{$arr_data['lattitude'] or ''}}">
                                      <span class='red'>{{ $errors->first('lattitude') }}</span>
                                      </div>
                                </div> 

                                <div class="form-group">
                                    <label class="control-label" for="longitude">Longitude<i class="red">*</i></label>
                                      <div>
                                       <input type="text" name="longitude" id="longitude" placeholder="Enter Longitude" data-parsley-required="true" data-parsley-required-message="Please enter longitude" class="form-control"  value="{{$arr_data['longitude'] or ''}}">
                                      <span class='red'>{{ $errors->first('longitude') }}</span>
                                      </div>
                                </div>

                                
                                <div class="form-group">
                                    <label class="control-label">Contact Number<i class="red">*</i></label>
                                      <div>
                                        {!! Form::text('site_contact_number',isset($arr_data['site_contact_number'])?$arr_data['site_contact_number']:'',['class'=>'form-control','placeholder'=>'Enter Contact Number','data-parsley-required'=>'true','data-parsley-required-message'=>'Please enter contact number','data-parsley-maxlength'=>'10', 'data-parsley-maxlength-message'=>'Contact number should be 10 digits','data-parsley-minlength'=>'10','data-parsley-minlength-message'=>'Contact number should be 10 digits',
                                        'data-parsley-type'=>'digits','data-parsley-type-message'=>'Please enter valid contact number']) !!}

                                        <span class='red'>{{ $errors->first('site_contact_number') }}</span>
                                       </div>
                                </div>
                                  
                                
                               
                                   
                                

                              <div class="form-group">
                                <label class="control-label">Meta Keyword
                                <i class="red">*</i></label>

                                <div>

                                  {!! Form::text('meta_keyword',isset($arr_data['meta_keyword'])?$arr_data['meta_keyword']:'',['class'=>'form-control','data-parsley-maxlength'=>'255','data-parsley-required'=>'true','data-parsley-required-message'=>'Please enter meta keyword','placeholder'=>'Enter Meta Keyword']) !!}

                                    <span class='red'>{{ $errors->first('meta_keyword') }}</span>

                                </div> 
                              </div>
                              
                              <div class="form-group">
                                <label class="control-label">Website URL
                                <i class="red">*</i></label>
                                <div>
                                  {!! Form::text('website_url',isset($arr_data['website_url'])?$arr_data['website_url']:'',['class'=>'form-control','data-parsley-required'=>'true','data-parsley-required-message'=>'Please enter website url','data-parsley-url'=>'true','data-parsley-maxlength'=>'500','data-parsley-type'=>'url','data-parsley-type-message'=>'Please enter valid url','placeholder'=>'Enter Website url']) !!}

                                  <span class='red'>{{$errors->first('website_url') }}</span>
                                </div>
                              </div>
                                          
                              </div>
                              <div class="col-sm-12 col-md-12 col-lg-6">
                                  <div class="form-group">
                                    <label class="control-label">Email<i class="red">*</i></label>
                                    <div>
                                        {!! Form::email('site_email_address',isset($arr_data['site_email_address'])?$arr_data['site_email_address']:'',['class'=>'form-control', 'data-parsley-required'=>'true',
                                        'data-parsley-required-message'=>'Please enter email',
                                        'data-parsley-maxlength'=>'255','id'=>'email','placeholder'=>'Enter Email']) !!}

        
                                       <span class='red' id="err_email_msg"></span>

                                    </div>  
                              </div>
                              

                              <div class="form-group">
                                  <label class="control-label">LinkedIn URL<i class="red">*</i></label>

                                  <div>
                                         {!! Form::text('linkdin_url',isset($arr_data['linkdin_url'])?$arr_data['linkdin_url']:'',['class'=>'form-control','data-parsley-required'=>'true','data-parsley-required-message'=>'Please enter linkdin url', 'data-parsley-url'=>'true', 'data-parsley-maxlength'=>'500','data-parsley-type'=>'url','data-parsley-type-message'=>'Please enter valid url','placeholder'=>'Enter Linkdin url']) !!}
                                       

                                  <span class='red'>{{ $errors->first('linkdin_url') }}</span>
  
                                  </div>
                              </div>
                                  
                              

                              <div class="form-group">
                                  <label class="control-label">Facebook URL<i class="red">*</i></label>

                                  <div>
                                      {!! Form::text('fb_url',isset($arr_data['fb_url'])?$arr_data['fb_url']:'',['class'=>'form-control','data-parsley-required'=>'true','data-parsley-required-message'=>'Please enter facebook url','data-parsley-url'=>'true', 'data-parsley-maxlength'=>'500','data-parsley-type'=>'url','data-parsley-type-message'=>'Please enter valid url','placeholder'=>'Enter Facebook url']) !!}
                                        
                                      <span class='red'>{{ $errors->first('fb_url') }}</span>
                                  </div>
                              </div>
                                   
                              
                               
                              <div class="form-group">
                                <label class="control-label">Twitter URL<i class="red">*</i></label>

                                <div>
                                  {!! Form::text('twitter_url',isset($arr_data['twitter_url'])?$arr_data['twitter_url']:'',['class'=>'form-control','data-parsley-required'=>'true', 'data-parsley-required-message'=>'Please enter twitter url','data-parsley-url'=>'true', 'data-parsley-maxlength'=>'500','data-parsley-type'=>'url','data-parsley-type-message'=>'Please enter valid url','placeholder'=>'Enter Twitter url']) !!}
                                     
                                <span class='red'>{{ $errors->first('twitter_url') }}</span>
                                </div>
                              </div>
                                    
                              

                              <div class="form-group">
                                <label class="control-label">YouTube URL<i class="red">*</i></label>

                                <div>
                                  {!! Form::text('youtube_url',isset($arr_data['youtube_url'])?$arr_data['youtube_url']:'',['class'=>'form-control','data-parsley-required'=>'true', 'data-parsley-required-message'=>'Please enter youtube url','data-parsley-url'=>'true', 'data-parsley-maxlength'=>'500','data-parsley-type'=>'url','data-parsley-type-message'=>'Please enter valid url','placeholder'=>'Enter Youtube url']) !!}
                                     
                                <span class='red'>{{ $errors->first('youtube_url') }}</span>
                              </div>
                              </div>
{{-- 
                              <div class="form-group">
                                <label class="control-label">Whats App URL<i class="red">*</i></label>

                                  <div>
                                        {!! Form::text('whatsapp_url',isset($arr_data['whatsapp_url'])?$arr_data['whatsapp_url']:'',['class'=>'form-control','data-parsley-required'=>'true', 'data-parsley-url'=>'true', 'data-parsley-maxlength'=>'500','data-parsley-type'=>'url','data-parsley-type-message'=>'Please enter valid url','placeholder'=>'Enter Whatsapp url']) !!}
                                     
                                  <span class='red'>{{ $errors->first('whatsapp_url') }}</span>
                                  </div>
                              </div> --}}
                                  
                                

                              <div class="form-group">
                                  <label class="control-label">RSS Feed URL <i class="red">*</i></label>
                                  <div>
                                      {!! Form::text('rss_feed_url',isset($arr_data['rss_feed_url'])?$arr_data['rss_feed_url']:'',['class'=>'form-control','data-parsley-required'=>'true', 'data-parsley-required-message'=>'Please enter RSS Feed url','data-parsley-maxlength'=>'500','data-parsley-type'=>'url','data-parsley-type-message'=>'Please enter valid url','placeholder'=>'Enter Rss Feed url']) !!}
                                     
                                        <span class='red'>{{ $errors->first('rss_feed_url') }}</span>
                                  </div>  
                                        
                              </div>
                                      
                              

                              <div class="form-group">
                                  <label class="control-label">Instagram URL<i class="red">*</i></label>

                                  <div>
                                         {!! Form::text('instagram_url',isset($arr_data['instagram_url'])?$arr_data['instagram_url']:'',['class'=>'form-control','data-parsley-required'=>'true', 'data-parsley-required-message'=>'Please enter instagram url','data-parsley-maxlength'=>'500','data-parsley-type'=>'url','data-parsley-type-message'=>'Please enter valid url','placeholder'=>'Enter Instagram url']) !!}
                                       
                                    <span class='red'>{{ $errors->first('instagram_url') }}</span>

                                  </div>  
                              </div> 

                             {{--  
                               <div class="form-group">
                                  <label class="control-label"> Mailchimp Api Key<i class="red">*</i></label>

                                  <div>
                                         {!! Form::text('mailchimp_api_key',isset($arr_data['mailchimp_api_key'])?$arr_data['mailchimp_api_key']:'',['class'=>'form-control','data-parsley-required'=>'true','placeholder'=>'Enter mailchimp api key']) !!}
                                       
                                       
                                    <span class='red'>{{ $errors->first('mailchimp_api_key') }}</span>

                                  </div>  
                              </div>  --}}

                               
                               <div class="form-group">
                                  <label class="control-label"> Whatsapp URL<i class="red">*</i></label>

                                  <div>
                                         {!! Form::text('whatsapp_url',isset($arr_data['whatsapp_url'])?$arr_data['whatsapp_url']:'',['class'=>'form-control','data-parsley-required'=>'true','placeholder'=>'Enter Whatsapp Url']) !!}
                                       
                                       
                                    <span class='red'>{{ $errors->first('whatsapp_url') }}</span>

                                  </div>  
                              </div> 

                               <div class="form-group">
                                  <label class="control-label"> Site Short Name<i class="red">*</i></label>

                                  <div>
                                         {!! Form::text('site_short_name',isset($arr_data['site_short_name'])?$arr_data['site_short_name']:'',['class'=>'form-control','data-parsley-required'=>'true','data-parsley-required-message'=>'Please enter site short name','placeholder'=>'Enter Site Short Name']) !!}
                                       
                                       
                                           
                                    <span class='red'>{{ $errors->first('site_short_name') }}</span>


                                  </div>  
                              </div> 

                               <div class="form-group">
                                  <label class="control-label">Site Short Description<i class="red">*</i></label>

                                  <div>

                                         {!! Form::textarea('site_short_description',isset($arr_data['site_short_description'])?$arr_data['site_short_description']:'',['class'=>'form-control height-forms','data-parsley-required'=>'true','data-parsley-errors-container'=>'#error_container','data-parsley-required-message'=>'Please enter site short description','height'=>'48','row'=>'6','placeholder'=>'Enter Site Short Description']) !!}


                                       
                                       
                                    <span class='red'>{{ $errors->first('site_short_description') }}</span>
                                    <div id="error_container">
                                    </div>
                                  </div>  
                                </div> 

                                <div class="form-group">
                                  <label class="control-label">Product Maximum Purchase Quantity<i class="red">*</i></label>

                                  <div>
                                         {!! Form::text('product_max_qty',isset($arr_data['product_max_qty'])?$arr_data['product_max_qty']:'',['class'=>'form-control','data-parsley-required'=>'true','data-parsley-required-message'=>'Please enter product maximum purchase quantity',
                                         'data-parsley-trigger'=>'keyup', 
                                         'data-parsley-type'=>'digits',
                                         'data-parsley-type-message'=>'Please enter valid number', 
                                         'placeholder'=>'Enter Product Maximum Purchase Quantity']) !!}
                                       
                                       
                                           
                                    <span class='red'>{{ $errors->first('product_min_qty') }}</span>


                                  </div>  
                                </div> 


                                <div class="form-group">
                                  <label class="control-label">TinyMCE API Key<i class="red">*</i></label>

                                  <div>
                                         {!! Form::text('tinymce_api_key',isset($arr_data['tinymce_api_key'])?$arr_data['tinymce_api_key']:'',['class'=>'form-control','data-parsley-required'=>'true','data-parsley-required-message'=>'Please enter TinyMCE API Key','placeholder'=>'Enter TinyMCE API Key']) !!}
                                       
                                       
                                           
                                    <span class='red'>{{ $errors->first('tinymce_api_key') }}</span>


                                  </div>  
                               </div> 

                                <div class="form-group">
                                  <label class="control-label" for="category_name">Meta Description<i class="red">*</i></label>
                                  <div>
                                    {!! Form::text('meta_desc',isset($arr_data['meta_desc'])?$arr_data['meta_desc']:'',['class'=>'form-control','data-parsley-required'=>'true','data-parsley-required-message'=>'Please enter meta description','data-parsley-maxlength'=>'255','placeholder'=>'Enter Meta Description']) !!}

                                  <span class='red'>{{ $errors->first('meta_desc') }}</span>

                                </div>
                                </div>

                                 <div class="form-group">
                                  <label class="control-label"> Site Status<i class="red"></i></label>

                                  <div id="swichery">
                                         <input type="checkbox" name="site_status" id="site_status" value="{{$arr_data['site_status']}}" data-size="small" class="js-switch " @if($arr_data['site_status']=='1') checked="checked" @endif data-color="#99d683" data-secondary-color="#f96262" onchange="return confirm_box(this);" />

                                  </div>  
                              </div> 


                             {{--  
                               <div class="form-group">
                                  <label class="control-label"> Mailchimp Listing Id<i class="red">*</i></label>

                                  <div>
                                         {!! Form::text('mailchimp_list_id',isset($arr_data['mailchimp_list_id'])?$arr_data['mailchimp_list_id']:'',['class'=>'form-control','data-parsley-required'=>'true']) !!}
                                       
                                       
                                    <span class='red'>{{ $errors->first('mailchimp_api_key') }}</span>

                                  </div>  
                              </div> 

                              </br> --}}


                              

                              </br>
                                
                           {{--      
                              <div class="form-group">
                                  <label class="control-label">Site Status</label>&nbsp;&nbsp;&nbsp;
                                    <div>
                                        <input type="checkbox" name="site_status" id="site_status" value="1" data-size="small" class="js-switch " @if($arr_data['site_status']=='1') checked="checked" @endif data-color="#99d683" data-secondary-color="#f96262" onchange="return toggle_status();" />
                                    </div>  
                              </div>  --}} 
                              </div>
                           <!--  <div class="col-md-12">
                               <div class="commission-sectns">
                                <h3 class="commission-tilt">Commission</h3>
                                <div class="row">
                                  <div class="col-md-6">
                                     <div class="form-group">
                                  <label class="col-md-6 control-label">Admin Commission(%)
                                  <i class="red">*</i></label>

                                  <div class="col-md-6">

                                    {!! Form::text('commission',isset($arr_data['commission'])?num_format($arr_data['commission']):'',['class'=>'form-control','data-parsley-type'=>"number",'data-parsley-type-message'=>'Please enter valid commission.','data-parsley-required'=>'true','data-parsley-required-message'=>'Please enter admin commission.','data-parsley-minlength'=>"1", 'data-parsley-maxlength'=>"5",'placeholder'=>'Enter admin commision']) !!}
                                      <span class='red'>{{ $errors->first('commission') }}</span>
                                  </div> 
                                </div>

                                <div class="form-group">
                                  <label class="col-md-6 control-label">Representative/Sales Manager Commission(%)
                                  <i class="red">*</i></label>
                                  <div class="col-md-6">
                                    {!! Form::text('representative_commission',isset($arr_data['representative_commission'])?num_format($arr_data['representative_commission']):'',['class'=>'form-control','data-parsley-type'=>"number",'data-parsley-type-message'=>'Please enter valid commission.','data-parsley-required'=>'true','data-parsley-required-message'=>'Please enter representative/salesmanager commission.','data-parsley-minlength'=>"1", 'data-parsley-maxlength'=>"5",'placeholder'=>'Enter representative/sales manager commision']) !!}
                                      <span class='red'>{{ $errors->first('representative_commission') }}</span>
                                  </div> 
                                </div>

                               
                                  </div>
                                </div>
                               
                               </div>
                              </div> -->
                            </div>

                            

                              
                                
                           {{--    <div class="input-group row">
                                <div class="col-md-12 text-center">
                                <button type="submit" class="btn btn-outline btn-success waves-effect waves-light" value="Update" id="update">Update</button>
                                </div>
                              </div> --}}

                              <div class="form-group row">
                              <div class="col-12 p-0">
                                <a class="btn btn-success waves-effect waves-light pull-left" href="{{url('/')}}/admin/dashboard"><i class="fa fa-arrow-left"></i> Back</a>
                                <button type="button" style="float: right" class="btn btn-success waves-effect waves-light" value="Update" id="update" onclick="saveTinyMceContent();">Update</button>
                              </div>
                           </div>

                            {!! Form::close() !!}
                    </div>
                </div>
           </div>
        </div>
    </div>
    
 

  <!-- END Main Content --> 
  <script type="text/javascript">
  function saveTinyMceContent()
  {
      tinyMCE.triggerSave();
  }
  $("#update").click(function()
   {
      if($('#validation-form').parsley().validate()==false)
          {  
             hideProcessingOverlay();
             return;
          }
          else
          { 
            showProcessingOverlay();
            $("#validation-form").submit();
          }    
   }); 

   $("#email").keyup(function()
   {
      var email = $("#email").val();
      if(email!="")
      {  
      var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
       if(!re.test(String(email)))
       {
         $("#err_email_msg").html('Please enter valid email address');
         return false;
       }
       else
       {
         $("#err_email_msg").html(""); 
         return true;
       }
      } 
   });

  function confirm_box(ref)
  {

    swal({
        title: "Need Confirmation",
        text: "Are you sure? Do you want to change site maintenance mode.",
        type: "warning",
        showCancelButton: true,
        confirmButtonClass: "btn-danger",
        confirmButtonText: "OK",
        cancelButtonText: "Cancel",
        closeOnConfirm: false,
        closeOnCancel: true
      },
      function(isConfirm) {
        if (isConfirm) {
                  toggle_status();
        } else {
            $(ref).trigger('click');
        }
      });
  }
 
  function toggle_status()
  { 
      let site_status = $('#site_status').val();
      
      if(site_status == '1')
      {
        $('#site_status').val('0');
      }
      else
      {
        $('#site_status').val('1');
      }

      site_status = $('#site_status').val();
      /*alert(site_status);
      return;*/

      let module_url = '{{$module_url_path}}';
      let id         =  '{{base64_encode($arr_data['id'])}}';
      let url = module_url+'/update_site_status';

      $.ajax({
          url: url,
          method:"POST",
          data:{id:id,status:site_status,"_token":"{{csrf_token()}}"},
          dataType:'json',
          beforeSend : function()
          {
            showProcessingOverlay();
          },

          success:function(data)
          {
            hideProcessingOverlay();
             swal({
                    title:"Success", 
                    text: data.description, 
                    type: data.status
                }
               // function(){ 
               //     location.reload();
               // }
            );

          }
          
        });   

  } 
</script>
@endsection