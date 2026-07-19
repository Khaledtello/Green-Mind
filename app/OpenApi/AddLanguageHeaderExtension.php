<?php

namespace App\OpenApi;

use Dedoc\Scramble\Extensions\OperationExtension;
use Dedoc\Scramble\Support\Generator\Operation;
use Dedoc\Scramble\Support\Generator\Parameter;
use Dedoc\Scramble\Support\Generator\Schema;
use Dedoc\Scramble\Support\Generator\Types\StringType;
use Dedoc\Scramble\Support\RouteInfo;

class AddLanguageHeaderExtension extends OperationExtension
{
    public function handle(Operation $operation, RouteInfo $routeInfo): void
    {
        $param = new Parameter('Accept-Language', 'header');
        $param->description('Response language (ar or en)');
        $param->required(false);
        $param->setSchema(Schema::fromType((new StringType())->default('ar'))); 
        
        $operation->parameters[] = $param;
    }
}