@extends('maker.layout.master')
@section('main_content')

<style>
  .slimScrollDiv{
    height: 400px !important;
  }
</style>
<!-- Page Content -->
<div id="page-wrapper">
<div class="container-fluid">
<div class="row bg-title">
   <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">{{$page_title or ''}}</h4>
   </div>
   <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
          <li><a href="{{ url(config('app.project.maker_panel_slug').'/dashboard') }}">Dashboard</a></li>
          <li><a href="{{ $module_url_path or '' }}">Representative Leads</a></li>
         <li class="active">{{$page_title or ''}}</li>
      </ol>
   </div>
   <!-- /.col-lg-12 -->
</div>

<!-- BEGIN Main Content -->
<div class="row">
   <div class="col-md-12">
      <div class="white-box">

    <!-- .chat-row -->
                <div class="chat-main-box">
                    <!-- .chat-left-panel -->
                    <div class="chat-left-aside">
                      <div class="open-panel"><i class="ti-angle-right"></i></div>
                        <div class="chat-left-inner">
                          <div class="user-profile-chat-main">
                            <div class="user-profile-chat">
                              <img alt="male" id="retailer-prof-img" src="{{ getProfileImage($lead_arr['representative_user_details']['profile_image']) }}">
                            </div>
                            <div class="user-profile-chat-txt">{{ $lead_arr['representative_user_details']['first_name'].' '.$lead_arr['representative_user_details']['last_name']}} </div>                             
                          </div>
                          {{-- <div class="chat-descript">
                            <div class="chat-descript-title">Description</div>
                            <div class="chat-descript-p">
                              Lorem ipsum dolor sit amet, consectetur adipisicing elit. Magnam atque facere dicta consectetur repellendus officia ducimus alias perspiciatis, reprehenderit doloribus tempora expedita fugiat ipsum deserunt magni itaque saepe sit ullam.
                            </div>
                          </div> --}}
                        </div>
                    </div>
                    <!-- .chat-left-panel -->
                    <!-- .chat-right-panel -->
                         <div class="chat-right-aside">
                        <div class="chat-main-header">
                               <div class="quote-cnvrttion"><span id="chat-title">Chat Details</span>
                                
                                @if(check_is_user_online($lead_arr['representative_id']))
                                <span class="badge badge-success">Online</span>
                                @else
                                <span class="badge badge-warning">Offline</span>
                                @endif

                                <a href="javascript:void(0)" class="qute-constn-link" id="quote-details-btn">View Lead Details </a>
                               </div>
                                <div class="main-quote-dtls" id="quote-details" style="display: none;">
                                  <div class="row">
                                    <div class="col-md-6">
                                      <div class="table-style-mkr">
                                        <div class="enquiry-id-left">Lead ID</div>
                                        <div class="enquiry-id-right">{{ $lead_arr['id'] or '' }}</div>
                                        <div class="clearfix"></div>
                                      </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                      <div class="table-style-mkr">
                                        <div class="enquiry-id-left">Representative Name</div>
                                        <div class="enquiry-id-right">{{ $lead_arr['representative_user_details']['first_name'] or '' }} {{ $lead_arr['representative_user_details']['last_name'] or '' }}</div>
                                        <div class="clearfix"></div>
                                      </div>
                                    </div>
                                    <div class="col-md-6">
                                      <div class="table-style-mkr">
                                        <div class="enquiry-id-left">Lead Date</div>
                                        <div class="enquiry-id-right">{{ isset($lead_arr['created_at'])?date('d-M-Y',strtotime($lead_arr['created_at'])):'' }}</div>
                                        <div class="clearfix"></div>
                                      </div>
                                    </div>
                                    <div class="col-md-6">
                                      <div class="table-style-mkr">
                                        <div class="enquiry-id-left">Total Costing (Retail)</div>
                                        <div class="enquiry-id-right">$ {{ isset($lead_arr['total_retail_price'])?num_format($lead_arr['total_retail_price']) : '' }}</div>
                                        <div class="clearfix"></div>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                        </div>
                        <div class="chat-box">
                            <ul class="chat-list slimscroll p-t-30 messages-section">
                              @if(isset($conversation_arr) && count($conversation_arr)>0)
                              @foreach($conversation_arr as $chat)

                                @if($chat['sender_id'] == $loggedInUserId)                                 
                                <li class="odd">
                                    <div class="chat-image"> <img alt="male" src="{{getProfileImage($chat['sender_details']['profile_image'])}}"> </div>
                                    <div class="chat-body">
                                        <div class="chat-text">
                                            <h4>{{ $chat['sender_details']['first_name'] or ''}} {{ $chat['sender_details']['last_name'] or ''}}</h4>
                                            <b>{{ date('d-M-Y H:i A',strtotime($chat['created_at'])) }}</b>
                                            <div class="clearfix"></div>

                                            @if(isset($chat['attachment']) && $chat['attachment'] != '' && file_exists($images_base_path.$chat['attachment']))
                                            <a target="_blank" href="{{$images_public_path.'/'.$chat['attachment']}}"><img src="{{$images_public_path.'/'.$chat['attachment']}}" width="200px;" height="200px;"></a>
                                            @else
                                            <p> {{$chat['message'] or ''}} </p>
                                            @endif

                                             </div>
                                    </div>
                                </li>
                                @else

                                <li>
                                    <div class="chat-image"> <img alt="Female" src="{{getProfileImage($chat['sender_details']['profile_image'])}}"> </div>
                                    <div class="chat-body">
                                        <div class="chat-text">
                                            <h4>{{ $chat['sender_details']['first_name'] or ''}} {{ $chat['sender_details']['last_name'] or ''}}</h4>
                                            <b>{{ date('d-M-Y H:i A',strtotime($chat['created_at'])) }}</b>
                                            <div class="clearfix"></div>

                                            @if(isset($chat['attachment']) && $chat['attachment'] != '' && file_exists($images_base_path.$chat['attachment']))
                                            <a target="_blank" href="{{$images_public_path.'/'.$chat['attachment']}}"><img src="{{$images_public_path.'/'.$chat['attachment']}}" width="200px;" height="200px;"></a>
                                            @else
                                            <p> {{$chat['message'] or ''}} </p>
                                            @endif

                                              </div>
                                    </div>
                                </li>
                                @endif

                              <input type="hidden" class="last_msg_retrieved_id" value="{{$chat['id'] or '0'}}">
                              @endforeach
                              @else
                               <div id="no-msg-found">No message found 😥</div>
                              @endif
                              <div class="chat_box"></div>  
                            </ul>
                            <div class="row send-chat-box">
                                <div class="col-sm-12">
                                  <form id="chat-frm">
                                    {{ csrf_field() }}                                     
                                     <input type="hidden" name="lead_id" id="lead_id" value="{{$lead_arr['id'] or ''}}">      
                                     <input type="hidden" name="receiver_id" id="receiver_id" value="{{ $lead_arr['representative_id'] or '' }}"> 
                                    <input type="text" class="form-control" name="message" id="message" placeholder="Type your message" onkeypress="HitEnter(event)" />
                                    <div class="custom-send">                                     
                                      <div class="uplod-llink">
                                          <div class="input-group">                                            
                                            <input type="file" id="attachment" style="visibility:hidden; height: 0;" name="attachment"/>                  
                                             <div class="btn btn-primary btn-file btn-gry">
                                               <a class="file" onclick="browseImage()"><i class="fa fa-link"></i></a>
                                             </div>
                                          </div>
                                     </div>
                                        <button class="btn btn-danger btn-rounded" type="button" id="btn-send" onclick="sendMessage();">Send</button>
                                    </div>
                                  </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- .chat-right-panel -->
                </div>
                <!-- /.chat-row -->
  	</div>
  </div>
