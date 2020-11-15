<?php

namespace Vox\Data;

interface TypeAwareObjectExtractor extends ObjectExtractorInterface
{
    public function getSupportedClassName(): string;
}