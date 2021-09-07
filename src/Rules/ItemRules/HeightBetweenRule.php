<?php

namespace Spatie\MediaLibraryPro\Rules\ItemRules;

class HeightBetweenRule extends MediaItemRule
{
    protected int $minHeight;

    protected int $maxHeight;

    public function __construct(int $minHeight = 0, int $maxHeight = 0)
    {
        $this->minHeight = $minHeight;
        $this->maxHeight = $maxHeight;
    }

    public function validateMediaItem(): bool
    {
        if (! $media = $this->getTemporaryUploadMedia()) {
            return true;
        }

        $size = getimagesize($media->getPath());
        $actualHeight = $size[1];

        return $actualHeight >= $this->minHeight && $actualHeight <= $this->maxHeight;
    }

    public function message()
    {
        return __('media-library::validation.height_not_between', [
            'min' => $this->minHeight,
            'max' => $this->maxHeight,
        ]);
    }
}
