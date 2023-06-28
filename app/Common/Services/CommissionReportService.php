<?php
namespace App\Common\Services;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\StripeTransactionModel;
use App\Models\UserModel;
use App\Models\MakerModel;
use App\Models\RetailerModel;
use App\Models\RetailerQuotesModel;
use App\Models\CustomerQuotesModel;
use App\Models\RoleUsersModel;
use App\Models\RepresentativeLeadsModel;
use App\Models\TransactionMappingModel;
use App\Models\RepresentativeModel;
use App\Models\SalesManagerModel;
use App\Models\EmailTemplateModel;
use App\Models\RetailerQuotesProductModel;
use App\Models\RepresentativeProductLeadsModel;
use App\Models\CustomerModel;
use App\Models\CustomerQuotesProductModel;
use App\Models\SiteSettingModel;
use Cartalyst\Stripe\Stripe;
use Stripe\Error\Card;
use App\Common\Services\EmailService;
use App\Common\Services\CommissionService;
use App\Events\NotificationEvent;
use Session;
use DB;
use Datatables;
use Excel;
use PDF;
use Storage;
use Sentinel;
use Mail;
use DateTime;


class CommissionReportService
{
    public function __construct(StripePaymentService $StripePaymentService,
                                StripeTransactionModel $StripeTransactionModel,
                                GeneralService $GeneralService,
                                RetailerQuotesModel $RetailerQuotesModel,
                                CustomerQuotesModel $CustomerQuotesModel,
                                RetailerModel $RetailerModel,
                                RoleUsersModel $RoleUsersModel,
                                RepresentativeLeadsModel $RepresentativeLeadsModel,
                                TransactionMappingModel $TransactionMappingModel,
                                UserModel $UserModel,
                                RepresentativeModel $RepresentativeModel,
                                SalesManagerModel $SalesManagerModel,
                                MakerModel $MakerModel,
                                EmailTemplateModel $EmailTemplateModel,
                                RetailerQuotesProductModel $RetailerQuotesProductModel,
                                RepresentativeProductLeadsModel $RepresentativeProductLeadsModel,
                                CustomerModel $CustomerModel,
                                CustomerQuotesProductModel $CustomerQuotesProductModel,
                                SiteSettingModel $SiteSettingModel,
                                CommissionService $CommissionService,
                                EmailService $EmailService

                                )
    {
        $this->arr_view_data      = [];
        $this->StripeTransactionModel     = $StripeTransactionModel;
        $this->MakerModel                 = $MakerModel;
        $this->RetailerModel              = $RetailerModel;
        $this->RetailerQuotesModel        = $RetailerQuotesModel;
        $this->CustomerQuotesModel        = $CustomerQuotesModel;
        $this->RepresentativeLeadsModel   = $RepresentativeLeadsModel;
        $this->TransactionMappingModel    = $TransactionMappingModel;
        $this->RoleUsersModel             = $RoleUsersModel;
        $this->RepresentativeModel        = $RepresentativeModel;
        $this->SalesManagerModel          = $SalesManagerModel;
        $this->UserModel                  = $UserModel;
        $this->EmailTemplateModel         = $EmailTemplateModel;
        $this->RetailerQuotesProductModel = $RetailerQuotesProductModel;
        $this->RepresentativeProductLeadsModel = $RepresentativeProductLeadsModel;
        $this->CustomerModel              = $CustomerModel;
        $this->CustomerQuotesProductModel = $CustomerQuotesProductModel;
        $this->SiteSettingModel           = $SiteSettingModel;
        $this->CommissionService          = $CommissionService; 
        $this->EmailService               = $EmailService; 
    }

    public function all_rep_order_query()
    {
        $user_table =  $this->UserModel->getTable();
        $prefix_user_table = DB::getTablePrefix().$user_table;

        $makerTable =  $this->MakerModel->getTable();
        $prefixmakerTable= DB::getTablePrefix().$makerTable;

        $RepresentativeTable =  $this->RepresentativeModel->getTable();
        $prefixRepresentativeTable= DB::getTablePrefix().$RepresentativeTable;

        $SalesManagerTable =  $this->SalesManagerModel->getTable();
        $prefixSalesManagerTable= DB::getTablePrefix().$SalesManagerTable;

        $RepresentativeLeadsTable =  $this->RepresentativeLeadsModel->getTable();
        $prefixRepresentativeLeadsTable = DB::getTablePrefix().$RepresentativeLeadsTable;

        $StripeTransactionTable =  $this->StripeTransactionModel->getTable();
        $prefixStripeTransactionTable = DB::getTablePrefix().$StripeTransactionTable;

        $TransactionMappingTable =  $this->TransactionMappingModel->getTable();
        $prefixTransactionMappingTable = DB::getTablePrefix().$TransactionMappingTable;

        $role_user_table =  $this->RoleUsersModel->getTable();
        $prefix_role_user_table = DB::getTablePrefix().$role_user_table;

        $objprefixStripeTransaction = DB::table($RepresentativeLeadsTable)
                                      ->select(DB::raw(
                                            $StripeTransactionTable.".*,".  
                                            $RepresentativeLeadsTable.".id as order_id,".
                                            $RepresentativeLeadsTable.".order_no,".
                                            $RepresentativeLeadsTable.".maker_id,".
                                            $RepresentativeLeadsTable.".retailer_id,".
                                            $RepresentativeLeadsTable.".representative_id,".
                                            $RepresentativeLeadsTable.".sales_manager_id,".
                                            $RepresentativeLeadsTable.".total_wholesale_price,".
                                            $RepresentativeLeadsTable.".rep_commission_status,".
                                            $RepresentativeLeadsTable.".maker_commission_status,".
                                            $RepresentativeLeadsTable.".sales_manager_commission_status,".  
                                            $RepresentativeLeadsTable.".created_at as order_date,".  
                                            $prefixTransactionMappingTable.".transaction_status,".  
                                            $makerTable.".company_name as vendor_name,".  

                                            // "CONCAT(VN.first_name,' ',VN.last_name) as vendor_name,".
                                            "CONCAT(RP.first_name,' ',RP.last_name) as rep_name,".
                                            "CONCAT(SM.first_name,' ',SM.last_name) as sales_manager_name,".
                                            "CONCAT(RN.first_name,' ',RN.last_name) as retailer_name"
                                        ))          

                            ->leftjoin($StripeTransactionTable,$RepresentativeLeadsTable.'.id','=',$StripeTransactionTable.'.quote_id')

                            ->leftjoin($user_table.' AS RN','RN.id','=',$prefixRepresentativeLeadsTable.'.retailer_id')

                            ->leftjoin($role_user_table,$prefix_role_user_table.'.user_id','=',$prefixStripeTransactionTable.'.received_by')

                            // ->leftjoin($StripeTransactionTable.' as ST',$StripeTransactionTable.'.user_role','!=','2')
                    
                            
                            ->leftjoin($prefixTransactionMappingTable,function($join) use($RepresentativeLeadsTable,$prefixTransactionMappingTable){

                                $join->on($RepresentativeLeadsTable.'.id','=',$prefixTransactionMappingTable.'.order_id')
                                ->on($RepresentativeLeadsTable.'.order_no','=',$prefixTransactionMappingTable.'.order_no');

                            })

                            ->leftjoin($user_table.' AS VN','VN.id','=',$RepresentativeLeadsTable.'.maker_id')

                            ->leftjoin($makerTable,$makerTable.'.user_id','=',$RepresentativeLeadsTable.'.maker_id')
                    
                            ->leftjoin($user_table.' AS RP','RP.id','=',$RepresentativeLeadsTable.'.representative_id')
                      
                            ->leftjoin($user_table.' AS SM','SM.id','=',$RepresentativeLeadsTable.'.sales_manager_id')
                      
                            ->whereNull('RP.deleted_at')
                            
                            ->where($RepresentativeLeadsTable.'.is_confirm', '=', 1)
                            
                            ->orderBy('RP.created_at','DESC');
                    return $objprefixStripeTransaction;
    }


