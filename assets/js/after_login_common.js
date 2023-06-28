  //update current time to database every 5 seconds
	setInterval(function(){
    	update_user_active_time();
	},5000);	


function update_user_active_time()
{

	$.ajax({
      url: SITE_URL+'/update_user_active_time',
      type:"GET",
      dataType:'json',
      beforeSend : function()
      {
        
      },
      success:function(response)
      {

      }    
	});  	
}


        