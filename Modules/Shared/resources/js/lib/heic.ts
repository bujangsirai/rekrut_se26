import heic2any from 'heic2any';

const HEIC_MIME_TYPES = ['image/heic', 'image/heif'];
const DEFAULT_SUPPORTED_IMAGE_MIME_TYPES = ['image/jpeg', 'image/jpg', 'image/png'];

export interface NormalizeImageFileOptions {
    jpegQuality?: number;
    supportedMimeTypes?: string[];
}

export interface NormalizeImageFileResult {
    file: File;
    convertedFromHeic: boolean;
}

export function isHeicImageFile(file: File): boolean {
    const fileName = file.name.toLowerCase();
    const mimeType = file.type.toLowerCase();

    return HEIC_MIME_TYPES.includes(mimeType) || fileName.endsWith('.heic') || fileName.endsWith('.heif');
}

export function isSupportedImageFile(file: File, supportedMimeTypes: string[] = DEFAULT_SUPPORTED_IMAGE_MIME_TYPES): boolean {
    return supportedMimeTypes.includes(file.type);
}

export async function normalizeImageFile(
    file: File,
    options: NormalizeImageFileOptions = {},
): Promise<NormalizeImageFileResult> {
    const { jpegQuality = 0.9, supportedMimeTypes = DEFAULT_SUPPORTED_IMAGE_MIME_TYPES } = options;
    let normalizedFile = file;
    let convertedFromHeic = false;

    if (isHeicImageFile(normalizedFile)) {
        normalizedFile = await convertHeicToJpeg(normalizedFile, jpegQuality);
        convertedFromHeic = true;
    }

    if (!isSupportedImageFile(normalizedFile, supportedMimeTypes)) {
        throw new Error('unsupported_image_type');
    }

    return {
        file: normalizedFile,
        convertedFromHeic,
    };
}

async function convertHeicToJpeg(file: File, quality: number): Promise<File> {
    const convertedBlob = await heic2any({
        blob: file,
        toType: 'image/jpeg',
        quality,
    });

    const normalizedBlob = Array.isArray(convertedBlob) ? convertedBlob[0] : convertedBlob;

    if (!normalizedBlob) {
        throw new Error('heic_conversion_failed');
    }

    return new File([normalizedBlob], file.name.replace(/\.(heic|heif)$/i, '.jpg'), {
        type: 'image/jpeg',
        lastModified: Date.now(),
    });
}
