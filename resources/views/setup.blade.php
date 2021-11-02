@extends('vendor.shopify-app.layouts.default')
@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .domaindiv .right_btn a span{
            color:black !important;
        }
        .right_drop h3 span{
            color:white !important;
        }
        .select2-container{
            width:100% !important;
        }
        input[type=checkbox], input[type=radio]{
            margin-top: 0px; 
        }
        .l_1{
            margin-right: 5px;
        }
        .step-button[aria-expanded="true"]{
            background-color: #251c6b;
        }
        .step-button{
            background-color: #9f9f9f;
        }
    </style>
@endsection
@section('content')
<section>
    <div class="container">
        {{-- <a href="{{ route('test') }}">plans</a> --}}
        <form class="ajaxForm">
            <div class="accordion" id="accordionExample">
                <div class="steps">
                    <progress id="progress" value=0 max=100></progress>
                    <div class="step-item">
                        <button class="step-button text-center" type="button" data-toggle="collapse"
                            data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                          1
                        </button>
                        <div class="step-title">
                          Connect Google Account
                        </div>
                    </div>
                    <div class="step-item">
                        <button class="step-button text-center collapsed" type="button" data-toggle="collapse"
                            data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            2
                        </button>
                        <div class="step-title">
                            Store Settings
                        </div>
                    </div>
                    <div class="step-item">
                        <button class="step-button text-center collapsed" type="button" data-toggle="collapse"
                            data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                            3
                        </button>
                        <div class="step-title">
                            Merchant Feed Setting
                        </div>
                    </div>
                </div>

                <!--------------Step-1--------------------->

                <div class="card">
                    <div id="headingOne"></div>

                    <div id="collapseOne" class="collapse show" aria-labelledby="headingOne"
                        data-parent="#accordionExample">
                        <div class="card-body">
                            <div class="row step_1">
                            <div class="col-md-4 left_text">
                                    <h3>Google Account</h3>
                                    <p>Connect your account. <br><a target="_blank" href="https://youtu.be/dFf9a4D2gFw"><strong>Help Video</strong> How To Setup</a></p>
                            </div>
                            <div class="col-md-8 right_drop p-0">
                                    <div class="col-md-12 right_main pb-0">
                                        <h3 class="img_w">
                                            <img src="assets/img/logo-google.png" style="width:30px;">
                                            <span class="emaildiv" style="color: #595959 !important;">
                                            @if(auth()->user()->settings->googleRefreshToken)
                                                {{ auth()->user()->settings->googleAccountEmail }}
                                            @endif
                                            </span>
                                            <div class="right_btn float-right w_b">
                                                @if(auth()->user()->settings->googleRefreshToken)
                                                    <a class="disconnect" href="javascript:void(0);" >Disconnect</a>    
                                                @else
                                                    <a class="connect" target="_blank" href="{{ route('redirect') }}">Connect</a>
                                                @endif
                                            </div>
                                        </h3>  
                                    </div>      
                                </div>
                                <div class="row step_1">
                                    <div class="col-md-4 left_text">
                                        <h3>Google Merchant Center Account</h3>
                                        <p>Shopify product that are synced to your Google Merchant Center product feed will be used by Google to create ads.</p>
                                    </div>
                                    <div class="col-md-8 right_drop p-0">
                                        <div class="col-md-12 right_main">
                                            <h3>
                                                Connect a Google Merchant Center Account
                                                <span class="float-right"> 
                                                    <a href="https://support.google.com/merchants/answer/188924?hl=en" target="blank"> Create New</a>
                                                </span>
                                            </h3>
                                            <div class="right_img">
                                                <img src="assets/img/icon.svg">
                                            </div>
                                            <div class="right_dropdown">
                                                <select name="selected_account_id" class="selectaccount" @if(auth()->user()->settings->merchantAccountId) disabled @endif>
                                                    @if(auth()->user()->settings->googleRefreshToken)
                                                        @foreach ($accounts as $account)
                                                            @isset($account['merchantId'])
                                                                <option value="{{ $account['merchantId'] }}">{{ $account['merchantName'].' ('.$account['merchantId'].')' }}</option>
                                                            @endisset
                                                            @foreach ($account['subAccounts'] as $subAccount)
                                                                <option value="{{ $subAccount['id'] }}">{{ $subAccount['name'].' ('.$subAccount['id'].')' }}</option>
                                                            @endforeach
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="right_btn">
                                                @if(auth()->user()->settings->merchantAccountId)
                                                    <a class="domaindisconnect" href="javascript:void(0);" >Disconnect</a>    
                                                @else
                                                    <a class="domainconnect" href="javascript:void(0);">Connect</a>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="border_div">
                                            <p class="domaindiv"></p>
                                        </div>
                                    </div>          
                                </div>             
                            </div>
                            <button type="button" class="float-right btn_next collapsed" data-toggle="collapse"
                                    data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    Next
                            </button> 
                        </div>
                    </div>
                </div>
                <!--------------Step-1-Close--------------------->

                <!--------------Step-2--------------------->

                <div class="card">
                    <div id="headingTwo"></div>
                    <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionExample">
                        <div class="card-body">

                            <div class="col-md-12 primary_div">
                                <div class="row">

                                    <div class="col-md-5 left_text">
                                        <h3>Primary Feed Settings</h3>
                                        <p>
                                            Select the Target Market  where your shopping ads will be appear.If you're selling to multiple countries, please choose only the primary country in this step.You will be able to select other countries at a later stage from your Google Merchant account
                                        </p>
                                    </div>
                                    <div class="col-md-7 primary-right">
                                        <label>
                                            Target Market options detemine are based on your store currency & language ( <strong>{{ auth()->user()->settings->currency }}, {{ auth()->user()->settings->language }} </strong> )
                                           </label>
                                        <select class="form-control" name="country">
                                            @foreach ($countries as $country)
                                                <option {{ $country->code == auth()->user()->settings->country ? 'selected' : '' }} value="{{ $country->code }}">
                                                    {{ $country->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 primary_div mt-3">
                                <div class="row">
                                    <div class="col-md-5 left_text">
                                        <h3>Shipping Settings</h3>
                                        <p>Set up your shipping costs based on how you charge shipping.The costs that you
                                            submit to Merchant Center must match the costs
                                            that you charge on your website</p>
                                    </div>
                                    <div class="col-md-7 primary_setting">
                                        <input value="manual" type="radio" name="shipping" {{ auth()->user()->settings->shipping == "manual" ? "checked" : ""}}>
                                            <label class="form_label ml-1">Manually set up shipping in Google Merchant
                                                Center</label>
                                            <p>You will go to Google Merchant Center and enter your product shipping
                                                information yourself.</p>
                                        <div class="mt-4">
                                            <input value="auto" type="radio" name="shipping" {{ auth()->user()->settings->shipping == "auto" ? "checked" : ""}}>
                                            <label class="form_label ml-1">Setup Free Shipping
                                                <span>(RECOMMENDED)</span></label>
                                            <p>Add the Free Shipping on the product data feed level </p>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <button type="button" class="float-left btn_next collapsed" data-toggle="collapse"
                            data-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">Prev</button>
                            <button type="button" class="float-right btn_next collapsed" data-toggle="collapse"
                            data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">Next</button>
                            
                        </div>
                    </div>
                </div>

                <!--------------Step-2-Close--------------------->

                <!--------------Step-3--------------------->

                <div class="card">
                    <div id="headingThree"></div>
                    <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordionExample">
                        <div class="card-body">

                            <div class="col-md-12 primary_div mt-3">
                                <div class="row">
                                    <div class="col-md-3 left_text_2">
                                        <h3>Product ID Format:</h3>
                                    </div>

                                    <div class="col-md-9 primary_setting_2">
                                        <input value="global" type="radio" name="productIdFormat" {{ auth()->user()->settings->productIdFormat == "global" ? "checked" : ""}}>
                                        <label class="form_label ml-1">
                                            Global Format (Ex: Shopify_US_123456_987654)
                                            <span>(RECOMMENDED)</span>
                                        </label>
                                        <p class="mt_n">
                                            This is common ID format which is generally used for Shopify store.
                                        </p>
                                        <input value="sku" type="radio"  name="productIdFormat" {{ auth()->user()->settings->productIdFormat == "sku" ? "checked" : ""}}>
                                        <label class="form_label ml-1">
                                            SKU as Product ID (Ex:ABCD1234)
                                        </label>
                                        <p>All of your product must have an SKU, Otherwise they won't be imported</p>
                                        <input value="variant" type="radio" value="variant"  name="productIdFormat" {{ auth()->user()->settings->productIdFormat == "variant" ? "checked" : ""}}>
                                        <label class="form_label ml-1">
                                            Variant ID as Product ID (Ex: 123456789)
                                        </label>
                                        <p>
                                            Choose this option if you would like to submit variant ID as unique product ID in the feed.
                                        </p>
                                    </div>

                                </div>
                            </div>

                            <div class="col-md-12 primary_div mt-3 mb-3">

                                <div class="row">
                                    <div class="col-md-3 left_text_2">
                                        <h3>Which Products Need to be Submitted in Feed</h3>
                                    </div>
                                    <div class="col-md-9 primary_setting_2">
                                        <div class="mt-3">
                                            <div class="l_1">
                                                <input value="all" type="radio" name="whichProducts" {{ auth()->user()->settings->whichProducts == "all" ? "checked" : ""}}>
                                                <label class="form_label ml-1">All
                                                    Products <span>(RECOMMENDED) </span>
                                                </label>
                                            </div>
                                            <div class="l_2">
                                                <input value="collection" type="radio" name="whichProducts" {{ auth()->user()->settings->whichProducts == "collection" ? "checked" : ""}}>
                                                <label class="form_label ml-1">Products From
                                                    Collection</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="whichProductsDiv" 
                                    @if(auth()->user()->settings->whichProducts == "all") 
                                        style="display:none;"
                                    @endif
                                >
                                    <div class="row mt-4">
                                        <div class="col-md-3 left_text_2">
                                            <h3>Collection Type</h3>
                                        </div>
                                        <div class="col-md-9 primary_setting_2">
                                            <div class="mt-1">
                                                <div class="l_1">
                                                    <input value="auto" type="radio" name="collectionType" {{ auth()->user()->settings->collectionType == "auto" ? "checked" : ""}}>
                                                    <label class="form_label ml-1">
                                                        Automated Collection
                                                    </label>
                                                </div>
                                                <div class="l_2">
                                                    <input value="custom" type="radio" name="collectionType" {{ auth()->user()->settings->collectionType == "custom" ? "checked" : ""}}>
                                                    <label class="form_label ml-1">
                                                        Manual Collection
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-4">
                                        <div class="col-md-3 left_text_2">
                                            <h3>Select Collection</h3>
                                        </div>
                                        <div class="col-md-9 primary_setting_2">
                                            <select name="collectionsId">
                                                @if((auth()->user()->settings->collectionType == "custom"))
                                                    @foreach ($customCollectionIds as $collection)
                                                        <option {{ auth()->user()->settings->collectionsId == $collection['id'] ? "selected" : "" }} value="{{ $collection['id'] }}">{{ $collection['title'] }}</option>
                                                    @endforeach
                                                @endif
                                                @if(auth()->user()->settings->collectionType == "auto")
                                                    @foreach ($automaticCollectionIds as $collection)
                                                        <option {{ auth()->user()->settings->collectionsId == $collection['id'] ? "selected" : "" }} value="{{ $collection['id'] }}">{{ $collection['title'] }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-md-3 left_text_2">
                                        <h3>Product Title</h3>
                                    </div>
                                    <div class="col-md-9 primary_setting_2">
                                        <div class="mt-1">
                                            <div class="l_1">
                                                <input value="default" type="radio" name="productTitle" {{ auth()->user()->settings->productTitle == "default" ? "checked" : "" }}>
                                                <label class="form_label ml-1">
                                                    Default Product Title
                                                </label>
                                            </div>
                                            <div class="l_2">
                                                <input type="radio" name="productTitle" value="seo" {{ auth()->user()->settings->productTitle == "seo" ? "checked" : "" }}>
                                                <label class="form_label ml-1">
                                                    SEO Title
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-md-3 left_text_2">
                                        <h3>Product Description</h3>
                                    </div>
                                    <div class="col-md-9 primary_setting_2">
                                        <div class="mt-1">
                                            <div class="l_1">
                                                <input type="radio" name="productdescription" value="default" {{ auth()->user()->settings->productdescription == "default" ? "checked" : "" }}>
                                                <label class="form_label ml-1">Default
                                                    Product Description
                                                </label>
                                            </div>
                                            <div class="l_2">
                                                <input type="radio" name="productdescription" value="seo" {{ auth()->user()->settings->productdescription == "seo" ? "checked" : "" }}>
                                                <label class="form_label ml-1">
                                                    SEO Description
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-md-3 left_text_2">
                                        <h3>Variant Submission</h3>
                                    </div>
                                    <div class="col-md-9 primary_setting_2">
                                        <div class="mt-1">
                                            <div class="l_1">
                                                <input type="radio" name="variantSubmission" value="first" {{ auth()->user()->settings->variantSubmission == "first" ? "checked" : "" }}>
                                                <label class="form_label ml-1">
                                                    First Variant Only
                                                </label>
                                            </div>
                                            <div class="l_2">
                                                <input type="radio" name="variantSubmission" value="all" {{ auth()->user()->settings->variantSubmission == "all" ? "checked" : "" }}>
                                                <label class="form_label ml-1">
                                                    All Variants
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
								<div class="row mt-4">
                                    <div class="col-md-3 left_text_2">
                                        <h3>Barcode (ISBN, UPC, GTIN)</h3>
                                    </div>
                                    <div class="col-md-9 primary_setting_2">
                                        <div class="mt-1">
                                            <div class="l_1">
                                                <input type="checkbox" name="gtinSubmission" value="1" {{ auth()->user()->settings->gtinSubmission == "1" ? "checked" : "" }}>
                                                <label class="form_label ml-1">
                                                    Do Your Products Have GTINs ?
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-md-3 left_text_2">
                                        <h3>Sale Price with Compare Price</h3>
                                    </div>
                                    <div class="col-md-9 primary_setting_2">
                                        <div class="mt-1">
                                            <div class="l_1">
                                                <input type="checkbox" name="salePrice" value="1" {{ auth()->user()->settings->salePrice == "1" ? "checked" : "" }}>
                                                <label class="form_label ml-1">
                                                    Enable Sale Price
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-md-3 left_text_2">
                                        <h3>Product Image Option</h3>
                                    </div>
                                    <div class="col-md-9 primary_setting_2 sm_w">
                                        <div class="mt-1">
                                            <div class="l_1">
                                                <input type="checkbox" name="secondImage" value="1" {{ auth()->user()->settings->secondImage == "1" ? "checked" : "" }}>
                                                <label class="form_label ml-1">
                                                    Use second image for product having no variant
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-md-3 left_text_2">
                                        <h3>Submit Additional Images</h3>
                                    </div>
                                    <div class="col-md-9 primary_setting_2 sm_w">
                                        <div class="mt-1">
                                            <div class="l_1">
                                                <input type="checkbox" name="additionalImages" value="1" {{ auth()->user()->settings->additionalImages == "1" ? "checked" : "" }}>
                                                <label class="form_label ml-1">
                                                    Check this if you would like to submit Additional images
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <div class="row mt-4">
                                    <div class="col-md-3 left_text_2">
                                        <h3>Default Google Product Category</h3>
                                        <p>( Optional )</p>
                                    </div>
                                    <div class="col-md-9 primary_setting_2">
                                        <select class="productcategorysearch form-control" name="product_category_id" >
                                            @if(auth()->user()->settings->productCategory)
                                                <option value="{{ auth()->user()->settings->productCategory->id }}">{{ auth()->user()->settings->productCategory->name }}</option>
                                            @endif
                                        </select>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-md-3 left_text_2">
                                        <h3>Default Age Group</h3>
                                    </div>
                                    <div class="col-md-9 primary_setting_2">
                                        <select name="ageGroup">
                                            <option>Select An Option</option>
                                            <option value="adult" {{ auth()->user()->settings->ageGroup == "adult" ? "selected" : "" }}>Adult</option>
                                            <option value="kids" {{ auth()->user()->settings->ageGroup == "kids" ? "selected" : "" }}>Kids</option>
                                            <option value="infant" {{ auth()->user()->settings->ageGroup == "infant" ? "selected" : "" }}>Infant</option>
                                            <option value="newborn" {{ auth()->user()->settings->ageGroup == "newborn" ? "selected" : "" }}>NewBorn</option>
                                            <option value="toddler" {{ auth()->user()->settings->ageGroup == "toddler" ? "selected" : "" }}>Toddler</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-md-3 left_text_2">
                                        <h3>Default Gender</h3>
                                    </div>
                                    <div class="col-md-9 primary_setting_2">
                                        <select name="gender">
                                            <option>Select An Option</option>
                                            <option value="female" {{ auth()->user()->settings->gender == "female" ? "selected" : "" }}>Female</option>
                                            <option value="male" {{ auth()->user()->settings->gender == "male" ? "selected" : "" }}>Male</option>
                                            <option value="unisex" {{ auth()->user()->settings->gender == "unisex" ? "selected" : "" }}>Unisex</option>
                                        </select>
                                    </div>
                                </div>


                                <div class="row mt-4">
                                    <div class="col-md-3 left_text_2">
                                        <h3>Default Product Condition</h3>
                                    </div>
                                    <div class="col-md-9 primary_setting_2">
                                        <select name="productCondition">
                                            <option>Select An Option</option>
                                            <option value="new" {{ auth()->user()->settings->productCondition == "new" ? "selected" : "" }}>New</option>
                                            <option value="used" {{ auth()->user()->settings->productCondition == "used" ? "selected" : "" }}>Used</option>
                                            <option value="refurbished" {{ auth()->user()->settings->productCondition == "refurbished" ? "selected" : "" }}>Refurbished</option>
                                        </select>
                                    </div>
                                </div>

                            </div>
                            <button type="button" class="float-left btn_next collapsed" data-toggle="collapse"
                            data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">Prev</button>
                            <button type="button" class="float-right btn_next sync">Sync</button>
                        </div>
                    </div>
                </div>
                <!--------------Step-3-Close--------------------->
            </div>
        </form>
    </div>
</section>
@if($popup)
    <div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Video Guide</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <iframe width="700" height="315" src="https://www.youtube.com/embed/dFf9a4D2gFw" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection
@section('scripts')
    @parent
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
    <script>
        $(function(){
            @if($popup)
                $('.modal').modal('show');
            @endif
            $('.productcategorysearch').select2({
                minimumInputLength: 3,
                ajax: {
                    url: '{{ route("product.category.search") }}',
                    dataType: 'json'
                }
            });
            $(document).on('click',".connect",function(){
                addSpinner($(this));
                var inter = setInterval(function() {
                    ajaxRequest("{{ route('getConnectionStatus') }}",function(response) {
                        if(response.status){
                            $(".connect").empty().html("Disconnect");
                            $(".connect").addClass("disconnect");
                            $(".connect").removeAttr("target");
                            $(".connect").attr("href","javascript:void(0);");
                            $(".connect").removeClass("connect");
                            $(".emaildiv").html(response.email);
                            getAccounts();
                            clearInterval(inter);
                        }
                    });
                }, 3000);
            });
            function getAccounts(){
                ajaxRequest("{{ route('getAcounts') }}",function(response) {
                    if(response.hasOwnProperty('accounts')){
                        $(".domaindisconnect").html("connect");
                        $(".domaindiv").html("");              
                        $(".selectaccount").prop("disabled",false);              
                        $(".selectaccount").html(response.accounts);
                    }
                });
            }
            $(document).on('click',".disconnect",function(){
                addSpinner($(this));
                ajaxRequest("{{ route('disconnect') }}",function(response) {
                    if(response.status){
                        $(".disconnect").empty().html("Connect");
                        $(".disconnect").addClass("connect");
                        $(".disconnect").attr("target","_blank");
                        $(".disconnect").attr("href","{{ route('redirect') }}");
                        $(".disconnect").removeClass("disconnect");
                        $(".emaildiv").html("");
                        $(".selectaccount").html("");
                        $(".domaindiv").html("");
                        $(".domaindisconnect").empty().html("connect");
                        $(".domaindisconnect").addClass("domainconnect");
                        $(".domaindisconnect").removeClass("domaindisconnect");
                        $(".selectaccount").prop("disabled","disabled"); 
                    }
                });
            });
            $(document).on('click',".domainconnect",function(){
                addSpinner($(this));
                ajaxRequest("{{ route('accountConnect') }}",function(response){
                    if(response.success){
                        $(".domainconnect").empty().html("Disconnect");
                        $(".domainconnect").addClass("domaindisconnect");
                        $(".domainconnect").removeClass("domainconnect");
                        $(".selectaccount").prop("disabled","disabled");
                        domainVerify();
                    }else{
                        $(".domainconnect").empty().html("Connect");
                    }

                },"POST",{account_id : $(`select[name="selected_account_id"]`).val()});
            });
            $(document).on('click',".domaindisconnect",function(){
                addSpinner($(this));
                ajaxRequest("{{ route('accountDisconnect') }}",function(response){
                    if(response.status){
                        $(".domaindisconnect").empty().html("connect");
                        $(".domaindisconnect").addClass("domainconnect");
                        $(".domaindisconnect").removeClass("domaindisconnect");
                        $(".selectaccount").prop("disabled",false);
                        $(".domaindiv").html("");
                    }
                });
            });
            @if(auth()->user()->settings->merchantAccountId)
                domainVerify();
            @endif
            function domainVerify(){
                $(".domaindiv").empty().html("Verifying Webite Claim.");
                addSpinner($(".domaindiv"));
                ajaxRequest("{{ route('domainVerify') }}",function(response) {
                    let html = "";
                    if(response.status){
                        html+=`<i class="fa fa-check"></i> `;
                    }else{
                        html+=`&times; `;
                    }
                    html+=response.message;
                    $(".domaindiv").html(html);
                });
            }
            $(`input[name="whichProducts"]`).on('change',function(){
                if($(`input[name='whichProducts']:checked`).val() == "collection"){
                    $(".whichProductsDiv").show();
                }else{
                    $(".whichProductsDiv").hide();
                }
            });
            $(`input[name="collectionType"]`).on('change',function(){
                if($(`input[name='collectionType']:checked`).val() == "auto"){
                    let html = `@foreach($automaticCollectionIds as $collection)
                                    <option value="{{ $collection['id'] }}">{{ $collection['title'] }}</option>
                                @endforeach`;
                    $(`select[name="collectionsId"]`).html(html);
                }else{
                    let html = `@foreach($customCollectionIds as $collection)
                                    <option value="{{ $collection['id'] }}">{{ $collection['title'] }}</option>
                                @endforeach`;
                    $(`select[name="collectionsId"]`).html(html);
                }
            });
            $('.sync').on('click',function(){
                addSpinner($(this));
                ajaxRequest("{{ route('sync') }}",function(response){
                    $(".sync").empty().html("Sync");
                },"POST",$(".ajaxForm").serialize());
            });
        });
    </script>
@endsection
