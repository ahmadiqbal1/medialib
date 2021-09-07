<?php

namespace Spatie\MediaLibraryPro\Http\Livewire\Concerns;

/** @mixin \Livewire\Component */
trait WithMedia
{
    public function getMediaComponentNames(): array
    {
        return $this->mediaComponentNames ?? [];
    }

    public function mountWithMedia(): void
    {
        foreach ($this->getMediaComponentNames() as $mediaComponentName) {
            $this->$mediaComponentName = null;
        }
    }

    public function hydrateWithMedia()
    {
        $this->listeners['mediaChanged'] = 'onMediaChanged';
    }

    public function onMediaChanged($name, $media): void
    {
        $media = $this->makeSureCustomPropertiesUseRightCasing($media);

        $this->$name = $media;
    }

    public function renderingWithMedia(): void
    {
        $errorBag = $this->getErrorBag();

        foreach ($this->getMediaComponentNames() as $mediaComponentName) {
            $this->emit('mediaComponentValidationErrors', $mediaComponentName, $errorBag->toArray());
        }
    }

    public function clearMedia($mediaComponentNames = null)
    {
        if (is_null($mediaComponentNames)) {
            $mediaComponentNames = $this->getMediaComponentNames();
        }

        if (is_string($mediaComponentNames)) {
            $mediaComponentNames = [$mediaComponentNames];
        }

        foreach ($mediaComponentNames as $mediaComponentName) {
            $this->emit('clearMedia', $mediaComponentName);

            $this->$mediaComponentName = [];
        }
    }

    protected function makeSureCustomPropertiesUseRightCasing(array $media): array
    {
        $media = collect($media)
            ->map(function (array $mediaItemAttributes) {
                if (! isset($mediaItemAttributes['custom_properties']) && isset($mediaItemAttributes['customProperties'])) {
                    $mediaItemAttributes['custom_properties'] = $mediaItemAttributes['customProperties'];
                    unset($mediaItemAttributes['customProperties']);
                }

                return $mediaItemAttributes;
            })
            ->toArray();

        return $media;
    }
}
