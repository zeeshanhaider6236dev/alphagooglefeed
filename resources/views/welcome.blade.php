@extends('vendor.shopify-app.layouts.default')
@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.3/skins/all.min.css"
    integrity="sha512-wcKDxok85zB8F9HzgUwzzzPKJhHG7qMfC7bSKrZcFTC2wZXVhmgKNXYuid02cHVnFSC8KOJCXQ8M83UVA7v5Bw=="
    crossorigin="anonymous" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .productsBox .card{
        margin:10px 0px;
    }
    .productsBox .card .table-responsive{
        border: 1px solid #dadada;
    }
    .blue-bg{
        background-color:#007bff;
        color: white;
    }
    .tab_bg_2 {
        padding: 5px;
    }

    .nav-tabs .nav-link:hover {
        cursor: pointer;
    }

    form.btnform {
        margin-top: 0px;
    }

    .nav-tabs {
        border-bottom: none;
    }

    .btnform input {
        border: none;
        background-color: #8f8fa51f;
    }

    .select2-container{
        width:100% !important;
    }

    .productOverlay,.variantOverlay{
        width: 100%;
        height:300px;
        background-color:#8f8fa51f;    
        position: relative;
    }

    .productOverlay .fa-spinner,.variantOverlay .fa-spinner{
        position: absolute;
        top: 50%;
        left: 50%;
        font-size: 50px;
        color:#007bff;
    }

    .productSearchButton{
        max-height: 40px;
    }

    .bg-blue{
        background-color: #007bff;
    }

    .sticky {
        position: fixed;
        top: 0;
        width: 97%;
        background: white;
        z-index: +1;
        margin-left: 0px;
    }
