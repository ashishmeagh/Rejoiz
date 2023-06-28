function confirm_action(ref,evt,msg)
{
  var msg = msg || false;
  
    evt.preventDefault();  
    swal({
          title: "Need Confirmation",
          type: "warning",
          text: msg,
          showCancelButton: true,
          confirmButtonColor: "#8CD4F5",
          confirmButtonText: "OK",
          closeOnConfirm: true
        },
        function(isConfirm,tmp)
        { 
          if(isConfirm==true)
          {
             showProcessingOverlay(); 
            // swal("Performed!", "Record Deleted Successfully.", "success");
            window.location = $(ref).attr('href');
          }
        });
}    

/*---------- Multi_Action-----------------*/

  function check_multi_action(checked_record,frm_id,action,is_product_module = false)
  {
    

    var len = $('input[name="'+checked_record+'"]:checked').length;
    var flag=1;
    var frm_ref = $("#"+frm_id);   
  
    if(len<=0)
    {
      swal("Opps..","Please select the record to perform this action.",'warning');
      return false;
    }
    
    if(action=='delete')
    {
      var confirmation_msg = "Are you sure? Do you want to delete selected record(s).";
    }
    else if(action == 'deactivate')
    {
      if(is_product_module =='true')
      {
        var confirmation_msg = "Are you sure? Do you want to disapprove selected record(s).";
      }
      else
      {
        var confirmation_msg = "Are you sure? Do you want to deactivate selected record(s).";
      }
      
    }
    else if(action == 'block')
    {
      if(is_product_module =='true')
      {
        var confirmation_msg = "Are you sure? Do you want to disapprove selected record(s).";
      }
      else
      {
        var confirmation_msg = "Are you sure? Do you want to block selected record(s).";
      }
      
    }
    else if(action == 'activate')
    {
     if(is_product_module =='true')
      {
        var confirmation_msg = "Are you sure? Do you want to approve selected record(s).";
      }
      else
      {
        var confirmation_msg = "Are you sure? Do you want to activate selected record(s).";
      }
      
    }

    else if(action == 'approve')
    {
     var confirmation_msg = "Are you sure? Do you want to approve selected record(s).";      
    }

    else if(action == 'reject')
    {
     var confirmation_msg = "Are you sure? Do you want to reject selected record(s).";      
    }

    else if(action == 'mark_as_read')
    {
     var confirmation_msg = "Are you sure? Do you want to mark as read selected record(s).";
      
    }
   
    swal({
          title: "Need Confirmation",
          type: "warning",
          text: confirmation_msg,

          showCancelButton: true,
          confirmButtonColor: "#8CD4F5",
          confirmButtonText: "OK",
          cancelButtonText: "Cancel",
          closeOnConfirm: false,
          closeOnCancel: true
        },
        function(isConfirm)
        {
          if(isConfirm)
          {
             showProcessingOverlay();
            $('input[name="multi_action"]').val(action);
            $(frm_ref)[0].submit();
          }
          else
          {
           return false;
          }
        }); 

  }


  /* This function shows simple alert box for showing information */
  function showAlert(msg,type,confirm_btn_txt)
  {
      confirm_btn_txt = confirm_btn_txt || 'OK';
      swal({
        title: "",
        text: msg,
        type: type,
        confirmButtonText: confirm_btn_txt
      });
      return false; 
  }