</div>
 
<script src="{{url('/')}}/assets/common/moment/moment.js"></script>
 <script type="text/javascript">

  $(document).ready(function(){
    
     var d = $("ul.messages-section");       
    d.animate({ scrollTop: $(document).height() }, 1000);

  });
  
  const module_url_path = "{{$module_url_path or ''}}";
  
  //Sent message
  function sendMessage(exFlage = false) {
      // var date              = moment.utc().format();
      var current_date_time = moment.utc(moment.utc().format()).local().format("HH:mm A");
  
      var message                    = $('#message').val();
      var chatdata                   = new FormData($('#chat-frm')[0]);
      var representative_profile_img = $('#profile-image').attr('src');
      var receiver_id                = $('#receiver_id').val();
      $('#attachment').val(''); 
     
      if(message=='' && exFlage==false)
      {
        $('#message').focus();
        return false;
      }

      var scrolled = 0;      

        var latest_msg =  `<li class="odd">
                            <div class="chat-image"> <img alt="male" src="`+representative_profile_img+`"> </div>
                            <div class="chat-body">
                                <div class="chat-text">                                    
                                    <p>`+message+`</p> <b>
                                    `+current_date_time+`</b> </div>
                          </li>`;

      $.ajax({
          url:module_url_path+'/conversation/send_message',
          data:chatdata,
          method:'POST',
          dataType:'json',
          contentType:false,
          processData:false,
          cache: false,
          success:function(response){
              if(exFlage==false) {
                  $('#no-msg-found').hide();
                  $('.chat_box:last').append(latest_msg);
                  //clear the form
                  var frm = $('#chat-frm')[0];
                  frm.reset();
              }               

              var d = $("ul.messages-section");
                  d.scrollTop(d[0].scrollHeight);     
          }  
      });        
  };   

  //get lates message after every 3 seconds
  setInterval(function(){
    populateChatMessages();
  },3000);


  function populateChatMessages()
  {
    var last_retrieved_id  = $('.last_msg_retrieved_id:last').val() || null;
    var lead_id            = $('#lead_id').val();
    var representative_id  = $('#receiver_id').val();

      $.ajax({
          url:module_url_path+'/conversation/chat/get_message',
          data:{
                  last_retrieved_id:last_retrieved_id,
                  lead_id:lead_id,
                  receiver_id:representative_id
          },
          dataType:'json',
          success:function(response)
          {            
             var chat_box = '';
              if(typeof (response) == 'object'){

                if(response.status=='SUCCESS'){

                   $.each(response.chat_arr,function(index,chat_obj){
                       
                        if(chat_obj.sender_details.profile_image==null){

                        var profile_img = SITE_URL+'/assets/images/default.png';

                        }else{
                          var profile_img = response.profile_img_public_path+'/'+chat_obj.sender_details.profile_image;                            
                        }
                       
                        //if sender is send image then show that image else show the message
                        var msgContent = '';                          
                        if(chat_obj.attachment!= '' && chat_obj.attachment!= null) {

                          msgContent = `<a target="_blank" href="`+response.images_public_path+'/'+chat_obj.attachment+`">
                          <img src="`+response.images_public_path+'/'+chat_obj.attachment+`" width="200px;" height="200px;"></a>`;

                        } else {                            
                          msgContent = chat_obj.message;
                        }                    

                         chat_box = `<li>
                                        <div class="chat-image"> <img alt="male" src="`+profile_img+`"> </div>
                                        <div class="chat-body">
                                            <div class="chat-text">
                                                <h4>`+chat_obj.sender_details.first_name+` `+chat_obj.sender_details.last_name+`</h4>
                                                <b> `+moment(chat_obj.created_at).format("HH:mm A")+`</b> 
                                                <div class="clearfix"></div>
                                                <p>`+msgContent+`</p> </div>
                                        <input type="hidden" class="last_msg_retrieved_id" value="`+chat_obj.id+`">
                                      </li>`;                      

                        $('.chat_box:last').append(chat_box);
                        
                        $('#no-msg-found').hide();

                        $('.chat_box:last').animate({scrollTop: $('.chat_box:last').prop("scrollHeight")}, 500);
                    });
                }
             }   
          }  
      });       
  }

  function HitEnter(event) {
   var x = event.which || event.keyCode;

   if(x==13){
      event.preventDefault();    
    sendMessage();
   }
    return event.key != "Enter";
  }

  //upload attachment
  $("#attachment").change(function() {
    readURL(this);

  });

  function readURL(input) {

      if (input.files && input.files[0]) {
          var reader = new FileReader();
          var retailer_profile_img= $('#profile-image').attr('src');       

          reader.onload = function (e) {
            var current_date_time = moment(new Date()).utc().format("HH:mm A");
            var seller_profile_img= $('#profile-image').attr('src'); 

              var imagePreview =  `<li class="odd">
                            <div class="chat-image"> <img alt="male" src="`+retailer_profile_img+`"> </div>
                            <div class="chat-body">
                                <div class="chat-text">                                    
                                    <p><img src='`+e.target.result+`' width="200px;" height="200px;"></p> <b>
                                    `+current_date_time+`</b> </div>
                          </li>`;

              $('.chat_box:last').append(imagePreview);
              $('#no-msg-found').hide();   

              //call send message function to send attachment
              sendMessage('exFlage');          
             var s = $("div.messages-section");
             s.scrollTop(s[0].scrollHeight);
          }
          
          reader.readAsDataURL(input.files[0]);
      }
  }

  function browseImage() {
   $("#attachment").trigger('click');
  }    
 $('#quote-details-btn').click(function(){

    if($("#quote-details").is(":visible"))
    {
      $('#chat-title').html('Chat Details');
      $('#quote-details-btn').html('View Lead Details');   
      $('#quote-details').slideUp();
    }
    else
    { 
      $('#chat-title').html('Lead Details');
      $('#quote-details-btn').html('Hide Lead Details');
      $('#quote-details').slideDown();
    } 
  }); 
 </script>
@stop