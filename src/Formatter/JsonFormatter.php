<?php


namespace Vox\Data\Formatter;


class JsonFormatter implements ToFormatInterface, FromFormatInterface, FormatAwareInterface
{

    public function getFormatName(): string
    {
        return 'json';
    }

    public function toFormat($data, array &$context = [])
    {
        return json_encode($data, $context['jsonOptions'] ?? 0);
    }

    public function fromFormat($data, array &$context = [])
    {
        return json_decode($data, true, $context['depth'] ?? 512, $context['jsonOptions'] ?? 0);
    }
}