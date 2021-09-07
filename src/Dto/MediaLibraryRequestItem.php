<?php

namespace Spatie\MediaLibraryPro\Dto;

use Illuminate\Support\Str;

class MediaLibraryRequestItem
{
    public string $uuid;
    public string $name;
    public int $order;
    public array $customProperties;
    public array $customHeaders;
    public ?string $fileName = null;

    public static function fromArray(array $properties): self
    {
        $properties = collect($properties)
            ->keyBy(fn ($value, $key) => Str::snake($key));

        return new static(
            $properties['uuid'],
            $properties['name'] ?? '',
            $properties['order'] ?? 0,
            $properties['custom_properties'] ?? [],
            $properties['custom_headers'] ?? [],
            $properties['file_name'] ?? null,
        );
    }

    protected function __construct(
        string $uuid,
        string $name,
        int $order,
        array $customProperties,
        array $customHeaders,
        ?string $fileName = null
    ) {
        $this->uuid = $uuid;

        $this->name = $name;

        $this->order = $order;

        $this->customProperties = $customProperties;

        $this->customHeaders = $customHeaders;

        $this->fileName = $fileName;
    }
}
