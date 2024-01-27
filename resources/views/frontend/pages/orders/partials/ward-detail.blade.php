{{-- @dd($oldWard); --}}
<select class="form-control w-100 filter-select" id="select-ward" data-total-cost="{{ $totalAllProduct }}" name="ward">
    <option value="default">@lang('Choose Ward')</option>
    @foreach ($wards as $ward)
        <option class="ward-option-{{ $ward['WardCode'] }}" value="{{ $ward['WardCode'] }}"
            {{ $oldWard == $ward['WardCode'] ? 'selected' : '' }}
            data-url="{{ route('frontend.orders.getShippingCost', ['districtID' => $districtId, 'wardCode' => $ward['WardCode']]) }}"
            data-ward-code="{{ $ward['WardCode'] }}" data-district-id="{{ $districtId }}"
            data-ward-name="{{ $ward['WardName'] }}" data-total-cost="{{ $totalAllProduct }}">
            {{ $ward['WardName'] }}</option>
    @endforeach
</select>
<small id="error_order"
       class="error text-danger">{{ $errors->first('ward_name') }}</small>
<input type="hidden" id="selected-ward-name" name="ward_name">

<script src="{{ asset('js/pages/checkout/address.js') }}"></script>
<script src="{{ asset('js/pages/filter.js') }}"></script>
