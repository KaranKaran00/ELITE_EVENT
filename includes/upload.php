<?php
/* includes/upload.php — handles image/video uploads for the Event Feedback Card.
   Files are stored under /uploads/feedback/ with a random filename (original
   name is never trusted or reused). */

const FEEDBACK_UPLOAD_DIR   = __DIR__ . '/../uploads/feedback/';
const FEEDBACK_UPLOAD_URL   = 'uploads/feedback/';
const FEEDBACK_MAX_IMAGE_MB = 8;
const FEEDBACK_MAX_VIDEO_MB = 40;

const FEEDBACK_ALLOWED_IMAGE_MIME = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    'image/gif'  => 'gif',
];

const FEEDBACK_ALLOWED_VIDEO_MIME = [
    'video/mp4'       => 'mp4',
    'video/webm'      => 'webm',
    'video/quicktime' => 'mov',
];

/**
 * Validates and stores an uploaded feedback photo or video.
 *
 * @param array $file One entry from $_FILES (e.g. $_FILES['media']).
 * @return array{url:string,type:string}|string Array with 'url' + 'type' on success, or an error string.
 */
function handleFeedbackMediaUpload(array $file) {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return 'none'; // No file selected — not an error, caller checks for this sentinel.
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return match ($file['error']) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'That file is too large to upload.',
            default => 'The file could not be uploaded. Please try again.',
        };
    }

    if (!is_uploaded_file($file['tmp_name'])) {
        return 'The file could not be uploaded. Please try again.';
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']) ?: '';

    if (isset(FEEDBACK_ALLOWED_IMAGE_MIME[$mime])) {
        $type = 'image';
        $ext  = FEEDBACK_ALLOWED_IMAGE_MIME[$mime];
        $maxBytes = FEEDBACK_MAX_IMAGE_MB * 1024 * 1024;
        if ($file['size'] > $maxBytes) {
            return "Photos must be under " . FEEDBACK_MAX_IMAGE_MB . "MB.";
        }
    } elseif (isset(FEEDBACK_ALLOWED_VIDEO_MIME[$mime])) {
        $type = 'video';
        $ext  = FEEDBACK_ALLOWED_VIDEO_MIME[$mime];
        $maxBytes = FEEDBACK_MAX_VIDEO_MB * 1024 * 1024;
        if ($file['size'] > $maxBytes) {
            return "Videos must be under " . FEEDBACK_MAX_VIDEO_MB . "MB.";
        }
    } else {
        return 'Please upload a JPG, PNG, WEBP, GIF photo or an MP4, WEBM, MOV video.';
    }

    if (!is_dir(FEEDBACK_UPLOAD_DIR)) {
        mkdir(FEEDBACK_UPLOAD_DIR, 0755, true);
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    $destination = FEEDBACK_UPLOAD_DIR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return 'The file could not be saved. Please try again.';
    }

    return ['url' => FEEDBACK_UPLOAD_URL . $filename, 'type' => $type];
}

/**
 * Deletes a previously uploaded feedback media file from disk, if it exists.
 */
function deleteFeedbackMedia(?string $relativeUrl): void {
    if (!$relativeUrl) return;
    $path = __DIR__ . '/../' . $relativeUrl;
    if (str_starts_with(realpath($path) ?: '', realpath(FEEDBACK_UPLOAD_DIR) ?: "\0") && is_file($path)) {
        @unlink($path);
    }
}
