<?php

namespace Spatie\MediaLibraryPro\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\FileUploadConfiguration;
use Livewire\TemporaryUploadedFile;
use Spatie\MediaLibrary\Conversions\FileManipulator;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibraryPro\Models\TemporaryUpload;

class ConvertLivewireUploadToMediaAction
{
    public function execute(TemporaryUploadedFile $temporaryUploadedFile): Media
    {
        return FileUploadConfiguration::isUsingS3()
            ? $this->createFromS3LivewireUpload($temporaryUploadedFile)
            : $this->createFromLocalLivewireUpload($temporaryUploadedFile);
    }

    protected function createFromLocalLivewireUpload(TemporaryUploadedFile $livewireUpload): Media
    {
        $uploadedFile = new UploadedFile($livewireUpload->path(), $livewireUpload->getClientOriginalName());

        $temporaryUploadModelClass = config('media-library.temporary_upload_model');

        $livewireUpload = $temporaryUploadModelClass::createForFile(
            $uploadedFile,
            session()->getId(),
            (string)Str::uuid(),
            $livewireUpload->getClientOriginalName()
        );

        return $livewireUpload->getFirstMedia();
    }

    protected function createFromS3LivewireUpload(TemporaryUploadedFile $livewireUpload): Media
    {
        $temporaryUploadModelClass = config('media-library.temporary_upload_model');

        /** @var TemporaryUpload $temporaryUpload */
        $temporaryUpload = $temporaryUploadModelClass::create([
            'session_id' => session()->getId(),
        ]);

        $livewireDisk = config('livewire.temporary_file_upload.disk', 's3');

        /** @var \Spatie\MediaLibrary\MediaCollections\Models\Media $media */
        $media = $temporaryUpload->media()->create([
            'name' => $livewireUpload->getClientOriginalName(),
            'collection_name' => 'default',
            'uuid' => (string)Str::uuid(),
            'disk' => $livewireDisk,
            'conversions_disk' => $livewireDisk,
            'manipulations' => [],
            'custom_properties' => [],
            'generated_conversions' => [],
            'responsive_images' => [],
            'size' => $livewireUpload->getSize(),
            'file_name' => $livewireUpload->getClientOriginalName(),
        ]);

        /** @var \Spatie\MediaLibrary\Support\PathGenerator\PathGenerator $pathGenerator */
        $pathGenerator = app(config('media-library.path_generator'));

        $targetPath = $pathGenerator->getPath($media) . '/' . $livewireUpload->getClientOriginalName();

        $livewireDirectory = config('livewire.temporary_file_upload.directory') ?? '/livewire-tmp/';

        Storage::disk($livewireDisk)->move(
            Str::of($livewireDirectory)->start('/')->finish('/') . $livewireUpload->getFilename(),
            $targetPath
        );

        /** @var \Spatie\MediaLibrary\Conversions\FileManipulator $fileManipulator */
        $fileManipulator = app(FileManipulator::class);

        $fileManipulator->createDerivedFiles($media);

        return $media;
    }
}
