<form class="ajaxForm2">
    @foreach ($products as $key => $product)
        @php
            $flag = isset($product['node']) ? ($product['node']['publishedAt'] ? true : false) : true;
        @endphp
        @if($flag)
            <div class="card">
                <div class="card-header">
                    <div class="row d-flex align-items-center">
                        <div class="col-md-1">
                            <input name="products[]" class="customchkbox selectorClass" type="checkbox" value="{{ isset($product['node']) ? str_replace(config('shopifyApi.strings.graphQlProductIdentifier'),'',$product['node']['id']) : $product->productId }}">
                        </div>
                        <div class="col-md-1">
                            <img src="{{ isset($product['node']) ?  optional($product['node']['featuredImage'])['src'] : ($product->image ? $product->image : asset('assets/img/defaultpic.png')) }}" width="50px"/>
                        </div>
                        <div class="col-md-8">
                            {{ isset($product['node']) ?  $product['node']['title'] : $product->title  }}
                        </div>
                        <div class="col-md-2">
                            <i class="fa fa-eye btn btn-sm text-white btn-success card-link" class="card-link" data-toggle="collapse" href="#collapse{{ $key }}" style="cursor: pointer;"></i>
                            {{-- <i data-id="{{ isset($product['node']) ? str_replace(config('shopifyApi.strings.graphQlProductIdentifier'),'',$product['node']['id']) : $product->productId }}" class="fa fa-pencil editPProduct btn btn-sm text-white" style="background: #007bff"></i> --}}
                            @if($tag || in_array((isset($product['node']) ? str_replace(config('shopifyApi.strings.graphQlProductIdentifier'),'',$product['node']['id']) : ''),$foundProductIds->toArray()))
                                <i data-id="{{ isset($product['node']) ? str_replace(config('shopifyApi.strings.graphQlProductIdentifier'),'',$product['node']['id']) : $product->productId }}" class="fa fa-trash deletePProduct btn btn-danger btn-sm text-white"></i>
                            @endif
                        </div>
                    </div>
                </div>
                <div id="collapse{{ $key }}" class="collapse productCollapse" data-parent="#accordion">
                    <div class="card-body" data-id="{{ isset($product['node']) ?  str_replace(config('shopifyApi.strings.graphQlProductIdentifier'),'',$product['node']['id']) : $product->productId }}">
                        <div class="variantOverlay">
                            <i class="fa fa-spinner fa-spin"></i>
                        </div>
                        <div class="variantContent">

                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
</form>
<div class="pagination justify-content-center my-2 ">
    @if($links != null)
        @if ($links['hasPreviousPage'])
            <a href="{{ route('productSearch',[$query,$products[0]['cursor'],'before']) }}" class="page-link prodLink" >« Previous</a>    
        @endif
        @if ($links['hasNextPage'])
            <a href="{{ route('productSearch',[$query,$products[count($products)-1]['cursor'],'after']) }}" class="page-link prodLink" >Next »</a>
        @endif
    @endif
    {{ $tag ? $products->appends(['tag' => $tag ])->render() : '' }}
</div>