<?php

namespace App\Http\Controllers;


use App\User;
use Carbon\Carbon;
use App\Models\Country;
use App\Models\ShopProduct;
use Illuminate\Support\Arr;
use App\Jobs\UpdateProductsJob;
use App\Jobs\SyncProductStatusJob;
use App\Jobs\UploadAllProductsJob;
use Illuminate\Support\Facades\DB;
use App\Http\Traits\GoogleApiTrait;
use App\Jobs\DeleteSingleProductJob;
use App\Jobs\UploadSingleProductJob;
use App\Jobs\UpdateProductDetailsJob;
use App\Jobs\SyncSingleStoreStatusJob;
use App\Jobs\ChangeSettingFeedDeleteJob;
use Osiset\ShopifyApp\Storage\Models\Plan;
use Symfony\Component\HttpFoundation\Request;

class WelcomeController extends Controller
{
    use GoogleApiTrait;
    
    public function index()
    {
        $status['all'] = $this->shopifyApiRequest('getProductsCount',null,['published_status' => 'published'],['body','count']);
        $status['pending'] =  ShopProduct::select(DB::raw('count(*) as count'))->whereJsonContains('status', config('shopifyApi.strings.googleStatusPending'))->where('user_id',auth()->user()->id)->first()->count;
        $status['approved'] =  ShopProduct::select(DB::raw('count(*) as count'))->whereJsonContains('status', config('shopifyApi.strings.googleStatusApproved'))->where('user_id',auth()->user()->id)->first()->count;
        $status['disapproved'] =  ShopProduct::select(DB::raw('count(*) as count'))->whereJsonContains('status', config('shopifyApi.strings.googleStatusDisapproved'))->where('user_id',auth()->user()->id)->first()->count;
        $countryName = Country::where('code',auth()->user()->settings->country)->first()->name;
    	return view("welcome",compact('countryName','status'));
    }

    public function productSearch($query,$cursor = '',$type = 'after',Request $request)
    {
        $foundProductIds = [];
        $query = trim($query);
        $tag = $request->query('tag');
        $arr = [];
        $links = null;
        $limit = 50;
        if($tag):
            $q = ShopProduct::where('user_id' , auth()->user()->id);
            $q->whereJsonContains('status', $tag);
            if($query == "All"):
                $products = $q->simplePaginate($limit);
            else:
                $products = $q->where('title', 'like', "%{$query}%")->simplePaginate($limit);
            endif;
        else:
            if($query == "All"):
                $arr[] = "";
            else:
                $arr[] = $query;
            endif;
            if($cursor):
                $arr [] = $type == "after" ? "first" : "last";
                $arr[] = $limit;
                $arr[] = ','.$type.':';
                $arr [] = '"'.$cursor.'"';
            else:
                $arr [] = "first";
                $arr[] = $limit;
            endif;
            $response = $this->shopifyApiGraphQuery('getProductsBySearch',$arr);
            $products = collect($response['body']['data']['products']['edges']);
            $foundProductIds = ShopProduct::select('productId')->where(['user_id' => auth()->user()->id])->whereIn('productId',($products->pluck('node'))->pluck('id')->map(function($id){ return str_replace(config('shopifyApi.strings.graphQlProductIdentifier'),'',$id); }))->pluck('productId');
            if(isset($response['body']['data']['products']['pageInfo'])):
                $links = $response['body']['data']['products']['pageInfo'];
            endif;
        endif;
        if(!$products):
            return response()->json(['error' => "error"]);
        endif;
        return view('includes.products',compact('products','links','query','tag','foundProductIds'));
    }

    public function getProductDetails($id)
    {
        $title = '';
        $description = '';
        $productCategoryName = '';
        $productCategoryValue = '';
        $ageGroup = '';
        $gender = '';
        $condition = '';
        $labels = [];
        $product = ShopProduct::with(['labels'])->where(['user_id' =>auth()->user()->id,'productId' => $id])->first();
        if($product):
            $title = $product->seoTitle;
            $description = $product->seoDescription;
            $ageGroup = $product->ageGroup;
            $gender = $product->gender;
            $condition = $product->productCondition;
            if($product->product_category_id):
                $product->load('category');
                $productCategoryName = $product->category->name;
                $productCategoryValue = $product->category->value;
            endif;
            $labels = $product->labels;
        endif;
        return response()->json([
            'title'                 => $title,
            'description'           => $description,
            'productCategoryName'   => $productCategoryName,
            'productCategoryValue'  => $productCategoryValue,
            'ageGroup'              => $ageGroup,
            'gender'                => $gender,
            'condition'             => $condition,
            'labels'                => $labels
        ]);
    }
    
