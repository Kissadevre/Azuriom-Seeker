@if($count > 0)
    <span class="text-warning" aria-hidden="true"><i class="bi bi-star-fill"></i></span>
    <strong>{{ number_format((float) $rating, 1) }}</strong>
    <span class="text-muted">{{ trans_choice('seeker::messages.reviews.count', $count, ['count' => $count]) }}</span>
@else
    <span class="text-muted"><i class="bi bi-star me-1" aria-hidden="true"></i>@lang('seeker::messages.reviews.no_reviews')</span>
@endif
