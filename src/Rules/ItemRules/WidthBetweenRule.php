<?php

namespace Spatie\MediaLibraryPro\Rules\ItemRules;

class WidthBetweenRule extends MediaItemRule
{
    protected int $minWidth;

    protected int $maxWidth;

    public function __construct(int $minWidth = 0, int $maxWidth = 0)
    {
        $this->minWidth = $minWidth;
        $this->maxWidth = $maxWidth;
    }

    public function validateMediaItem(): bool
    {
        if (! $media = $this->getTemporaryUploadMedia()) {
            return true;
        }

        $size = getimagesize($media->getPath());
        $actualWidth = $size[0];

        return $actualWidth >= $this->minWidth && $actualWidth <= $this->maxWidth;
    }

    public function message()
    {
        return __('media-library::validation.width_not_between', [
            'min' => $this->minWidth,
            'max' => $this->maxWidth,
        ]);
    }
}
