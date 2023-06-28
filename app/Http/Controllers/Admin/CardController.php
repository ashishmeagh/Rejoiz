<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Common\Services\StripePaymentService;
use App\Models\CardModel;
use Validator;
use Stripe;
use Flash;

class CardController extends Controller
{
    function __construct()
    {
    	$this->module_title         = 'Cards';
    	$this->module_view_folder   = 'admin.card'; 
    	$this->retailer_panel_slug  = config('app.project.admin_panel_slug');
    	$this->module_url_path      = url($this->retailer_panel_slug.'/card');
    	$this->module_view_folder   = 'admin/card';
        $this->BaseModel            = new CardModel();
        $this->StripePaymentService = new StripePaymentService();
    	$this->arr_view_data        = [];
        // $this->stripe_api_key       = 'sk_test_UQE8wx6WNY7Ogj1A5Uy1ZMWA00Cjg1fs3r';
        $this->stripe_api_key       = get_admin_stripe_key();

    }

    public function index()
    {
        $user_id = \Sentinel::getUser()->id;
        $arr_card_details = [];
        
    	$arr_card_details = $this->BaseModel->where('user_id',$user_id)
                                            ->get()
                                            ->toArray();
                                            

        /*Stripe\Stripe::setApiKey($this->stripe_api_key);

        $new_card_details = $card_details= [];

        if(isset($arr_card_details) && sizeof($arr_card_details)>0)
        {
           foreach($arr_card_details as $list)
           {
                $customer = \Stripe\Customer::retrieve($list['stripe_customer_id']);
                $card = $customer->sources->retrieve($list['stripe_card_id']);
                
                $card_details['id']         = $list['id'];
                $card_details['customer_id']= $customer->id;
                $card_details['card_type']  = $card->brand;
                $card_details['card_no']    = $card->last4;
                $card_details['exp_month']  = $card->exp_month;
                $card_details['exp_year']   = $card->exp_year;
                $card_details['stripe_card_id'] = $card->id;
                $new_card_details[] = $card_details;
                
           } 
        }*/

        $arr_cards = [];

        if($arr_card_details && count($arr_card_details))
        {
          $arr_cards = $this->StripePaymentService->get_card_data($arr_card_details);
        }
        
        if(isset($arr_cards['status']) && $arr_cards['status'] == 'Error'){
          /*flash::error(isset($arr_cards['description'])?$arr_cards['description']:'Something went wrong,please try again.');
          return redirect('retailer/dashboard');*/
          $arr_cards = [];
        }

        $this->arr_view_data['module_title']     = str_plural($this->module_title);
    	$this->arr_view_data['page_title']       = str_plural($this->module_title);
        $this->arr_view_data['module_url_path']  = $this->module_url_path;
    	// $this->arr_view_data['arr_card_details'] = $new_card_details;
        $this->arr_view_data['arr_card_details'] = $arr_cards;

    	return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

    public function add()
    {
        
    	$this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['module_title'] = 'Add '.$this->module_title;
    	$this->arr_view_data['page_title'] = 'Add '.str_singular($this->module_title);
    	return view($this->module_view_folder.'.add',$this->arr_view_data);
    }

    public function store(Request $request)
    {
        $user = \Sentinel::check();
        $form_data = $request->all();
        
        if($user)
        {
          $user_id = $user->id;
        }

        $adminId = get_admin_id();
        $adminStripeKeyData = $this->StripePaymentService->get_active_stripe_key($adminId);

        $stripe_key_id = isset($adminStripeKeyData['id'])?$adminStripeKeyData['id']:false;

        $get_user = $this->CardModel->where('user_id',$user_id)
                                    ->where('stripe_key_id',$stripe_key_id)
                                    ->groupBy('fingerprint')
                                    ->get();
        

        if(!isset($request->card_id))
        {
            if(count($get_user) >= 6)
            {
               $response['status']      = 'warning';
               $response['description'] = 'You can add only six cards.';
               return response()->json($response); 
            }
        }
    
        $arr_rule=[
                    'number' => 'required',
                    'expiry' => 'required',
                    'cvc'    => 'required',
                  ];

        $validator = Validator::make($request->all(),$arr_rule);
        if($validator->fails())
        {
           $response['status']      = 'warning';
           $response['description'] ='Form validation failed, please check all fields.';

          return response()->json($response);
        }

        Stripe\Stripe::setApiKey($this->stripe_api_key);

        /* Seprate Card Expiry Month And Year */

        $card_expiry_date = $request->input('expiry');
        $arr_expire       = explode('/', $card_expiry_date);
        $expire_month     = trim($arr_expire[0]);
        $expire_year      = trim($arr_expire[1]);
        $cvv              = trim($request->cvc);


        // Create a token
        $token = $this->StripePaymentService->create_card_token($request->number,$expire_month,$expire_year,$cvv);
        
        if(isset($token['status']) && $token['status'] == 'Error')
        {
            $response['status']      = 'warning';
            $response['description'] = isset($token['description'])?$token['description']:'Something went wrong,please try again.';
            return response()->json($response);
        }

        if($token)
        {
            if(count($get_user) > 0)
            {
                $cust_list = $get_user->toArray();

                $customer = \Stripe\Customer::retrieve($cust_list['stripe_customer_id']);
                
                if($customer)
                {
                   /* $card = $customer->sources->create(array(
                        "source" => $token
                    ));*/

                    try
                    {

                      $response = $customer->sources->create(array(
                                "source" => $token,
                                 "metadata" => ["CardNo"   => $request->number,
                                                "ExpMonth" => $expire_month,
                                                "ExpYear"  => $expire_year,
                                                "Cvv"      => $cvv
                                               ]
                              ));

                    }
                    catch (\Stripe\Error\RateLimit $e) 
                    {
                         $error = $e->getJsonBody();
                         $msg = $error['error']['message'];
                         $response['status'] = 'Error';
                         $response['description'] = $msg;
                        
                    } catch (\Stripe\Error\InvalidRequest $e) {
                         $error = $e->getJsonBody();
                         $msg = $error['error']['message'];
                         $response['status'] = 'Error';
                         $response['description'] = $msg;
                        
                    } catch (\Stripe\Error\Authentication $e) {
                         $error = $e->getJsonBody();
                         $msg = $error['error']['message'];
                         $response['status'] = 'Error';
                         $response['description'] = $msg;
                        
                    } catch (\Stripe\Error\ApiConnection $e) {
                         $error = $e->getJsonBody();
                         $msg = $error['error']['message'];
                         $response['status'] = 'Error';
                         $response['description'] = $msg;
                        
                    } catch (\Stripe\Error\Base $e) {
                         $error = $e->getJsonBody();
                         $msg = $error['error']['message'];
                         $response['status'] = 'Error';
                         $response['description'] = $msg;
                        
                    } catch (Exception $e) {
                         $error = $e->getJsonBody();
                         $msg = $error['error']['message'];
                         $response['status'] = 'Error';
                         $response['description'] = $msg;
                    
                    }


                    if(isset($response['status']) && $response['status'] == 'Error')
                    {
                        $response['status'] = 'warning';
                        $response['description'] = $response['description'] or '';
                        return response()->json($response);
                    }
                    else
                    {
                        $data['user_id']            = $user_id;
                        $data['stripe_customer_id'] = $cust_list['stripe_customer_id'];
                        $data['stripe_card_id']     = $response->id;
                        $data['stripe_card_id']     = $response->id;
                        $data['fingerprint']        = isset($token->card['fingerprint'])?$token->card['fingerprint']:'';

                    }

                }

                // $action = $this->BaseModel->create($data);
            }
            else
            {
                // Create a Customer
                $new_customer = \Stripe\Customer::create(array(
                    "email" => $user->email
                ));

                if($new_customer)
                {
                    $customer = \Stripe\Customer::retrieve($new_customer->id);

                    if($customer) 
                    {
                       /* $card = $customer->sources->create(array(
                            "source" => $token
                        ));  */



                        try
                        {
                           $response = $customer->sources->create(array(
                                "source" => $token,
                                 "metadata" => ["CardNo"   => $request->number,
                                                "ExpMonth" => $expire_month,
                                                "ExpYear"  => $expire_year,
                                                "Cvv"      => $cvv
                                               ]
                            ));  

                        }
                        catch (\Stripe\Error\RateLimit $e) 
                        {
                             $error = $e->getJsonBody();
                             $msg = $error['error']['message'];
                             $response['status'] = 'Error';
                             $response['description'] = $msg;

                        } catch (\Stripe\Error\InvalidRequest $e) {
                             $error = $e->getJsonBody();
                             $msg = $error['error']['message'];
                             $response['status'] = 'Error';
                             $response['description'] = $msg;
                        } catch (\Stripe\Error\Authentication $e) {
                             $error = $e->getJsonBody();
                             $msg = $error['error']['message'];
                             $response['status'] = 'Error';
                             $response['description'] = $msg;
                        } catch (\Stripe\Error\ApiConnection $e) {
                             $error = $e->getJsonBody();
                             $msg = $error['error']['message'];
                             $response['status'] = 'Error';
                             $response['description'] = $msg;
                        } catch (\Stripe\Error\Base $e) {
                             $error = $e->getJsonBody();
                             $msg = $error['error']['message'];
                             $response['status'] = 'Error';
                             $response['description'] = $msg;
                        } catch (Exception $e) {
                             $error = $e->getJsonBody();
                             $msg = $error['error']['message'];
                             $response['status'] = 'Error';
                             $response['description'] = $msg;
                        }

                        if(isset($response['status']) && $response['status'] == 'Error')
                        {
                            $response['status'] = 'warning';
                            $response['description'] = $response['description'] or '';
                            return response()->json($response);
                        }
                        else
                        {
                            $data['user_id']            = $user_id;
                            $data['stripe_customer_id'] = $customer->id;
                            $data['stripe_card_id']     = $response->id;
                            $data['fingerprint']        = isset($token->card['fingerprint'])?$token->card['fingerprint']:'';

                        }

                        
                    }
                }                
            } 

            $action      = $this->BaseModel->create($data);

            if($action)
            {
                $response['status']      = 'success';
                $response['description'] = 'Your card details has been saved.';
                $response['link'] = url(config('app.project.admin_panel_slug').'/card');
                return response()->json($response);
            }
        }
        else
        {
            $response['status'] = 'warning';
            $response['description'] = $response['description'] or 'Something went wrong, please try again.';
            return response()->json($response);
        }
    }

    public function edit($enc_id,$customer_id)
    {
        $card_id     = isset($enc_id)?base64_decode($enc_id):'';
        $customer_id = isset($customer_id)?base64_decode($customer_id):'';

        $card_data = [];

        if($card_id && $customer_id){

             $card_data = $this->StripePaymentService->get_single_card_details($card_id,$customer_id);

             if(isset($card_data['status']) && $card_data['status'] == 'Error')
             {
                $msg = isset($card_data['description'])?$card_data['description']:'Something went wrong, please try again.';

                flash::error($msg);
                return redirect()->back();
             }

             if(empty($card_data))
             {
                flash::error('Something went wrong, please try again.');
                return redirect()->back();
             }
        }

        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['module_title']    = 'Edit '.$this->module_title;
        $this->arr_view_data['page_title']      = 'Edit '.str_singular($this->module_title);
        $this->arr_view_data['card']            = $card_data;
        return view($this->module_view_folder.'.edit',$this->arr_view_data);
    }

    public function delete_card($enc_id,$customer_id)
    {
        $card_id     = isset($enc_id)?base64_decode($enc_id):'';
        $customer_id = isset($customer_id)?base64_decode($customer_id):'';
        // dd($card_id);
        if($card_id)
        {
            $card_data = $this->StripePaymentService->delete_card_details($card_id,$customer_id);

            if(isset($card_data['status']) && $card_data['status'] == 'Error')
            {
                $response['status']      = 'warning';
                $response['description'] = isset($card_data['description'])?$card_data['description']:'Something went wrong.';
                return response()->json($response);
            }
            else
            {
               $delete = $this->BaseModel->where('stripe_card_id',$card_id)->delete();

               if($delete){
                    $response['status']      = 'success';
                    $response['description'] = 'Your card has been removed.';
                    return response()->json($response);
               }
               else
               {
                    $response['status']      = 'error';
                    $response['description'] = 'Something went wrong, please try again.';
                    return response()->json($response);
               }  
            }
        }
    }

    public function update(Request $request)
    {
        $user = \Sentinel::check();
        $form_data = $request->all();
        
        if($user)
        {
          $user_id = $user->id;
        }

        $get_user = $this->BaseModel->where('user_id',$user_id)->first();
    
        $arr_rule=['expiry' => 'required'];

        $validator = Validator::make($request->all(),$arr_rule);
        if($validator->fails())
        {
           $response['status']      = 'warning';
           $response['description'] = 'Form validation failed, please check all fields.';

          return response()->json($response);
        }

        $card_id     = $request->card_id;
        $customer_id = $request->customer_id;

        Stripe\Stripe::setApiKey($this->stripe_api_key);

        /* Seprate Card Expiry Month And Year */

        $card_expiry_date = $request->input('expiry');
        $arr_expire       = explode('/', $card_expiry_date);
        $expire_month     = trim($arr_expire[0]);
        $expire_year      = trim($arr_expire[1]);
        $cvv              = trim($request->cvv);

        $update_card_data = $this->StripePaymentService->update_card_details($card_id,$customer_id,$expire_month,$expire_year);
        
        if(isset($update_card_data['status']) && $update_card_data['status'] == 'Error')
        {
            $response['status']      = 'warning';
            $response['description'] = isset($update_card_data['description'])?$update_card_data['description']:'Something went wrong.';
            return response()->json($response);
        }

        if($update_card_data)
        {
            $response['status']      = 'success';
            $response['description'] = 'Your card details has been saved.';
            $response['link']        = url(config('app.project.admin_panel_slug').'/card');
           
            return response()->json($response);
        }
        else
        {
            $response['status']      = 'error';
            $response['description'] = 'Something went wrong, please try again.';
            return response()->json($response);
        }
    }
}


