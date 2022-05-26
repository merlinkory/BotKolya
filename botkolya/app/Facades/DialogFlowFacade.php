<?php
namespace App\Facades;

use Illuminate\Support\Facades\Facade;
use App\Classes\DialogFlow;

class DialogFlowFacade extends Facade
{
    protected static function getFacadeAccessor() 
    { 
        return DialogFlow::class; 
    }
}