    public function getProductVariants($id,$tag = null)
    {
        $product = $this->getProductById($id);
        $dbproduct = ShopProduct::where(['user_id' => auth()->user()->id,'productId' => $id])->with('variants')->first();
        $variants = $product['variants'];
        if($dbproduct):
            $dbVariantsIds = $dbproduct->variants->pluck('variantId');
            foreach($variants as $variant):
                $index = array_search($variant['id'],$dbVariantsIds->toArray());
                if($index !== false):
                    $variant['status'] = $dbproduct->variants[$index]->status;
                    $variants[$index] = $variant;
                endif;
            endforeach;
        endif;
        $variants = $this->getVariantStatuses($product,collect($variants));
        return view('includes.variants',compact('variants','id','product','tag'));
    }

    public function plans()
    {
        $basicplan = Plan::where('name',config('shopifyApi.plans')[0])->first();
        $smallplan = Plan::where('name',config('shopifyApi.plans')[1])->first();
        $mediumplan = Plan::where('name',config('shopifyApi.plans')[2])->first();
        $all_inplan = Plan::where('name',config('shopifyApi.plans')[3])->first();
        return view('plans',compact('basicplan','smallplan','mediumplan','all_inplan'));
    }

    public function setup()
    {
        $countries = Country::where('currency_code',auth()->user()->settings->currency)->get();
        auth()->user()->settings->load('productCategory');
        $customCollectionIds = $this->getCustomCollectionIds();
        $automaticCollectionIds = $this->getAutomaticCollectionIds();
        $popup  = false;
        if(!auth()->user()->settings->enable):
            $popup = true;
            auth()->user()->settings->update(['enable' => true]);
            auth()->user()->load('settings');
        endif;
        if(auth()->user()->settings->googleRefreshToken):
            $accounts = $this->getMerchantAccounts();
            return view('setup',compact('accounts','customCollectionIds','automaticCollectionIds','countries','popup'));
        endif;
        return view('setup',compact('customCollectionIds','automaticCollectionIds','countries','popup'));
    }

    public function getAccounts()
    {
        $accounts = $this->getMerchantAccounts();
        if(count($accounts)):
            return response()->json([ 'success' => "Accounts Retrieved Successfully." ,"accounts" => view('includes.accounts',compact('accounts'))->render()]);
        else:
            return response()->json([ 'error' => "Please Create Your Google Merchant Center Account." ]);
        endif;
    }
    public function getConnectionStatus()
    {
        if(auth()->user()->settings->googleRefreshToken):
            return response()->json(["status" => true,"email" => auth()->user()->settings->googleAccountEmail,'success' => 'Please wait! We are getting account details.']);
        endif;
        return response()->json(["status" => false]);
    }

    public function productCategorySearch(Request $request)
    {
        $categories = DB::table('product_categories')->where('name', 'LIKE', '%'.$request->input('term', '').'%')
        ->get(['id', 'name as text']);
        return ['results' => $categories];
    }

    public function sync(Request $request)
    {
        if(!auth()->user()->settings->merchantAccountId):
            return response()->json([ 'error' => "You Must Connect A Merchant Account To Sync." ]);
        endif;
        $validator = validator($request->all(),config('formValidation.syncForm'));
        if($validator->fails()):
            return response()->json([ 'errors' => $validator->errors() ]);
        endif;
        $validated = $validator->validated();
        $countries = Country::where('currency_code',auth()->user()->settings->currency)->get();
        if($countries): 
            if(!in_array($validated['country'],$countries->pluck('code')->toArray())):
                return response()->json([ 'error' => "Invalid Country Code Against Store's Currency." ]);
            endif;
        else:
            return response()->json([ 'error' => "Invalid Country Code Agains Store's Currency." ]);
        endif;
        if($validated['whichProducts'] == "collection"):
            if($validated['collectionType'] == 'auto'):
                if(!$this->checkSingleAutoCollection($validated['collectionsId'])):
                    return response()->json([ 'error' => "Invalid Collection Id." ]);
                endif;
            else:
                if(!$this->checkSingleCustomCollection($validated['collectionsId'])):
                    return response()->json([ 'error' => "Invalid Collection Id." ]);
                endif;
            endif;
        endif;
        $validated['setup'] = true;
        $validated['notification_date'] = Carbon::now();
        if(auth()->user()->settings->update($validated)):
            auth()->user()->load('settings');
            if(auth()->user()->settings->shipping == 'auto'):
                if($this->updateShippingtoMerchantAccount()):
                    UploadAllProductsJob::dispatch(auth()->user());
                    return response()->json([ 'success' => "Products Being Uploaded." , "url" => route('home')]);
                else:
                    return response()->json([ 'error' => "Something Went Wrong." ]);
                endif;
            else:
                UploadAllProductsJob::dispatch(auth()->user());
                return response()->json([ 'success' => "Products Being Uploaded." , "url" => route('home')]);
            endif;            
        else:
            return response()->json([ 'error' => "Something Went Wrong." ]);
        endif;
    }