</style>
@endsection
@section('content')
<div class="tabs">
    <div class="container-fluid tab_bg">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#home">
                    <i class="fa fa-dashboard"></i>Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#menu1">
                    <i class="fa fa-cogs"></i>Setting
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#menu2">
                    <i class="fa fa-question-circle"></i>FAQ's
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#menu3">
                    <i class="fa fa-phone-square"></i>Help & Support
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#menu4">
                    <i class="fa fa-book"></i>Tutorials
                </a>
            </li>
            <li class="nav-item ml-auto">
                <a class="nav-link" href="{{ route('plans') }}">
                    <span class="bg-success p-1 border rounded text-white">
                        <i class="fa fa-arrow-up"></i>Upgrade Plan
                    </span>
                </a>
            </li>
        </ul>
        @if(auth()->user()->settings->limit_notification)
            <div class="alert alert-danger alert-dismissible" role="alert">
                <button class="close dismissButton">&times;</button>
                You have reached Products limit in your Current Plan. Please Upgrade Your Plan.
            </div>
        @endif
        @if(auth()->user()->settings->notification_date->gte(Carbon\Carbon::now()->subDays(3)))
            <div class="alert alert-info alert-dismissible" role="alert">
                Google takes 3-5 business days to review your submitted feed.So it may take some time to reflect your product status (is Approved/Disapproved)
            </div>
        @endif
        <!----------------Tab-panes----------------->
        <div class="tab-content mt-3">
            <div id="home" class="tab-pane active">
                <div class="container-fluid tab_bg_2">
                    <div class="row fixedrow">
                        <div class="col-md-9">
                            <ul class="nav nav-tabs statusTabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-filter="">All Products <span class="badge badge-primary">{{ $status['all'] }}</span></a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-filter="{{ config('shopifyApi.strings.googleStatusApproved') }}">Approved <span class="badge badge-success">{{ $status['approved'] }}</span></a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-filter="{{ config('shopifyApi.strings.googleStatusDisapproved') }}">Disapproved <span class="badge badge-danger">{{ $status['disapproved'] }}</span></a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link"  data-filter="{{ config('shopifyApi.strings.googleStatusPending') }}">Pending <span class="badge badge-info">{{ $status['pending'] }}</span></a>
                                </li>
                                <li class="nav-item">
                                    <div class="" style="padding: 5px;">
                                        @if(auth()->user()->settings->last_updated == null || auth()->user()->settings->last_updated <= \Carbon\Carbon::now()->subDay())
                                            <button class="productStatusSyncButton text-white btn bg-success btn-sm" type="button">
                                                <i class="fa fa-refresh"></i> Refresh Status
                                            </button>
                                        @else
                                            <button class="productStatusSyncButtonFalse text-white btn bg-success btn-sm" type="button">
                                            <i class="fa fa-refresh"></i> Refresh Status
                                        </button>
                                        @endif
                                    </div>
                                </li>
                                <li>
                                    <div class="editContentBar">

                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-3">
                            <span class="btnform d-flex align-items-vertical">
                                <input class="productSearch" type="search" placeholder="Search Product...">
                                <button class="productSearchButton text-white btn bg-blue btn-sm" type="button"><i class="fa fa-search"></i></button>
                            </span>
                        </div>
                    </div>
                    <div class="productsBox">
                        <div class="productOverlay">
                            <i class="fa fa-spinner fa-spin"></i>
                        </div>
                        <div class="productContent">

                        </div>
                    </div>
                </div>
            </div>
            <!-------------------tab-1-Close--------------------->
            <div id="menu1" class="tab-pane fade p-0">
                <div class="container-fluid tab_bg_2 setting">
                    <h2>Google Merchant Setting</h2>
                    <table class="table_setting">
                        <tr>
                            <td class="t_w">Your Google Account :</td>
                            <td class="td_2">{{ auth()->user()->settings->googleAccountEmail }} </td>
                        </tr>
                        <tr>
                            <td class="t_w">Your Merchant ID :</td>
                            <td class="td_2">{{ auth()->user()->settings->merchantAccountId }} </td>
                        </tr>
                        <tr>
                            <td class="t_w">Your Primary Domain :</td>
                            <td class="td_2">{{ auth()->user()->settings->domain }}</td>
                        </tr>
                        <tr>
                            <td class="t_w">Your Store Currency :</td>
                            <td class="td_2">{{ auth()->user()->settings->currency }}</td>
                        </tr>
                        <tr>
                            <td class="t_w">Your Target Market :<br><span class="content">(Content Language) </span>
                            </td>
                            <td class="td_2">{{ $countryName }} {{ auth()->user()->settings->language }}
                            </td>
                        </tr>
                    </table>
                    <div class="col-md-12 text-center">
                        <div class="alert alert-danger">
                            if you Apply <strong> Change settings </strong>, it will be treated like a new product data and hence feed needs to be <strong> reapproved </strong>to show in ads again, which could take up to <strong> 3-5 days </strong>. Also, past performance history for the feed associated with existing <strong> Merchant Center </strong>will also be <strong> lost </strong>. If you still want to change the Merchant Center kindly <strong> contact support</strong>.
                        </div>
                        <a href="javascript:void(0);" class="btn btn-danger text-white changeSettingsBtn" style="display: inline-block;cursor:pointer;">Change Settings</a>
                    </div>
                </div>
            </div>
            <!-----------------Tab-2-Close----------------->
            <div id="menu2" class="tab-pane fade p-0">
                <div class="container-fluid faq p-0">
                    <div class="panel-group" id="accordion">
                        <!----------------1---------------------------->
                        <div class="panel panel-default panel_defalt">
                            <div class="panel-heading panel_head">
                                <h4 class="panel-title">
                                    <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion"
                                        href="#collapseOne">How often is the product feed updated?</a>
                                </h4>
                            </div>
                            <div id="collapseOne" class="panel-collapse collapse">
                                <div class="panel-body panel_body">
                                    The app is <b>updating</b> the feed daily.
                                </div>
                            </div>
                        </div>
                        <!----------------2---------------------------->
                        <div class="panel panel-default panel_defalt">
                            <div class="panel-heading panel_head">
                                <h4 class="panel-title">
                                    <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion"
                                        href="#collapsetwo">Will ALPHA FEED automatically claim my website in Google
                                        Merchant Center?</a>
                                </h4>
                            </div>
                            <div id="collapsetwo" class="panel-collapse collapse ">
                                <div class="panel-body panel_body">
                                    ALPHA will try to automatically claim your website in <b>Google Merchant Center.</b>
                                    However,
                                    If you have already claimed the URL in another merchant center, you might have to
                                    manually reclaim the website.
                                </div>
                            </div>
                        </div>
                        <!----------------3---------------------------->
                        <div class="panel panel-default panel_defalt">
                            <div class="panel-heading panel_head">
                                <h4 class="panel-title">
                                    <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion"
                                        href="#collapsethree">How are Shipping settings handled by ALPHA ?</a>
                                </h4>
                            </div>
                            <div id="collapsethree" class="panel-collapse collapse ">
                                <div class="panel-body panel_body">
                                    ALPHA Google Shopping Feed have Two option For Shipping One is Setup Shipping Manual
                                    in Your <b>Merchant Center</b> and the other one is
                                    FREE Shipping(default Setting ), in which we can configure Free shipping on Weights
                                    ( lb/kg)
                                </div>
                            </div>
                        </div>
                        <!----------------4---------------------------->
                        <div class="panel panel-default panel_defalt">
                            <div class="panel-heading panel_head">
                                <h4 class="panel-title">
                                    <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion"
                                        href="#collapsefour">How are tax settings handled by ALPHA
                                        Google Shopping Feed App?</a>
                                </h4>
                            </div>
                            <div id="collapsefour" class="panel-collapse collapse ">
                                <div class="panel-body panel_body">
                                    If you are selling in the US, You need to set up tax settings in your <b>merchant
                                        center.</b> If you opt-in to our automatic tax settings creation,
                                    we will copy your tax settings from Shopify to Google Merchant Center.We support
                                    automatic tax collection as well as manual
                                    tax collection settings. For advanced tax handling, it is always advised to edit tax
                                    settings directly in <b>Google Merchant Center.</b>
                                </div>
                            </div>
                        </div>
                        <!----------------5---------------------------->
                        <div class="panel panel-default panel_defalt">
                            <div class="panel-heading panel_head">
                                <h4 class="panel-title">
                                    <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion"
                                        href="#collapsefive">What are the requirements for a Shopify store to start
                                        Google
                                        Shopping?</a>
                                </h4>
                            </div>
                            <div id="collapsefive" class="panel-collapse collapse ">
                                <div class="panel-body panel_body">
                                    The following are certain requirements for <b>Merchant Center</b> to be approved:
                                    <ul>
                                        <li>online store with a unique domain - A domain can only have a single merchant
                                            center associated with it. This means that you cannot use
                                            myshopify.com URL for Google Shopping</li>
                                        <li>An active terms of service page</li>
                                        <li>An active refund policy page</li>
                                        <li>A store which is accessible publicly (no password)</li>
                                        <li>A valid payment provider. This ensures that every customer can check out
                                            after coming to your site</li>
                                        <li>Have an easily accessible page containing contact information on your
                                            website</li>
                                        <li>Sales tax & Shipping rates depending on the country of sale</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <!----------------6---------------------------->
                        <div class="panel panel-default panel_defalt">
                            <div class="panel-heading panel_head">
                                <h4 class="panel-title">
                                    <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion"
                                        href="#collapsesix">I also have custom made items in my shop which
                                        don’t have a GTIN. Is the app automatically generating the missing GTINs?
                                    </a>
                                </h4>
                            </div>
                            <div id="collapsesix" class="panel-collapse collapse ">
                                <div class="panel-body panel_body">
                                    Yes, the app will automatically generate the <b>missing GTINs</b> for your products.
                                    The app might present some errors
                                    if you don’t have all the info on your website.
                                </div>
                            </div>
                        </div>
                        <!----------------7---------------------------->
                        <div class="panel panel-default panel_defalt">
                            <div class="panel-heading panel_head">
                                <h4 class="panel-title">
                                    <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion"
                                        href="#collapseseven">Some product descriptions are missing from
                                        the product feed. What should I do?</a>
                                </h4>
                            </div>
                            <div id="collapseseven" class="panel-collapse collapse ">
                                <div class="panel-body panel_body">
                                    The app is extracting all the info from your website. If you have any descriptions
                                    missing in the <b>product feed,</b> that means you have some
                                    products on your website that do not have any description.
                                </div>
                            </div>
                        </div>
                        <!----------------8---------------------------->
                        <div class="panel panel-default panel_defalt">
                            <div class="panel-heading panel_head">
                                <h4 class="panel-title">
                                    <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion"
                                        href="#collapseeight">I have setup the feed and my Google Merchant account got
                                        suspended</a>
                                </h4>
                            </div>
                            <div id="collapseeight" class="panel-collapse collapse ">
                                <div class="panel-body panel_body">
                                    If you use <b>Google Merchant</b> for the first time, you must know that Google will
                                    check your website for some things.
                                    In case Google does not find the info, the account will be <b>suspended</b> until
                                    the details are included, and here is what you will have to do:
                                    <ul>
                                        <li>You need to add a page (and a link in the footer) which explains how
                                            customers can return products and get their money back.</li>
                                        <li>You need to add a page (and a link in the footer) which explains how much it
                                            will cost to get the product shipped.</li>
                                        <li>You need to add a page (and a link in the footer) which explains where you
                                            are located, and maybe your address. You can add this in
                                            about us-page or contact-page. If you can include a phone number, that helps
                                            too.After fixing everything, you can request a review
                                            from Google support.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <!----------------9---------------------------->
                        <div class="panel panel-default panel_defalt">
                            <div class="panel-heading panel_head">
                                <h4 class="panel-title">
                                    <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion"
                                        href="#collapsenine">Does Google frown upon having every variant
                                        of the product in the product feed?</a>
                                </h4>
                            </div>
                            <div id="collapsenine" class="panel-collapse collapse ">
                                <div class="panel-body panel_body">
                                    Google likes to have all <b>product variants,</b> because it can show the right
                                    product to the users. For example, if you search for <b>"red shoes",</b>
                                    it would be much better for the user to actually see the <b>"red shoe"</b> and not a
                                    shoe which can be ordered in 10 different colors.
                                </div>
                            </div>
                        </div>
                        <!----------------10---------------------------->
                        <div class="panel panel-default panel_defalt">
                            <div class="panel-heading panel_head">
                                <h4 class="panel-title">
                                    <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion"
                                        href="#collapseten">Google Merchant Center & how to solve them?</a>
                                </h4>
                            </div>
                            <div id="collapseten" class="panel-collapse collapse ">
                                <div class="panel-body panel_body">
                                    The following are common errors in <b>Merchant Center:</b>
                                    <ul>
                                        <li>Image too small - Google needs a resolution of at least 100*100 for
                                            non-apparel products and 250*250 for apparel products.
                                            Any resolution less than this might cause an error.</li>
                                        <li>Low image quality - Images which does not clearly show the product can get
                                            disapprovals.</li>
                                        <li>Promotional overlay on image - If you use a logo or brand or promotional
                                            offer on the image, there is a high chance that
                                            Google will disapprove your product.</li>
                                    </ul>
                                    To solve these errors, you have to make changes directly to Shopify. Kindly reupload
                                    a new image after you fix the errors & ALPHA
                                    will update Merchant Center with the changes.
                                </div>
                            </div>
                        </div>
                        <!----------------11---------------------------->
                        <div class="panel panel-default panel_defalt">
                            <div class="panel-heading panel_head">
                                <h4 class="panel-title">
                                    <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion"
                                        href="#collapseeleven">What are the common errors in the Apparels & Accessories
                                        category?
                                        how to solve them?</a>
                                </h4>
                            </div>
                            <div id="collapseeleven" class="panel-collapse collapse ">
                                <div class="panel-body panel_body">
                                    Products in Apparels and Accessories category have special requirements. As a rule
                                    of thumb, If a product is in <b>Apparels & Accessories,</b>
                                    it needs to have the following 4 attributes:
                                    <ul>
                                        <li>gender</li>
                                        <li>age group</li>
                                        <li>size</li>
                                        <li>color</li>
                                    </ul>
                                    Common <b>errors</b> due to these values missing are:
                                    <ul>
                                        <li>Missing required attribute for apparel</li>
                                        <li>Missing recommended attribute for apparel</li>
                                    </ul>
                                    You can make changes to these either in bulk or individually via <b>ALPHA Google
                                        Shopping Feed app.</b> We recommend adding these attributes whenever
                                    you can even if Google does not give an error
                                </div>
                            </div>
                        </div>
                        <!----------------12---------------------------->
                        <div class="panel panel-default panel_defalt">
                            <div class="panel-heading panel_head">
                                <h4 class="panel-title">
                                    <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion"
                                        href="#collapsetwele">How many countries can I advertise in using
                                        Google Shopping App? How can I add a new country?
                                    </a>
                                </h4>
                            </div>
                            <div id="collapsetwele" class="panel-collapse collapse ">
                                <div class="panel-body panel_body">
                                    Based on the language of your <b>store & currency,</b> you might be eligible to show
                                    ads in multiple countries. Here is an official link to check the
                                    <b>currency & language</b> requirements.To add a country to your feed, you can add
                                    it via settings > feed > add target country.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <!------------Tab-3-Clsoe-------------->
            <div id="menu3" class="tab-pane fade p-0">
                <div class="container-fluid contact-form p-0">
                    <div class="form-contact">
                        <form action="" class="contactform">
                            <div class="form-group">
                                <label>Email:</label>
                                <input type="email" class="form-control input-height" name="email">
                            </div>
                            <div class="form-group">
                                <label>Subject:</label>
                                <input type="text" class="form-control input-height" name="subject">
                            </div>
                            <div class="form-group">
                                <label>Message:</label>
                                <textarea class="form-control" placeholder="Write something.."
                                    style="height:200px" name="message"></textarea>
                            </div>
                            <button  type="button" value="Send Message" class="setting_btn sendbutton">Send</button>
                        </form>
                    </div>
                </div>
            </div>
            <!-----------4_tab_close--------------->
            <!------------Tab-5-Clsoe-------------->
            <div id="menu4" class="tab-pane fade p-0">
                <div class="container-fluid contact-form p-0">
                    <div class="row">
                        <div class="col-md-4">
                            <a href="https://youtu.be/dFf9a4D2gFw" target="_blank">
                                <img class="img-fluid m-1" src="{{ asset('assets/img/step1.jpg') }}"/>
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="https://youtu.be/TaVriIHDTrE" target="_blank">
                                <img class="img-fluid m-1" src="{{ asset('assets/img/step2.jpg') }}"/>
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="https://youtu.be/sZ4H2M-gR3Y" target="_blank">
                                <img class="img-fluid m-1" src="{{ asset('assets/img/step3.jpg') }}"/>
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href=" https://www.youtube.com/watch?v=tlyoY-QxKwo&feature=youtu.be" target="_blank">
                                <img class="img-fluid m-1" src="{{ asset('assets/img/step4.jpg') }}"/>
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href=" https://www.youtube.com/watch?v=jxoWtgAx5y0&t=841s" target="_blank">
                                <img class="img-fluid m-1" src="{{ asset('assets/img/step5.jpg') }}"/>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <!-----------5_tab_close--------------->
        </div>
    </div>
