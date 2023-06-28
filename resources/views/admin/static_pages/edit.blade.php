@extends('admin.layout.master')                
@section('main_content')
<!-- Page Content -->
<div id="page-wrapper">
<div class="container-fluid">
   <div class="row bg-title">
      <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
         <h4 class="page-title">{{$page_title or ''}}</h4>
      </div>
      <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
         <ol class="breadcrumb">
            <li><a href="{{url('/')}}/admin/dashboard">Dashboard</a></li>
            <li><a href="{{$module_url_path}}">{{$module_title or ''}}</a></li>
            <li class="active">Edit CMS</li>
         </ol>
      </div>
      <!-- /.col-lg-12 -->
   </div>
   <!-- .row -->
   <div class="row">
      <div class="col-sm-12">
         <div class="white-box">
            @include('admin.layout._operation_status')
            {!! Form::open([ 'url' => $module_url_path.'/update/'.$enc_id,
            'method'=>'POST',
            'enctype' =>'multipart/form-data',   
            'class'=>'form-horizontal', 
            'id'=>'validation-form' 
            ]) !!} 
            <ul  class="nav nav-tabs">
               @include('admin.layout._multi_lang_tab')
            </ul>
            <div id="myTabContent1" class="tab-content">
               @if(isset($arr_lang) && sizeof($arr_lang)>0)
               @foreach($arr_lang as $lang)
               <?php 
                  /* Locale Variable */  
                  $locale_page_title = "";
                  $locale_meta_keyword = "";
                  $locale_meta_desc = "";
                  $locale_page_desc = "";
                  
                  
                  if(isset($arr_static_page['translations'][$lang['locale']]))
                  {
                      $locale_page_title = $arr_static_page['translations'][$lang['locale']]['page_title'];
                      $locale_meta_keyword = $arr_static_page['translations'][$lang['locale']]['meta_keyword'];
                      $locale_meta_desc = $arr_static_page['translations'][$lang['locale']]['meta_desc'];
                      $locale_page_desc = $arr_static_page['translations'][$lang['locale']]['page_desc'];
                  }
                  ?>
               <div class="tab-pane fade {{ $lang['locale']=='en'?'in active':'' }}"
                  id="{{ $lang['locale'] }}">
                  <div class="form-group row">
                     <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label" for="page_title">Page Title
                     @if($lang['locale'] == 'en') 
                     <i class="red">*</i>
                     @endif
                     </label>
                     <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                        @if($lang['locale'] == 'en')        
                        {!! Form::text('page_title_'.$lang['locale'],$locale_page_title,['class'=>'form-control','data-parsley-required'=>'true','data-parsley-required-message'=>'Please enter page title','data-parsley-maxlength'=>'255','data-parsley-maxlength-message'=>'Page title should be maximum 255 characters long','placeholder'=>'Page Title']) !!}
                        @else
                        {!! Form::text('page_title_'.$lang['locale'],$locale_page_title,['class'=>'form-control','placeholder'=>'Page Title']) !!}
                        @endif    
                        <span class='red'>{{ $errors->first('page_name') }}</span>
                     </div>
                  </div>
                  <div class="form-group row">
                     <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label" for="meta_keyword">Meta Keyword
                     @if($lang['locale'] == 'en') 
                     <i class="red">*</i>
                     @endif
                     </label>
                     <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                        @if($lang['locale'] == 'en')        
                        {!! Form::text('meta_keyword_'.$lang['locale'],$locale_meta_keyword,['class'=>'form-control','data-parsley-required'=>'true','data-parsley-maxlength'=>'255','placeholder'=>'Meta Keyword','data-parsley-required-message'=>'Please enter meta keyword','data-parsley-maxlength-message'=>'Meta keyword should be maximum 255 characters long']) !!}
                        @else
                        {!! Form::text('meta_keyword_'.$lang['locale'],$locale_meta_keyword,['class'=>'form-control','placeholder'=>'Meta Keyword']) !!}
                        @endif
                        <span class='red'>{{ $errors->first('meta_keyword_') }}</span>
                     </div>
                  </div>
                  <div class="form-group row">
                     <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label" for="meta_desc">Meta Description
                     @if($lang['locale'] == 'en') 
                     <i class="red">*</i>
                     @endif
                     </label>
                     <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                        @if($lang['locale'] == 'en')        
                        {!! Form::text('meta_desc_'.$lang['locale'],$locale_meta_desc,['class'=>'form-control','data-parsley-required'=>'true','data-parsley-maxlength'=>'255','placeholder'=>'Meta Description','data-parsley-required-message'=>'Please enter meta description','data-parsley-maxlength-message'=>'Meta description should be maximum 255 characters long']) !!}
                        @else
                        {!! Form::text('meta_desc_'.$lang['locale'],$locale_meta_desc,['class'=>'form-control','placeholder'=>'Meta Description']) !!}
                        @endif
                        <span class='red'>{{ $errors->first('meta_desc_'.$lang['locale']) }}</span>
                     </div>
                  </div>
                  <div class="form-group row">
                     <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label" for="page_desc">Page Content
                     @if($lang['locale'] == 'en') 
                     <i class="red">*</i>
                     @endif
                     </label>
                     <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                        @if($lang['locale'] == 'en')        
                        {!! Form::textarea('page_desc_'.$lang['locale'],$locale_page_desc,['class'=>'form-control','data-parsley-required'=>'true','rows'=>'20','placeholder'=>'Page Content','data-parsley-errors-container'=>'#error_container','data-parsley-required-message'=>'Please enter page content','data-parsley-maxlength-message'=>'Page content should be maximum 255 characters long']) !!}
                        @else
                        {!! Form::textarea('page_desc_'.$lang['locale'],$locale_page_desc,['class'=>'form-control','placeholder'=>'Page Content']) !!}
                        @endif
                        <span class='red'>{{ $errors->first('page_desc_'.$lang['locale']) }}</span>
                        <div id="error_container">
                        </div>
                     </div>
                  </div>
               </div>
               @endforeach
               @endif
            </div>
            <br>
            <div class="form-group row">
               <div class="col-md-6 text-left"><a class="btn btn-success waves-effect waves-light" href="{{$module_url_path or ''}}"><i class="fa fa-arrow-left"></i> Back</a></div>
               <div class="col-md-6 text-right">
                  <button type="submit" onclick="saveTinyMceContent();" class="btn btn-outline btn-success waves-effect waves-light" value="Update">Update</button>
               </div>
            </div>
            {!! Form::close() !!}
         </div>
      </div>
   </div>
</div>
<!-- END Main Content -->
<script type="text/javascript">
   function saveTinyMceContent()
   {
      //var content =  tinyMCE.getContent('page_desc_en');
      var content = tinyMCE.get('page_desc_en').getContent();

      if(content!="" && $('#validation-form').parsley().validate()==true)
      {
            showProcessingOverlay();
            tinyMCE.triggerSave();
            $("#validation-form").submit();
      }
      else
      { 
             hideProcessingOverlay();
             return false;
      }    
   }
</script>
<script type="text/javascript" src="{{url('/assets/js/tinyMCE.js')}}"></script>
@stop