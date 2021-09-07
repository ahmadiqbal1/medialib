<?php

namespace Spatie\MediaLibraryPro\Rules\GroupRules;

use Illuminate\Contracts\Validation\Rule;

class MaxItemsRule implements Rule
{
    protected int $maxItemCount;

    public function __construct(int $maxItemsCount)
    {
        $this->maxItemCount = $maxItemsCount;
    }

    public function passes($attribute, $value)
    {
        return count($value) <= $this->maxItemCount;
    }

    public function message()
    {
        return trans_choice('media-library::validation.max_items', $this->maxItemCount, [
            'max' => $this->maxItemCount,
        ]);
    }
}