    public function rep_order_query($formData,$arr_search_column)
    {
        $user_table =  $this->UserModel->getTable();
        $prefix_user_table = DB::getTablePrefix().$user_table;

        $makerTable =  $this->MakerModel->getTable();
        $prefixmakerTable= DB::getTablePrefix().$makerTable;

        $RepresentativeTable =  $this->RepresentativeModel->getTable();
        $prefixRepresentativeTable= DB::getTablePrefix().$RepresentativeTable;

        $SalesManagerTable =  $this->SalesManagerModel->getTable();
        $prefixSalesManagerTable= DB::getTablePrefix().$SalesManagerTable;

        $RepresentativeLeadsTable =  $this->RepresentativeLeadsModel->getTable();
        $prefixRepresentativeLeadsTable = DB::getTablePrefix().$RepresentativeLeadsTable;

        $StripeTransactionTable =  $this->StripeTransactionModel->getTable();
        $prefixStripeTransactionTable = DB::getTablePrefix().$StripeTransactionTable;

        $TransactionMappingTable =  $this->TransactionMappingModel->getTable();
        $prefixTransactionMappingTable = DB::getTablePrefix().$TransactionMappingTable;

        $role_user_table =  $this->RoleUsersModel->getTable();
        $prefix_role_user_table = DB::getTablePrefix().$role_user_table;

        $objprefixStripeTransaction = DB::table($RepresentativeLeadsTable)
                                      ->select(DB::raw(
                                            $StripeTransactionTable.".*,".  
                                            $RepresentativeLeadsTable.".id as order_id,".
                                            $RepresentativeLeadsTable.".order_no,".
                                            $RepresentativeLeadsTable.".maker_id,".
                                            $RepresentativeLeadsTable.".retailer_id,".
                                            $RepresentativeLeadsTable.".representative_id,".
                                            $RepresentativeLeadsTable.".sales_manager_id,".
                                            $RepresentativeLeadsTable.".total_wholesale_price,".
                                            $RepresentativeLeadsTable.".rep_commission_status,".
                                            $RepresentativeLeadsTable.".maker_commission_status,".
                                            $RepresentativeLeadsTable.".sales_manager_commission_status,".  
                                            $RepresentativeLeadsTable.".created_at as order_date,".  
                                            $prefixTransactionMappingTable.".transaction_status,".  
                                            $makerTable.".company_name as vendor_name,".  

                                             // "CONCAT(VN.first_name,' ',VN.last_name) as vendor_name,".
                                            "CONCAT(RP.first_name,' ',RP.last_name) as rep_name,".
                                            "CONCAT(SM.first_name,' ',SM.last_name) as sales_manager_name,".
                                            "CONCAT(RN.first_name,' ',RN.last_name) as retailer_name"
                                        ))          

                            ->leftjoin($StripeTransactionTable,$RepresentativeLeadsTable.'.id','=',$StripeTransactionTable.'.quote_id')                           
                      
                            ->leftjoin($user_table.' AS RN','RN.id','=',$prefixRepresentativeLeadsTable.'.retailer_id')

                     
                            ->leftjoin($role_user_table,$prefix_role_user_table.'.user_id','=',$prefixStripeTransactionTable.'.received_by')

                            ->leftjoin($user_table.' AS VN','VN.id','=',$RepresentativeLeadsTable.'.maker_id')
                            ->leftjoin($makerTable,$makerTable.'.user_id','=',$RepresentativeLeadsTable.'.maker_id')
                    
                            ->leftjoin($user_table.' AS RP','RP.id','=',$RepresentativeLeadsTable.'.representative_id')
                      
                            ->leftjoin($user_table.' AS SM','SM.id','=',$RepresentativeLeadsTable.'.sales_manager_id')
                            ->whereNull('RP.deleted_at')

                            ->leftjoin($prefixTransactionMappingTable,function($join) use($RepresentativeLeadsTable,$prefixTransactionMappingTable){

                                $join->on($RepresentativeLeadsTable.'.id','=',$prefixTransactionMappingTable.'.order_id')
                                ->on($RepresentativeLeadsTable.'.order_no','=',$prefixTransactionMappingTable.'.order_no');

                            })

                            
                            ->where($RepresentativeLeadsTable.'.is_confirm', '=', 1);

                            if (isset($formData['orderStatus']) && $formData['orderStatus'] == 2) {

                                $objprefixStripeTransaction = $objprefixStripeTransaction->where($TransactionMappingTable.'.transaction_status' , $formData['orderStatus']);
                            }
                          // dd($objprefixStripeTransaction->get()->toArray());
                            if ($formData['commissionStatus'] != false && $formData['commissionStatus'] != 1) {
                                
                                //-------for paid commision status---------
                                $objprefixStripeTransaction = $objprefixStripeTransaction->where($StripeTransactionTable.'.status', '=', $formData['commissionStatus']);
                            }

                            if ($formData['commissionStatus'] != false && $formData['commissionStatus'] == 1) {

                        
                                //-------for pending commision status---------
                                $objprefixStripeTransaction = $objprefixStripeTransaction->whereNotIN($RepresentativeLeadsTable.'.id',function($query) use($StripeTransactionTable){
                                        $query->select('quote_id')->from($StripeTransactionTable);
                                    });
                            }

                           
                            

                            if (isset($formData['orderStatus']) && $formData['orderStatus'] == 1) {

                                $objprefixStripeTransaction = $objprefixStripeTransaction->whereNotExists(function($query)
                                        {
                                            $query->select(\DB::raw("
                                                  transaction_mapping.*
                                              FROM
                                                  `transaction_mapping`
                                              WHERE
                                                  `transaction_mapping`.`order_no` = representative_leads.order_no AND `transaction_mapping`.`order_id` = representative_leads.id
                                            "));
                                        });
                            } 
                            if ($formData['vendorPaymentStatus'] != false && $formData['vendorPaymentStatus'] == 1) {
                                
                                //-------for paid commision status---------
                                $objprefixStripeTransaction = $objprefixStripeTransaction->where($RepresentativeLeadsTable.'.maker_commission_status', '=', '0');
                            }
                            if ($formData['vendorPaymentStatus'] != false && $formData['vendorPaymentStatus'] == 2) {
                                
                                //-------for paid commision status---------
                                $objprefixStripeTransaction = $objprefixStripeTransaction->where($RepresentativeLeadsTable.'.maker_commission_status', '=', '1');
                            }


        if (isset($formData['salesId']) && $formData['salesId'] != false) {

            $objprefixStripeTransaction = $objprefixStripeTransaction->join($SalesManagerTable,$SalesManagerTable.'.user_id','=',$prefixStripeTransactionTable.'.received_by');
        }
        elseif(isset($formData['repId']) && $formData['repId'] != false){

            $objprefixStripeTransaction = $objprefixStripeTransaction->leftjoin($RepresentativeTable,$RepresentativeTable.'.user_id','=',$prefixStripeTransactionTable.'.received_by');
        }
       


        if (isset($formData['salesId']) && $formData['salesId'] != false) {

            $objprefixStripeTransaction = $objprefixStripeTransaction->where($RepresentativeLeadsTable.'.sales_manager_id', '=', $formData['salesId']);
            
        }
        elseif(isset($formData['repId']) && $formData['repId'] != false){

            $objprefixStripeTransaction = $objprefixStripeTransaction->where($RepresentativeLeadsTable.'.representative_id', '=', $formData['repId']);
        }
                            


        if (isset($formData['retailerId']) && $formData['retailerId'] != false) {

            $objprefixStripeTransaction = $objprefixStripeTransaction
                                            ->where($RepresentativeLeadsTable.'.retailer_id', '=',$formData['retailerId']);
        }
        if (isset($formData['makerId']) && $formData['makerId'] != false) {

            $objprefixStripeTransaction = $objprefixStripeTransaction
                                            ->where($RepresentativeLeadsTable.'.maker_id', '=',$formData['makerId'])

                                            ->leftjoin($RepresentativeTable.' as rep','rep.user_id','=',$prefixStripeTransactionTable.'.received_by');
        }
        if (isset($formData['toDate']) && $formData['toDate'] != false && isset($formData['fromDate']) && $formData['fromDate'] != false) {
            $from_date              = \DateTime::createFromFormat('m-d-Y',$formData['fromDate']);
            $formData['fromDate']             = $from_date->format('Y-m-d');
            $to_date                = \DateTime::createFromFormat('m-d-Y',$formData['toDate']);
            $formData['toDate']                = $to_date->format('Y-m-d');


            $objprefixStripeTransaction   = $objprefixStripeTransaction->whereDate($RepresentativeLeadsTable.'.created_at','<=',$formData['toDate'])->whereDate($RepresentativeLeadsTable.'.created_at','>=',$formData['fromDate'])
            ;
        }

        if (isset($formData['toDate']) && $formData['toDate'] != false && $formData['fromDate'] == false) {

            $formData['toDate'] = date('Y-d-m',strtotime($formData['toDate']));

            $objprefixStripeTransaction   = $objprefixStripeTransaction->where($RepresentativeLeadsTable.'.created_at','<=',$formData['toDate'].'%');
        }

        if (isset($formData['fromDate']) && $formData['fromDate'] != false && $formData['toDate'] == false) {

            $from_date              = \DateTime::createFromFormat('m-d-Y',$formData['fromDate']);
            $formData['fromDate']             = $from_date->format('Y-m-d');

            $objprefixStripeTransaction   = $objprefixStripeTransaction->where($RepresentativeLeadsTable.'.created_at','>=',$formData['fromDate'].'%');
        }
        
        $objprefixStripeTransaction = $objprefixStripeTransaction->orderBy('RP.created_at','DESC');

        //dd($objprefixStripeTransaction->toArray());

        return $objprefixStripeTransaction;
    }

    public function calculate_rep_total_commission($status,$formData,$arr_search_column=false)
    {
       
        $totalCommission = $totalOrderAmount = 0;
        $totalAmounts    = [];
        $totalAmounts['totalCommissionPending'] = $totalAmounts['totalCommissionPaid'] = $totalAmounts['totalOrderAmountPending'] = $totalAmounts['totalOrderAmountPaid'] = $totalAmounts['adminCommissionEarned'] = $totalAmounts['vendorCommissionPending'] = $totalAmounts['vendorCommissionPaid'] = 0;

        if (isset($status)) {
            
            if ($status == 'reps') {

                $objOrders = $this->rep_order_query($formData,$arr_search_column);
            } 
            elseif ($status == 'all') {

               $objOrders = $this->all_rep_order_query();
            }

            if ($objOrders) 
            {

                $orders = $objOrders->get()->toArray();

                foreach ($orders as $key => $order) {

                    $adminCommission  = $this->CommissionService->get_admin_commission();

                    if ($order->sales_manager_id != 0) {

                        $repCommission    = $this->CommissionService->get_sales_manager_commission($order->sales_manager_id);
                    }
                    else{

                        $repCommission    = $this->CommissionService->get_representative_commission($order->representative_id);

                    }

                    $totalPrice       = isset($order->total_wholesale_price)?num_format($order->total_wholesale_price):0.00;

                    //-------------get commission totals-----------------
                    if ($order->status == '') {

                        $adminCommissionAmount  = $totalPrice * ($adminCommission / 100);
                        $totalAmounts['totalCommissionPending'] += $adminCommissionAmount * ($repCommission / 100); 
                        
                    }
                    else{

                        $totalAmounts['totalCommissionPaid'] += $order->amount;
                        $adminCommissionAmount  = $totalPrice * ($adminCommission / 100);
                    }

                    $totalAmounts['adminCommissionEarned']    = $totalAmounts['totalCommissionPending'] + $totalAmounts['totalCommissionPaid'];

                    //-------------get commission totals-----------------
                    if ($order->transaction_status == '') {

                        $totalAmounts['totalOrderAmountPending']   += isset($order->total_wholesale_price)?num_format($order->total_wholesale_price):0.00;
                    }
                    else{

                        $totalAmounts['totalOrderAmountPaid']     += isset($order->total_wholesale_price)?num_format($order->total_wholesale_price):0.00;

                        
                    }

                    /*Calculate vendor commission*/
                    if ($order->maker_commission_status == 0) {

                        $adminCommissionAmount  = $totalPrice * ($adminCommission / 100);
                        $totalAmounts['vendorCommissionPending'] += $totalPrice - $adminCommissionAmount;
                        
                    }
                    elseif($order->maker_commission_status == 1){

                        $adminCommissionAmount  = $totalPrice * ($adminCommission / 100);
                        $totalAmounts['vendorCommissionPaid'] += $totalPrice - $adminCommissionAmount;

                    }
                }
            }   
            
        }

        return $totalAmounts;
        
    }


    public function retailer_order_query ($formData=false,$arr_search_column=false)
    {  
        //dd("ok");

          
        $user_table =  $this->UserModel->getTable();
        $prefix_user_table = DB::getTablePrefix().$user_table;

        $makerTable =  $this->MakerModel->getTable();
        $prefixmakerTable= DB::getTablePrefix().$makerTable;

        $RetailerTable =  $this->RetailerModel->getTable();
        $prefixRetailerTable= DB::getTablePrefix().$RetailerTable;

        $RetailerQuotesTable =  $this->RetailerQuotesModel->getTable();
        $prefixRetailerQuotesTable = DB::getTablePrefix().$RetailerQuotesTable;

        $StripeTransactionTable =  $this->StripeTransactionModel->getTable();
        $prefixStripeTransactionTable = DB::getTablePrefix().$StripeTransactionTable;

        $TransactionMappingTable =  $this->TransactionMappingModel->getTable();
        $prefixTransactionMappingTable = DB::getTablePrefix().$TransactionMappingTable;

        $RoleUserTable =  $this->RoleUsersModel->getTable();
        $prefixRoleUserTable = DB::getTablePrefix().$RoleUserTable;

        $CustomerQuotesTable = $this->CustomerQuotesModel->getTable();
        $prefixCustomerQuotesTable = DB::getTablePrefix().$CustomerQuotesTable;

        $RepresentativeLeadsTable =  $this->RepresentativeLeadsModel->getTable();
        $prefixRepresentativeLeadsTable = DB::getTablePrefix().$RepresentativeLeadsTable;

        $objprefixStripeTransaction = DB::table($RetailerQuotesTable)
                                          ->select(DB::raw(
                                                $StripeTransactionTable.".*,". 
                                                $StripeTransactionTable.".status as maker_payment_status,".
                                                $RetailerQuotesTable.".id as order_id,".
                                                $RetailerQuotesTable.".order_no,".
                                                $RetailerQuotesTable.".maker_id,".
                                                $RetailerQuotesTable.".retailer_id,".                                                
                                                $RetailerQuotesTable.".ship_status,".
                                                $RetailerQuotesTable.".is_split_order,".
                                                $RetailerQuotesTable.".payment_term,".
                                                $RetailerQuotesTable.".total_wholesale_price as total_wholesale_price,".
                                                $RetailerQuotesTable.".promo_code,".
                                                $RetailerQuotesTable.".is_direct_payment,".
                                                $RepresentativeLeadsTable.".representative_id,".
                                                $RepresentativeLeadsTable.".sales_manager_id,".
                                                $RepresentativeLeadsTable.".total_shipping_charges,". 
                                                $RepresentativeLeadsTable.".sales_manager_commission_status,". 
                                                $RepresentativeLeadsTable.".rep_commission_status as rep_commission_status,". 
                                                $RetailerQuotesTable.".created_at as order_date,". 
                                                $RetailerQuotesTable.".maker_commission_status,". 
                                                $RetailerQuotesTable.".maker_confirmation,". 
                                                $makerTable.".company_name as vendor_name,".
                                                $makerTable.".admin_commission as maker_admin_commission,".  
                                                $RoleUserTable.".role_id as role_id,".
                                                $RetailerQuotesTable.".admin_commission as admin_commission,".
                                                $RetailerQuotesTable.".admin_commission_status as admin_commission_status,".
                                                $prefixTransactionMappingTable.".transaction_status,".  
                                                //"CONCAT(RN.first_name,' ',RN.last_name) as orderer_name"
                                                "RR.store_name as orderer_name"
                                            ))          

                                ->leftjoin($StripeTransactionTable,$RetailerQuotesTable.'.id','=',$StripeTransactionTable.'.lead_id')
                          
                                ->leftjoin($user_table.' AS RN','RN.id','=',$RetailerQuotesTable.'.retailer_id')
                                ->leftjoin($RetailerTable.' AS RR','RR.user_id','=','RN.id')
                                ->leftjoin($RoleUserTable,$prefixRoleUserTable.'.user_id','=',$RetailerQuotesTable.'.retailer_id')

                                ->leftjoin($user_table.' AS VN','VN.id','=',$RetailerQuotesTable.'.maker_id')

                                ->leftjoin($makerTable,$makerTable.'.user_id','=',$RetailerQuotesTable.'.maker_id');

                                
                                $objprefixStripeTransaction = $objprefixStripeTransaction ->where($RetailerQuotesTable.'.is_direct_payment','=',1);                              

                                $objprefixStripeTransaction = $objprefixStripeTransaction->where($RetailerQuotesTable.'.order_cancel_status', '=', '0');

                                $objprefixStripeTransaction= $objprefixStripeTransaction->where($RetailerQuotesTable.'.is_split_order','=','0');

                                $objprefixStripeTransaction = $objprefixStripeTransaction->leftjoin($TransactionMappingTable,function($join) use($RetailerQuotesTable,$TransactionMappingTable){

                                $join->on($RetailerQuotesTable.'.order_no','=',$TransactionMappingTable.'.order_no');

                                });

                                $objprefixStripeTransaction = $objprefixStripeTransaction->leftjoin($RepresentativeLeadsTable,function($join) use($RepresentativeLeadsTable,$RetailerQuotesTable){

                                $join->on($RepresentativeLeadsTable.'.order_no','=',$RetailerQuotesTable.'.order_no');
                                });

                                $objprefixStripeTransaction = $objprefixStripeTransaction->orderBy($RetailerQuotesTable.'.created_at','DESC');
                 

        $objprefixRepStripeTransaction = DB::table($RepresentativeLeadsTable)
                                          ->select(DB::raw(
                                                $StripeTransactionTable.".*,". 
                                                $StripeTransactionTable.".status as maker_payment_status,". 
                                                $RepresentativeLeadsTable.".id as order_id,".
                                                $RepresentativeLeadsTable.".order_no,".
                                                $RepresentativeLeadsTable.".maker_id,".
                                                $RepresentativeLeadsTable.".retailer_id,".
                                                $RepresentativeLeadsTable.".ship_status,".         
                                                $RepresentativeLeadsTable.".is_split_order,".         
                                                $RepresentativeLeadsTable.".payment_term,". 
                                                $RepresentativeLeadsTable.".total_wholesale_price as total_wholesale_price,".
                                                $RepresentativeLeadsTable.".promo_code,". 
                                                $RepresentativeLeadsTable.".is_direct_payment,".
                                               $RepresentativeLeadsTable.".representative_id,".
                                                $RepresentativeLeadsTable.".sales_manager_id,". 
                                                $RepresentativeLeadsTable.".total_shipping_charges,". 
                                                $RepresentativeLeadsTable.".sales_manager_commission_status,". 
                                                $RepresentativeLeadsTable.".rep_commission_status as rep_commission_status,". 
                                                $RepresentativeLeadsTable.".created_at as order_date,". 
                                                $RepresentativeLeadsTable.".maker_commission_status,".
                                                $RepresentativeLeadsTable.".maker_confirmation,". 
                                                $makerTable.".company_name as vendor_name,". 
                                                $makerTable.".admin_commission as maker_admin_commission,". 
                                                $RoleUserTable.".role_id as role_id,".
                                                $RepresentativeLeadsTable.".admin_commission as admin_commission,".
                                                $RepresentativeLeadsTable.".admin_commission_status as admin_commission_status,".
                                                $prefixTransactionMappingTable.".transaction_status,".
                                                "RR.store_name as orderer_name"
                                                // "CONCAT(RN.first_name,' ',RN.last_name) as orderer_name"
                                            ))          

                                ->leftjoin($StripeTransactionTable,$RepresentativeLeadsTable.'.id','=',$StripeTransactionTable.'.quote_id')
                          
                                ->leftjoin($user_table.' AS RN','RN.id','=',$RepresentativeLeadsTable.'.retailer_id')

                                ->leftjoin($RoleUserTable,$prefixRoleUserTable.'.user_id','=',$RepresentativeLeadsTable.'.retailer_id')

                                ->leftjoin($user_table.' AS VN','VN.id','=',$RepresentativeLeadsTable.'.maker_id')
                                 ->leftjoin($RetailerTable.' AS RR','RR.user_id','=','RN.id')
                                ->leftjoin($makerTable,$makerTable.'.user_id','=',$RepresentativeLeadsTable.'.maker_id');

                                $objprefixRepStripeTransaction = $objprefixRepStripeTransaction ->where($RepresentativeLeadsTable.'.is_direct_payment','=',1);

                                $objprefixRepStripeTransaction = $objprefixRepStripeTransaction ->where($RepresentativeLeadsTable.'.is_confirm','=',1);
                               
                                $objprefixRepStripeTransaction = $objprefixRepStripeTransaction->where($RepresentativeLeadsTable.'.is_split_order', '=', '0');

                                $objprefixRepStripeTransaction = $objprefixRepStripeTransaction->where($RepresentativeLeadsTable.'.order_cancel_status', '=', '0');
        
                                $objprefixRepStripeTransaction = $objprefixRepStripeTransaction->leftjoin($TransactionMappingTable,function($join) use($RepresentativeLeadsTable,$TransactionMappingTable){

                                $join->on($RepresentativeLeadsTable.'.order_no','=',$TransactionMappingTable.'.order_no');

                                });
                                  $objprefixRepStripeTransaction  = $objprefixRepStripeTransaction->groupBy($RepresentativeLeadsTable.'.id');

                                $objprefixRepStripeTransaction  = $objprefixRepStripeTransaction->orderBy($RepresentativeLeadsTable.'.created_at','DESC');
        
        //Retailer Payment Status (to Vendor)
        if($formData['orderStatus']!=false && $formData['is_direct_payment']=="1")
        {   
            if($formData['orderStatus']=="1")
            {
             $objprefixStripeTransaction = $objprefixStripeTransaction
                                          ->where(function($tmpQuery) use($prefixTransactionMappingTable,$formData) {
                                            $tmpQuery->whereNull($prefixTransactionMappingTable.'.transaction_status')
                                            ->orWhere($prefixTransactionMappingTable.'.transaction_status','=', $formData['orderStatus']);  
                                          });  
                                          
                                          

             $objprefixRepStripeTransaction = $objprefixRepStripeTransaction
                                            ->where(function($tmpQuery) use($prefixTransactionMappingTable,$formData) {
                                                $tmpQuery->whereNull($prefixTransactionMappingTable.'.transaction_status')
                                                ->orWhere($prefixTransactionMappingTable.'.transaction_status','=', $formData['orderStatus']);  
                                            });  
            }
            else
            {
                $objprefixStripeTransaction = $objprefixStripeTransaction
                ->where($prefixTransactionMappingTable.'.transaction_status','=', $formData['orderStatus']);

                $objprefixRepStripeTransaction = $objprefixRepStripeTransaction
                ->where($prefixTransactionMappingTable.'.transaction_status','=', $formData['orderStatus']);
            }  
        }


        //Rep Commission payment status
        if($formData['repPaymentStatus']!= false || $formData['commissionStatus']!=false && $formData['is_direct_payment']=="1")
        {
           if($formData['repPaymentStatus']=="2" || $formData['commissionStatus'] == "1")
           { 
                
                $objprefixStripeTransaction = $objprefixStripeTransaction->where(function($tmpQuery) use($RepresentativeLeadsTable,$formData) {
                                            $tmpQuery->where(function($q) use($RepresentativeLeadsTable,$formData){

                                                return $q->where(function($q1) use($RepresentativeLeadsTable,$formData){
                                                   return $q1->where($RepresentativeLeadsTable.'.sales_manager_id','=','0')
                                                           ->where($RepresentativeLeadsTable.'.rep_commission_status','=', '1'); 
                                                })
                                                ->orWhere(function($q2) use($RepresentativeLeadsTable,$formData){
                                                    return $q2->where($RepresentativeLeadsTable.'.representative_id','=','0')
                                                               ->where($RepresentativeLeadsTable.'.sales_manager_commission_status','=', '1');     
                                                });
                                          }); 
                                    }); 
                $objprefixRepStripeTransaction = $objprefixRepStripeTransaction->where(function($tmpQuery) use($RepresentativeLeadsTable,$formData) {
                                            $tmpQuery->where(function($q) use($RepresentativeLeadsTable,$formData){

                                                return $q->where(function($q1) use($RepresentativeLeadsTable,$formData){
                                                   return $q1->where($RepresentativeLeadsTable.'.sales_manager_id','=','0')
                                                           ->where($RepresentativeLeadsTable.'.rep_commission_status','=', '1'); 
                                                })
                                                ->orWhere(function($q2) use($RepresentativeLeadsTable,$formData){
                                                    return $q2->where($RepresentativeLeadsTable.'.representative_id','=',0)
                                                               ->Where($RepresentativeLeadsTable.'.sales_manager_commission_status','=', '1');     
                                                });
                                          }); 
                                    }); 
            }

           if($formData['repPaymentStatus']=="2" || $formData['commissionStatus'] == "2")
           { 
                $objprefixStripeTransaction = $objprefixStripeTransaction->where(function($tmpQuery) use($RepresentativeLeadsTable,$formData) {
                                            $tmpQuery->where(function($q) use($RepresentativeLeadsTable,$formData){

                                                return $q->where(function($q1) use($RepresentativeLeadsTable,$formData){
                                                   return $q1->where($RepresentativeLeadsTable.'.sales_manager_id','=','0')
                                                           ->where($RepresentativeLeadsTable.'.rep_commission_status','!=', '1'); 
                                                })
                                                ->orWhere(function($q2) use($RepresentativeLeadsTable,$formData){
                                                    return $q2->where($RepresentativeLeadsTable.'.representative_id','=','0')
                                                               ->where($RepresentativeLeadsTable.'.sales_manager_commission_status','!=', '1');     
                                                });
                                          }); 
                                    }); 

               $objprefixRepStripeTransaction = $objprefixRepStripeTransaction->where(function($tmpQuery) use($RepresentativeLeadsTable,$formData) {
                                            $tmpQuery->where(function($q) use($RepresentativeLeadsTable,$formData){

                                                return $q->where(function($q1) use($RepresentativeLeadsTable,$formData){
                                                   return $q1->where($RepresentativeLeadsTable.'.sales_manager_id','=','0')
                                                           ->where($RepresentativeLeadsTable.'.rep_commission_status','!=', '1'); 
                                                })
                                                ->orWhere(function($q2) use($RepresentativeLeadsTable,$formData){
                                                    return $q2->where($RepresentativeLeadsTable.'.representative_id','=',0)
                                                               ->Where($RepresentativeLeadsTable.'.sales_manager_commission_status','!=', '1');     
                                                });

                                          }); 
                                    }); 
            }


        }

        if($formData['vendorPaymentStatus']!=false && $formData['is_direct_payment']=="1")
        {  
            
            if($formData['vendorPaymentStatus']=="1")//paid
            {
               
             $objprefixStripeTransaction = $objprefixStripeTransaction
                                            ->where($RetailerQuotesTable.'.admin_commission_status', '=',1);
                                            

             $objprefixRepStripeTransaction = $objprefixRepStripeTransaction
                                            ->where($RepresentativeLeadsTable.'.admin_commission_status', '=',1);
                
            }
            if($formData['vendorPaymentStatus']=="2")//pending
            {
            
                $objprefixStripeTransaction = $objprefixStripeTransaction
                                          ->where(function($tmpQuery) use($RetailerQuotesTable,$formData) {
                                            $tmpQuery->whereNull($RetailerQuotesTable.'.admin_commission_status')
                                            ->orWhere($RetailerQuotesTable.'.admin_commission_status','!=', 1);  
                                          });  
                                          
                                          

                $objprefixRepStripeTransaction = $objprefixRepStripeTransaction
                                            ->where(function($tmpQuery) use($RepresentativeLeadsTable,$formData) {
                                                $tmpQuery->whereNull($RepresentativeLeadsTable.'.admin_commission_status')
                                                ->orWhere($RepresentativeLeadsTable.'.admin_commission_status','!=',1 );  
                                            });  
           
            }
        }
               
        if (isset($formData['toDate']) && $formData['toDate'] != false && isset($formData['fromDate']) && $formData['fromDate'] != false) {

           
            $from_date              = \DateTime::createFromFormat('m/d/Y',$formData['fromDate']);
            $formData['fromDate']   = $from_date->format('Y-m-d');

            $to_date                = \DateTime::createFromFormat('m/d/Y',$formData['toDate']);

            $formData['toDate']    = $to_date->format('Y-m-d');
            
            $objprefixStripeTransaction   = $objprefixStripeTransaction
            ->whereDate($RetailerQuotesTable.'.created_at','>=',$formData['fromDate'])
            ->whereDate($RetailerQuotesTable.'.created_at','<=',$formData['toDate']);

            $objprefixRepStripeTransaction   = $objprefixRepStripeTransaction
            ->whereDate($RepresentativeLeadsTable.'.created_at','>=',$formData['fromDate'])
            ->whereDate($RepresentativeLeadsTable.'.created_at','<=',$formData['toDate']);

        }
 
        if($formData['repId']!=false)
        {
            $objprefixStripeTransaction = $objprefixStripeTransaction
            ->where($RepresentativeLeadsTable.'.representative_id','=', $formData['repId']);

            $objprefixRepStripeTransaction = $objprefixRepStripeTransaction
            ->where($RepresentativeLeadsTable.'.representative_id','=', $formData['repId']);

        }

        if (isset($formData['makerId']) && $formData['makerId'] != false) {

            $objprefixStripeTransaction = $objprefixStripeTransaction
                                            ->where($RetailerQuotesTable.'.maker_id', '=',$formData['makerId']);
            $objprefixRepStripeTransaction = $objprefixRepStripeTransaction
                                            ->where($RepresentativeLeadsTable.'.maker_id', '=',$formData['makerId']);
                                           
        }

        if (isset($formData['retailerId']) && $formData['retailerId'] != false) {

            $objprefixStripeTransaction = $objprefixStripeTransaction
                                            ->where($RetailerQuotesTable.'.retailer_id', '=',$formData['retailerId']);

            $objprefixRepStripeTransaction = $objprefixRepStripeTransaction
                                            ->where($RepresentativeLeadsTable.'.retailer_id', '=',$formData['retailerId']);
        }

        if($formData['salesId']!= false)
        {
           $objprefixStripeTransaction = $objprefixStripeTransaction
            ->where($RepresentativeLeadsTable.'.sales_manager_id','=', $formData['salesId']);

            $objprefixRepStripeTransaction = $objprefixRepStripeTransaction
            ->where($RepresentativeLeadsTable.'.sales_manager_id','=', $formData['salesId']);  
        }

        if (isset($formData['vendorId']) && $formData['vendorId'] != false) {

            $objprefixStripeTransaction = $objprefixStripeTransaction
                                            ->where($RetailerQuotesTable.'.maker_id', '=',$formData['vendorId']);
            $objprefixRepStripeTransaction = $objprefixRepStripeTransaction
                                            ->where($RepresentativeLeadsTable.'.maker_id', '=',$formData['vendorId']);
                                           
        }
    

        // $objprefixStripeTransaction   = $objprefixStripeTransaction->orderBy('RN.created_at','DESC');
        $objprefixStripeTransaction   =  $objprefixStripeTransaction->union($objprefixRepStripeTransaction)->orderBy('order_date','DESC');

      
       // dd($objprefixStripeTransaction->toSql(),$objprefixStripeTransaction->getBindings());
      

        return $objprefixStripeTransaction; 
    }



    public function retailer_order_query_old($formData=false,$arr_search_column=false)
    {
        // $formData['retailerId']=null;
        // $formData['makerId']=null;


        $user_table =  $this->UserModel->getTable();
        $prefix_user_table = DB::getTablePrefix().$user_table;

        $makerTable =  $this->MakerModel->getTable();
        $prefixmakerTable= DB::getTablePrefix().$makerTable;

        $RetailerTable =  $this->RetailerModel->getTable();
        $prefixRetailerTable= DB::getTablePrefix().$RetailerTable;

        $RetailerQuotesTable =  $this->RetailerQuotesModel->getTable();
        $prefixRetailerQuotesTable = DB::getTablePrefix().$RetailerQuotesTable;

        $StripeTransactionTable =  $this->StripeTransactionModel->getTable();
        $prefixStripeTransactionTable = DB::getTablePrefix().$StripeTransactionTable;

        $TransactionMappingTable =  $this->TransactionMappingModel->getTable();
        $prefixTransactionMappingTable = DB::getTablePrefix().$TransactionMappingTable;

        $RoleUserTable =  $this->RoleUsersModel->getTable();
        $prefixRoleUserTable = DB::getTablePrefix().$RoleUserTable;

        $CustomerQuotesTable = $this->CustomerQuotesModel->getTable();
        $prefixCustomerQuotesTable = DB::getTablePrefix().$CustomerQuotesTable;

        $RepresentativeLeadsTable =  $this->RepresentativeLeadsModel->getTable();
        $prefixRepresentativeLeadsTable = DB::getTablePrefix().$RepresentativeLeadsTable;

        $objprefixStripeTransaction = DB::table($RetailerQuotesTable)
                                          ->select(DB::raw(
                                                $StripeTransactionTable.".*,". 
                                                $StripeTransactionTable.".status as maker_payment_status,".  
                                                $RetailerQuotesTable.".id as order_id,".
                                                $RetailerQuotesTable.".order_no,".
                                                $RetailerQuotesTable.".ship_status,".
                                                $RetailerQuotesTable.".is_direct_payment,".
                                                $RetailerQuotesTable.".payment_term,".
                                                $RetailerQuotesTable.".is_split_order,".
                                                $RetailerQuotesTable.".maker_id,".
                                                $RetailerQuotesTable.".retailer_id,".
                                                $RetailerQuotesTable.".total_wholesale_price,".
                                                $RepresentativeLeadsTable.".representative_id,".
                                                $RepresentativeLeadsTable.".sales_manager_id,". 
                                                $RepresentativeLeadsTable.".total_shipping_charges,". 
                                                $RepresentativeLeadsTable.".sales_manager_commission_status,". 
                                                $RepresentativeLeadsTable.".rep_commission_status as rep_commission_status,". 
                                                $RetailerQuotesTable.".created_at as order_date,". 
                                                $RetailerQuotesTable.".maker_commission_status,". 
                                                $RetailerQuotesTable.".maker_confirmation,". 
                                                $makerTable.".company_name as vendor_name,". 
                                                $makerTable.".admin_commission as maker_admin_commission,". 
                                                $RoleUserTable.".role_id as role_id,".
                                                $RetailerQuotesTable.".admin_commission as admin_commission,".
                                                $RetailerQuotesTable.".admin_commission_status as admin_commission_status,".
                                                $prefixTransactionMappingTable.".transaction_status,". 
                                                "RR.store_name as orderer_name" 
                                                // "CONCAT(RN.first_name,' ',RN.last_name) as orderer_name"
                                            ))          

                                ->leftjoin($StripeTransactionTable,$RetailerQuotesTable.'.id','=',$StripeTransactionTable.'.lead_id')
                          
                                ->leftjoin($user_table.' AS RN','RN.id','=',$RetailerQuotesTable.'.retailer_id')
                                ->leftjoin($RetailerTable.' AS RR','RR.user_id','=','RN.id')
                                ->leftjoin($RoleUserTable,$prefixRoleUserTable.'.user_id','=',$RetailerQuotesTable.'.retailer_id')

                                ->leftjoin($user_table.' AS VN','VN.id','=',$RetailerQuotesTable.'.maker_id')

                                ->leftjoin($makerTable,$makerTable.'.user_id','=',$RetailerQuotesTable.'.maker_id')
                                ->where($RetailerQuotesTable.'.is_direct_payment','=',1)
                                ->where($RetailerQuotesTable.'.is_split_order','=',0);
                    

                                $objprefixStripeTransaction = $objprefixStripeTransaction->where($RetailerQuotesTable.'.order_cancel_status', '=', '0');

        
                               
                                $objprefixStripeTransaction = $objprefixStripeTransaction->leftjoin($TransactionMappingTable,function($join) use($RetailerQuotesTable,$TransactionMappingTable){

                                    $join->on($RetailerQuotesTable.'.order_no','=',$TransactionMappingTable.'.order_no')
                                          ->on($RetailerQuotesTable.'.id','=',$TransactionMappingTable.'.order_id');

                                });

                                $objprefixStripeTransaction = $objprefixStripeTransaction->leftjoin($RepresentativeLeadsTable,function($join) use($RepresentativeLeadsTable,$RetailerQuotesTable){

                                $join->on($RepresentativeLeadsTable.'.order_no','=',$RetailerQuotesTable.'.order_no');
                                });


    $objprefixRepStripeTransaction = DB::table($RepresentativeLeadsTable)
                                          ->select(DB::raw(
                                                $StripeTransactionTable.".*,". 
                                                $StripeTransactionTable.".status as maker_payment_status,". 
                                                $RepresentativeLeadsTable.".id as order_id,".
                                                $RepresentativeLeadsTable.".order_no,".
                                                $RepresentativeLeadsTable.".ship_status,".
                                                $RepresentativeLeadsTable.".is_direct_payment,".
                                                $RepresentativeLeadsTable.".payment_term,".
                                                $RepresentativeLeadsTable.".is_split_order,".
                                                $RepresentativeLeadsTable.".maker_id,".
                                                $RepresentativeLeadsTable.".retailer_id,".
                                                
                                                $RepresentativeLeadsTable.".total_wholesale_price,".
                                                $RepresentativeLeadsTable.".representative_id,".
                                                $RepresentativeLeadsTable.".sales_manager_id,".  
                                                $RepresentativeLeadsTable.".total_shipping_charges,".  
                                                $RepresentativeLeadsTable.".sales_manager_commission_status,".  
                                                $RepresentativeLeadsTable.".rep_commission_status as rep_commission_status,". 
                                                $RepresentativeLeadsTable.".created_at as order_date,". 
                                                $RepresentativeLeadsTable.".maker_commission_status,".
                                                $RepresentativeLeadsTable.".maker_confirmation,". 
                                                $makerTable.".company_name as vendor_name,". 
                                                $makerTable.".admin_commission as maker_admin_commission,". 
                                                $RoleUserTable.".role_id as role_id,".
                                                $RepresentativeLeadsTable.".admin_commission as admin_commission,".
                                                $RepresentativeLeadsTable.".admin_commission_status as admin_commission_status,".
                                                $prefixTransactionMappingTable.".transaction_status,".
                                                
                                                //"CONCAT(RN.first_name,' ',RN.last_name) as orderer_name"
                                                "RR.store_name as orderer_name"
                                            ))          

                                ->leftjoin($StripeTransactionTable,$RepresentativeLeadsTable.'.id','=',$StripeTransactionTable.'.quote_id')
                          
                                ->leftjoin($user_table.' AS RN','RN.id','=',$RepresentativeLeadsTable.'.retailer_id')

                                ->leftjoin($RetailerTable.' AS RR','RR.user_id','=','RN.id')

                                ->leftjoin($RoleUserTable,$prefixRoleUserTable.'.user_id','=',$RepresentativeLeadsTable.'.retailer_id')

                                ->leftjoin($user_table.' AS VN','VN.id','=',$RepresentativeLeadsTable.'.maker_id')

                                ->leftjoin($makerTable,$makerTable.'.user_id','=',$RepresentativeLeadsTable.'.maker_id')

                                ->where($RepresentativeLeadsTable.'.is_direct_payment','=',1)
                                
                                ->where($RepresentativeLeadsTable.'.is_confirm','=',1)

                                ->where($RepresentativeLeadsTable.'.is_split_order','=','0');

                                $objprefixRepStripeTransaction = $objprefixRepStripeTransaction->where($RepresentativeLeadsTable.'.order_cancel_status', '=', '0');
        
                                $objprefixRepStripeTransaction = $objprefixRepStripeTransaction->leftjoin($TransactionMappingTable,function($join) use($RepresentativeLeadsTable,$TransactionMappingTable){

                                $join->on($RepresentativeLeadsTable.'.order_no','=',$TransactionMappingTable.'.order_no')
                                    ->on($RepresentativeLeadsTable.'.id','=',$TransactionMappingTable.'.order_id')
                                    ->where($RepresentativeLeadsTable.'.is_direct_payment','=','1');
                                });
                               


                                
        if($formData['repPaymentStatus']!= false || $formData['commissionStatus']!= false)
        {  
           
           if($formData['repPaymentStatus']=="2" || $formData['commissionStatus'] == "2")
           {
            
            $formData['repPaymentStatus'] = "0";
            $formData['commissionStatus'] = "2";
            $formData['salesPaymentStatus'] = "0";
          
           $objprefixStripeTransaction = $objprefixStripeTransaction->where($RepresentativeLeadsTable.'.rep_commission_status','=', $formData['repPaymentStatus'])->where($RepresentativeLeadsTable.'.sales_manager_commission_status','=', $formData['repPaymentStatus']);

            $objprefixRepStripeTransaction = $objprefixRepStripeTransaction->where($RepresentativeLeadsTable.'.rep_commission_status','=', $formData['repPaymentStatus'])->where($RepresentativeLeadsTable.'.sales_manager_commission_status','=', $formData['repPaymentStatus']);

           }
           else
           {
            $formData['repPaymentStatus'] = "1";
            $formData['salesPaymentStatus'] = "1";
           
            $objprefixStripeTransaction = $objprefixStripeTransaction->where($RepresentativeLeadsTable.'.rep_commission_status','=', $formData['repPaymentStatus'])->orWhere($RepresentativeLeadsTable.'.sales_manager_commission_status','=', $formData['salesPaymentStatus']);

            $objprefixRepStripeTransaction = $objprefixRepStripeTransaction->where($RepresentativeLeadsTable.'.rep_commission_status','=', $formData['repPaymentStatus'])->orWhere($RepresentativeLeadsTable.'.sales_manager_commission_status','=', $formData['salesPaymentStatus']);
            }


        }

       //Retailer Payment Status (to Vendor)
        if($formData['orderStatus']!=false && $formData['is_direct_payment']=="1")
        {  
           
            if($formData['orderStatus']=="1")
            {
             $objprefixStripeTransaction = $objprefixStripeTransaction
                                          ->where(function($tmpQuery) use($prefixTransactionMappingTable,$formData) {
                                            $tmpQuery->whereNull($prefixTransactionMappingTable.'.transaction_status')
                                            ->orWhere($prefixTransactionMappingTable.'.transaction_status','=', $formData['orderStatus']);  
                                          });  
                                          
                                          

             $objprefixRepStripeTransaction = $objprefixRepStripeTransaction
                                            ->where(function($tmpQuery) use($prefixTransactionMappingTable,$formData) {
                                                $tmpQuery->whereNull($prefixTransactionMappingTable.'.transaction_status')
                                                ->orWhere($prefixTransactionMappingTable.'.transaction_status','=', $formData['orderStatus']);  
                                            });  
            }
            else
            {
                $objprefixStripeTransaction = $objprefixStripeTransaction
                ->where($prefixTransactionMappingTable.'.transaction_status','=', $formData['orderStatus']);

                $objprefixRepStripeTransaction = $objprefixRepStripeTransaction
                ->where($prefixTransactionMappingTable.'.transaction_status','=', $formData['orderStatus']);
            }  
        }


         //Commission Receipt Status
         if($formData['vendorPaymentStatus']!=false && $formData['is_direct_payment']=="1")
        {  
             if($formData['vendorPaymentStatus']=="1")
            {
               
             $objprefixStripeTransaction = $objprefixStripeTransaction
                                            ->where($RetailerQuotesTable.'.admin_commission_status', '=',1);
                                            

             $objprefixRepStripeTransaction = $objprefixRepStripeTransaction
                                            ->where($RepresentativeLeadsTable.'.admin_commission_status', '=',1);
                                            
            }
            else
            {
               
              $objprefixStripeTransaction = $objprefixStripeTransaction
                                            ->where(function($tmpQuery) use($RetailerQuotesTable,$formData) {
                                                $tmpQuery->whereNull($RetailerQuotesTable.'.admin_commission_status')
                                                ->orWhere($RetailerQuotesTable.'.admin_commission_status','=', $formData['vendorPaymentStatus']);  
                                            });

                                       

              $objprefixRepStripeTransaction = $objprefixRepStripeTransaction
                                            ->where(function($tmpQuery) use($RepresentativeLeadsTable,$formData) {
                                                $tmpQuery->whereNull($RepresentativeLeadsTable.'.admin_commission_status')
                                                ->orWhere($RepresentativeLeadsTable.'.admin_commission_status','=', $formData['vendorPaymentStatus']);  
                                            });
            }
        }
       


        if (isset($formData['toDate']) && $formData['toDate'] != false && isset($formData['fromDate']) && $formData['fromDate'] != false) {

           
            $from_date              = \DateTime::createFromFormat('m-d-Y',$formData['fromDate']);
            
            $to_date                = \DateTime::createFromFormat('m-d-Y',$formData['toDate']);
            
             
            $formData['toDate']                = $to_date->format('Y-m-d');
            $formData['fromDate']             = $from_date->format('Y-m-d');
          
            $objprefixStripeTransaction   = $objprefixStripeTransaction
            ->whereDate($RetailerQuotesTable.'.created_at','>=',$formData['fromDate'])
            ->whereDate($RetailerQuotesTable.'.created_at','<=',$formData['toDate']);

            $objprefixRepStripeTransaction   = $objprefixRepStripeTransaction
            ->whereDate($RepresentativeLeadsTable.'.created_at','>=',$formData['fromDate'])
            ->whereDate($RepresentativeLeadsTable.'.created_at','<=',$formData['toDate']);

        }
        if($formData['repId']!=false)
        {   //dd($formData);
            $objprefixStripeTransaction = $objprefixStripeTransaction
            ->where($RepresentativeLeadsTable.'.representative_id','=', $formData['repId'])
            ->where($RepresentativeLeadsTable.'.is_direct_payment','=', '1');

            $objprefixRepStripeTransaction = $objprefixRepStripeTransaction
            ->where($RepresentativeLeadsTable.'.representative_id','=', $formData['repId'])
            ->where($RepresentativeLeadsTable.'.is_direct_payment','=', '1');

            //dd("ok");
        }

        if (isset($formData['makerId']) && $formData['makerId'] != false ) {


            //$formData['makerId'] = (string) $formData['vendorId'];    ---> 
        
           

            $objprefixStripeTransaction = $objprefixStripeTransaction
                                            ->where($RetailerQuotesTable.'.maker_id', '=',$formData['makerId']);
            $objprefixRepStripeTransaction = $objprefixRepStripeTransaction
                                            ->where($RepresentativeLeadsTable.'.maker_id', '=',$formData['makerId']);
                                           
        }

        if (isset($formData['retailerId']) && $formData['retailerId'] != false) {

            $objprefixStripeTransaction = $objprefixStripeTransaction
                                            ->where($RetailerQuotesTable.'.retailer_id', '=',$formData['retailerId']);

            $objprefixRepStripeTransaction = $objprefixRepStripeTransaction
                                            ->where($RepresentativeLeadsTable.'.retailer_id', '=',$formData['retailerId']);
        }

        if($formData['salesId']!= false)
        {
           $objprefixStripeTransaction = $objprefixStripeTransaction
            ->where($RepresentativeLeadsTable.'.sales_manager_id','=', $formData['salesId']);

            $objprefixRepStripeTransaction = $objprefixRepStripeTransaction
            ->where($RepresentativeLeadsTable.'.sales_manager_id','=', $formData['salesId']);  
        }

        if(isset($formData['vendorId']) && $formData['vendorId'] != false) {
            $formData['makerId'] = strval($formData['vendorId']);
            
            $objprefixStripeTransaction = $objprefixStripeTransaction
                                            ->where($RetailerQuotesTable.'.maker_id', '=',$formData['makerId']);
            $objprefixRepStripeTransaction = $objprefixRepStripeTransaction
                                            ->where($RepresentativeLeadsTable.'.maker_id', '=',$formData['makerId']);
        }
                                      
        // $objprefixRepStripeTransaction = $objprefixRepStripeTransaction->groupBy('order_no');
        $objprefixStripeTransaction   = $objprefixStripeTransaction->orderBy('RN.created_at','DESC');
        $objprefixStripeTransaction   =  $objprefixStripeTransaction->union($objprefixRepStripeTransaction);

        //dd($formData);

        return $objprefixStripeTransaction; 
    }



    public function retailer_indirect_order_query ($formData=false,$arr_search_column=false)
    {  
          
        $user_table =  $this->UserModel->getTable();
        $prefix_user_table = DB::getTablePrefix().$user_table;

        $makerTable =  $this->MakerModel->getTable();
        $prefixmakerTable= DB::getTablePrefix().$makerTable;

        $RetailerTable =  $this->RetailerModel->getTable();
        $prefixRetailerTable= DB::getTablePrefix().$RetailerTable;

        $RetailerQuotesTable =  $this->RetailerQuotesModel->getTable();
        $prefixRetailerQuotesTable = DB::getTablePrefix().$RetailerQuotesTable;

        $StripeTransactionTable =  $this->StripeTransactionModel->getTable();
        $prefixStripeTransactionTable = DB::getTablePrefix().$StripeTransactionTable;

        $TransactionMappingTable =  $this->TransactionMappingModel->getTable();
        $prefixTransactionMappingTable = DB::getTablePrefix().$TransactionMappingTable;

        $RoleUserTable =  $this->RoleUsersModel->getTable();
        $prefixRoleUserTable = DB::getTablePrefix().$RoleUserTable;

        $CustomerQuotesTable = $this->CustomerQuotesModel->getTable();
        $prefixCustomerQuotesTable = DB::getTablePrefix().$CustomerQuotesTable;

        $RepresentativeLeadsTable =  $this->RepresentativeLeadsModel->getTable();
        $prefixRepresentativeLeadsTable = DB::getTablePrefix().$RepresentativeLeadsTable;

        $objprefixStripeTransaction = DB::table($RetailerQuotesTable)
                                          ->select(DB::raw(
                                                $StripeTransactionTable.".*,". 
                                                $StripeTransactionTable.".status as maker_payment_status,".
                                                $RetailerQuotesTable.".id as order_id,".
                                                $RetailerQuotesTable.".order_no,".
                                                $RetailerQuotesTable.".maker_id,".
                                                $RetailerQuotesTable.".retailer_id,".                                                
                                                $RetailerQuotesTable.".ship_status,".
                                                $RetailerQuotesTable.".is_split_order,".
                                                $RetailerQuotesTable.".payment_term,".
                                                $RetailerQuotesTable.".total_wholesale_price as total_wholesale_price,".
                                                $RetailerQuotesTable.".promo_code,".

                                                $RetailerQuotesTable.".is_direct_payment,".
                                                $RepresentativeLeadsTable.".representative_id,".
                                                $RepresentativeLeadsTable.".sales_manager_id,".
                                                $RepresentativeLeadsTable.".total_shipping_charges,". 
                                                $RepresentativeLeadsTable.".sales_manager_commission_status,". 
                                                $RepresentativeLeadsTable.".rep_commission_status as rep_commission_status,". 
                                                $RetailerQuotesTable.".created_at as order_date,". 
                                                $RetailerQuotesTable.".maker_commission_status,". 
                                                $RetailerQuotesTable.".maker_confirmation,". 
                                                $makerTable.".company_name as vendor_name,".
                                                $makerTable.".admin_commission as maker_admin_commission,".  
                                                $RoleUserTable.".role_id as role_id,".
                                                $RetailerQuotesTable.".admin_commission as admin_commission,".
                                                $RetailerQuotesTable.".admin_commission_status as admin_commission_status,".
                                                $prefixTransactionMappingTable.".transaction_status,".  
                                                //"CONCAT(RN.first_name,' ',RN.last_name) as orderer_name"
                                                "RR.store_name as orderer_name"
                                            ))          

                                ->leftjoin($StripeTransactionTable,$RetailerQuotesTable.'.id','=',$StripeTransactionTable.'.lead_id')
                          
                                ->leftjoin($user_table.' AS RN','RN.id','=',$RetailerQuotesTable.'.retailer_id')
                                ->leftjoin($RetailerTable.' AS RR','RR.user_id','=','RN.id')
                                ->leftjoin($RoleUserTable,$prefixRoleUserTable.'.user_id','=',$RetailerQuotesTable.'.retailer_id')

                                ->leftjoin($user_table.' AS VN','VN.id','=',$RetailerQuotesTable.'.maker_id')

                                ->leftjoin($makerTable,$makerTable.'.user_id','=',$RetailerQuotesTable.'.maker_id');

                                
                                $objprefixStripeTransaction = $objprefixStripeTransaction ->where($RetailerQuotesTable.'.is_direct_payment','=',0);                              

                                $objprefixStripeTransaction = $objprefixStripeTransaction->where($RetailerQuotesTable.'.order_cancel_status', '=', '0');

                                $objprefixStripeTransaction= $objprefixStripeTransaction->where($RetailerQuotesTable.'.is_split_order','=','0');

                                $objprefixStripeTransaction = $objprefixStripeTransaction->leftjoin($TransactionMappingTable,function($join) use($RetailerQuotesTable,$TransactionMappingTable){

                                $join->on($RetailerQuotesTable.'.order_no','=',$TransactionMappingTable.'.order_no');

                                });

                                $objprefixStripeTransaction = $objprefixStripeTransaction->leftjoin($RepresentativeLeadsTable,function($join) use($RepresentativeLeadsTable,$RetailerQuotesTable){

                                $join->on($RepresentativeLeadsTable.'.order_no','=',$RetailerQuotesTable.'.order_no');
                                });

                                $objprefixStripeTransaction = $objprefixStripeTransaction->orderBy($RetailerQuotesTable.'.created_at','DESC');
                 
                                

        $objprefixRepStripeTransaction = DB::table($RepresentativeLeadsTable)
                                          ->select(DB::raw(
                                                $StripeTransactionTable.".*,". 
                                                $StripeTransactionTable.".status as maker_payment_status,". 
                                                $RepresentativeLeadsTable.".id as order_id,".
                                                $RepresentativeLeadsTable.".order_no,".
                                                $RepresentativeLeadsTable.".maker_id,".
                                                $RepresentativeLeadsTable.".retailer_id,".
                                                $RepresentativeLeadsTable.".ship_status,".         
                                                $RepresentativeLeadsTable.".is_split_order,".         
                                                $RepresentativeLeadsTable.".payment_term,". 
                                                $RepresentativeLeadsTable.".total_wholesale_price as total_wholesale_price,".
                                                $RepresentativeLeadsTable.".promo_code,".
                                                $RepresentativeLeadsTable.".is_direct_payment,". 
                                                $RepresentativeLeadsTable.".representative_id,".
                                                $RepresentativeLeadsTable.".sales_manager_id,". 
                                                $RepresentativeLeadsTable.".total_shipping_charges,". 
                                                $RepresentativeLeadsTable.".sales_manager_commission_status,". 
                                                $RepresentativeLeadsTable.".rep_commission_status as rep_commission_status,". 
                                                $RepresentativeLeadsTable.".created_at as order_date,". 
                                                $RepresentativeLeadsTable.".maker_commission_status,".
                                                $RepresentativeLeadsTable.".maker_confirmation,". 
                                                $makerTable.".company_name as vendor_name,". 
                                                $makerTable.".admin_commission as maker_admin_commission,". 
                                                $RoleUserTable.".role_id as role_id,".
                                                $RepresentativeLeadsTable.".admin_commission as admin_commission,".
                                                $RepresentativeLeadsTable.".admin_commission_status as admin_commission_status,".
                                                $prefixTransactionMappingTable.".transaction_status,".
                                                "RR.store_name as orderer_name"
                                                // "CONCAT(RN.first_name,' ',RN.last_name) as orderer_name"
                                            ))          

                                ->leftjoin($StripeTransactionTable,$RepresentativeLeadsTable.'.id','=',$StripeTransactionTable.'.quote_id')
                          
                                ->leftjoin($user_table.' AS RN','RN.id','=',$RepresentativeLeadsTable.'.retailer_id')

                                ->leftjoin($RoleUserTable,$prefixRoleUserTable.'.user_id','=',$RepresentativeLeadsTable.'.retailer_id')

                                ->leftjoin($user_table.' AS VN','VN.id','=',$RepresentativeLeadsTable.'.maker_id')
                                 ->leftjoin($RetailerTable.' AS RR','RR.user_id','=','RN.id')
                                ->leftjoin($makerTable,$makerTable.'.user_id','=',$RepresentativeLeadsTable.'.maker_id');

                                $objprefixRepStripeTransaction = $objprefixRepStripeTransaction ->where($RepresentativeLeadsTable.'.is_direct_payment','=',0);

                                $objprefixRepStripeTransaction = $objprefixRepStripeTransaction ->where($RepresentativeLeadsTable.'.is_confirm','=',1);
                               
                                $objprefixRepStripeTransaction = $objprefixRepStripeTransaction->where($RepresentativeLeadsTable.'.is_split_order', '=', '0');

                                $objprefixRepStripeTransaction = $objprefixRepStripeTransaction->where($RepresentativeLeadsTable.'.order_cancel_status', '=', '0');
        
                                $objprefixRepStripeTransaction = $objprefixRepStripeTransaction->leftjoin($TransactionMappingTable,function($join) use($RepresentativeLeadsTable,$TransactionMappingTable){

                                $join->on($RepresentativeLeadsTable.'.order_no','=',$TransactionMappingTable.'.order_no');

                                });
                                  $objprefixRepStripeTransaction  = $objprefixRepStripeTransaction->groupBy($RepresentativeLeadsTable.'.id');

                                  $objprefixRepStripeTransaction  = $objprefixRepStripeTransaction->orderBy($RepresentativeLeadsTable.'.created_at','DESC');
                                  // dd($objprefixRepStripeTransaction->get());
        
        //Retailer Payment Status (to Vendor)
        if($formData['orderStatus']!=false && $formData['is_direct_payment']=="0")
        {   
            if($formData['orderStatus']=="1")
            {
             $objprefixStripeTransaction = $objprefixStripeTransaction
                                          ->where(function($tmpQuery) use($prefixTransactionMappingTable,$formData) {
                                            $tmpQuery->whereNull($prefixTransactionMappingTable.'.transaction_status')
                                            ->orWhere($prefixTransactionMappingTable.'.transaction_status','=', $formData['orderStatus']);  
                                          });  
                                          
                                          

             $objprefixRepStripeTransaction = $objprefixRepStripeTransaction
                                            ->where(function($tmpQuery) use($prefixTransactionMappingTable,$formData) {
                                                $tmpQuery->whereNull($prefixTransactionMappingTable.'.transaction_status')
                                                ->orWhere($prefixTransactionMappingTable.'.transaction_status','=', $formData['orderStatus']);  
                                            });  
            }
            else
            {
                $objprefixStripeTransaction = $objprefixStripeTransaction
                ->where($prefixTransactionMappingTable.'.transaction_status','=', $formData['orderStatus']);

                $objprefixRepStripeTransaction = $objprefixRepStripeTransaction
                ->where($prefixTransactionMappingTable.'.transaction_status','=', $formData['orderStatus']);
            }  
        }


        // //Rep Commission payment status
        // if($formData['repPaymentStatus']!= false || $formData['commissionStatus'] && $formData['is_direct_payment']=="0")
        // {
        //    if($formData['repPaymentStatus']=="2" || $formData['commissionStatus'] == "2")
        //    {
        //     $formData['repPaymentStatus'] = "0";
        //     $formData['commissionStatus'] = "2";
        //    }
        //     $objprefixStripeTransaction = $objprefixStripeTransaction->where($RepresentativeLeadsTable.'.rep_commission_status','=', $formData['repPaymentStatus']);

        //     $objprefixRepStripeTransaction = $objprefixRepStripeTransaction->where($RepresentativeLeadsTable.'.rep_commission_status','=', $formData['repPaymentStatus']);
        // }

        if($formData['repPaymentStatus']!= false && $formData['is_direct_payment']=="0")
        {
           
           if($formData['repPaymentStatus']=="1")
           { 
                
                $objprefixStripeTransaction = $objprefixStripeTransaction->where(function($tmpQuery) use($RepresentativeLeadsTable,$formData) {
                                            $tmpQuery->where(function($q) use($RepresentativeLeadsTable,$formData){

                                                return $q->where(function($q1) use($RepresentativeLeadsTable,$formData){
                                                   return $q1->where($RepresentativeLeadsTable.'.sales_manager_id','=','0')
                                                           ->where($RepresentativeLeadsTable.'.rep_commission_status','=', '1'); 
                                                })
                                                ->orWhere(function($q2) use($RepresentativeLeadsTable,$formData){
                                                    return $q2->where($RepresentativeLeadsTable.'.representative_id','=','0')
                                                               ->where($RepresentativeLeadsTable.'.sales_manager_commission_status','=', '1');     
                                                });
                                          }); 
                                    }); 
                $objprefixRepStripeTransaction = $objprefixRepStripeTransaction->where(function($tmpQuery) use($RepresentativeLeadsTable,$formData) {
                                            $tmpQuery->where(function($q) use($RepresentativeLeadsTable,$formData){

                                                return $q->where(function($q1) use($RepresentativeLeadsTable,$formData){
                                                   return $q1->where($RepresentativeLeadsTable.'.sales_manager_id','=','0')
                                                           ->where($RepresentativeLeadsTable.'.rep_commission_status','=', '1'); 
                                                })
                                                ->orWhere(function($q2) use($RepresentativeLeadsTable,$formData){
                                                    return $q2->where($RepresentativeLeadsTable.'.representative_id','=',0)
                                                               ->Where($RepresentativeLeadsTable.'.sales_manager_commission_status','=', '1');     
                                                });
                                          }); 
                                    }); 
            }

           if($formData['repPaymentStatus']=="2")
           { 
                $objprefixStripeTransaction = $objprefixStripeTransaction->where(function($tmpQuery) use($RepresentativeLeadsTable,$formData) {
                                            $tmpQuery->where(function($q) use($RepresentativeLeadsTable,$formData){

                                                return $q->where(function($q1) use($RepresentativeLeadsTable,$formData){
                                                   return $q1->where($RepresentativeLeadsTable.'.sales_manager_id','=','0')
                                                           ->where($RepresentativeLeadsTable.'.rep_commission_status','!=', '1'); 
                                                })
                                                ->orWhere(function($q2) use($RepresentativeLeadsTable,$formData){
                                                    return $q2->where($RepresentativeLeadsTable.'.representative_id','=','0')
                                                               ->where($RepresentativeLeadsTable.'.sales_manager_commission_status','!=', '1');     
                                                });
                                          }); 
                                    }); 

               $objprefixRepStripeTransaction = $objprefixRepStripeTransaction->where(function($tmpQuery) use($RepresentativeLeadsTable,$formData) {
                                            $tmpQuery->where(function($q) use($RepresentativeLeadsTable,$formData){

                                                return $q->where(function($q1) use($RepresentativeLeadsTable,$formData){
                                                   return $q1->where($RepresentativeLeadsTable.'.sales_manager_id','=','0')
                                                           ->where($RepresentativeLeadsTable.'.rep_commission_status','!=', '1'); 
                                                })
                                                ->orWhere(function($q2) use($RepresentativeLeadsTable,$formData){
                                                    return $q2->where($RepresentativeLeadsTable.'.representative_id','=',0)
                                                               ->Where($RepresentativeLeadsTable.'.sales_manager_commission_status','!=', '1');     
                                                });

                                          }); 
                                    }); 
            }


        }









           
        //Vendor Payment Status (Outbound)
        if($formData['vendorPaymentStatus']!=false && $formData['is_direct_payment']=="0")
        {  
            if($formData['vendorPaymentStatus']=="1")
            { 
             $objprefixStripeTransaction = $objprefixStripeTransaction
                                            ->where($RetailerQuotesTable.'.maker_commission_status', '=',1);
                                            

             $objprefixRepStripeTransaction = $objprefixRepStripeTransaction
                                            ->where($RepresentativeLeadsTable.'.maker_commission_status', '=','1');
                                            
            }
            if($formData['vendorPaymentStatus']=="2")
            {
                $objprefixStripeTransaction = $objprefixStripeTransaction
                                          ->where(function($tmpQuery) use($RetailerQuotesTable,$formData) {
                                            $tmpQuery->whereNull($RetailerQuotesTable.'.maker_commission_status')
                                            ->orWhere($RetailerQuotesTable.'.maker_commission_status','!=', 1);  
                                          });  
                                          
                                          

                $objprefixRepStripeTransaction = $objprefixRepStripeTransaction
                                            ->where(function($tmpQuery) use($RepresentativeLeadsTable,$formData) {
                                                $tmpQuery->whereNull($RepresentativeLeadsTable.'.maker_commission_status')
                                                ->orWhere($RepresentativeLeadsTable.'.maker_commission_status','!=',1);  
                                            });  
            }
        }
               
        if (isset($formData['toDate']) && $formData['toDate'] != false && isset($formData['fromDate']) && $formData['fromDate'] != false) {

           
            $from_date              = \DateTime::createFromFormat('m/d/Y',$formData['fromDate']);

            $formData['fromDate']   = $from_date->format('Y-m-d');

            $to_date                = \DateTime::createFromFormat('m/d/Y',$formData['toDate']);

            $formData['toDate']    = $to_date->format('Y-m-d');
            
            $objprefixStripeTransaction   = $objprefixStripeTransaction
            ->whereDate($RetailerQuotesTable.'.created_at','>=',$formData['fromDate'])
            ->whereDate($RetailerQuotesTable.'.created_at','<=',$formData['toDate']);

            $objprefixRepStripeTransaction   = $objprefixRepStripeTransaction
            ->whereDate($RepresentativeLeadsTable.'.created_at','>=',$formData['fromDate'])
            ->whereDate($RepresentativeLeadsTable.'.created_at','<=',$formData['toDate']);

        }
 
        if($formData['repId']!=false)
        {
            $objprefixStripeTransaction = $objprefixStripeTransaction
            ->where($RepresentativeLeadsTable.'.representative_id','=', $formData['repId']);

            $objprefixRepStripeTransaction = $objprefixRepStripeTransaction
            ->where($RepresentativeLeadsTable.'.representative_id','=', $formData['repId']);

        }

        if (isset($formData['makerId']) && $formData['makerId'] != false) {

            $objprefixStripeTransaction = $objprefixStripeTransaction
                                            ->where($RetailerQuotesTable.'.maker_id', '=',$formData['makerId']);
            $objprefixRepStripeTransaction = $objprefixRepStripeTransaction
                                            ->where($RepresentativeLeadsTable.'.maker_id', '=',$formData['makerId']);
                                           
        }

        if (isset($formData['retailerId']) && $formData['retailerId'] != false) {

            $objprefixStripeTransaction = $objprefixStripeTransaction
                                            ->where($RetailerQuotesTable.'.retailer_id', '=',$formData['retailerId']);

            $objprefixRepStripeTransaction = $objprefixRepStripeTransaction
                                            ->where($RepresentativeLeadsTable.'.retailer_id', '=',$formData['retailerId']);
        }

        if($formData['salesId']!= false)
        {
           $objprefixStripeTransaction = $objprefixStripeTransaction
            ->where($RepresentativeLeadsTable.'.sales_manager_id','=', $formData['salesId']);

            $objprefixRepStripeTransaction = $objprefixRepStripeTransaction
            ->where($RepresentativeLeadsTable.'.sales_manager_id','=', $formData['salesId']);  
        }

        if (isset($formData['vendorId']) && $formData['vendorId'] != false) {

            $objprefixStripeTransaction = $objprefixStripeTransaction
                                            ->where($RetailerQuotesTable.'.maker_id', '=',$formData['vendorId']);
            $objprefixRepStripeTransaction = $objprefixRepStripeTransaction
                                            ->where($RepresentativeLeadsTable.'.maker_id', '=',$formData['vendorId']);
                                           
        }
    

        // $objprefixStripeTransaction   = $objprefixStripeTransaction->orderBy('order_date','ASC');
        $objprefixStripeTransaction   =  $objprefixStripeTransaction->union($objprefixRepStripeTransaction)->orderBy('order_date','DESC');
      // dd($objprefixStripeTransaction->get());

       // dd($objprefixStripeTransaction->toSql(),$objprefixStripeTransaction->getBindings());
      

        return $objprefixStripeTransaction; 
    }

    public function customer_order_query($formData=false,$arr_search_column=false)
    {   
       
        
        $user_table =  $this->UserModel->getTable();
        $prefix_user_table = DB::getTablePrefix().$user_table;

        $makerTable =  $this->MakerModel->getTable();
        $prefixmakerTable= DB::getTablePrefix().$makerTable;

        $RetailerTable =  $this->RetailerModel->getTable();
        $prefixRetailerTable= DB::getTablePrefix().$RetailerTable;

        $RetailerQuotesTable =  $this->RetailerQuotesModel->getTable();
        $prefixRetailerQuotesTable = DB::getTablePrefix().$RetailerQuotesTable;

        $StripeTransactionTable =  $this->StripeTransactionModel->getTable();
        $prefixStripeTransactionTable = DB::getTablePrefix().$StripeTransactionTable;

        $TransactionMappingTable =  $this->TransactionMappingModel->getTable();
        $prefixTransactionMappingTable = DB::getTablePrefix().$TransactionMappingTable;

        $RoleUserTable =  $this->RoleUsersModel->getTable();
        $prefixRoleUserTable = DB::getTablePrefix().$RoleUserTable;

        $CustomerQuotesTable = $this->CustomerQuotesModel->getTable();
        $prefixCustomerQuotesTable = DB::getTablePrefix().$CustomerQuotesTable;

        // $CustomerQuotesTable = $this->CustomerQuotesModel->getTable();
        // $prefixCustomerQuotesTable = DB::getTablePrefix().$CustomerQuotesTable;

        $RepresentativeLeadsTable =  $this->RepresentativeLeadsModel->getTable();
        $prefixRepresentativeLeadsTable = DB::getTablePrefix().$RepresentativeLeadsTable;

        $objprefixCustomerStripeTransaction = DB::table($CustomerQuotesTable)
                                          ->select(DB::raw(
                                                $StripeTransactionTable.".*,".  
                                                $CustomerQuotesTable.".id as order_id,".
                                                $CustomerQuotesTable.".order_no,".
                                                $CustomerQuotesTable.".maker_id,".
                                                $CustomerQuotesTable.".customer_id,".
                                                $CustomerQuotesTable.".total_retail_price,". 
                                                $CustomerQuotesTable.".created_at as order_date,". 
                                                $CustomerQuotesTable.".admin_commission_status,".
                                                $CustomerQuotesTable.".maker_commission_status,".
                                                $CustomerQuotesTable.".maker_confirmation,".
                                                $CustomerQuotesTable.".ship_status,". 
                                                $CustomerQuotesTable.".is_split_order,". 
                                                $CustomerQuotesTable.".payment_term,". 
                                                $CustomerQuotesTable.".is_direct_payment,". 
                                                $makerTable.".company_name as vendor_name,". 
                                                $RoleUserTable.".role_id as role_id,".
                                                $CustomerQuotesTable.".admin_commission as admin_commission,".
                                                $prefixTransactionMappingTable.".transaction_status,".  
                                                "CONCAT(RN.first_name,' ',RN.last_name) as orderer_name"
                                            ))          

                                ->leftjoin($StripeTransactionTable,$CustomerQuotesTable.'.id','=',$StripeTransactionTable.'.customer_order_id')
                          
                                ->leftjoin($user_table.' AS RN','RN.id','=',$CustomerQuotesTable.'.customer_id')

                                ->leftjoin($RoleUserTable,$prefixRoleUserTable.'.user_id','=',$CustomerQuotesTable.'.customer_id')

                                ->leftjoin($user_table.' AS VN','VN.id','=',$CustomerQuotesTable.'.maker_id')

                                ->leftjoin($TransactionMappingTable,function($join) use($CustomerQuotesTable,$TransactionMappingTable){

                                    $join->on($CustomerQuotesTable.'.id','=',$TransactionMappingTable.'.order_id')
                                    ->on($CustomerQuotesTable.'.order_no','=',$TransactionMappingTable.'.order_no');

                                })
                                ->leftjoin($makerTable,$makerTable.'.user_id','=',$CustomerQuotesTable.'.maker_id')
                                ->where($CustomerQuotesTable.'.is_direct_payment','=',1)
                                ->where($CustomerQuotesTable.'.order_cancel_status', '=', '0')
                                ->where($CustomerQuotesTable.'.is_split_order', '=', '0');
                         

        // $objprefixCustomerStripeTransaction   = $objprefixCustomerStripeTransaction->orderBy('RN.created_at','DESC');
                                $objprefixCustomerStripeTransaction = $objprefixCustomerStripeTransaction->orderBy($CustomerQuotesTable.'.created_at','DESC');


// dd($formData);
        if (isset($formData['orderStatus']) && $formData['orderStatus'] != false) {

            if($formData['orderStatus']=='1')
            {
            $objprefixCustomerStripeTransaction = $objprefixCustomerStripeTransaction
                                            ->where($CustomerQuotesTable.'.admin_commission_status', '=','1');
            }
            else
            {
            $objprefixCustomerStripeTransaction = $objprefixCustomerStripeTransaction
                                            ->where($CustomerQuotesTable.'.admin_commission_status', '=','0')
                                            ->orWhereNull($CustomerQuotesTable.'.admin_commission_status');

            }


                                           
        }



        if (isset($formData['vendorPaymentStatus']) && $formData['vendorPaymentStatus'] != false) 
        {

            if($formData['vendorPaymentStatus']=='1')
            {
    
            $objprefixCustomerStripeTransaction = $objprefixCustomerStripeTransaction
                                            ->where($CustomerQuotesTable.'.maker_confirmation','=',1);
            }
            else
            {
            $objprefixCustomerStripeTransaction = $objprefixCustomerStripeTransaction
                                            ->where($CustomerQuotesTable.'.maker_commission_status', '=','0')
                                            ->orWhereNull($CustomerQuotesTable.'.maker_confirmation');

            }
                                           
        }


        if (isset($formData['makerId']) && $formData['makerId'] != false) {

            $objprefixCustomerStripeTransaction = $objprefixCustomerStripeTransaction
                                            ->where($CustomerQuotesTable.'.maker_id', '=',$formData['makerId']);
      
                                           
        }
        if (isset($formData['retailerId']) && $formData['retailerId'] != false) {

            $objprefixCustomerStripeTransaction = $objprefixCustomerStripeTransaction
                                            ->where($CustomerQuotesTable.'.customer_id', '=',$formData['retailerId']);

        
        }

        if(isset($formData['vendorId']) && $formData['vendorId'] != false)
        {
            $objprefixCustomerStripeTransaction = $objprefixCustomerStripeTransaction
                                            ->where($CustomerQuotesTable.'.maker_id', '=',$formData['vendorId']);
        }


        if (isset($formData['toDate']) && $formData['toDate'] != false && isset($formData['fromDate']) && $formData['fromDate'] != false) {


            $from_date              = \DateTime::createFromFormat('m/d/Y',$formData['fromDate']);

            $formData['fromDate']   = $from_date->format('Y-m-d');

            $to_date                = \DateTime::createFromFormat('m/d/Y',$formData['toDate']);

            $formData['toDate']     = $to_date->format('Y-m-d');
            
            $objprefixCustomerStripeTransaction   = $objprefixCustomerStripeTransaction
            ->whereDate($CustomerQuotesTable.'.created_at','>=',$formData['fromDate'])
            ->whereDate($CustomerQuotesTable.'.created_at','<=',$formData['toDate']);
        }


        
        
        $objprefixCustomerStripeTransaction = $objprefixCustomerStripeTransaction
                                ->where($CustomerQuotesTable.'.is_direct_payment','=',1)
                                ->where($CustomerQuotesTable.'.order_cancel_status', '=', '0');
        
        //dd($formData);

        if($formData['orderStatus'] == 1 && $formData['vendorPaymentStatus'] == 2)
        {
             $objprefixCustomerStripeTransaction = $objprefixCustomerStripeTransaction
                                                             ->where($CustomerQuotesTable.'.admin_commission_status',1)
                                                             ->where($CustomerQuotesTable.'.is_payment_status',0);
        }

        return $objprefixCustomerStripeTransaction; 
    }

    public function indirect_customer_order_query($formData=false,$arr_search_column=false)
    {   
        
        $user_table =  $this->UserModel->getTable();
        $prefix_user_table = DB::getTablePrefix().$user_table;

        $makerTable =  $this->MakerModel->getTable();
        $prefixmakerTable= DB::getTablePrefix().$makerTable;

        $RetailerTable =  $this->RetailerModel->getTable();
        $prefixRetailerTable= DB::getTablePrefix().$RetailerTable;

        $RetailerQuotesTable =  $this->RetailerQuotesModel->getTable();
        $prefixRetailerQuotesTable = DB::getTablePrefix().$RetailerQuotesTable;

        $StripeTransactionTable =  $this->StripeTransactionModel->getTable();
        $prefixStripeTransactionTable = DB::getTablePrefix().$StripeTransactionTable;

        $TransactionMappingTable =  $this->TransactionMappingModel->getTable();
        $prefixTransactionMappingTable = DB::getTablePrefix().$TransactionMappingTable;

        $RoleUserTable =  $this->RoleUsersModel->getTable();
        $prefixRoleUserTable = DB::getTablePrefix().$RoleUserTable;

        $CustomerQuotesTable = $this->CustomerQuotesModel->getTable();
        $prefixCustomerQuotesTable = DB::getTablePrefix().$CustomerQuotesTable;

        $CustomerQuotesTable = $this->CustomerQuotesModel->getTable();
        $prefixCustomerQuotesTable = DB::getTablePrefix().$CustomerQuotesTable;

        $RepresentativeLeadsTable =  $this->RepresentativeLeadsModel->getTable();
        $prefixRepresentativeLeadsTable = DB::getTablePrefix().$RepresentativeLeadsTable;

        $objprefixCustomerStripeTransaction = DB::table($CustomerQuotesTable)
                                          ->select(DB::raw(
                                                $StripeTransactionTable.".*,".  
                                                $CustomerQuotesTable.".id as order_id,".
                                                $CustomerQuotesTable.".order_no,".
                                                $CustomerQuotesTable.".maker_id,".
                                                $CustomerQuotesTable.".customer_id,".
                                                $CustomerQuotesTable.".total_retail_price,". 
                                                $CustomerQuotesTable.".created_at as order_date,".
                                                $CustomerQuotesTable.".admin_commission_status,".
                                                $CustomerQuotesTable.".maker_confirmation,". 
                                                $CustomerQuotesTable.".maker_commission_status,".
                                                $CustomerQuotesTable.".ship_status,". 
                                                $CustomerQuotesTable.".is_split_order,". 
                                                $CustomerQuotesTable.".payment_term,". 
                                                $CustomerQuotesTable.".is_direct_payment,". 
                                                $makerTable.".company_name as vendor_name,". 
                                                $RoleUserTable.".role_id as role_id,".
                                                $CustomerQuotesTable.".admin_commission as admin_commission,".
                                                $StripeTransactionTable.".status as stripe_trxn_status,".
                                                $prefixTransactionMappingTable.".transaction_status,".  
                                                "CONCAT(RN.first_name,' ',RN.last_name) as orderer_name"
                                            ))          

                                ->leftjoin($StripeTransactionTable,$CustomerQuotesTable.'.id','=',$StripeTransactionTable.'.lead_id')
                          
                                ->leftjoin($user_table.' AS RN','RN.id','=',$CustomerQuotesTable.'.customer_id')

                                ->leftjoin($RoleUserTable,$prefixRoleUserTable.'.user_id','=',$CustomerQuotesTable.'.customer_id')

                                ->leftjoin($user_table.' AS VN','VN.id','=',$CustomerQuotesTable.'.maker_id')

                                ->leftjoin($makerTable,$makerTable.'.user_id','=',$CustomerQuotesTable.'.maker_id')

                                ->leftjoin($TransactionMappingTable,function($join) use($CustomerQuotesTable,$TransactionMappingTable){

                                    $join->on($CustomerQuotesTable.'.id','=',$TransactionMappingTable.'.order_id')
                                    ->on($CustomerQuotesTable.'.order_no','=',$TransactionMappingTable.'.order_no');

                                })
                                ->where($CustomerQuotesTable.'.is_direct_payment','=',0)
                                ->where($CustomerQuotesTable.'.order_cancel_status', '=', '0')
                                ->where($CustomerQuotesTable.'.is_split_order', '=', '0');
                                // $objprefixCustomerStripeTransaction =  $objprefixCustomerStripeTransaction->join($TransactionMappingTable, function($join)use($CustomerQuotesTable,$TransactionMappingTable)
                                // {
                                //     $join->on($CustomerQuotesTable.'.id','=',$TransactionMappingTable.'.order_id')
                                //           ->on($CustomerQuotesTable.'.order_no','=',$TransactionMappingTable.'.order_no');
                                // });

        // $objprefixCustomerStripeTransaction   = $objprefixCustomerStripeTransaction->orderBy('RN.created_at','DESC');

         $objprefixCustomerStripeTransaction = $objprefixCustomerStripeTransaction->orderBy($CustomerQuotesTable.'.created_at','DESC');
        
       // if(isset($formData['orderStatus']) && $formData['orderStatus'] != false && isset($formData['vendorPaymentStatus']) && $formData['vendorPaymentStatus'] != false)
       // {

       //  dd($formData);
       // }

       if (isset($formData['orderStatus']) && $formData['orderStatus'] != false) {
          
            if($formData['orderStatus']=='1')
            {
                $objprefixCustomerStripeTransaction = $objprefixCustomerStripeTransaction
                                                 ->where($CustomerQuotesTable.'.is_payment_status', '=','1');
            }
            else
            {
                // $objprefixCustomerStripeTransaction = $objprefixCustomerStripeTransaction
                //                                      ->where($CustomerQuotesTable.'.maker_confirmation', '!=','1')
                //                                      ->orWhereNull($CustomerQuotesTable.'.maker_confirmation');
               
               $objprefixCustomerStripeTransaction = $objprefixCustomerStripeTransaction
                                                    ->where(function($tmpQuery) use($CustomerQuotesTable,$formData)
                                                    {
                                                     $tmpQuery->whereNull($CustomerQuotesTable.'.maker_confirmation')
                                                         ->orWhere($CustomerQuotesTable.'.maker_confirmation','!=','1');  
                                                    });  
            }
        }


         if (isset($formData['vendorPaymentStatus']) && $formData['vendorPaymentStatus'] != false) 
        {
            //dd($formData);
            if($formData['vendorPaymentStatus']=='2')
            {
    
                $objprefixCustomerStripeTransaction = $objprefixCustomerStripeTransaction
                                                      ->where($CustomerQuotesTable.'.maker_commission_status', '=','1');
            }
            else if($formData['vendorPaymentStatus']!='2')
            {   
                $objprefixCustomerStripeTransaction = $objprefixCustomerStripeTransaction
                                                     ->whereNull($CustomerQuotesTable.'.maker_commission_status')
                                                     ->orWhere($CustomerQuotesTable.'.maker_commission_status','!=','1');
            }
        }

        if(isset($formData['vendorId']) && $formData['vendorId'] != false)
        {
            $objprefixCustomerStripeTransaction = $objprefixCustomerStripeTransaction
                                            ->where($CustomerQuotesTable.'.maker_id', '=',$formData['vendorId']);
        }
        

        // $objprefixCustomerStripeTransaction = $objprefixCustomerStripeTransaction
        //                         ->where($CustomerQuotesTable.'.is_direct_payment','=',0)
        //                         ->where($CustomerQuotesTable.'.order_cancel_status', '=', '0');


        if (isset($formData['makerId']) && $formData['makerId'] != false) {

            $objprefixCustomerStripeTransaction = $objprefixCustomerStripeTransaction
                                            ->where($CustomerQuotesTable.'.maker_id', '=',$formData['makerId']);
      
                                           
        }
        if (isset($formData['retailerId']) && $formData['retailerId'] != false) {

            $objprefixCustomerStripeTransaction = $objprefixCustomerStripeTransaction
                                            ->where($CustomerQuotesTable.'.customer_id', '=',$formData['retailerId']);
        }
        if (isset($formData['toDate']) && $formData['toDate'] != false && isset($formData['fromDate']) && $formData['fromDate'] != false) {

            $from_date              = \DateTime::createFromFormat('m/d/Y',$formData['fromDate']);

            $formData['fromDate']   = $from_date->format('Y-m-d');

            $to_date                = \DateTime::createFromFormat('m/d/Y',$formData['toDate']);

            $formData['toDate']    = $to_date->format('Y-m-d');
            
            $objprefixCustomerStripeTransaction   = $objprefixCustomerStripeTransaction
            ->whereDate($CustomerQuotesTable.'.created_at','>=',$formData['fromDate'])
            ->whereDate($CustomerQuotesTable.'.created_at','<=',$formData['toDate']);
        }

        $objprefixCustomerStripeTransaction = $objprefixCustomerStripeTransaction
                                ->where($CustomerQuotesTable.'.is_direct_payment','=',0)
                                ->where($CustomerQuotesTable.'.order_cancel_status', '=', '0');
        //this condition for invalid condition

        // if($formData['orderStatus'] == 2 && $formData['vendorPaymentStatus'] == 2)
        // {
        //      $objprefixCustomerStripeTransaction = $objprefixCustomerStripeTransaction
        //                                                      ->where($CustomerQuotesTable.'.maker_commission_status', '=','1')
        //                                                      ->where($CustomerQuotesTable.'.is_payment_status',0);
        // }


        return $objprefixCustomerStripeTransaction; 
    }




    public function calculate_vendor_total_commission($status,$formData,$arr_search_column=false)
    {   

        $totalCommission = $totalOrderAmount = 0;
        $totalAmounts    = [];
        $totalAmounts['totalCommission'] = 0;
        $totalAmounts['totalCommissionPending'] = $totalAmounts['totalCommissionPaid'] = $totalAmounts['totalOrderAmountPending'] = $totalAmounts['totalOrderAmountPaid'] = $totalAmounts['vendorCommissionPending'] = $totalAmounts['vendorCommissionPaid'] = $totalAmounts['adminCommissionEarned'] = $totalAmounts['adminCommissionRecived']= $totalAmounts['adminCommissionPending']=$totalAmounts['totalPaymentsPendingPayVendors'] = $totalAmounts['totalPaymentsPaidPayVendors'] =$totalAmounts['totalPayVendors'] = $totalAmounts['totalRepCommission']= $totalAmounts['totalRepCommissionPending']= $totalAmounts['totalRepCommissionPaid']=0;
     
        $objOrders = $this->retailer_order_query($formData,$arr_search_column);

                if ($objOrders) {

                    $orders = $objOrders->get()->toArray();
                    //dd($orders);
                    foreach ($orders as $key => $order) {

                       $adminCommission  = isset($order->admin_commission)?num_format($order->admin_commission):0.00;
                        if($adminCommission == 0)
                        {
                             $adminCommission =  $this->CommissionService->get_admin_commission();
                        }

                        $shipping_charges = $this->get_retailer_order_shipping_charges($order->order_id);

                        if(isset($order->representative_id)||isset($order->sales_manager_id))
                        {
                           $shipping_charges = $this->get_representative_order_shipping_charges($order->order_id); 
                        }
                        
                        $totalPrice             = isset($order->total_wholesale_price)?num_format($order->total_wholesale_price):0.00;



                       // $totalPrice = $totalPrice - $shipping_charges;

                        $promo_code = isset($order->promo_code)?is_promocode_freeshipping($order->promo_code):'';
                        if($promo_code == true)
                        {
                           $shipping_charges = 0;
                           $totalPrice = $totalPrice - $shipping_charges;
                        }
                        else
                        {
                            $totalPrice = $totalPrice - $shipping_charges;
                        }

                   
                        //-------------Retailers' Payments (to Vendor):-----------------
                        if ($order->transaction_status == '' || $order->transaction_status ==null) 
                        {
                            $totalAmounts['totalOrderAmountPending']   += isset($order->total_wholesale_price)?num_format($order->total_wholesale_price):0.00;
                        }
                        else
                        {
                            $totalAmounts['totalOrderAmountPaid']   += isset($order->total_wholesale_price)?num_format($order->total_wholesale_price):0.00;
                        }
                        //---------------------Retailers Payments (to Vendor)-----------------
                        
                        
                        //---------------------Commissions Receipts---------------------------
                        if ($order->admin_commission_status == 0 || $order->admin_commission_status==null)
                        {

                            $adminCommissionAmount  = ($totalPrice * $adminCommission) / 100;
                            
                            $totalAmounts['adminCommissionPending'] +=  $adminCommissionAmount;
                        }
                        elseif($order->admin_commission_status == 1)
                        {

                            $adminCommissionAmount  = ($totalPrice * $adminCommission) / 100;
                           
                            $totalAmounts['adminCommissionRecived'] += $adminCommissionAmount;
                        }
                        //----------------------Commissions Receipts-------------------------

                        

                        //----------------------Rep Commissions Payments:-------------------
                        if(isset($order->representative_id)||isset($order->sales_manager_id))
                        {
                            //dd($order);
                            if($order->sales_manager_id==0)
                            {
                                $representative_commission = $this->CommissionService->get_representative_commission($order->representative_id);
                            }
                            else
                            {
                                $representative_commission = $this->CommissionService->get_sales_manager_commission($order->sales_manager_id);  
                            }   
               

                            $rep_commission_amount = ($adminCommissionAmount * $representative_commission) / 100;
                            
                            $totalAmounts['totalRepCommission']+= $rep_commission_amount;
                            
                            if($order->rep_commission_status!="1"&&$order->sales_manager_commission_status!="1")
                            {
                                $totalAmounts['totalRepCommissionPending'] +=$rep_commission_amount; 
                            }
                            if($order->rep_commission_status=="1"||$order->sales_manager_commission_status=="1")
                            {
                                $totalAmounts['totalRepCommissionPaid'] += $rep_commission_amount;
                            }
                        }
                        //------------------------ Rep Commissions Payments------------------

                    }
                    $totalAmounts['totalPayVendors'] = $totalAmounts['totalPaymentsPendingPayVendors'] +$totalAmounts['totalPaymentsPaidPayVendors'];
                   


                    $totalAmounts['totalCommission'] = $totalAmounts['adminCommissionPending']+$totalAmounts['adminCommissionRecived'];

                    $totalAmounts['totalRetailersPayment'] = $totalAmounts['totalOrderAmountPaid']+$totalAmounts['totalOrderAmountPending'];
                }
        //dd($totalAmounts);        
        return $totalAmounts;
    }

    public function indirect_calculate_vendor_total_commission($status,$formData,$arr_search_column=false)
    {
        
        $totalCommission = $totalOrderAmount = 0;
        $totalAmounts    = [];
        $totalAmounts['totalCommission'] = 0;
        $totalAmounts['totalCommissionPending'] = $totalAmounts['totalCommissionPaid'] = $totalAmounts['totalOrderAmountPending'] = $totalAmounts['totalOrderAmountPaid'] = $totalAmounts['vendorCommissionPending'] = $totalAmounts['vendorCommissionPaid'] = $totalAmounts['adminCommissionEarned'] = $totalAmounts['adminCommissionRecived']= $totalAmounts['adminCommissionPending']=$totalAmounts['totalPaymentsPendingPayVendors'] = $totalAmounts['totalPaymentsPaidPayVendors'] =$totalAmounts['totalPayVendors'] = $totalAmounts['totalRepCommission']= $totalAmounts['totalRepCommissionPending']= $totalAmounts['totalRepCommissionPaid']=0;

       
        $objOrders = $this->retailer_indirect_order_query($formData,$arr_search_column);

                if ($objOrders) {

                    $orders = $objOrders->get()->toArray();

                    foreach ($orders as $key => $order) {

                        $adminCommission  = isset($order->admin_commission)?num_format($order->admin_commission):0.00;
                        if($adminCommission==0)
                        {
                        $adminCommission  = $this->CommissionService->get_admin_commission();
                        }

                        $promo_code = isset($order->promo_code)?is_promocode_freeshipping($order->promo_code):'';

                        if($promo_code == true)
                        {
                           $shipping_charges = 0.00;
                        }
                        else
                        {
                            
                            $shipping_charges = $this->get_retailer_order_shipping_charges($order->order_id);
                            if(isset($order->representative_id)||isset($order->sales_manager_id))
                            {
                               $shipping_charges = $this->get_representative_order_shipping_charges($order->order_id);
                            }

                        }
    

                        $totalPrice             = isset($order->total_wholesale_price)?num_format($order->total_wholesale_price):0.00;

                        $totalPrice = $totalPrice - $shipping_charges;

                   
                   
                        //-------------Retailers' Payments-----------------
                        if ($order->transaction_status == '' || $order->transaction_status ==null) 
                        {
                            $totalAmounts['totalOrderAmountPending']   += isset($order->total_wholesale_price)?num_format($order->total_wholesale_price):0.00;
                        }
                        else
                        {
                            $totalAmounts['totalOrderAmountPaid']   += isset($order->total_wholesale_price)?num_format($order->total_wholesale_price):0.00;
                        }
                        //---------------------Retailers Payments-----------------
                        
                        
                        //---------------------Vendor Payments---------------------------
                        if ($order->maker_commission_status == 1)
                        {   
                            if($order->rep_commission_status!=null||$order->rep_commission_status==0)
                            {

                              //$shipping_charges = $this->get_representative_order_shipping_charges($order->order_id);

                           /* $totalPrice = isset($order->total_wholesale_price)?num_format($order->total_wholesale_price):0.00;

                            $totalPrice = $totalPrice - $shipping_charges;*/
                            
                            $representative_commission = $this->CommissionService->get_representative_commission($order->representative_id);

                            $adminCommissionAmount  = $totalPrice * ($adminCommission / 100);


                            $rep_commission_amount = $adminCommissionAmount * ($representative_commission / 100);                                

                            //$adminCommissionAmount = $adminCommissionAmount + $rep_commission_amount;

                            $adminCommissionAmount = $totalPrice-$adminCommissionAmount;
                            
                            
                            }
                            else
                            {
                              $adminCommissionAmount  = $totalPrice * ($adminCommission / 100);
                              $adminCommissionAmount = $totalPrice-$adminCommissionAmount;
                            }   
                            
                            $totalAmounts['adminCommissionRecived'] +=  $adminCommissionAmount;
                            
                        }    

                        if ($order->maker_commission_status == 0 || $order->maker_commission_status==null)
                        {
                            if($order->rep_commission_status!=null||$order->rep_commission_status=="0")
                            {

                            $representative_commission = $this->CommissionService->get_representative_commission($order->representative_id);

                            $adminCommissionAmount  = $totalPrice * ($adminCommission / 100);

                            $rep_commission_amount = $adminCommissionAmount * ($representative_commission / 100);                                
                            //$adminCommissionAmount = $adminCommissionAmount + $rep_commission_amount;

                            $adminCommissionAmount = $totalPrice-$adminCommissionAmount;
                            
                            
                            }
                            else
                            {
                            
                            $adminCommissionAmount  = $totalPrice * ($adminCommission / 100);
                            $adminCommissionAmount = $totalPrice-$adminCommissionAmount;
                            }   
                            
                            $totalAmounts['adminCommissionPending'] +=  $adminCommissionAmount;
                            
                        }    
                            
                        //----------------------Vendor Payments-------------------------

                        

                        //----------------------Rep Commissions:-------------------
                        if((isset($order->representative_id) && $order->representative_id!=0) || (isset($order->sales_manager_id) && $order->sales_manager_id!=0))
                        {

                           if(isset($order->representative_id) && $order->representative_id!=0)
                           {
                              $representative_commission = $this->CommissionService->get_representative_commission($order->representative_id);
                           } 
                          
                           if(isset($order->sales_manager_id) && $order->sales_manager_id!=0)
                           {
                              $representative_commission = $this->CommissionService->get_sales_manager_commission($order->sales_manager_id);
                           }

                           $adminCommissionAmount  = ($totalPrice * $adminCommission) / 100;

                           $rep_commission_amount = ($adminCommissionAmount * $representative_commission) / 100;
                            
                           $rep_commission_amount = isset($rep_commission_amount)?num_format($rep_commission_amount):0.00;
                           
                            $totalAmounts['totalRepCommission']+= $rep_commission_amount;
                            
                            if($order->rep_commission_status=="0" || $order->sales_manager_commission_status=="0")
                            {   
                                
                                $totalAmounts['totalRepCommissionPending'] +=$rep_commission_amount; 
                            }



                            if($order->rep_commission_status=="1" || $order->sales_manager_commission_status=="1")
                            {
                                $totalAmounts['totalRepCommissionPaid'] += $rep_commission_amount;
                            }
                        }
                        //------------------------ Rep Commissions:------------------

                    }

                   
                    $totalAmounts['totalPayVendors'] = $totalAmounts['totalPaymentsPendingPayVendors'] +$totalAmounts['totalPaymentsPaidPayVendors'];

                    $totalAmounts['totalCommission'] = $totalAmounts['adminCommissionPending']+$totalAmounts['adminCommissionRecived'];

                    $totalAmounts['totalRetailersPayment'] = $totalAmounts['totalOrderAmountPaid']+$totalAmounts['totalOrderAmountPending'];

                    $totalAmounts['totalRepCommissionPending'] = $totalAmounts['totalRepCommissionPending'] - $totalAmounts['totalRepCommissionPaid'];

                    $totalAmounts['totalRepCommission'] = $totalAmounts['totalRepCommissionPending'] + $totalAmounts['totalRepCommissionPaid'];
                    $totalAmounts['totalRepCommission'] = isset($totalAmounts['totalRepCommission'])?num_format($totalAmounts['totalRepCommission']):0.00;
                }

        return $totalAmounts;
    }

    public function intermidiate_payment_customer_total_commission($formData,$arr_search_column=false)
    {
        
        $totalCommission = $totalOrderAmount = 0;
        $totalAmounts    = [];
        $totalAmounts['totalCommission'] = 0;
        $totalAmounts['totalCommissionPending'] = $totalAmounts['totalCommissionPaid'] = $totalAmounts['totalOrderAmountPending'] = $totalAmounts['totalOrderAmountPaid'] = $totalAmounts['vendorCommissionPending'] = $totalAmounts['vendorCommissionPaid'] = $totalAmounts['totalAdminCommission'] = $totalAmounts['adminCommissionRecived']= $totalAmounts['adminCommissionPending']=$totalAmounts['totalPaymentsPendingPayVendors'] = $totalAmounts['totalPaymentsPaidPayVendors'] =$totalAmounts['totalPayVendors'] = $totalAmounts['totalRepCommission']= $totalAmounts['totalRepCommissionPending']= $totalAmounts['totalRepCommissionPaid']=0;

       
        $objOrders = $this->indirect_customer_order_query($formData,$arr_search_column);

                if ($objOrders) {

                    $orders = $objOrders->get()->toArray();
                    
                    foreach ($orders as $key => $order) {

                      
                        $adminCommission  = isset($order->maker_admin_commission)?num_format($order->maker_admin_commission):0.00;
                        if($adminCommission==0)
                        {
                        $adminCommission  = isset($order->admin_commission)?num_format($order->admin_commission):0.00;
                        }
                        $shipping_charges = $this->get_customer_order_shipping_charges($order->order_id);
                        $shipping_charges =  isset($shipping_charges)?$shipping_charges:0;
                        

                        $totalPrice             = isset($order->total_retail_price)?num_format($order->total_retail_price):0.00;

                        $totalPrice = $totalPrice - $shipping_charges;

                      
                            
                   
                        //-------------Customer Payments-----------------
                        if ($order->transaction_status == '' || $order->transaction_status ==null) 
                        {
                            $totalAmounts['totalOrderAmountPending']   += isset($order->total_retail_price)?num_format($order->total_retail_price):0.00;
                        }
                        else
                        {
                            //dump($order->transaction_status);
                            $totalAmounts['totalOrderAmountPaid']   += isset($order->total_retail_price)?num_format($order->total_retail_price):0.00;
                        }
                        //---------------------Customer Payments-----------------
                        
                        
                        //---------------------Vendor Payments---------------------------
                        if ($order->maker_commission_status == 1)
                        {   
                           
                            $shipping_charges = $this->get_customer_order_shipping_charges($order->order_id);

                            $totalPrice = isset($order->total_retail_price)?$order->total_retail_price:0.00;

                            $totalPrice = $totalPrice - $shipping_charges;
                       

                            $adminCommissionAmount  = ($totalPrice * $adminCommission) / 100;
                            
                            $adminCommissionAmount = $totalPrice-$adminCommissionAmount;
                            
                            $totalAmounts['totalPaymentsPaidPayVendors'] +=  $adminCommissionAmount;
                        }    

                        if ($order->maker_commission_status == 0 || $order->maker_commission_status==null)
                        {
                            $adminCommissionAmount  = ($totalPrice * $adminCommission )/ 100;

                            $adminCommissionAmount = $totalPrice-$adminCommissionAmount;
                            
                            $totalAmounts['totalPaymentsPendingPayVendors'] +=  $adminCommissionAmount;
                        }    
                            
                        //----------------------Vendor Payments-------------------------

                        

                        //----------------------admin Commissions:-------------------
                         if ($order->maker_commission_status == 1)
                        {  
                            $adminCommissionAmount  = ($totalPrice * $adminCommission) / 100;

                            $totalAmounts['adminCommissionRecived'] +=  $adminCommissionAmount;
                        }  
                         if ($order->maker_commission_status == 0 || $order->maker_commission_status==null)
                        {
                             $adminCommissionAmount  = ($totalPrice * $adminCommission )/ 100;
                             $totalAmounts['adminCommissionPending'] +=  $adminCommissionAmount;
                        }  
                        

                        //------------------------ admin Commissions:------------------

                    }
                    $totalAmounts['totalPayVendors'] = $totalAmounts['totalPaymentsPendingPayVendors'] +$totalAmounts['totalPaymentsPaidPayVendors'];
                   


                    $totalAmounts['totalAdminCommission'] = $totalAmounts['adminCommissionPending']+$totalAmounts['adminCommissionRecived'];

                    $totalAmounts['totalRetailersPayment'] = $totalAmounts['totalOrderAmountPaid']+$totalAmounts['totalOrderAmountPending'];
                }
                //dd($totalAmounts);
        return $totalAmounts;
    }

 public function direct_payment_customer_total_commission($formData,$arr_search_column=false)
    {
       
        $totalCommission = $totalOrderAmount = 0;
        $totalAmounts    = [];
        $totalAmounts['totalCommission'] = 0;
        $totalAmounts['totalCommissionPending'] = $totalAmounts['totalCommissionPaid'] = $totalAmounts['totalOrderAmountPending'] = $totalAmounts['totalOrderAmountPaid'] = $totalAmounts['vendorCommissionPending'] = $totalAmounts['vendorCommissionPaid'] = $totalAmounts['totalAdminCommission'] = $totalAmounts['adminCommissionRecived']= $totalAmounts['adminCommissionPending']=$totalAmounts['totalPaymentsPendingPayVendors'] = $totalAmounts['totalPaymentsPaidPayVendors'] =$totalAmounts['totalPayVendors'] = $totalAmounts['totalRepCommission']= $totalAmounts['totalRepCommissionPending']= $totalAmounts['totalRepCommissionPaid']=0;

       
        $objOrders = $this->customer_order_query($formData,$arr_search_column);

                if ($objOrders) {

                    $orders = $objOrders->get()->toArray();
                    //dd($orders);
                    foreach ($orders as $key => $order) {

                        //dd($order);
                        $adminCommission  = isset($order->maker_admin_commission)?num_format($order->maker_admin_commission):0.00;
                        if($adminCommission==0)
                        {
                        $adminCommission  = isset($order->admin_commission)?num_format($order->admin_commission):0.00;
                        }
                        $shipping_charges = $this->get_customer_order_shipping_charges($order->order_id);
                        

                        $totalPrice             = isset($order->total_retail_price)?num_format($order->total_retail_price):0.00;

                        $totalPrice = $totalPrice - $shipping_charges;

                      
                            
                   
                        //-------------Customer Payments-----------------
                        if ($order->maker_confirmation !='1') 
                        {
                            $totalAmounts['totalOrderAmountPending']   += isset($order->total_retail_price)?num_format($order->total_retail_price):0.00;
                        }
                        else
                        {
                            $totalAmounts['totalOrderAmountPaid']   += isset($order->total_retail_price)?num_format($order->total_retail_price):0.00;
                        }
                        //---------------------Customer Payments-----------------
                        
                        
                        //---------------------Vendor Payments---------------------------
                        if ($order->transaction_status == 2)
                        {   
                           
                            $shipping_charges = $this->get_customer_order_shipping_charges($order->order_id);

                            $totalPrice = isset($order->total_retail_price)?num_format($order->total_retail_price):0.00;

                            $totalPrice = $totalPrice - $shipping_charges;
                            
                          

                            $adminCommissionAmount  = $totalPrice * ($adminCommission / 100);

                            $adminCommissionAmount = $totalPrice-$adminCommissionAmount;
                            
                            
                       
                            $totalAmounts['totalPaymentsPaidPayVendors'] +=  $adminCommissionAmount;
                        }    

                        if ($order->transaction_status == 0 || $order->transaction_status==null)
                        {
                            
                            $adminCommissionAmount  = $totalPrice * ($adminCommission / 100);

                            $adminCommissionAmount = $totalPrice-$adminCommissionAmount;
                            
                            
                            $totalAmounts['totalPaymentsPendingPayVendors'] +=  $adminCommissionAmount;
                        }    
                            
                        //----------------------Vendor Payments-------------------------

                        

                        //----------------------admin Commissions:-------------------
                         if ($order->admin_commission_status == 1)
                        {
                            $adminCommissionAmount  = $totalPrice * ($adminCommission / 100);

                            $totalAmounts['adminCommissionRecived'] +=  $adminCommissionAmount;
                        }  
                         if ($order->admin_commission_status == 0 || $order->admin_commission_status==null)
                        {
                            $adminCommissionAmount  = $totalPrice * ($adminCommission / 100);

                            $totalAmounts['adminCommissionPending'] +=  $adminCommissionAmount;
                        }  
                        

                        //------------------------ admin Commissions:------------------

                    }
                    $totalAmounts['totalPayVendors'] = $totalAmounts['totalPaymentsPendingPayVendors'] +$totalAmounts['totalPaymentsPaidPayVendors'];
                   


                    $totalAmounts['totalAdminCommission'] = $totalAmounts['adminCommissionPending']+$totalAmounts['adminCommissionRecived'];

                    $totalAmounts['totalRetailersPayment'] = $totalAmounts['totalOrderAmountPaid']+$totalAmounts['totalOrderAmountPending'];
                }
                //dd($totalAmounts);
        return $totalAmounts;
    }




    public function vendor_order_query($arr_search_column =false,$is_direct_payment = 0,$vendor_id = 0)
    {

        $user_table =  $this->UserModel->getTable();
        $prefix_user_table = DB::getTablePrefix().$user_table;

        $makerTable =  $this->MakerModel->getTable();
        $prefixmakerTable= DB::getTablePrefix().$makerTable;

        $RetailerTable =  $this->RetailerModel->getTable();
        $prefixRetailerTable= DB::getTablePrefix().$RetailerTable;

        $RetailerQuotesTable =  $this->RetailerQuotesModel->getTable();
        $prefixRetailerQuotesTable = DB::getTablePrefix().$RetailerQuotesTable;

        $CustomerQuotesTable = $this->CustomerQuotesModel->getTable();
        $prefixCustomerQuotesTable = DB::getTablePrefix().$CustomerQuotesTable;

        $StripeTransactionTable =  $this->StripeTransactionModel->getTable();
        $prefixStripeTransactionTable = DB::getTablePrefix().$StripeTransactionTable;

        $TransactionMappingTable =  $this->TransactionMappingModel->getTable();
        $prefixTransactionMappingTable = DB::getTablePrefix().$TransactionMappingTable;

        $RoleUserTable =  $this->RoleUsersModel->getTable();
        $prefixRoleUserTable = DB::getTablePrefix().$RoleUserTable;

        $objTransactionData = DB::table($RetailerQuotesTable)
                                          ->select(DB::raw(
                                                $StripeTransactionTable.".*,".  
                                                $RetailerQuotesTable.".id as order_id,".
                                                $RetailerQuotesTable.".order_no,".
                                                $RetailerQuotesTable.".maker_id,".
                                                $RetailerQuotesTable.".retailer_id,".
                                                $RetailerQuotesTable.".total_wholesale_price,". 
                                                $RetailerQuotesTable.".created_at as order_date,". 
                                                $RetailerQuotesTable.".is_direct_payment as is_direct_payment,". 
                                                $RetailerQuotesTable.".maker_commission_status,". 
                                                $RoleUserTable.".role_id,".
                                                $makerTable.".company_name as vendor_name,". 
                                                "CONCAT(RN.first_name,' ',RN.last_name) as customer_name"
                                            ))          

                                ->leftjoin($StripeTransactionTable,$RetailerQuotesTable.'.id','=',$StripeTransactionTable.'.lead_id')
                          
                                ->leftjoin($user_table.' AS RN','RN.id','=',$RetailerQuotesTable.'.retailer_id')

                                
                         
                                ->leftjoin($RoleUserTable,$prefixRoleUserTable.'.user_id','=',$RetailerQuotesTable.'.retailer_id')

                                
                                ->leftjoin($user_table.' AS VN','VN.id','=',$RetailerQuotesTable.'.maker_id')

                                ->leftjoin($makerTable,$makerTable.'.user_id','=',$RetailerQuotesTable.'.maker_id')
                             
                                // ->where($RetailerQuotesTable.'.is_direct_payment', '=', $is_direct_payment)

                               

                                ->where($RetailerQuotesTable.'.order_cancel_status', '=', '0');
                                
                                $query = $objTransactionData;
                                
                                 if($vendor_id!=0)
                                  {
                                    $objTransactionData =  $query->where($RetailerQuotesTable.'.maker_id', '=', $vendor_id);  
                                  }

        $customerObjTransactionData = DB::table($CustomerQuotesTable)
                                          ->select(DB::raw(
                                                $StripeTransactionTable.".*,".  
                                                $CustomerQuotesTable.".id as order_id,".
                                                $CustomerQuotesTable.".order_no,".
                                                $CustomerQuotesTable.".maker_id,".
                                                $CustomerQuotesTable.".customer_id,".
                                                $CustomerQuotesTable.".total_retail_price,". 
                                                $CustomerQuotesTable.".created_at as order_date,". 
                                                $CustomerQuotesTable.".is_direct_payment as is_direct_payment,". 
                                                $CustomerQuotesTable.".maker_commission_status,".
                                                 $RoleUserTable.".role_id,".
                                                $makerTable.".company_name as vendor_name,". 
                                                "CONCAT(RN.first_name,' ',RN.last_name) as customer_name"
                                            ))
                                            ->leftjoin($StripeTransactionTable,$CustomerQuotesTable.'.id','=',$StripeTransactionTable.'.customer_order_id')

                                            ->leftjoin($user_table.' AS RN','RN.id','=',$CustomerQuotesTable.'.customer_id')

                                            
                                            ->leftjoin($makerTable,$makerTable.'.user_id','=',$CustomerQuotesTable.'.maker_id')                        
                                            ->leftjoin($RoleUserTable,$prefixRoleUserTable.'.user_id','=',$CustomerQuotesTable.'.customer_id')

                                            // ->where($CustomerQuotesTable.'.is_direct_payment', '=', $is_direct_payment)

                                            

                                            ->where($CustomerQuotesTable.'.order_cancel_status', '=', '0');


         //dd($arr_search_column);
        if(isset($arr_search_column['q_vendor_name']) && $arr_search_column['q_vendor_name']!="")
        {
            $search_term  = $arr_search_column['q_vendor_name'];
            
            $objTransactionData = $objTransactionData->where($RetailerQuotesTable.'.maker_id','=', $search_term);
            $customerObjTransactionData = $customerObjTransactionData->where($CustomerQuotesTable.'.maker_id',$search_term);
        } 

       
        if(isset($arr_search_column['q_order_no']) && $arr_search_column['q_order_no']!="")
        {   
            $search_term  = $arr_search_column['q_order_no'];
            
            $objTransactionData     = $objTransactionData->having('order_no','LIKE', '%'.$search_term.'%');
            $customerObjTransactionData  = $customerObjTransactionData->having('order_no','LIKE', '%'.$search_term.'%');
        } 

        
        if(isset($arr_search_column['q_from_date']) && isset($arr_search_column['q_to_date']) && $arr_search_column['q_from_date']!='' && $arr_search_column['q_to_date'] != '')
        {
            $from_date              = \DateTime::createFromFormat('m-d-Y',$arr_search_column['q_from_date']);
            $from_date              = $from_date->format('Y-m-d');
            
            $to_date                = \DateTime::createFromFormat('m-d-Y',$arr_search_column['q_to_date']);
            $to_date                = $to_date->format('Y-m-d');

            $objTransactionData     = $objTransactionData->whereBetween($RetailerQuotesTable.'.created_at', array($from_date, $to_date));

            $customerObjTransactionData     = $customerObjTransactionData->whereBetween($CustomerQuotesTable.'.created_at', array($from_date, $to_date));
        } 


        if(isset($arr_search_column['q_status']) && $arr_search_column['q_status']!="")
        {
            $search_term  = $arr_search_column['q_status'];
            if($search_term=='1')
            {
                $objTransactionData = $objTransactionData->where($StripeTransactionTable.'.status','=', null);
                $customerObjTransactionData = $customerObjTransactionData->where($StripeTransactionTable.'.status','=', null);
            }
            else
            {   
                $objTransactionData     = $objTransactionData->having('status','LIKE', '%'.$search_term.'%');
                $customerObjTransactionData     = $customerObjTransactionData->having('status','LIKE', '%'.$search_term.'%');
            }
        }   

        
        if(isset($arr_search_column['q_ordered_by']) && $arr_search_column['q_ordered_by']!="")
        {
           $search_term      = $arr_search_column['q_ordered_by'];
           $search_term      = intval($search_term);
           
           $objTransactionData = $objTransactionData->where($RoleUserTable.'.role_id','=', $search_term);
           $customerObjTransactionData = $customerObjTransactionData->where($RoleUserTable.'.role_id','=', $search_term);
        }  
       



       $results = $objTransactionData->union($customerObjTransactionData);
       //dd($results->get()->toArray());
       return $results;
    }


    public function commission_invoice_generator($invoice_data=false,$is_vendor=false,$order_id = false)
    {       

        if($invoice_data['vendor_id']=='0')
        {
            return false;
        }
        $response = [];
        $order_date = ''; 

        $view_href = '';                    

        $vendor_id = $invoice_data['vendor_id'];
        
        $vendor_data = $this->UserModel->with(['address_details'])->where('id',$vendor_id)->first();
     
        if(isset($vendor_data))
        {
            $vendor_data = $vendor_data->toArray();
        }                              
        
        $company_name = isset($invoice_data['vendor_name'])?$invoice_data['vendor_name']:'N/A';
        $store_name = isset($invoice_data['retail_store_name'])?$invoice_data['retail_store_name']:'N/A';        
        $vendor_data['company_name'] = isset($company_name)?$company_name:'';
        $vendor_data['store_name'] = isset($store_name)?$store_name:'';
        $order_no = isset($invoice_data['order_no'])?$invoice_data['order_no']:'N/A';
        $order_date = us_date_format($invoice_data['order_date']);
        $company_name = isset($vendor_data['company_name'])?$vendor_data['company_name']:'N/A';
        $order_amount = isset($invoice_data['order_amount'])?$invoice_data['order_amount']:0.00;
        $commission_amount = isset($invoice_data['commission_amount'])?$invoice_data['commission_amount']:0.00;
        $admin_commission_percent = $this->CommissionService->get_admin_commission(isset($vendor_id)?$vendor_id:false);
        $admin_commission_amt = 0.00;

        if($order_amount !=0.00 || $commission_amount!= 0.00)
        {
            $admin_commission_amount = $order_amount - $commission_amount;
        }
           
          $user_type = $user_type_show = ""; 

        if(isset($invoice_data['order_no']) && $invoice_data['order_no']!="")
        {
            $retailer_details = $this->RetailerQuotesModel->where('order_no',$invoice_data['order_no'])
                                                          ->first();

            if(isset($retailer_details))
            {
                $user_type = 'Retailer-Order';
                $user_type_show = 'Retailer';
                $view_href =  url('/').'/vendor/retailer_orders/view/'.base64_encode($order_id);
            }

            $rep_sales_orders = $this->RepresentativeLeadsModel->where('order_no',$invoice_data['order_no'])->first();

            if(isset($rep_sales_orders))
            {
               $user_type = "Rep-Sales Order";
               $user_type_show = 'Rep-Sales';
               $view_href =  url('/').'/vendor/leads/view/'.base64_encode($order_id);
            }

            $customer_orders = $this->CustomerQuotesModel->where('order_no',$invoice_data['order_no'])->first();

            if(isset($customer_orders))
            {
               $user_type = "Customer Order";
               $user_type_show = 'Customer';
               $view_href =  url('/').'/vendor/customer_orders/view/'.base64_encode($order_id);
            }
        }

        $pdf = PDF::loadView('front/admin_commission',compact('order_no','order_date','company_name','order_amount','commission_amount','admin_commission_amount','admin_commission_percent','vendor_data','user_type','user_type_show'));

        //dd($pdf->html);
        
        $currentDateTime = $order_no.date('H:i:s').'.pdf';


        //send mail
        $pdf_arr =  [
                       'PDF'           => $pdf,
                       'PDF_FILE_NAME' => $currentDateTime
                    ];


        /*Get site setting data from helper*/

        $arr_site_setting = get_site_settings(['site_name','website_url']);

        $admin_role = Sentinel::findRoleBySlug('admin');        
        $admin_obj  = \DB::table('role_users')->where('role_id',$admin_role->id)->first();                      
        $admin_id   = $admin_obj->user_id;      
       
        // Send Mail To maker 
        if($is_vendor==false)
        {
           $from_user_id = Sentinel::findById($admin_id)->email;
           $to_mail_id = isset($vendor_data['email'])?$vendor_data['email']:"";
        }
        else
        {
            $from_user_id = isset($vendor_data['email'])?$vendor_data['email']:"";
            $to_mail_id = Sentinel::findById($admin_id)->email;
        }


        $credentials = ['email' => $to_mail_id];
      
        $arr_user = get_user_by_credentials($credentials);


       try
       {
        $arr_built_content = [
                                'order_no'   => $order_no,
                                'order_date' => $order_date,
                                'project_name' => $arr_site_setting['site_name']
                             ];                 



        $arr_mail_data = $this->EmailService->build_pdf_email_arr($arr_built_content,$pdf_arr,'65',$arr_user);
        $email_status  = $this->EmailService->send_mail($arr_mail_data,true,$pdf_arr); 


        /* send admin notification*/
        $admin_id  = get_admin_id();       
        $arr_notify_data                 = [];
        $arr_notify_data['from_user_id'] = $admin_id or '';
        $arr_notify_data['to_user_id']   = $vendor_id or '';
        $arr_notify_data['description']  = 'Commission Payment reminder of order no '.$order_no.' dated on '.$order_date;
        $arr_notify_data['title']        = 'Payment Reminder';
        $arr_notify_data['type']         = 'maker'; 
        $arr_notify_data['link']         = $view_href; 

        
        $this->save_notification($arr_notify_data);
        
        return true;
    }
    catch(\ Excetion $e)
    {   
        return false;
        
    }
}



    public function commission_bulk_invoice_generator($invoice_dataArr=false,$is_vendor=false)
    {

       
        $email_htmlTable_body = ""; $cnt=1;
        
        foreach ($invoice_dataArr as $key => $invoice_data) 
        {
            $pdf_data = array();
            
            if($key == 0){

                if($invoice_data['vendor_id']=='0')
                {
                    return false;
                }

                $order_date = '';                     

                $vendor_id = $invoice_data['vendor_id'];
                
                $vendor_data = $this->UserModel->with(['address_details'])->where('id',$vendor_id)->first();

             
                if(isset($vendor_data))
                {
                    $vendor_data = $vendor_data->toArray();
                }

                $invoice_id = "00".time();
                $vendor_data['invoice_id'] = $invoice_id;
            }                   
            

            $pdf_data['company_name'] = isset($invoice_data['vendor_name'])?$invoice_data['vendor_name']:'N/A';
            
            $vendor_data['company_name'] = isset($pdf_data['company_name']) ? $pdf_data['company_name']:'';

            $pdf_data['order_no'] = isset($invoice_data['order_no'])?$invoice_data['order_no']:'N/A';
            $pdf_data['order_date'] = us_date_format($invoice_data['order_date']);

            $pdf_data['company_name'] = isset($vendor_data['company_name'])?$vendor_data['company_name']:'N/A';

            $pdf_data['order_amount'] = isset($invoice_data['order_amount'])?$invoice_data['order_amount']:0.00;

            $pdf_data['commission_amount'] = isset($invoice_data['commission_amount'])?$invoice_data['commission_amount']:0.00;


           $pdf_data['admin_commission_percent'] = $this->CommissionService->get_admin_commission(isset($vendor_id)?$vendor_id:false);
           $pdf_data['admin_commission_amt'] = 0.00;

            if($pdf_data['order_amount'] !=0.00 || $pdf_data['commission_amount']!= 0.00)
            {
                $pdf_data['admin_commission_amount'] = $pdf_data['order_amount'] - $pdf_data['commission_amount'];
            }

            $pdf_dataArr[] = $pdf_data;

            $bg_color = "#fff";
            if($cnt % 2 == 0)
                $bg_color = "#eeeeee";

            $email_htmlTable_body .= "<tr style='background-color:".$bg_color."; font-wejght:bold; height: 22px;'> 
                                        <td align='center'>".$cnt++."</td>
                                        <td align='center'>".$pdf_data['order_no']."</td>
                                        <td align='center'>".$pdf_data['order_date']."</td>
                                    </tr>";

        }// foreach



        /* send admin notification*/
        $admin_id  = get_admin_id();       
        $arr_notify_data                 = [];
        $arr_notify_data['from_user_id'] = $admin_id or '';
        $arr_notify_data['to_user_id']   = $vendor_id or '';
        $arr_notify_data['description']  = 'Commission Payment reminder for Orders';
        $arr_notify_data['title']        = 'Payment Reminder';
        $arr_notify_data['type']         = 'maker'; 
        
        $this->save_notification($arr_notify_data);   


        $pdf = PDF::loadView('front/admin_bulkCommission',compact('pdf_dataArr','vendor_data'));
    
        $inv_no          = $invoice_id;
        $currentDateTime = $invoice_id."_".date('m-d-Y').'.pdf';


        $pdf_arr =  [
                      'PDF'           => $pdf,
                      'PDF_FILE_NAME' => $currentDateTime
                    ];


        /*Get site setting data from helper*/

        $arr_site_setting = get_site_settings(['site_name','website_url']);    


        $admin_role = Sentinel::findRoleBySlug('admin');        
        $admin_obj  = \DB::table('role_users')->where('role_id',$admin_role->id)->first();                      
        $admin_id   = $admin_obj->user_id;      
       
        // Send Mail To maker 
        if($is_vendor==false)
        {
            $from_user_id = Sentinel::findById($admin_id)->email;
            $to_mail_id   = isset($vendor_data['email'])?$vendor_data['email']:"";
        }
        else
        {
            $from_user_id = isset($vendor_data['email'])?$vendor_data['email']:"";
            $to_mail_id   = Sentinel::findById($admin_id)->email;
        }


        $credentials = ['email' => $to_mail_id];
      
        $arr_user = get_user_by_credentials($credentials);
   

        $email_htmlTable = '<table width="60%"> 
                                <tr style="color:#fff; background-color:#717171; font-wejght:bold; height: 20px;"> 
                                    <th width="10%" align="center"> # </th>
                                    <th width="40%" align="center">Order No</th>
                                    <th width="40%" align="center">Order Date</th>
                                </tr>';

        $email_htmlTable .= $email_htmlTable_body; 

        $email_htmlTable .= '
                                <tr style="color:#fff; background-color:#717171; font-wejght:bold; height: 5px;"> 
                                    <td colspan="100%"> </td>
                                </tr>
                                </table>';      
       

        $arr_built_content = [
                               'project_name' => $arr_site_setting['site_name'],
                               'htmltable'    => $email_htmlTable,

                            ];     



        $arr_mail_data = $this->EmailService->build_pdf_email_arr($arr_built_content,$pdf_arr,'69',$arr_user);

        $email_status  = $this->EmailService->send_mail($arr_mail_data,true,$pdf_arr);

  
    }

   

  public function get_retailer_order_shipping_charges($orderId)
    {
    $shippingCharges = 0;

        $shipCharge = $this->RetailerQuotesProductModel->where('retailer_quotes_id',$orderId)->sum('shipping_charge');

        $shipChargeDisount = $this->RetailerQuotesProductModel->where('retailer_quotes_id',$orderId)->sum('shipping_discount');
        
        return $shippingCharges = $shipCharge-$shipChargeDisount;
    }

     public function get_representative_order_shipping_charges($orderId)
    {
        $shippingCharges = 0;

        $shipCharge = $this->RepresentativeProductLeadsModel->where('representative_leads_id',$orderId)->sum('product_shipping_charge');

        $shipChargeDisount = $this->RepresentativeProductLeadsModel->where('representative_leads_id',$orderId)->sum('shipping_charges_discount');
        
        return $shippingCharges = $shipCharge-$shipChargeDisount;
    }

     public function get_customer_order_shipping_charges($orderId)
    {
        $shippingCharges = 0;

        $shipCharge = $this->CustomerQuotesProductModel->where('customer_quotes_id',$orderId)->sum('shipping_charge');

        $shipChargeDisount = $this->CustomerQuotesProductModel->where('customer_quotes_id',$orderId)->sum('shipping_discount');
        
        return $shippingCharges = $shipCharge-$shipChargeDisount;
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
            //dd($ARR_EVENT_DATA);
            event(new NotificationEvent($ARR_EVENT_DATA));

            return true;
        }
        return false;
    }

    /************************Notification Event END  **************************/
}
?>