import { CancelTokenSource } from 'axios';
import { MediaLibrary } from '.';
export declare function getCancelTokenSource(): CancelTokenSource;
declare type UploadFileProps = {
    routePrefix: string;
    file: File;
    uuid: string;
    cancelTokenSource: CancelTokenSource;
    vapor: MediaLibrary['config']['vapor'];
    vaporSignedStorageUrl: MediaLibrary['config']['vaporSignedStorageUrl'];
    uploadDomain: string;
    withCredentials: boolean;
    onUploadProgress: (progress: ProgressEvent) => void;
};
export declare function uploadFile({ routePrefix, file, uuid, cancelTokenSource, vapor, vaporSignedStorageUrl, uploadDomain, withCredentials, onUploadProgress, }: UploadFileProps): Promise<import("axios").AxiosResponse<any>>;
export {};
