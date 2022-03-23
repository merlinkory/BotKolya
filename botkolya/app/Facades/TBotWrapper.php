<?php
namespace App\Facades;

use Illuminate\Support\Facades\Facade;
use App\Services\TBotWrapper as Wrapper;

class TBotWrapper extends Facade
{
    protected static function getFacadeAccessor() 
    { 
        return Wrapper::class; 
    }
}