</div>
<div class="modal fade editPModel" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Update</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="ajaxForm3">
                    <input type="hidden" name="productId">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for=""><strong style="color: #007bff;">Update Title</strong> (Max 150 characters allowed)</label>
                                <input class="form-control" name="title"/>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for=""><strong style="color: #007bff;"> Update Description</strong> (Max 4990 characters allowed)</label>
                                <textarea class="form-control" name="description"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="">Update Google Product Category</label>
                        <select class="productcategorysearch form-control" name="product_category_id" >
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="">Update Age Group</label>
                                <select class="form-control" name="ageGroup">
                                    <option>Select An Option</option>
                                    <option value="adult" {{ auth()->user()->settings->ageGroup == "adult" ? "selected" : "" }}>Adult</option>
                                    <option value="kids" {{ auth()->user()->settings->ageGroup == "kids" ? "selected" : "" }}>Kids</option>
                                    <option value="infant" {{ auth()->user()->settings->ageGroup == "infant" ? "selected" : "" }}>Infant</option>
                                    <option value="newborn" {{ auth()->user()->settings->ageGroup == "newborn" ? "selected" : "" }}>NewBorn</option>
                                    <option value="toddler" {{ auth()->user()->settings->ageGroup == "toddler" ? "selected" : "" }}>Toddler</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="">Update Gender</label>
                                <select class="form-control" name="gender">
                                    <option>Select An Option</option>
                                    <option value="female" {{ auth()->user()->settings->gender == "female" ? "selected" : "" }}>Female</option>
                                    <option value="male" {{ auth()->user()->settings->gender == "male" ? "selected" : "" }}>Male</option>
                                    <option value="unisex" {{ auth()->user()->settings->gender == "unisex" ? "selected" : "" }}>Unisex</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="">Update Product Condition</label>
                                <select class="form-control" name="productCondition">
                                    <option>Select An Option</option>
                                    <option value="new" {{ auth()->user()->settings->productCondition == "new" ? "selected" : "" }}>New</option>
                                    <option value="used" {{ auth()->user()->settings->productCondition == "used" ? "selected" : "" }}>Used</option>
                                    <option value="refurbished" {{ auth()->user()->settings->productCondition == "refurbished" ? "selected" : "" }}>Refurbished</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row formrow">

                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary updatePButton">Update</button>
                <button class="addCustomLabel btn btn-success">Add Custom Label</button>
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
    @parent
    <script src="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.3/icheck.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
    <script src="https://cdn.ckeditor.com/4.15.0/standard/ckeditor.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/10.15.0/sweetalert2.all.min.js"></script>
    <script>
        $(document).ready(function() {
            var ckeditor = CKEDITOR.replace( 'description' ,{
                customConfig: '{{ asset("assets/js/ckeditor_config.js") }}'
            });
            $('.productSearch').on('keyup', function(e) {
                e.preventDefault();
                var keyCode = e.keyCode || e.which;
                if (keyCode === 13) { 
                    $(".productSearchButton").trigger('click');
                }
                if($(this).val() == ''){
                    $(".productSearchButton").trigger('click');
                }
                return false;
            });
            $(document).scroll(function() {
                if (window.pageYOffset > 124) {
                    $(".fixedrow").addClass("sticky");
                } else {
                    $(".fixedrow").removeClass("sticky");
                }
            });
            ajaxRequest(`{{ route('productSearch','') }}/All`,function(response){
                ResponeData(response);
            });
            $(document).on('click','.page-link',function(e){
                e.stopPropagation()
                e.preventDefault(true);
                ResponeData2();
                ajaxRequest($(this).attr('href'),function(response){
                    ResponeData(response);
                });
            });
            $(".productSearchButton").click(function(){
                ResponeData2();
                if($(".productSearch").val() == ''){
                    query = "All";
                }else{
                    query = $(".productSearch").val();
                }
                ajaxRequest(`{{ route('productSearch','') }}/${query}?tag=${$(".statusTabs .nav-link.active").data('filter')}`,function(response){
                    ResponeData(response);
                });
            });
            $(".statusTabs .nav-link").on('click',function(e){
                e.stopPropagation()
                $(".statusTabs .nav-link").removeClass('active');
                $(this).addClass('active');
                ResponeData2();
                if($(".productSearch").val() == ''){
                    query = "All";
                }else{
                    query = $(".productSearch").val();
                }
                ajaxRequest(`{{ route('productSearch','') }}/${query}?tag=${$(this).data('filter')}`,function(response){
                    ResponeData(response);
                });
            });
            function ResponeData2(){
                $(".productsBox .productContent").empty();
                $(".productOverlay").show();
                $(".editContentBar").empty();
            }
            function ResponeData(response){
                $(".productOverlay").hide();
                $(".productsBox .productContent").empty().html(response);
                $('.selectorClass').iCheck({
                    checkboxClass: `icheckbox_square-blue`,
                    increaseArea: '10%' // optional
                });
            }
            let editContentBarHtml = `
                    <div style="padding:5px;">
                        <button class="btn btn-primary selectAll btn-sm"><i class="fa fa-check-circle"></i> Select All</button>
                        <button class="editPProduct btn btn-primary btn-sm"><i class="fa fa-edit"></i> Edit Options </button>
                    </div>
                `;
            $('.productcategorysearch').select2({
                minimumInputLength: 3,
                ajax: {
                    url: '{{ route("product.category.search") }}',
                    dataType: 'json'
                }
            });
            $(document).on('show.bs.collapse', '.productCollapse' ,function (event) {
                let element = $(this);
                variantData2(element);
                let url = `{{ route('getVarients','') }}/${$(element).find('.card-body').data('id')}`;
                console.log($(".statusTabs .nav-link.active").data('filter'));
                ajaxRequest(`{{ route('getVarients','') }}/${$(element).find('.card-body').data('id')}${$(".statusTabs .nav-link.active").data('filter') ? '/'+$(".statusTabs .nav-link.active").data('filter') : ''}`,function(response){
                    variantData(element,response);
                });
            });
            $(document).on('hide.bs.collapse', '.productCollapse' ,function (event) {
                let element = $(this);
                variantData2(element);
            });
            function variantData2(element){
                $(element).find('.variantContent').empty();
                $(element).find('.variantOverlay').show();
                $(`.card-link[href="#${$(element).attr('id')}"]`).find('.accordionIcon').toggleClass(['fa-plus','fa-minus']);
            }
            function variantData(element,response){
                $(element).find('.variantOverlay').hide();
                $(element).find('.variantContent').empty().html(response);;
                $('.selectorClass').iCheck({
                    checkboxClass: `icheckbox_square-blue`,
                    increaseArea: '10%' // optional
                });
            }
            $(document).on('click', '.selectAll',function(event) {
                $(this).toggleClass(['btn-primary','btn-success']);
                if($(this).hasClass('btn-primary')){
                    $(".editContentBar").empty();
                    $('.customchkbox').iCheck('uncheck');
                }else{
                    $(this).html('<i class="fa fa-ban"></i> De-Select All');
                    $('.customchkbox').iCheck('check');
                }
            });
            $(document).on('ifChecked', '.customchkbox',function(event){
                $(this).iCheck('check');
                let count = 0;
                $('.customchkbox').each(function() {
                    if ($(this).iCheck('update')[0].checked) {
                        count++;
                    }
                });
                if (count < 1) {
                    $(".editContentBar").empty();
                }
                if(count == 1){
                    $(".editContentBar").empty().html(editContentBarHtml);
                }
            });
            $(document).on('ifUnchecked', '.customchkbox',function(event) {
                $(this).iCheck('uncheck');
                let flag = true;
                $('.customchkbox').each(function() {
                    if ($(this).iCheck('update')[0].checked) {
                        flag = false;
                    }
                });
                if (flag) {
                    $(".editContentBar").empty();
                }
            });
            $(document).on('click', '.editPProduct', function() {
                let count = 0;
                let chk = false;
                $('.customchkbox').each(function() {
                    if ($(this).iCheck('update')[0].checked) {
                        chk = $(this);
                        count++;
                    }
                });
                $('input[name="title"]').val('');
                $('textarea[name="description"]').html('');
                CKEDITOR.instances.description.setData('');
                $(".formrow").empty();
                if (count > 1) {
                    $(".editPModel").modal('show');
                }else{
                    el = $(this);
                    text = $(this).html();
                    addSpinner(el);
                    if(chk){
                        ajaxRequest("{{ route('productDetails','') }}/"+$(chk).val(), function(response) {
                            $('input[name="title"]').val(response.title);
                            $('textarea[name="description"]').html(response.description);
                            CKEDITOR.instances.description.setData(response.description);
                            $('input[name="ageGroup"]').val(response.ageGroup);
                            $('input[name="gender"]').val(response.gender);
                            $('input[name="productCondition"]').val(response.productCondition);
                            html = ``;
                            response.labels.forEach(function(label,index){
                                html+=`
                                    <div class="col-md-6 customlabel">
                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-md-10">
                                                    <label class="customLabelText">Custom Label ${index}</label>
                                                </div>
                                                <div class="col-md-2 text-right">
                                                    <span class="delLabel text-danger close" style ="cursor:pointer;">&times;</span>
                                                </div>
                                            </div>
                                            <input class="form-control" name="customLabel[]" value="${label.label}"/>
                                        </div>
                                    </div>
                                `;
                            });
                            $(".formrow").empty().html(html);
                            $(el).empty().html(text);
                            $(".editPModel").modal('show');
                        },"GET");
                    }
                }
            });
            $(".addCustomLabel").on('click',function() {
                length = $(".customlabel").length;
                console.log(length);
                if(length < 5){
                    let html = `
                    <div class="col-md-6 customlabel">
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-10">
                                    <label class="customLabelText">Custom Label ${length}</label>
                                </div>
                                <div class="col-md-2 text-right">
                                    <span class="delLabel text-danger close" style ="cursor:pointer;">&times;</span>
                                </div>
                            </div>
                            <input class="form-control" name="customLabel[]"/>
                        </div>
                    </div>
                `;
                $(".formrow").append(html);
                }else{
                    toastr.error("You Can Not Add more Custom Labels.",'error!');
                }
            });
            $(document).on('click','.delLabel',function(){
                $(this).closest('.customlabel').remove();
                $(".customLabelText").each(function(index,element){
                    $(this).empty().html("Custom Label "+(index));
                })
            });
            $(".updatePButton").click(function(){
                addSpinner($(this));
                $('textarea[name="description"]').html(CKEDITOR.instances.description.getData());
                ajaxRequest("{{ route('updateProduct') }}", function(response) {
                    $(".updatePButton").empty().html("Update");
                    $(".editPModel").modal('hide');
                },"POST",$(".ajaxForm2,.ajaxForm3").serialize());
            });
            $(document).on('click',".deletePProduct",function(){
                console.log($(this));
                addSpinner($(this));
                ajaxRequest("{{ route('deleteProduct','') }}/"+$(this).data('id'), function(response) {
                    $(".deletePProduct").empty().html('');
                });
            });
            $(document).on('click','.syncNow',function(){
                addSpinner($(this));
                element = $(this);
                ajaxRequest("{{ route('syncNow') }}", function(response) {
                    $(element).empty().html('Sync Now');
                },"POST",{variantId : $(this).data('id')});
            });
            $('.sendbutton').on('click', function (e) {
                e.preventDefault();
                addSpinner($(this));
                element = $(this);
                ajaxRequest("{{ route('contact.store') }}", function(response) {
                    $(element).empty().html('Send');
                },"POST",$('.contactform').serialize());
            }); 
            $('.changeSettingsBtn').on('click', function (e) {
                addSpinner($(this));
                element = $(this);
                Swal.fire({
                    title: 'Are You Sure ?',
                    showDenyButton: true,
                    showCancelButton: true,
                    showConfirmButton:false,
                    denyButtonText: `Yes`,
                }).then((result) => {
                    if (result.isDenied) {
                        ajaxRequest("{{ route('updateSettings') }}", function(response) {
                            $(element).empty().html('Change Settings');
                        });
                    }else{
                        $(element).empty().html('Change Settings');
                    }
                })
            });
            $(document).on('click',".productStatusSyncButton",function(e){
                e.preventDefault();
                addSpinner($(this));
                element = $(this);
                ajaxRequest("{{ route('SyncStatusNow') }}", function(response) {
                    if(response.customError){
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Product refresh status sync is allowed once every 24 hours.'
                        })
                    }
                    $(element).removeClass('productStatusSyncButton').addClass('productStatusSyncButtonFalse');
                    $(element).empty().html('<i class="fa fa-refresh"></i> Refresh Status');
                });
            });
            $(document).on('click',".productStatusSyncButtonFalse",function(e){
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Product refresh status sync is allowed once every 24 hrs.'
                })
            });
            $(document).on('click',".dismissButton",function(e){
                addSpinner($(this));
                element = $(this);
                ajaxRequest("{{ route('dismissLimitAlert') }}", function(response) {
                    if(response.status){
                        $(element).parent('.alert-danger').remove();
                    }
                });
            });
        });
    </script>
@endsection
