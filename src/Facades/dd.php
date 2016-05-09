<?php 
namespace Woldy\ddsdk\Facades;
use Illuminate\Support\Facades\Facade;
class dd extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'dd';
    }
}