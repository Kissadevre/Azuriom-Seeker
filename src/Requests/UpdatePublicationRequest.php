<?php

namespace Azuriom\Plugin\Seeker\Requests;

use Azuriom\Plugin\Seeker\Models\Publication;
use Azuriom\Plugin\Seeker\Services\SeekerSettings;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdatePublicationRequest extends StorePublicationRequest
{
    public function authorize(): bool
    {
        $publication = $this->route('publication');

        return $publication instanceof Publication
            && $this->user() !== null
            && $publication->user_id === $this->user()->id;
    }

    public function rules(): array
    {
        $settings = app(SeekerSettings::class);

        return [
            ...parent::rules(),
            'images' => ['nullable', 'prohibited_unless:portfolio_type,'.Publication::PORTFOLIO_IMAGES, 'array', 'max:'.$settings->assetCountLimit(Publication::PORTFOLIO_IMAGES)],
            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => ['integer', Rule::exists('seeker_publication_images', 'id')],
            'video' => $this->mediaRules(Publication::PORTFOLIO_VIDEO, false),
            'audio' => $this->mediaRules(Publication::PORTFOLIO_AUDIO, false),
            'remove_media' => ['nullable', 'array'],
            'remove_media.*' => ['integer', Rule::exists('seeker_publication_media', 'id')],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $publication = $this->route('publication');
                $removed = $publication->images()
                    ->whereIn('id', array_unique($this->input('remove_images', [])))
                    ->count();
                $uploaded = count($this->file('images', []));
                $remaining = $publication->images()->count() - $removed + $uploaded;

                if ($this->input('portfolio_type') === Publication::PORTFOLIO_IMAGES && $remaining < 1) {
                    $validator->errors()->add('images', trans('seeker::messages.validation.images_required'));
                }

                $settings = app(SeekerSettings::class);
                $imageLimit = $settings->assetCountLimit(Publication::PORTFOLIO_IMAGES);

                if ($this->input('portfolio_type') === Publication::PORTFOLIO_IMAGES && $remaining > $imageLimit) {
                    $validator->errors()->add('images', trans('seeker::messages.validation.max_assets', [
                        'count' => $imageLimit,
                        'type' => trans('seeker::messages.fields.images'),
                    ]));
                }

                $selectedType = $this->input('portfolio_type');
                $portfolioTypeDisabled = ! app(SeekerSettings::class)
                    ->portfolioTypeEnabled($publication->portfolio_type);

                if ($portfolioTypeDisabled && $selectedType === $publication->portfolio_type) {
                    $hasNewUpload = $selectedType === Publication::PORTFOLIO_IMAGES
                        ? $this->hasFile('images')
                        : (in_array($selectedType, Publication::uploadedPortfolioTypes(), true)
                            && $this->hasFile($selectedType));
                    $externalUrlChanged = $selectedType === Publication::PORTFOLIO_EXTERNAL
                        && $this->input('portfolio_url') !== $publication->portfolio_url;

                    if ($hasNewUpload || $externalUrlChanged) {
                        $validator->errors()->add('portfolio_type', trans(
                            'seeker::messages.validation.disabled_portfolio_locked'
                        ));
                    }
                }

                if (in_array($selectedType, Publication::uploadedPortfolioTypes(), true)) {
                    $removedMedia = $publication->media()
                        ->where('type', $selectedType)
                        ->whereIn('id', array_unique($this->input('remove_media', [])))
                        ->count();
                    $uploadedMedia = count($this->file($selectedType, []));
                    $remainingMedia = $publication->media()->where('type', $selectedType)->count()
                        - $removedMedia
                        + $uploadedMedia;
                    $mediaLimit = $settings->assetCountLimit($selectedType);

                    if ($remainingMedia < 1) {
                        $validator->errors()->add($selectedType, trans('seeker::messages.validation.media_required', [
                            'type' => trans('seeker::messages.portfolio_types.'.$selectedType),
                        ]));
                    } elseif ($remainingMedia > $mediaLimit) {
                        $validator->errors()->add($selectedType, trans('seeker::messages.validation.max_assets', [
                            'count' => $mediaLimit,
                            'type' => trans('seeker::messages.fields.'.$selectedType),
                        ]));
                    }
                }
            },
        ];
    }

    protected function allowedPortfolioTypes(): array
    {
        $publication = $this->route('publication');
        $types = parent::allowedPortfolioTypes();

        if ($publication instanceof Publication && ! in_array($publication->portfolio_type, $types, true)) {
            $types[] = $publication->portfolio_type;
        }

        return $types;
    }
}
