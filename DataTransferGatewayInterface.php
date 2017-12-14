<?php

namespace Vox\Data;

/**
 * @author Jhonatan Teixeira <jhonatan.teixeira@gmail.com>
 */
interface DataTransferGatewayInterface
{
    public function transferDataTo($fromObject, $toObject);
    
    public function transferDataFrom($fromObject, $toObject);
}
