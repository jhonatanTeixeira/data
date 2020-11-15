<?php


namespace Vox\Data\Formatter;


interface FromFormatInterface
{
    public function fromFormat($data, array &$context = []);
}