<?php

namespace App\Http\Controllers;

use App\Models\TasklistModel;
use App\Models\TaskModel;
use Exception;
use Illuminate\Console\View\Components\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Task extends Controller
{
    /**
     * user home route method
     *
     * @return void
     */
    public function userhome()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $amountOfCompletedTasks = TaskModel::where('status', 'completed')->count();

        ['lvl' => $lvl, 'exp' => $exp] = Task::getLevelAndExp($amountOfCompletedTasks);

        $tasks = TaskModel::where('user_id', Auth::user()->id)
            ->where('tasklist_id', null)
            ->orderBy('created_at', 'DESC')
            ->take(5)
            ->get();

        $lists = TasklistModel::where('user_id', Auth::user()->id)
            ->orderBy('created_at', 'DESC')
            ->take(15)
            ->get();

        $status = ["completed", "cancelled", "in_progress", "new", "total"];

        foreach ($lists as $list) {
            foreach ($status as $value) {

                if ($value == "total") {
                    $list[$value] = TaskModel::where('tasklist_id', $list->id)->count();
                } else {

                    $list[$value] = TaskModel::where('tasklist_id', $list->id)
                        ->where('status', $value)->count();
                }
            }
        }

        $data = [
            'title' => 'Página do Usuário',
            'user_id' => Auth::user()->id,
            'tasks' => $tasks,
            'lists' => $lists,
            'user_level' => $lvl,
            'user_experience' => $exp,
            'user_name' => Auth::user()->name,
        ];

        return view('pages.userhome', $data);
    }

    /**
     * Return view thats show tasks with list or without list
     *
     * @param integer|null|null $listId
     * @param string $filter
     * @return RedirectResponse
     */
    public function tasks(
        int|null $listId = null, 
        string $filter = 'all'
    ) {
        
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        
        $amountOfCompletedTasks = TaskModel::where('status', 'completed')->count();
        
        ['lvl' => $lvl, 'exp' => $exp] = Task::getLevelAndExp($amountOfCompletedTasks);
        
        $tasks = Task::getTasksBySearch(userId: Auth::user()->id, listId: $listId, search: $filter);

        $data = [
            'title' => 'Minhas Tarefas',
            'tasks' => $tasks,
            'filter' => $filter,
            'user_id' => Auth::user()->id,
            'list_name' => 'Tarefas sem lista',
            'list_id' => $listId ?? null,
            'user_level' => $lvl,
            'user_experience' => $exp,
            'user_name' => Auth::user()->name,
        ];
        
        return view('pages.user.tasks', $data);
    }

    /**
     * Store a new task
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function newTask(
        Request $request, 
        $list_id = null
    ): RedirectResponse {
        $request->validate([
            'name' => 'required|unique:tasks|min:3|max:200',
            'description' => 'max:1000',
        ], [
            'name.required' => 'O campo é obrigatório.',
            'name.unique' => 'Já existe uma tarefa com este nome.',
            'name.min' => 'O campo deve ter no mínimo :min caracteres.',
            'name.max' => 'O campo deve ter no máximo :max caracteres.',
            'description.max' => 'O campo deve ter no máximo :max caracteres.',
        ]);

        TaskModel::create([
            'tasklist_id' => $request->input('list_choice'),
            'name' => $request->input('name'),
            'user_id' => Auth::user()->id,
            'description' => $request->input('description'),
            'status' => 'new',
            'commentary' => null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return back();
    }

    /**
     * edit a task
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function editTask(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|min:3|max:200',
            'description' => 'max:1000',
        ], [
            'name.required' => 'O campo é obrigatório.',
            'name.min' => 'O campo deve ter no mínimo :min caracteres.',
            'name.max' => 'O campo deve ter no máximo :max caracteres.',
            'description.max' => 'O campo deve ter no máximo :max caracteres.',
        ]);
        
        $list_id = $request->input('list_choice');

        $list_id = $list_id === 'null'
            ? null 
            : $request->input('list_choice');

        // update the task
        TaskModel::where('id', $request->input('task_id'))
            ->update([
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'status' => $request->input('status'),
                'tasklist_id' =>  $list_id,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        return back();
    }

    /**
     * delete task without list
     *
     * @param $id
     * @return void
     */
    public function deleteTask(int $taskId): RedirectResponse
    {
        try {
            TaskModel::find($taskId)->delete();
        } catch (Exception $e) {
            return redirect()->route('task.index');
        }

        return back();
    }

    /**
     * search task based a text
     *
     * @param string $search
     * @return RedirectResponse
     */
    public function searchTask(
        $listId = null, 
        $search = 'all'
    ) {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!empty($listId)) {
            $tasklist = TasklistModel::where('id', $listId)->first();
        }

        $tasks = Task::getTasksBySearch(userId: Auth::user()->id, listId: $listId, search: $search);

        ['lvl' => $lvl, 'exp' => $exp] = Task::getLevelAndExp(Task::getCompletedTasks());

        $data = [
            'title' => 'Minhas Tarefas',
            'tasks' => $tasks,
            'filter' => $search,
            'user_id' => Auth::user()->id,
            'list_id' => $listId,
            'list_name' => $tasklist->name,
            'list_description' => $tasklist->description,
            'user_level' => $lvl,
            'user_experience' => $exp,
            'user_name' => Auth::user()->name,
        ];

        return $listId 
            ? view('pages.user.tasksWithList', $data)
            : view('pages.user.tasks', $data);
    }


    /**
     * search task based a text
     *
     * @param string $search
     * @return RedirectResponse
     */
    public function filterTask(
        $listId = null, 
        $filter = 'all'
    ): RedirectResponse {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $tasklist = TasklistModel::where('id', $listId)->first();
        $tasks = Task::getTaskByFilter($listId, $filter);

        ['lvl' => $lvl, 'exp' => $exp] = Task::getLevelAndExp(Task::getCompletedTasks());

        $data = [
            'title' => 'Minhas Tarefas',
            'tasks' => $tasks,
            'filter' => $filter,
            'user_id' => Auth::user()->id,
            'list_id' => $listId,
            'list_name' => $tasklist->name,
            'list_description' => $tasklist->description,
            'user_level' => $lvl,
            'user_experience' => $exp,
            'user_name' => Auth::user()->name,
        ];

        return !empty($taskId) 
            ? view('pages.user.tasksWithList', $data)
            : view('pages.user.tasks', $data);
    }

    /**
     * update commentary of task
     *
     * @param $taskId
     * @param Request $request
     * @return RedirectResponse
     */
    public function setCommentaryTask(
        int $taskId, 
        Request $request
    ): RedirectResponse {
        $newCommentary = $request->input('commentary');

        try {
            TaskModel::where('id', $taskId)->update([
                'commentary' => $newCommentary,
            ]);
        } catch (\Throwable $th) {
            $th->getMessage();
            dd($taskId);
        }

        return back();
    }


    /**
     * search tasks based in text
     *
     * @param $tasklist_id
     * @param string $filter
     * @return array
     */
    private static function getTaskByFilter(
        int|null $tasklist_id = null, 
        string $filter = 'all'
    ): array {
        $tasks = [];
        $allTasks = [];

        ['lvl' => $lvl, 'exp' => $exp] = Task::getLevelAndExp(Task::getCompletedTasks());

        if ($filter != 'all') {
            $allTasks = TaskModel::where('tasklist_id', $tasklist_id)
                ->where('user_id', Auth::user()->id)
                ->where('status', $filter)
                ->orderBy('created_at', 'DESC')
                ->whereNull('deleted_at')
                ->get();
        } else {
            $allTasks = TaskModel::where('tasklist_id', $tasklist_id)
                ->where('user_id', Auth::user()->id)
                ->orderBy('created_at', 'DESC')
                ->whereNull('deleted_at')
                ->get();
        }

        foreach ($allTasks as $task) {

            $tasks[] = [
                'id' => $task->id,
                'name' => $task->name,
                'description' => $task->description,
                'status' => Task::statusName($task->status),
                'status_style' => Task::statusBadge($task->status),
                'tasklist_id' => $task->tasklist_id,
                'commentary' => $task->commentary,
                'user_id' => Auth::user()->id,
            ];
        }

        return $tasks;
    }

    /**
     * get tasks based on search
     *
     * @param int|null $userId
     * @param int|null $listId
     * @param string $search
     * @return array
     */
    private static function getTasksBySearch(
        int|null $userId = null, 
        int|null $listId = null, 
        string $search = 'all'
    ): array {
        $query = TaskModel::where('user_id', $userId)
            ->where('tasklist_id', $listId)
            ->orderBy('created_at', 'DESC')
                ->whereNull('deleted_at')->get();

        if ($search !== 'all') {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $tasks = array_map(function ($task) {
            return [
                'id' => $task['id'],
                'name' => $task['name'],
                'description' => $task['description'],
                'status' => Task::statusName($task['status']),
                'status_style' => Task::statusBadge($task['status']),
                'tasklist_id' => $task['tasklist_id'],
                'user_id' => $task['user_id'],
                'commentary' => $task['commentary'],
            ];
        }, $query->toArray());

        return $tasks;
    }

    /**
     * get status name in portuguese
     *
     * @param string $status
     * @return string
     */
    private static function statusName(string $status): string
    {
        $status_collection = [
            'all' => 'Minhas Tarefas',
            'new' => 'Nova',
            'in_progress' => 'Em progresso',
            'not_started' => 'Não iniciada',
            'cancelled' => 'Cancelada',
            'completed' => 'Concluída',
        ];

        if (key_exists($status, $status_collection)) {
            return $status_collection[$status];
        }

        return "Desconhecido";
    }

    /**
     * get bootstrap class name based on status
     *
     * @param string $status
     * @return string
     */
    private static function statusBadge(string $status): string
    {
        $status_collection = [
            'new' => 'badge bg-success',
            'in_progress' => 'badge bg-info',
            'not_started' => 'badge bg-primary',
            'cancelled' => 'badge bg-danger',
            'completed' => 'badge bg-secondary',
        ];

        if (key_exists($status, $status_collection)) {
            return $status_collection[$status];
        }

        return "Desconhecido";
    }

    /**
     * get level and experience based on completed tasks
     *
     * @param integer $completedTasksAmount
     * @return array
     */
    static function getLevelAndExp(int $completedTasksAmount): array
    {
        $lvl = (int) ($completedTasksAmount / 100) + 1;
        $exp = $completedTasksAmount % 100;

        return ['lvl' => $lvl, 'exp' => $exp];
    }

    /**
     * get amount of completed tasks of user
     *
     * @return integer
     */
    static function getCompletedTasks(): int
    {
        return TaskModel::where('user_id', Auth::user()->id)
            ->where('status', 'completed')
            ->count();
    }
}
