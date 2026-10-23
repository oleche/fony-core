<?php

/* Binary Response
 * DESCRIPTION: Response with raw content (a PDF, an image...) instead of JSON
 */

namespace Geekcow\FonyCore\Utils;

/**
 * Set it as the controller response to send a file:
 *
 *   $this->response = new BinaryResponse($pdf, 'application/pdf', 'report.pdf');
 *   $this->response = BinaryResponse::fromFile('/path/report.pdf', 'application/pdf', null, true);
 *
 * It can be read like the array responses for its code ($response['code']), so routers and controllers that
 * read the code keep working.
 */
class BinaryResponse implements \ArrayAccess
{
    private $content;
    private $content_type;
    private $filename;
    private $inline;
    private $code;

    /**
     * @param string $content raw bytes
     * @param string $content_type e.g. application/pdf
     * @param string|null $filename name for Content-Disposition
     * @param bool $inline display it (inline) instead of downloading it (attachment)
     * @param int $code HTTP status code
     */
    public function __construct(
        string $content,
        string $content_type = 'application/octet-stream',
        ?string $filename = null,
        bool $inline = false,
        int $code = 200
    ) {
        $this->content = $content;
        $this->content_type = $content_type;
        $this->filename = $filename;
        $this->inline = $inline;
        $this->code = $code;
    }

    /**
     * @param string $path
     * @param string|null $content_type detected from the file when null
     * @param string|null $filename defaults to the file name
     * @param bool $inline
     * @return BinaryResponse
     * @throws \RuntimeException when the file can not be read
     */
    public static function fromFile(
        string $path,
        ?string $content_type = null,
        ?string $filename = null,
        bool $inline = false
    ): BinaryResponse {
        $content = is_file($path) ? file_get_contents($path) : false;
        if ($content === false) {
            throw new \RuntimeException("Can not read file: $path");
        }
        if (is_null($content_type)) {
            $content_type = (function_exists('mime_content_type') ? mime_content_type($path) : false)
                ?: 'application/octet-stream';
        }
        return new BinaryResponse($content, $content_type, $filename ?? basename($path), $inline);
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getContentType(): string
    {
        return $this->content_type;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @return string[] Content-Type, Content-Length and Content-Disposition headers
     */
    public function getHeaders(): array
    {
        $headers = [
            'Content-Type: ' . $this->content_type,
            'Content-Length: ' . strlen($this->content),
        ];
        $disposition = $this->inline ? 'inline' : 'attachment';
        if (!is_null($this->filename) && $this->filename !== '') {
            $fallback = str_replace(['"', '\\'], '', preg_replace('/[^\x20-\x7e]/', '_', $this->filename));
            $disposition .= '; filename="' . $fallback . '"; filename*=UTF-8\'\'' . rawurlencode($this->filename);
        }
        $headers[] = 'Content-Disposition: ' . $disposition;
        return $headers;
    }

    public function offsetExists($offset): bool
    {
        return $offset === 'code';
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return ($offset === 'code') ? $this->code : null;
    }

    public function offsetSet($offset, $value): void
    {
        if ($offset === 'code') {
            $this->code = (int)$value;
        }
    }

    public function offsetUnset($offset): void
    {
    }
}
