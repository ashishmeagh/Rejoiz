@extends('maker.layout.master')                
@section('main_content')

<style>
.moretext{
   overflow: hidden;
}
a.readmore-toggle {
    color: #3a72e2;
    font-weight: 600;
    text-decoration: underline;
}
.main-nm-retailer-right {
    margin-left: 110px;
}
.main-nm-retailer {    margin-top: 0px;
    position: relative; float: right;
}
.main-nm-retailer-left {
    position: absolute; font-weight: 600;
    left: 0;
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
         <li><a href="{{$module_url_path}}">Manage {{$module_title or ''}}</a></li>
         <li class="active">{{$page_title or ''}}</li>
      </ol>
   </div>
   <!-- /.col-lg-12 -->
</div>
<!-- .row -->
   @include('admin.layout._operation_status')  
<div class="row">
  
  
   <div class="col-md-12">

    <div class="row">
        <div class="col-md-12">
          @php 
           $date = date('m-d-Y');
          @endphp
    

                   <div class="main-all-current-promotions">
                       
                        <div class="main-all-current-promotions-right">
                           
                            <div class="title-promotions-txt">{{$promotion_arr['title'] or ''}}</div>

                            <div class="date-tm">{{isset($promotion_arr['from_date'])?date('d M Y',strtotime($promotion_arr['from_date'])):''}}  To  {{isset($promotion_arr['to_date'])?date('d M Y',strtotime($promotion_arr['to_date'])):''}}</div>

    
                            <div class="clearfix"></div></br>

                            <ul class="promotions-ul">
                               
                                @if(isset($promotion_arr['get_promotions_offer_details']) && count($promotion_arr['get_promotions_offer_details'])>0)

                                    @foreach($promotion_arr['get_promotions_offer_details'] as $key=>$promotions_offer)
                                            
                                        @if($promotions_offer['promotion_type_id'] == 1)

                                        <li>Orders of ${{$promotions_offer['minimum_ammount']}} and above receive free shipping</li>

                                        @elseif($promotions_offer['promotion_type_id'] == 2)

                                        <li>Orders of ${{$promotions_offer['minimum_ammount']}} and above receive {{$promotions_offer['discount']}} % off</li>
                           
                                        @endif

                                    @endforeach

                                @endif
                
                            </ul>
                           
                           @if(isset($promotion_arr['get_promo_code_details']['promo_code_name']) && $promotion_arr['get_promo_code_details']['promo_code_name']!='') 
                           <div class="date-tm">{{$promotion_arr['get_promo_code_details']['promo_code_name'] or ''}}</div> 
                           @endif
                           
                        </div>
                    <div class="clearfix"></div>
                   </div>


        </div>
     {{--    <div class="col-md-12">
          <div class="main-all-current-promotions">   
              <div class="main-all-current-promotions-right">
                  <div class="title-promotions-txt">Turkey Trot Road Special</div>
                  <div class="date-tm">31 Oct 2019  To  31 Oct 2019</div>
                  <div class="clearfix"></div><br>
                  <ul class="promotions-ul">
                    <li>Orders of $334 receive free shipping</li> 
                    <li>Orders of $334 receive free shipping</li> 
                    <li>Orders of $334 receive free shipping</li>           
                  </ul>
              </div>
          <div class="clearfix"></div>
         </div>
        </div> --}}
      </div>
      <div class="form-group row">
         <div class="col-md-12">
            <div class="text-center">
               <a class="btn btn-inverse waves-effect waves-light pull-left" href="{{$module_url_path or ''}}"><i class="fa fa-arrow-left"></i> Back</a>
            </div>
         </div>
      </div>
      
   </div>
</div>
<!-- END Main Content -->


@stop