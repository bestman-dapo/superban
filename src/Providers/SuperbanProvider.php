<?php

namespace Superban\Providers;

use Illuminate\Support\ServiceProvider;

class SuperbanProvider extends ServiceProvider
{
    protected $commands = [
        'Superban\Commands\SuperbanInitCommand'
    ];

    public function register(){
        $this->commands($this->commands);
    }
}