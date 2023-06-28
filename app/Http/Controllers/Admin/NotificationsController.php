<?php

namespace App\Http\Controllers\Admin;

use App\Common\Services\LanguageService;
use App\Common\Services\ReportService;
use App\Common\Traits\MultiActionTrait;
use App\Http\Controllers\Controller;
use App\Models\ActivityLogsModel;
use App\Models\NotificationsModel;
use DateTime;
use DB;
use Flash;
use Illuminate\Http\Request;
use Sentinel;
use Validator;

class NotificationsController extends Controller {
	use MultiActionTrait;
	public $NotificationsModel;
	public function __construct(NotificationsModel $notification,
		LanguageService $langauge,
		ActivityLogsModel $activity_logs,
		ReportService $ReportService) {
		$this->NotificationsModel = $notification;
		$this->BaseModel = $this->NotificationsModel;
		$this->ActivityLogsModel = $activity_logs;

		$this->LanguageService = $langauge;
		$this->ReportService = $ReportService;
		$this->module_title = "Notifications";
		$this->module_url_slug = "notifications";
		$this->module_view_folder = "admin/notifications";
		$this->module_url_path = url(config('app.project.admin_panel_slug') . "/notifications");
	}

	public function index1(Request $request) {

		$order_from_date = $request->input('order_from_date', null);
		$order_to_date = $request->input('order_to_date', null);

		$loggedInUserId = 0;
		$user = Sentinel::check();
		if ($user) {
			$loggedInUserId = $user->id;
		}

		$arr_data = $this->BaseModel->orderBy('id', 'DESC')
			->where('type', 'admin')
			->where('to_user_id', $loggedInUserId);

		if ($order_from_date != null && $order_to_date != null) {
			$from_date = date("Y-d-m", strtotime($order_from_date));
			$to_date = date("Y-d-m", strtotime($order_to_date));

			$from_date = $from_date . ' 00:00:00';
			$to_date = $to_date . ' 23:59:59';
			//dd($from_date,$to_date);
			$arr_data = $arr_data->whereBetween('created_at', array($from_date, $to_date));
		}

		$arr_data = $arr_data->get()->toArray();

		$update_read_status = $this->BaseModel->where('type', 'admin')->update(['is_read' => '1']);

		$this->arr_view_data['arr_data'] = $arr_data;
		$this->arr_view_data['page_title'] = str_plural($this->module_title);
		$this->arr_view_data['module_title'] = str_plural($this->module_title);
		$this->arr_view_data['request_data'] = $request->all();
		$this->arr_view_data['module_url_path'] = $this->module_url_path;

		return view($this->module_view_folder . '.index', $this->arr_view_data);
	}

	public function index() {
		$this->arr_view_data['page_title'] = str_plural($this->module_title);
		$this->arr_view_data['module_title'] = str_plural($this->module_title);
		$this->arr_view_data['module_url_path'] = $this->module_url_path;

		return view($this->module_view_folder . '.index', $this->arr_view_data);
	}

	public function get_notifications(Request $request) {
		$loggedInUserId = 0;
		$user = Sentinel::check();
		if ($user) {
			$loggedInUserId = $user->id;
		}

		$arr_search_column = $request->input('column_filter');

		$notification_table = $this->NotificationsModel->getTable();
		$prefix_notification_table = DB::getTablePrefix() . $notification_table;

		$obj_notification = DB::table($notification_table)
			->select(DB::raw($prefix_notification_table . ".id as id," .
				$prefix_notification_table . ".type," .
				$prefix_notification_table . ".title," .
				$prefix_notification_table . ".from_user_id," .
				$prefix_notification_table . ".to_user_id," .
				$prefix_notification_table . ".description," .
				$prefix_notification_table . ".is_read," .
				$prefix_notification_table . ".status," .
				$prefix_notification_table . ".notification_url," .
				$prefix_notification_table . ".created_at"
			))
			->orderBy($prefix_notification_table . '.id', 'DESC')
			->where($prefix_notification_table . '.type', 'admin')
			->where($prefix_notification_table . '.to_user_id', $loggedInUserId);

		/* ---------------- Filtering Logic ----------------------------------*/

/*        if(isset($arr_search_column['q_username']) && $arr_search_column['q_username']!="")
{
$search_term      = $arr_search_column['q_username'];
$obj_user = $obj_user->having('user_name','LIKE', '%'.$search_term.'%');
}
if(isset($arr_search_column['q_amount']) && $arr_search_column['q_amount']!="")
{
$search_term      = $arr_search_column['q_amount'];
$obj_user = $obj_user->where($transaction_mapping_table.'.amount','LIKE', '%'.$search_term.'%');
}

if(isset($arr_search_column['q_transaction_status']) && $arr_search_column['q_transaction_status']!="")
{
$search_term      = $arr_search_column['q_transaction_status'];
$obj_user = $obj_user->where($transaction_mapping_table.'.transaction_status','LIKE', '%'.$search_term.'%');
}

if(isset($arr_search_column['q_transaction_id']) && $arr_search_column['q_transaction_id']!="")
{

$search_term      = $arr_search_column['q_transaction_id'];

$obj_user = $obj_user->where($transaction_mapping_table.'.transaction_id','LIKE', '%'.$search_term.'%');

}

if(isset($arr_search_column['q_order_no']) && $arr_search_column['q_order_no']!="")
{
$search_term      = $arr_search_column['q_order_no'];

$obj_user = $obj_user->where($transaction_mapping_table.'.order_no','LIKE', '%'.$search_term.'%');
}*/

		if ((isset($arr_search_column['q_from_date']) && $arr_search_column['q_from_date'] != "") && (isset($arr_search_column['q_to_date']) && $arr_search_column['q_to_date'] != "")) {
			$search_term_from_date = $arr_search_column['q_from_date'];
			$search_term_to_date = $arr_search_column['q_to_date'];

			$from_date = DateTime::createFromFormat('m/d/Y', $search_term_from_date);
			$from_date = $from_date->format('Y-m-d');
			$to_date = DateTime::createFromFormat('m/d/Y', $search_term_to_date);
			$to_date = $to_date->format('Y-m-d');

			$obj_notification = $obj_notification->whereDate($prefix_notification_table . '.created_at', '<=', $to_date);

			$obj_notification = $obj_notification->whereDate($prefix_notification_table . '.created_at', '>=', $from_date);

		}

		$current_context = $this;
		$json_result = \Datatables::of($obj_notification);

		$json_result = $json_result->editColumn('enc_id', function ($data) use ($current_context) {
			return base64_encode($data->id);
		})
			->editColumn('created_at', function ($data) use ($current_context) {
				return $formated_date = notification_format_date($data->created_at);
			})
			->editColumn('description', function ($data) use ($current_context) {
				/* if(isset($data->title) && $data->title=="Order Shipped")
					                            {
					                                $target="target='_blank'";
					                            }
					                            else
					                            {
					                                $target="";
				*/

				$noti_desc = isset($data->description) ? $data->description : "";
				$noti_url = isset($data->notification_url) ? $data->notification_url : "";

				if (isset($data->is_read) && $data->is_read == 0) {
					/*return $url = '<u><b><a class="linkankrtg" href="javascript:void(0);"'.$target.' data-id="'.$data->id.'" onclick="readNotification(this);">'.$noti_desc.'</a></b></u>';*/

					return $url = '<a class="linkankrtg" href="javascript:void(0);" data-id="' . $data->id . '" onclick="readNotification(this);">' . $noti_desc . '</a>';
				} else {
					return $url = '<a class="" href="' . $noti_url . '">' . $noti_desc . '</a>';
				}

			})
			->editColumn('action', function ($data) use ($current_context) {
				return $action = '<a href="' . $this->module_url_path . '/delete/' . base64_encode(
					$data->id) . '" data-toggle="tooltip" data-size="small" title="Delete" class="btn btn-outline btn-info btn-circle show-tooltip btn-retailer-view deletestyle" onclick="confirm_delete(this,event);" data-original-title="Delete">Delete</a>';
			})->make(true);

		$build_result = $json_result->getData();

		return response()->json($build_result);

	}

