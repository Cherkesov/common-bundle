<?php
namespace GFB\CommonBundle\Media\HttpFoundation;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class HandledFile extends UploadedFile
{
    /**
     * @param string $path
     */
    public function __construct($path)
    {
        return parent::__construct($path, basename($path), null, null, null, true);
    }
}