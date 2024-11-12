<?php

namespace App\Http\Controllers;

use App\Models\TasklistModel;
use App\Models\TaskModel;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TaskApi extends Controller
{
    /**
     * UTC format used on this case
     *
     * @var string
     */
    private $defaultDatetime = 'Y-m-d H:i:s';
    
    /**
     * Set task to started
     *
     * @param Request $request
     * @return void
     */
    public function startTask(Request $request)
    {
        $request->validate([
            'task_id' => 'required|integer|exists:tasks,id',
        ]);

        $task = TaskModel::find($request->input('task_id'));

        if (!$task) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tarefa não encontrada.',
            ], 404);
        }

        $list = TasklistModel::select('name')
            ->where('id', $task->tasklist_id)
            ->first();

        $currentTime = Carbon::now()->format($this->defaultDatetime);
        $startedAt = Carbon::parse($task->started_at);
        $diffInSeconds = $startedAt->diffInSeconds($currentTime) + $task->elapsed_time;

        $task->status = 'in_progress';
        $task->started_at = $currentTime;
        $task->elapsed_time = $diffInSeconds;
        $task->save();

        return response()->json([
            'status' => 'success',
            'message' => 'tarefa iniciada',
            'started_at' => $task->started_at,
            'elapsed_time' => $diffInSeconds,
            'task_name' => $task->name ?? "",
            'list_name' => $list->name ?? "sem lista",
        ], 201);
    }

    /**
     * Set task to not started
     *
     * @param Request $request
     * @return void
     */
    public function pauseTask(Request $request)
    {
        $request->validate([
            'task_id' => 'required|integer|exists:tasks,id',
        ]);

        $task = TaskModel::find($request->input('task_id'));

        if (!$task) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tarefa não encontrada.',
            ], 404);
        }

        $startedAt = Carbon::parse($task->started_at);
        $now = Carbon::now()->format($this->defaultDatetime);
        $diffInSeconds = $startedAt->diffInSeconds($now) + $task->elapsed_time;

        TaskModel::where('id', $task->id)->update([
            'status' => 'not_started',
            'started_at' => null,
            'elapsed_time' => $diffInSeconds,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'tarefa pausada',
            'elapsed_time' => $diffInSeconds,
        ], 201);
    }

    /**
     * Get task
     *
     * @param Request $request
     * @return void
     */
    public function getTask(Request $request)
    {
        $request->validate([
            'task_id' => 'required|integer|exists:tasks,id',
        ]);

        $task = TaskModel::find($request->input('task_id'));

        if (!$task) {
            return response()->json([
                'status' => 'error',
                'message' => 'task is not exist on database',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'elapsed_time' => $task->elapsed_time,
            'started_at' => $task->started_at,
        ], 200);
    }

    /**
     * Check is exista a task started
     *
     * @return void
     */
    public function checkForStartedTask()
    {
        $task = TaskModel::whereNotNull('started_at')
            ->whereNull('deleted_at')
            ->first();

        $isStarted = !empty($task)
            ? true
            : false;

        return response()->json([
            'is_started' => $isStarted,
            'status' => 'success',
        ], 200);
    }

    /**
     * Update elapsed time of task
     *
     * @param Request $request
     * @return void
     */
    public function updateElapsedTime(Request $request)
    {
        $request->validate([
            'elapsed_time' => 'required|integer',
            'task_id' => 'required|integer|exists:tasks,id',
        ]);

        TaskModel::where('id', $request->input('task_id'))->update([
            'elapsed_time'=> $request->input('elapsed_time'),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Tarefa atualizada com sucesso',
        ], 200);
    }
}
