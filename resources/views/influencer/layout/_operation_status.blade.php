<?php
		$flash_message = '';
		$flash_type = '';
		if(Session::has('flash_notification')){
			$arr_session_flash = Session::get('flash_notification')->toArray();
			if(isset($arr_session_flash) && sizeof($arr_session_flash)>0){
				$flash_message = isset($arr_session_flash[0]->message)?$arr_session_flash[0]->message:'';
				$flash_type = isset($arr_session_flash[0]->level)?$arr_session_flash[0]->level:'';
			}
		}

?>	

@if ($flash_message !='' && $flash_type !='')
    <div class="alert alert-{{ $flash_type }}">
        <button type="button" class="close" style="margin-top: 0px !important;padding: 0px !important;" data-dismiss="alert" aria-hidden="true">&times;</button>

        {{ $flash_message }}
    </div>
@endif