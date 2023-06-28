
<div class="row" id="size_{{$row_id.$size_row_id}}">

                <div class="col-sm-4 box">
                    <div class="form-group">
                        <label>Size</label>
                        <input type="hidden" name="product_size_id{{$row_id}}[]" value="">
                            <select id="size_id_{{$row_id.$size_row_id}}"  name="size_id{{$row_id}}[]" class="form-control" >
                            <option value="">Select Size</option>
                            @if(isset($size_arr) && count($size_arr)>0)
                              @foreach($size_arr as $size)

                                <option value="{{$size['id'] or 0}}"> {{ $size['size']?$size['size']:'NA'}}</option>
                              @endforeach
                            @endif
                          </select>
                    </div>
               </div>
               <div class="col-sm-4 box">
                    <div class="form-group">
                        <label>Size Inventory</label>
                            <input type="text" class="form-control size_class_{{$row_id}}" placeholder="size_inventory" onkeyup="checkSizeInventory('{{$row_id.$size_row_id}}','{{$row_id}}')" data-row="{{$row_id}}" name="size_inventory{{$row_id}}[]" id="size_inventory_{{$row_id.$size_row_id}}" >
                            <span class="red main_sku_class{{$row_id}}" id="size_inventory_error_msg_{{$row_id.$size_row_id}}"></span>
                    </div>
               </div>
               <div class="col-sm-3 box">
              <div class="form-group plus-btn">
                <label>Add/Delete</label>
                 {{--  <button type="button" class="btn" onclick="show_multiple_dropify({{$row}})" id="show_multiple_dropify_{{$row}}"><span id="btn_label_{{$row}}">Add</span> Multiple Images</button> --}}
                  <button type="button" class="btn" id="add_size_{{$row_id.$size_row_id}}" onclick="show_size({{$row_id}},{{$size_row_id+1}});" title="Add new SKU"><i class="fa fa-plus"></i></button>
                  @if($size_row_id != 0)
                    <button type="button" class="btn" onclick="deleteRowSize('{{$row_id.$size_row_id}}','','','');" title="Delete SKU"><i class="fa fa-trash"></i></button>
                  @endif  
              </div>
          </div>
</div>