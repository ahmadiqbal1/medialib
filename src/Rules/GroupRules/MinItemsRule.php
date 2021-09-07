<?php

namespace Spatie\MediaLibraryPro\Rules\GroupRules;

use Illuminate\Contracts\Validation\Rule;

class MinItemsRule implements Rule
{
    protected int $minItemCount;

    public function __construct(int $minItemCount)
    {
        $this->minItemCount = $minItemCount;
    }

    public function getMinItemCount()
    {
        return $this->minItemCount;
    }

    public function passes($attribute, $value)
    {
        return count($value) >= $this->minItemCount;
    }

    public function message()
    {
        return trans_choice('media-library::validation.min_items', $this->minItemCount, [
            'min' => $this->minItemCount,
        ]);
    }
}
