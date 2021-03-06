import {
    MediaLibrary as MediaLibraryClass,
    formatLaravelErrors,
    sanitizeForInput,
} from '@spatie/media-library-pro-core';
import { MediaLibrary } from '@spatie/media-library-pro-core/dist/types';
import { defineComponent, PropType } from 'vue';
import get from 'lodash/get';

export default defineComponent({
    props: {
        name: { required: false, type: String },
        routePrefix: { required: false, type: String as PropType<MediaLibraryClass['config']['routePrefix']> },
        initialValue: { default: () => [], type: [Array, Object] as PropType<MediaLibrary.Options['initialValue']> },
        validationErrors: { default: () => ({}), type: [Array, Object] as PropType<MediaLibrary.ValidationErrors> },
        validationRules: {
            required: false,
            type: Object as PropType<Partial<MediaLibraryClass['config']['validationRules']>>,
        },
        translations: { required: false, type: Object as PropType<MediaLibrary.Options['translations']> },
        multiple: { default: true, type: Boolean },
        maxItems: { required: false, type: Number },
        maxSizeForPreviewInBytes: { required: false, type: Number },
        vapor: { required: false, type: Boolean as () => MediaLibrary.Config['vapor'] },
        vaporSignedStorageUrl: { required: false, type: String },
        uploadDomain: { required: false, type: String },
        withCredentials: { required: false, type: Boolean },
        beforeUpload: { default: () => {}, type: Function },
        afterUpload: { default: () => {}, type: Function },
    },

    data(): {
        state: MediaLibraryClass['state'];
        mediaLibrary: MediaLibraryClass;
    } {
        return {
            state: { media: [], invalidMedia: [], validationErrors: {} },
            mediaLibrary: new MediaLibraryClass({
                config: {
                    immutable: false,
                    routePrefix: this.routePrefix,
                    validationRules: this.validationRules,
                    maxSizeForPreviewInBytes: this.maxSizeForPreviewInBytes,
                    vapor: this.vapor,
                    vaporSignedStorageUrl: this.vaporSignedStorageUrl,
                    uploadDomain: this.uploadDomain,
                    withCredentials: this.withCredentials,
                    beforeUpload: this.beforeUpload as MediaLibraryClass['config']['beforeUpload'],
                    afterUpload: this.afterUpload as MediaLibraryClass['config']['afterUpload'],
                },
                initialValue: this.initialValue,
                validationErrors: this.validationErrors,
                translations: this.translations,
            }),
        };
    },

    emits: [
        'changed',
        'is-ready-to-submit-change',
        'has-uploads-in-progress-change',
        'isReadyToSubmitChange',
        'hasUploadsInProgressChange',
    ],

    created() {
        this.state = this.mediaLibrary.state;

        this.mediaLibrary.subscribe((newState: any) => {
            this.$emit(
                'changed',
                (newState.media as MediaLibrary.MediaObject[]).reduce((value, media) => {
                    value[media.attributes.uuid] = media.attributes;
                    return value;
                }, {} as { [uuid: string]: MediaLibrary.MediaAttributes })
            );
        });
    },

    watch: {
        validationErrors: {
            deep: true,
            immediate: true,
            handler: function (val) {
                this.mediaLibrary.setValidationErrors(val ? formatLaravelErrors(this.name || '', val) : {});
            },
        },

        isReadyToSubmit: {
            immediate: true,
            handler: function (val) {
                this.$emit('is-ready-to-submit-change', val);
            },
        },

        hasUploadsInProgress: {
            immediate: true,
            handler: function (val) {
                this.$emit('has-uploads-in-progress-change', val);
            },
        },
    },

    methods: {
        getImgProps(object: MediaLibrary.MediaObject) {
            const extension = object.attributes.name ? object.attributes.name.split('.').pop() : '';
            return {
                src: object.attributes.preview_url || object.client_preview,
                alt: object.attributes.name,
                extension,
                size: object.attributes.size,
            };
        },

        getCustomPropertyInputProps(object: MediaLibrary.MediaObject, propertyName: string) {
            return {
                value: sanitizeForInput(get(object.attributes.custom_properties, propertyName)),
            };
        },

        getCustomPropertyInputListeners(object: MediaLibrary.MediaObject, propertyName: string) {
            return {
                input: (event: Event) =>
                    this.mediaLibrary.setCustomProperty(
                        object.attributes.uuid,
                        propertyName,
                        (event.target as HTMLInputElement).value
                    ),
            };
        },

        getCustomPropertyInputErrors(object: MediaLibrary.MediaObject, propertyName: string) {
            return this.mediaLibrary.getCustomPropertyInputErrors(object.attributes.uuid, propertyName);
        },

        getNameInputProps(object: MediaLibrary.MediaObject) {
            return {
                value: get(object, 'attributes.name'),
            };
        },

        getNameInputListeners(object: MediaLibrary.MediaObject) {
            return {
                input: (event: Event) =>
                    this.mediaLibrary.setProperty(
                        object.attributes.uuid,
                        'attributes.name',
                        (event.target as HTMLInputElement).value
                    ),
            };
        },

        getNameInputErrors(object: MediaLibrary.MediaObject) {
            return this.mediaLibrary.getNameInputErrors(object.attributes.uuid);
        },

        getFileInputProps() {
            const accept = this.validationRules
                ? this.validationRules.accept
                    ? this.validationRules.accept.join(',')
                    : ''
                : '';
            return { accept };
        },

        getFileInputListeners() {
            return {
                changed: (event: Event) => this.addFiles((event.target as HTMLInputElement).files as FileList),
            };
        },

        getDropZoneProps() {
            return { validationRules: this.validationRules, maxItems: this.maxItems };
        },

        getDropZoneListeners() {
            return {
                dropped: (event: DragEvent) => this.addFiles(event.dataTransfer!.files),
            };
        },

        addFiles(files: FileList) {
            if (this.multiple) {
                const end = this.maxItems ? this.maxItems - this.mediaLibrary.state.media.length : undefined;

                return Array.from(files)
                    .slice(0, end)
                    .forEach((file) => this.mediaLibrary.addFile(file));
            }

            const file = files[0];

            const existingItem = this.mediaLibrary.state.media[0];
            if (existingItem) {
                return this.mediaLibrary.replaceMedia(existingItem.attributes.uuid, file);
            }

            this.mediaLibrary.addFile(file);
        },

        removeMedia(object: MediaLibrary.MediaObject) {
            this.mediaLibrary.removeMedia(object.attributes.uuid);
        },

        setProperty(
            object: MediaLibrary.MediaObject,
            key: Exclude<keyof MediaLibrary.MediaObject, 'custom_properties' | 'uuid'>,
            value: any
        ) {
            this.mediaLibrary.setProperty(object.attributes.uuid, key, value);
        },

        setCustomProperty(object: MediaLibrary.MediaObject, key: string, value: any) {
            this.mediaLibrary.setCustomProperty(object.attributes.uuid, key, value);
        },

        setOrder(uuids: Array<string>) {
            this.mediaLibrary.setOrder(uuids);
        },

        replaceMedia(object: MediaLibrary.MediaObject, file: File) {
            this.mediaLibrary.replaceMedia(object.attributes.uuid, file);
        },

        addFile(file: File) {
            this.mediaLibrary.addFile(file);
        },

        getErrors(object: MediaLibrary.MediaObject) {
            return this.mediaLibrary.getErrors(object.attributes.uuid);
        },

        clearObjectErrors(object: MediaLibrary.MediaObject) {
            return this.mediaLibrary.clearObjectErrors(object.attributes.uuid);
        },

        clearInvalidMedia() {
            return this.mediaLibrary.clearInvalidMedia();
        },
    },

    computed: {
        hasUploadsInProgress(): boolean {
            return (this.$data.mediaLibrary as MediaLibraryClass).hasUploadsInProgress;
        },

        isReadyToSubmit(): boolean {
            return (this.$data.mediaLibrary as MediaLibraryClass).isReadyToSubmit;
        },
    },

    render() {
        if (this.$slots.default) {
            return this.$slots.default(this) /* as unknown */;
        }

        throw new Error('media-library-pro-vue3: no slot was found.');
    },
});
