@if($publication->pricing_type === 'points')
    @lang('seeker::messages.price_display.'.$publication->price_basis, ['price' => format_money((float) $publication->price)])
@else
    @lang('seeker::messages.pricing_types.'.$publication->pricing_type)
@endif
