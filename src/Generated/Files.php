<?php

declare(strict_types=1);

namespace Clockster\Generated;

use Clockster\Exception\ApiException;
use Clockster\Exception\TransportException;
use Clockster\Http\Caller;
use Clockster\Http\Upload;

/**
 * The operations of `$clockster->files`.
 *
 * @phpstan-import-type FilesUploadResponse from Shapes
 */
final class Files
{
    public function __construct(private readonly Caller $caller)
    {
    }

    /**
     * Upload a file.
     *
     * One file, `multipart/form-data`, field name `file`. The only route on this surface that
     * is not JSON.
     *
     * It answers an `id`. That id is what `POST /company/v3/documents/upsert` takes as
     * `file_id`; until a document claims it the file belongs to nothing.
     *
     * Up to 10 MB. `pdf`, `doc`, `docx`, `xls`, `xlsx`, `jpg`, `jpeg`, `png` — the content is
     * checked, not just the extension. `name` is optional and defaults to the uploaded
     * filename without its extension.
     *
     * `url` is signed and short-lived: read it, do not store it. Read the document again to
     * get a fresh one.
     *
     * @param string $file The bytes to store, as read from the file.
     * @param string $filename The name the bytes travel under.
     *
     * @return FilesUploadResponse
     *
     * @throws ApiException|TransportException
     */
    public function upload(string $file, string $filename = 'upload', ?string $name = null): array
    {
        /** @var FilesUploadResponse $answer */
        $answer = $this->caller->call(
            'POST',
            '/company/v3/files',
            upload: new Upload($file, $filename, ['name' => $name]),
        );

        return $answer;
    }
}