    public function syncNow(Request $request)
    {
        $validator = validator($request->all(),config('formValidation.SyncNowForm'));
        if($validator->fails()):
            return response()->json([ 'errors' => $validator->errors() ]);
        endif;
        $validated = $validator->validated();
        $values = explode(':',$validated['variantId']);
        $product_id = $values[0];
        $variant_id = $values[1];
        $products = $this->shopifyApiRequest("getProducts",null,['ids' => $product_id],['body','products']);
        if(count($products) > 0):
            UploadSingleProductJob::dispatch(auth()->user(),$products[0],$variant_id);
            return response()->json([ 'success' => "Product Being Uploaded."]);
        else:
            return response()->json([
                'error' => "Invalid Variants."
            ]);
        endif;
    }

    public function Pupdate(Request $request)
    {
        $validator = validator($request->all(),config('formValidation.PupdateForm'));
        if($validator->fails()):
            return response()->json([ 'errors' => $validator->errors() ]);
        endif;
        $validated = $validator->validated();
        $products = $this->shopifyApiRequest("getProducts",null,['ids' => implode(',',$validated['products'])],['body','products']);
        UpdateProductDetailsJob::dispatch(auth()->user(),collect($products),$validated);
        return response()->json([ 'success' => "Product Being Updated."]);
    }

    public function Pdelete($id)
    {
        $validator = validator(['id' => $id],config('formValidation.PdeleteForm'));
        if($validator->fails()):
            return response()->json([ 'errors' => $validator->errors() ]);
        endif;
        $validated = $validator->validated();
        $product = ShopProduct::where([
            'user_id' => auth()->user()->id,
            'productId' => $validated['id']
        ])->first();
        if($product):
            DeleteSingleProductJob::dispatch(auth()->user(),$product);
            return response()->json([ 'success' => "Product Is Being Deleted."]);
        else:
            return response()->json(['error' => 'Invalid Product']);
        endif;
            
    }

    public function updateSettings()
    {
        
        ChangeSettingFeedDeleteJob::dispatch(auth()->user());
        $shop = $this->shopApi(['body','shop']);
        auth()->user()->settings->update(['setup' => 0, 'domain' => $shop['domain'],'country' => $shop['country_code'],'country_name' => $shop['country_name'],'currency' => $shop['currency']]);
        auth()->user()->update(['updated_at' => Carbon::now()->subDay()]);
        return response()->json([ 'success' => "Products Being Deleted From Merchant Account.",'reload' => true]);
    }

    public function SyncStatusNow()
    {
        $settings = auth()->user()->settings;
        if($settings->last_updated == null || $settings->last_updated <= \Carbon\Carbon::now()->subDay()):
            SyncSingleStoreStatusJob::dispatch(auth()->user());
            auth()->user()->settings->update(['last_updated' => now()]);
            return response()->json([ 'success' => "Products Status Being Updated."]);
        else:
            return response()->json([ 'customError' => "Products Status Being Updated."]);
        endif;
    }

    public function dismissLimitAlert()
    {
        if(auth()->user()->settings->update([
            'limit_notification' => 0
        ])):
            return response()->json(['status' => 1]);
        else:
            return response()->json(['status' => 0]);
        endif;
    }
    
