<?php

namespace App\Models;

//use Illuminate\Database\Eloquent\Model;

use Cartalyst\Sentinel\Users\EloquentUser as CartalystUser;
use Cmgmyr\Messenger\Traits\Messagable;
use Watson\Rememberable\Rememberable;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserModel extends CartalystUser
{
    use Rememberable;
    use SoftDeletes;
   
    protected $table ='users';
    
	protected $fillable = [
                    		    'tax_id',
                            'email',
                            'password',
                            'first_name',
                            'last_name',
                            'wallet_address',
                            'country_code',
                            'contact_no',
                            'date_of_birth',
                            'country_id',
                            'state_id',
                            'city_id',
                            'street',
                            'address',
                            'post_code',
                            'nationality',
                            'profile_image',
                            'address_proof',
                            'id_proof_no',
                            'id_proof_copy',
                            'passport_no',
                            'passport_scan_copy',
                            'kyc_status',
                            'buying_status',
                            'store_name',
                            'store_website',
                            'status',
                            'is_approved',
                            'ref_no',
                            'last_login',
                            'commission',
                            'shipping_addr',
                            'shipping_addr_zip_code',
                            'billing_addr',
                            'billing_addr_zip_code',
                            'is_login',
                            'is_stripe_connected',
                            'influencer_reward_amount',
                            'influencer_remaining_order_amount',
                            'influencer_code'
                        ];

   
   public function country_details(){
        return $this->belongsTo('App\Models\CountryModel','country_id','id');
   }      

   public function state_details(){
        return $this->belongsTo('App\Models\StateModel','state_id','id');
   }

   public function city_details(){
        return $this->belongsTo('App\Models\CityModel','city_id','id');
   }      

   public function maker_details(){
     return $this->belongsTo('App\Models\MakerModel','id','user_id');
   }

   public function role_details(){
     return $this->belongsTo('App\Models\RoleUsersModel','id','user_id');
   }

   public function retailer_quotes(){
     return $this->belongsTo('App\Models\RetailerQuotesModel','id','retailer_id');
   }

   public function representative_leads()
   {
      return $this->belongsTo('App\Models\RepresentativeLeadsModel','id','retailer_id');
   }

   public function address_details()
   {
      return $this->belongsTo('App\Models\AddressModel','id','user_id');
   }

   public function store_details()
   {
      return $this->belongsTo('App\Models\ShopImagesModel','id','maker_id');
   }

    public function shop_settings()
   {
      return $this->belongsTo('App\Models\ShopSettings','id','maker_id');
   }

   public function representative_maker()
   {
      return $this->hasMany('App\Models\RepresentativeMakersModel','representative_id','id');  
   }

   public function maker_comission()
   {
      return $this->hasOne('App\Models\RepresentativeMakersModel','maker_id','id');  
   }

   public function representative_details()
   {
     return $this->belongsTo('App\Models\RepresentativeModel','id','user_id');
   }

   public function retailer_details()
   {
     return $this->belongsTo('App\Models\RetailerModel','id','user_id');
   }

   public function customer_details()
   {
     return $this->belongsTo('App\Models\CustomerModel','id','user_id');
   }

   public function retailer_store_details()
   {
     return $this->belongsTo('App\Models\RetailerStoreDetailsModel','id','retailer_id');
   }

   public function sales_manager_details()
   {
     return $this->belongsTo('App\Models\SalesManagerModel','id','user_id');
   }
   public function vendor_details()
    {
      return $this->hasMany('App\Models\VendorRepresentativeMappingModel', 'representative_id', 'id');
    }
}
