<?php

namespace App\Services\Contracts;

interface PrescriptionExtractor
{
    /**
     * Read a stored prescription file and return UNVERIFIED suggestions.
     *
     * @return array<int,array{name:string,strength?:string,form?:string,quantity?:int}>
     */
    public function extract(string $absolutePath, string $mimeType): array;
}