    public function test(Request $request)
    {
        $user = User::find(2);
        return $user->api()->rest('GET','/admin/api/2021-01/webhooks.json');
        // $user = User::find(2);
        // return $user->api()->rest('POST','/admin/api/2021-01/webhooks.json',["webhook" => [
        //     "topic" => "themes/update",
        //     "address" => "https://alpha-google-shopping-feed.com/webhook/themes-update",
        //     "format" => "json"
        //     ]]);
        // SyncProductStatusJob::dispatch();
        // $user = User::find(66);
        // return $user->api()->rest('GET','/admin/api/2021-01/themes/117826584727/assets.json',['asset[key]' => 'layout/theme.liquid']);
        // return auth()->user()->plan->name;
        // return ShopProduct::selectRaw('count(*) as count')->where('user_id', 100000)->first()->count;
        // return ShopProduct::selectRaw('count(*) as count')->where('user_id', auth()->user()->id)->first()->count;
        // $q =  ShopProduct::whereJsonContains('status', 'Disapproved')->where('user_id',auth()->user()->id);
        // $q2 = $q->whereJsonContains('status','Pending');
        // $q3 = $q->whereJsonContains('status','Disapproved');
        // return $q->get();
        // return ShopProduct::whereJsonContains('status', 'Pending')->where('user_id',auth()->user()->id)->count();
        // return ShopProduct::select(DB::raw('count(*) as count'))->whereJsonContains('status', 'Pending')->where('user_id',auth()->user()->id)->toSql();
        // $statusCount = DB::table('shop_products')->select([
        //     'pendingCount' => ShopProduct::select(DB::raw('count(*) as total'))->whereJsonContains('status', 'Pending')->groupBy('status'),
        //     'approvedCount' => ShopProduct::select(DB::raw('count(*) as total'))->whereJsonContains('status', 'Approved')->groupBy('status'),
        //     'disapprovedCount' => ShopProduct::select(DB::raw('count(*) as total'))->whereJsonContains('status', 'disapproved')->groupBy('status')
        //     // ])->where('user_id',auth()->user()->id)->toSql();  
        //     // ])->where('user_id',auth()->user()->id)->get();  
        //     ])->get();  
        // dd($statusCount);
        // $products = ShopProduct::chunk(1000,function($products){
        //     foreach($products as $product){
        //         echo json_encode($product);
        //     }
        // });
        //         select 
        //       count((select count(*) from `shop_products` where json_contains(`status`,"Pending"))) as `pendingCount`,
        //       count((select count(*)  from `shop_products` where json_contains(`status`,"Approved"))) as `approvedCount`,
        //       count((select count(*)  from `shop_products` where json_contains(`status`,"Disapproved"))) as `disapprovedCount`
        // from `shop_products` where `user_id` = 2
        // return;
        // $user =  User::where('name', 'client421.myshopify.com')
        // ->whereNotNull('password')
        // ->whereHas('settings', function ($query) {
        //     $query->whereNotNull('merchantAccountId');
        //     $query->where('setup',1);
        // })
        // ->whereHas('products', function ($query) {
        //     $query->where('productId','4635950055479');
        // })
        // ->has('products.variants')
        // ->with(['products' => function($q){$q->where('productId','4635950055479');},'products.variants'])
        // ->toSql();
        // return $user;
        // UploadAllProductsJob::dispatch(auth()->user());
        // Redis::command('flushdb');
        // $col = $this->shopifyApiRequest("getProductMetaFields", 1655821729847 , ['limit' => 250,'fields' => 'key,value']);
        // return $col;
        // logger()->channel('request')->info("hy");
        // Log::channel('request')->info(json_encode($request->header()));
        // $this->shop();
        // return $this->shopifyApiGraphQuery("insertGoogleStatusTags",null,[
        //     "id" => "gid://shopify/Product/5057590329475",
        //     "tags" => [
        //     "googleStatusApproved"
        //     ]
        // ]);
        // $arr = [];
        // $limit = 50;
        // $query = "tag:googleStatusDisapproved";
        // $arr[] = $query;
        // $arr [] = "first";
        // $arr[] = $limit;
        // return $this->shopifyApiGraphQuery('getProductsBySearch',$arr);
        // foreach ($this->shopifyApiRequest("getCustomCollection", null , ['limit' => 250])['body']['custom_collections'] as  $value) {
        //     dd($value['id']);
        // }
        // $col = collect($this->shopifyApiRequest("getCustomCollection", null , ['limit' => 250])['body']['custom_collections']);
        // return $this->shopifyApiRequest("getProducts",null,['ids' => 5057590329475]);

        // return $this->shopifyApiGraphQuery("getPrivateMetaField",[5057590329475,'instructions','dfds']);
        // return $this->shopifyApiGraphQuery("insertPrivateMetaField",null,[
            //     "input" => [
        //         "id" => "gid://shopify/Product/5057590329475",
        //         "metafields" => [
        //             [
        //             "namespace" => "dfdsfdsfdsfdsfd",
        //             "key" => "dfdsfdsfdsfdsfd",
        //             "value" => "dfdsfdsfdsfdsfd",
        //             "valueType" => "STRING"
        //             ]
        //         ]
        //     ]
        // ]);
        // return ["hy" => auth()->user()];
        // return redirect()->to("https://accounts.google.com/o/oauth2/auth/oauthchooseaccount?client_id=1036722218453-40vugqu2jcjgq2nsq5n830bb3og93gs6.apps.googleusercontent.com&redirect_uri=https%3A%2F%2Fwww.alpha-google-shopping-feed.com%2Fsetup%2Fgoogle%2Fcallback&scope=openid%20profile%20email%20https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fcontent%20https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fsiteverification&response_type=code&access_type=offline&prompt=consent%20select_account&flowName=GeneralOAuthFlow");
        // return redirect()->away("https://facebook.com");
        // return $this->shopifyApiRequest("getWebhooks");
        // return ($this->shopifyApiRequest("getCollectionProducts")['body']['shipping_zones']);
        // dd(  $col->only(['id','title']));
    }
}
