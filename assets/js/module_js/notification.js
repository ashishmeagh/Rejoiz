/*This js use for show notification to users*/

  /*var table_module = false;*/

  $(document).ready(function()
  {
       setInterval(ajaxCall, 5000); //300000 MS == 5 minutes
  });

  function ajaxCall() 
  {  
       var url  = SITE_URL+'/notifications';
       $.ajax({
          url:url,          
          type:'GET',
          dataType:'json',
          success:function(response)
          {  
             if(response.arr_data!="" || typeof response.arr_data !=undefined)
             {
                $(response.arr_data).each(function(index,noti_details)
                {
                    var title = noti_details.title;
                    var desc  = noti_details.description;
                    var logo  = noti_details.logo;
                    var link  = noti_details.notification_url;
       
                    notification(title,desc,logo,link);

                });     
             }
          }
     });
  }

  //Notification script
  function notification(title='',desc='',logo='',link='')
  {   
      var notification;

      if (!("Notification" in window)) 
      {
        alert("This browser does not support desktop notification.");
      }
      else if (Notification.permission === "granted") 
      {
            var options = 
            {
                    title:title,
                    body: desc,
                    icon: logo,
                    dir : "ltr",
                    link:link
            };
            notification = new Notification("Kadoe",options);
           
      }
      else if (Notification.permission !== 'denied') 
      {
        Notification.requestPermission(function (permission) 
        {
          if (!('permission' in Notification)) {
            Notification.permission = permission;
          }
        
          if (permission === "granted") {
            var options = 
            {
                  title:title,
                  body: desc,
                  icon: logo,
                  dir : "ltr",
                  link :link
            };
            //notification = new Notification("justgot2haveit.com",options);
            notification = new Notification("Kadoe",options);

          }
        });
      }

    
      notification.onclick = function(){
        window.location.href = link;
      }

  }

 