	public function delete($enc_id = FALSE) {
		if (!$enc_id) {
			return redirect()->back();
		}
		if ($this->perform_delete(base64_decode($enc_id))) {

			Flash::success('Notification has been deleted.');
			return redirect()->back();
		} else {
			Flash::error('Error occurred while notification deletion.');
			return redirect()->back();
		}

	}

	public function multi_action(Request $request) {

		$arr_rules = array();
		$arr_rules['multi_action'] = "required";
		$arr_rules['checked_record'] = "required";

		$validator = Validator::make($request->all(), $arr_rules);

		if ($validator->fails()) {
			Flash::error('Please select ' . $this->module_title . ' to perform multi actions.');
			return redirect()->back()->withErrors($validator)->withInput();
		}

		$multi_action = $request->input('multi_action');
		$checked_record = $request->input('checked_record');

		/* Check if array is supplied*/
		if (is_array($checked_record) && sizeof($checked_record) <= 0) {
			Flash::error('Problem occurred,while doing multi action.');
			return redirect()->back();
		}

		foreach ($checked_record as $key => $record_id) {

			if ($multi_action == "delete") {
				$this->perform_delete(base64_decode($record_id));
				Flash::success('notifications has been deleted.');
			}

			if ($multi_action == "mark_as_read") {
				$data = [];
				$data['is_read'] = '1';
				$this->NotificationsModel->where('id', base64_decode($record_id))->update($data);
				Flash::success('notifications has been read.');
			}
		}

		return redirect()->back();
	}

	public function export_notifications() {
		$loggedInUserId = 0;
		$user = Sentinel::check();
		if ($user) {
			$loggedInUserId = $user->id;
		}

		$notification_obj = $this->BaseModel->orderBy('id', 'DESC')
			->where('to_user_id', $loggedInUserId)
			->get();

		if ($notification_obj) {
			$notification_arr = $notification_obj->toArray();

		}

		$notification_data = [];
		$notification = [];

		foreach ($notification_arr as $key => $value) {

			$notification_data['Date'] = notification_format_date($value['created_at']);
			$notification_data['Title'] = $value['title'];
			$notification_data['Description'] = $value['description'];

			array_push($notification, $notification_data);
		}

		$this->ReportService->notification_report($notification);

	}

	public function read_notification($notification_id) {
		$notification_arr = [];
		$id = base64_decode($notification_id);

		$get_notifications_data = $this->NotificationsModel->where('id', $id)->first();

		if (isset($get_notifications_data)) {
			$notification_arr = $get_notifications_data->toArray();
		}
		$data = [];
		$data['is_read'] = '1';

		$result = $this->NotificationsModel->where('id', $id)->update($data);

		if ($result) {
			$response['status'] = 'success';
			$response['description'] = 'Notification has been read.';
			$response['url'] = $notification_arr['notification_url'];

			return response()->json($response);
		} else {
			$response['status'] = 'error';
			$response['description'] = 'Something went wrong,please try again.';

			return response()->json($response);
		}
	}

}