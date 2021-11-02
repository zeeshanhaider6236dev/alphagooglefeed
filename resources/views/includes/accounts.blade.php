@foreach ($accounts as $account)
    @isset($account['merchantId'])
        <option value="{{ $account['merchantId'] }}">{{ $account['merchantName'].' ('.$account['merchantId'].')' }}</option>
    @endisset
    @foreach ($account['subAccounts'] as $subAccount)
        <option value="{{ $subAccount['id'] }}">{{ $subAccount['name'].' ('.$subAccount['id'].')' }}</option>
    @endforeach
@endforeach