<?php


namespace Vox\Data;


interface ObjectExtractorInterface
{
    public function extract($object, array &$context = []);
}