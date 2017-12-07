<?php

namespace Vox\Data;

interface DataTransferGatewayInterface
{
    public function transferDataTo($fromObject, $toObject);
    
    public function transferDataFrom($fromObject, $toObject);
}
