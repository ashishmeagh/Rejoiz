<script type='text/javascript' src='{{url('/')}}/assets/front/js/menu_jquery.js'></script>
<script>
$(document).ready(function(){
  $(".click-category").click(function(){
    
    $(".shows-category").toggleClass("showscategorydiv");
  });
});
</script>
<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
<div class="sidebar-main-lisitng topspaceview-division">
    <div class="title-categorynm click-category">
        <span class="cat-none">Filter by Category</span>
        <img src="assets/front/images/chevron-down.svg" alt="">
    </div>
    <div id="cssmenu1" class="shows-category">
        <ul>
            @php
                $cat_id_arr = [];
                $search_type =  isset($search_value['search_type']) ?$search_value['search_type']:'';

                $search_term = isset($search_value['search_term'])?$search_value['search_term']:'';

                if($search_type == 'product' || $search_type =='brand')
                {
                    $href = url('/').'/search?search_type='.$search_type.'&search_term=';
                    $label = $search_type;
                }
                else
                {
                    $href = url('/').'/search?search_type=product&search_term=';
                    $label = 'Products';
                }

               
               $cat_id     =  app('request')->input('category_id');

               $sub_cat_id = app('request')->input('subcategory');

               $cat_id_arr = app('request')->input('category_id_arr');
               $cat_id_arr = json_decode($cat_id_arr);

            @endphp

            @if(isset($arr_category) && sizeof($arr_category)>0)
            
            @foreach($arr_category as $category)
                @php
                  $subcategory_id_arr = array_column($category['subcategory_details'], 'id');
                @endphp
            <li class='has-sub'>
            
                <a @if(isset($product_category_id) && $category['id'] == $product_category_id) class="active" @endif href="{{url('/')}}/search_vendor_from_cat/{{isset($area_id)?base64_encode($area_id):""}}/{{isset($category['id'])?base64_encode($category['id']):""}}/{{isset($category_div_id)?base64_encode($category_div_id):""}}"><span class="submenu1">{{isset($category['category_name'])?ucfirst($category['category_name']):""}}</span>
                
                </a>

             
            </li>
            @endforeach
            @endif
         
        </ul>
    </div>
  
</div>
</div>

