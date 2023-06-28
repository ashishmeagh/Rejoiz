<?php
namespace App\Common\Services;

use App\Models\UserModel;
use App\Models\InfluencerRewardsModel;

use DB;
use DateTime;

use App\Events\NotificationEvent;

class InfluencerService {

	public function __construct(UserModel $UserModel,
								InfluencerRewardsModel $InfluencerRewardsModel)
	{
		$this->UserModel              = $UserModel;
		$this->InfluencerRewardsModel = $InfluencerRewardsModel;
	}

	public function get_influencer_rewards_history_listing($form_data, $loggedIn_userId = false)
	{

		$arr_search_column = isset($form_data['column_filter'])?$form_data['column_filter']:[];

		$user_tbl        = $this->UserModel->getTable();
    	$prefix_user_tbl = DB::getTablePrefix().$user_tbl;

    	$influencer_rewards_tbl        = $this->InfluencerRewardsModel->getTable();
    	$prefix_influencer_rewards_tbl = DB::getTablePrefix().$influencer_rewards_tbl;


    	$obj_data = DB::table($influencer_rewards_tbl)
					->select(DB::raw($prefix_influencer_rewards_tbl.'.*,'.
						  "CONCAT(".$prefix_user_tbl.".first_name,' ',"
                          .$prefix_user_tbl.".last_name) as user_name"
					))
					->leftJoin($user_tbl,$user_tbl.'.id','=',$influencer_rewards_tbl.'.influencer_id')
                    ->orderBy($influencer_rewards_tbl.'.created_at','DESC');

        if($loggedIn_userId){
            $obj_data = $obj_data->where($influencer_rewards_tbl.'.influencer_id',$loggedIn_userId);
        }

    	if(isset($arr_search_column['q_user_name']) && $arr_search_column['q_user_name']!="")
        {
            $search_term   = $arr_search_column['q_user_name'];
            $obj_data      = $obj_data->having('user_name','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_reward_amount']) && $arr_search_column['q_reward_amount']!="")
        {
            $search_term  = $arr_search_column['q_reward_amount'];
            $obj_data     = $obj_data->where($influencer_rewards_tbl.'.reward_amount','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_status']) && $arr_search_column['q_status']!="")
        {
            $search_term  = $arr_search_column['q_status'];
            $obj_data     = $obj_data->where($influencer_rewards_tbl.'.status','=', $search_term);
        }

         if(isset($arr_search_column['q_reward_date']) && $arr_search_column['q_reward_date']!="")
        {
            $search_term  = $arr_search_column['q_reward_date'];
            $date         = DateTime::createFromFormat('m-d-Y',$search_term);
            $date         = $date->format('Y-m-d');

            $obj_data     = $obj_data->where($influencer_rewards_tbl.'.created_at','LIKE', '%'.$date.'%');
        }   

        return $obj_data;
	}

    public function get_transaction_history_listing($form_data, $loggedIn_userId = false)
    {

        $arr_search_column = isset($form_data['column_filter'])?$form_data['column_filter']:[];

        $user_tbl        = $this->UserModel->getTable();
        $prefix_user_tbl = DB::getTablePrefix().$user_tbl;

        $influencer_rewards_tbl        = $this->InfluencerRewardsModel->getTable();
        $prefix_influencer_rewards_tbl = DB::getTablePrefix().$influencer_rewards_tbl;


        $obj_data = DB::table($influencer_rewards_tbl)
                    ->select(DB::raw($prefix_influencer_rewards_tbl.'.*,'.
                          "CONCAT(".$prefix_user_tbl.".first_name,' ',"
                          .$prefix_user_tbl.".last_name) as user_name"
                    ))
                    ->leftJoin($user_tbl,$user_tbl.'.id','=',$influencer_rewards_tbl.'.influencer_id')
                    ->where($influencer_rewards_tbl.'.status','!=','1') //1 -Pending
                    ->orderBy($influencer_rewards_tbl.'.created_at','DESC');

        if($loggedIn_userId){
            $obj_data = $obj_data->where($influencer_rewards_tbl.'.influencer_id',$loggedIn_userId);
        }

        if(isset($arr_search_column['q_user_name']) && $arr_search_column['q_user_name']!="")
        {
            $search_term   = $arr_search_column['q_user_name'];
            $obj_data      = $obj_data->having('user_name','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_reward_amount']) && $arr_search_column['q_reward_amount']!="")
        {
            $search_term  = $arr_search_column['q_reward_amount'];
            $obj_data     = $obj_data->where($influencer_rewards_tbl.'.reward_amount','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_status']) && $arr_search_column['q_status']!="")
        {
            $search_term  = $arr_search_column['q_status'];
            $obj_data     = $obj_data->where($influencer_rewards_tbl.'.status','=', $search_term);
        }

        if(isset($arr_search_column['q_transaction_id']) && $arr_search_column['q_transaction_id']!="")
        {
            $search_term  = $arr_search_column['q_transaction_id'];
            $obj_data     = $obj_data->where($influencer_rewards_tbl.'.transaction_id','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_transfer_id']) && $arr_search_column['q_transfer_id']!="")
        {
            $search_term  = $arr_search_column['q_transfer_id'];
            $obj_data     = $obj_data->where($influencer_rewards_tbl.'.transfer_id','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_created_at']) && $arr_search_column['q_created_at']!="")
        {
            $search_term      = $arr_search_column['q_created_at'];
            $date             = DateTime::createFromFormat('m-d-Y',$search_term);
            $date             = $date->format('Y-m-d');
            
            //$search_term  = date('Y-m-d',strtotime($search_term));
            $obj_data = $obj_data->where($influencer_rewards_tbl.'.created_at','LIKE', '%'.$date.'%');
        }

        return $obj_data;
    }
    

    /************************Notification Event START**************************/

    public function save_notification($ARR_DATA = [])
    {  
        if(isset($ARR_DATA) && count($ARR_DATA)>0)
        {
            $ARR_EVENT_DATA                 = [];
            $ARR_EVENT_DATA['from_user_id'] = $ARR_DATA['from_user_id'];
            $ARR_EVENT_DATA['to_user_id']   = $ARR_DATA['to_user_id'];
            $ARR_EVENT_DATA['description']  = $ARR_DATA['description'];
            $ARR_EVENT_DATA['title']        = $ARR_DATA['title'];
            $ARR_EVENT_DATA['type']         = $ARR_DATA['type'];
            $ARR_EVENT_DATA['link']         = isset($ARR_DATA['link'])?$ARR_DATA['link']:'';

            $ARR_EVENT_DATA['status']       = isset($ARR_DATA['status'])?$ARR_DATA['status']:0; 

            event(new NotificationEvent($ARR_EVENT_DATA));

            return true;
        }
        return false;
    }

    /************************Notification Event END  **************************/
}

?>