/*$( document ).ready(function() 
{
  $('#cssmenu ul ul li:odd').addClass('odd');
  $('#cssmenu ul ul li:even').addClass('even');
  $('#cssmenu > ul > li > a').click(function() 
  {
    $('#cssmenu li').removeClass('active');
    $(this).closest('li').addClass('active');	
    var checkElement = $(this).next();
    if((checkElement.is('ul')) && (checkElement.is(':visible'))) 
    {
      $(this).closest('li').removeClass('active');
      checkElement.slideUp('normal');
    }
    if((checkElement.is('ul')) && (!checkElement.is(':visible'))) 
    {
      $('#cssmenu ul ul:visible').slideUp('normal');
      checkElement.slideDown('normal');
    }
    if($(this).closest('li').find('ul').children().length == 0) 
    {
      return true;
    } else {
      return false;	
    }		
  });
});*/


$( document ).ready(function() 
{

  $("#cssmenu1").find("li.has-sub > .lnkclk").click(function(event)
  {
      var ref = $(this).parent("li.has-sub");
      event.stopPropagation();

      var siblings = $(ref).siblings('li.has-sub').not($(ref));
      $(siblings).find('ul').eq(0).slideUp();
      $(siblings).find('span.plus-icon').eq(0).find('i.fa').removeClass('fa-minus').addClass('fa-plus');
      
      var li = $(ref).find("ul").eq(0);
      $(li).slideToggle();
      $('.sub_menu').removeClass('link-act');
      // $('.plus-icon').children().removeClass('fa-minus');
      // $('.plus-icon').children().addClass('fa-plus');
     
      $(li).toggleClass('link-act');
           
      if($(ref).find('span.plus-icon').eq(0).find('i.fa').hasClass('fa-plus'))
      {
        $(ref).find('span.plus-icon').eq(0).find('i.fa').removeClass('fa-plus').addClass('fa-minus');
      }
      else
      {
        $(ref).find('span.plus-icon').eq(0).find('i.fa').removeClass('fa-minus').addClass('fa-plus');
      }



  });

 

});


$(document).ready(function(){
   
  

    $("#cssmenu1").find("li.relative > .lnkclk").click(function(event)
  {

    var ref = $(this).parent("li.relative");
    event.stopPropagation();
    var siblings = $(ref).siblings('li.relative').not($(ref));

     var fourth_ul_id = $(this).attr('id');
      var str = fourth_ul_id.replace("fourth_cat_plus_", "");
       
       $("#"+fourth_ul_id+'i').removeClass('fa fa-plus').addClass('fa fa-minus');
         $("#fourth_ul_"+str).toggle();

    $(siblings).find('ul').eq(0).slideUp();
     
      var li = $(ref).find("ul").eq(0);

      var fourth_ul_id = $(this).attr('id');
           
      if($(ref).find('span.fourth_cat_plus').eq(0).find('i.fa').hasClass('fa-plus'))
      {
        $(ref).find('span.fourth_cat_plus').eq(0).find('i.fa').removeClass('fa-plus').addClass('fa-minus');
      }
      else
      {
        $(ref).find('span.fourth_cat_plus').eq(0).find('i.fa').removeClass('fa-minus').addClass('fa-plus');
      }

    
    
  });


      


});