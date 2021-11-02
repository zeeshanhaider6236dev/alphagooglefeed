@extends('vendor.shopify-app.layouts.default')
@section('styles')
	<style>
		.billingDiv{
			text-align: center;
		}
	</style>
	<!-- Global site tag (gtag.js) - Google Ads: 561514405 -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=AW-561514405"></script>
	<script>
	window.dataLayer = window.dataLayer || [];
	function gtag(){dataLayer.push(arguments);}
	gtag('js', new Date());

	gtag('config', 'AW-561514405');
	</script>
	<!-- Event snippet for ALPHA Feed Installed conversion page -->
	<script>
		gtag('event', 'conversion', {'send_to': 'AW-561514405/OpZuCKemju0BEKWP4IsC'});
	</script>	
@endsection
@section('content')
   <div class="container plans">
	<div class="btn-right">
		@if(auth()->user()->plan_id != null)
		   <button class="btn btn-primary btn-pb-primary " type="button"><a class="text-white" href="{{ route('home') }}">Go Back</a></button>
		@endif
	</div>
	</div>
	<section class="pricing-area">	
		<div class="container">
			<div class="row">
               <div class="col-md-3 col-sm-12">
					<div class="single-price" style="border-right: 1px solid;">
						<div class="price-title">
							<h4>Basic</h4>	  
						</div>
						<div class="price-tag center ">
							<h2 class="text-primary">$9.99 <span>/month</span></h2>
						</div>
						<b class="frees">( 7 Day FREE Trial )</b>
						<div class="price-item">
							<ul>
								<li>Add Up To 50,000 Products</li>
								<li>Unlimited Variants</li>
								<li>Add Multiple Countries</li>
								<li>Content API Feed</li>
								<li>instant Messenger Support</li>
							</ul>
						</div>
						<div class="billingDiv">
							<a class="btn btn-primary btn-pb-primary plan_btn" href="{{ route('billing', ['plan' => $basicplan->id]) }}">Select Plan</a>
						</div>
					</div>
				</div>
				<div class="col-md-3 col-sm-12">
					<div class="single-price" style="border-right: 1px solid;">
						<div class="price-title">
							<h4>Small<span></span></h4>	  
						</div>
						<div class="price-tag center">
							<h2 class="text-primary">$19.99 <span>/month</span></h2>
						</div>
						<b class="frees">( 7 Day FREE Trial )</b>
						<div class="price-item">
							<ul>
								<li>Add Up To 100,000 Products</li>
								<li>Unlimited Variants</li>
								<li>Add Multiple Countries</li>
								<li>Instant Update ContentAPI Feed</li>
								<li>instant Messenger Support</li>
							</ul>
						</div>
						<div class="billingDiv">
							<a class="btn btn-primary btn-pb-primary plan_btn" href="{{ route('billing', ['plan' => $smallplan->id]) }}">Select Plan</a>
						</div>
					</div>
			   	</div>
				<div class="col-md-3 col-sm-12">
					<div class="single-price" style="border-right: 1px solid;">
						<div class="price-title">
							<h4>Medium<span></span></h4>	  
						</div>
						<div class="price-tag center">
							<h2 class="text-primary">$29.99 <span>/month</span></h2>
						</div>
						<b class="frees">( 7 Day FREE Trial )</b>
						<div class="price-item">
							<ul>
								<li>Add Up To 200,000 Products</li>	
								<li>Unlimited Variants</li>
								<li>Add Multiple Countries</li>
								<li>Instant Update ContentAPI Feed</li>
								<li>instant Messenger Support</li>				
							</ul>
						</div>
						<div class="billingDiv">
							<a class="btn btn-primary btn-pb-primary plan_btn" href="{{ route('billing', ['plan' => $mediumplan->id]) }}">Select Plan</a>
						</div>
					</div>
			   	</div>
				<div class="col-md-3 col-sm-12">
					<div class="single-price">
						<div class="price-title">
							<h4>Ultimate<span></span></h4>	  
						</div>
						<div class="price-tag center">
							<h2 class="text-primary">$49.99 <span>/month</span></h2>
						</div>
						<b class="frees">( 7 Day FREE Trial )</b>
						<div class="price-item">
							<ul>
								<li>Unlimited Products</li>
								<li>Unlimited Variants</li>
								<li>Add Multiple Countries</li>
								<li>Instant Update ContentAPI Feed</li>
								<li>instant Messenger Support</li>					
							</ul>
						</div>
						<div class="billingDiv">
							<a class="btn btn-primary btn-pb-primary plan_btn" href="{{ route('billing', ['plan' => $all_inplan->id]) }}">Select Plan</a>
						</div>
					</div>
			   	</div>
            </div>
		</div>
	  </section>
@endsection
   
   
    
