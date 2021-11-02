<table class="table-responsive" style="display: table;">
    <thead>
        <tr>
            <td>Variant Title</td>
            <td>Google Status </td>
            <td>Errors From Merchant Account </td>
        </tr>
    </thead>
    <tbody>
        @foreach ($variants as $variant)
            @if($tag !=null)
                @unless($tag !=null && isset($variant['status']))
                    @continue 
                @endunless        
            @endif
            <tr>
                <td>
                    <div class="td_img"><img src="{{ $variant['image'] ? $variant['image'] : asset('assets/img/defaultpic.png') }}" style="width:50px;"></div>
                    <div class="td_heading"><span>{{ $variant['title'] }}</span></div>
                </td>
                @if($tag)
                    <td>
                        <span class="btn btn-sm btn-primary">{{ $tag }}</span>
                    </td>
                @else
                    @isset($variant['status'])
                        <td>
                            <span class="btn btn-sm btn-primary">{{ucfirst($variant['status']) }}</span>
                        </td>
                    @else
                        <td>
                            <span class="btn p-2 bg-success rounded syncNow text-white btn-sm" data-id="{{ $id.':'.$variant['id'] }}">Sync Now</span>
                        </td>
                    @endisset
                @endif
                @isset($variant['errors'])
                    <td>
                        <ul>
                            @foreach ($variant['errors'] as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </td>
                @else
                    <td>
                    </td>
                @endisset
            </tr>
        @endforeach
    </tbody>
</table>