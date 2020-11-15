<?php


namespace Vox\Data\Formatter;


interface ToFormatInterface
{
    public function toFormat($data, array &$context = []);
}