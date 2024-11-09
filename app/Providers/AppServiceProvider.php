<?php

namespace App\Providers;

use App\Models\TasklistModel;
use App\Models\TaskModel;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(UrlGenerator $url): void
    {
        if (env('APP_ENV') == 'production') {
            $url->forceScheme('https');
        }

        if (Schema::hasColumns('tasks', ['is_running','elapsed_time'])) {

            $task = TaskModel::select(['name', 'tasklist_id'])
                ->where('is_running', '1')->first() ?? "";
            
            $list = !empty($task->tasklist_id)
                ? TasklistModel::select('name')
                    ->where('id', $task->tasklist_id)
                    ->first() 
                : "";

            $taskNameGlobal = $task->name ?? "";
            $listNameGlobal = $list->name ?? "";

            View::share([
                'taskNameGlobal' => $taskNameGlobal,
                'listNameGlobal' => $listNameGlobal,
            ]);

            return;
        }

        View::share([
            'taskNameGlobal' => "",
            'listNameGlobal' => "",
        ]);
    }
}
