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

        if (Schema::hasColumns('tasks', ['started_at','elapsed_time'])) {

            $task = TaskModel::select(['name', 'tasklist_id'])
                ->whereNotNull('started_at')->first() ?? "";
            
            $list = !empty($task->tasklist_id)
                ? TasklistModel::select(['id', 'name'])
                    ->where('id', $task->tasklist_id)
                    ->first() 
                : "";

            $taskNameGlobal = $task->name ?? "";

            $listNameGlobal = !empty($task->tasklist_id)
                ? $list->name
                : false;

            $listIdGlobal = !empty($task->tasklist_id)
                ? $list->id
                : false;

            View::share([
                'taskNameGlobal' => $taskNameGlobal,
                'listNameGlobal' => $listNameGlobal,
                'listIdGlobal' => $listIdGlobal,
            ]);

            return;
        }

        View::share([
            'taskNameGlobal' => false,
            'listNameGlobal' => false,
            'listIdGlobal' => false,
        ]);
    }
}
