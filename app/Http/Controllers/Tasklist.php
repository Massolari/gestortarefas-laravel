<?php

namespace App\Http\Controllers;

use App\Models\TasklistModel;
use App\Models\TaskModel;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\isNull;

class Tasklist extends Controller
{
    /**
     * Get all task lists and format them as HTML select options
     * Used to populate task list dropdown in task forms
     *
     * @param int|null $task_id The ID of the task to get lists for
     * @return string HTML string containing select options for task lists
     */
    public function getTasklists(?int $task_id = null): string
    {
        $task = TaskModel::find($task_id);
        $allTasklists = TasklistModel::where('user_id', Auth::user()->id)->orderBy('created_at', 'DESC')->get();

        $noListOption = '<option value="null" selected >Sem lista.</option>';
        $options = '';

        foreach ($allTasklists as $list) {
            if ($task->tasklist_id === $list->id) {
                $options .= '<option value="' . $list->id . '" selected >' .  $list->name . '</option>';
            } else {
                $options .= '<option value="' . $list->id . '">' .  $list->name . '</option>';
            }
        }

        if (isNull($task)) {
            $options = $noListOption . $options;
        }

        return $options;
    }

    /**
     * Show all task lists for the authenticated user
     * Gets the user's task lists and calculates their level/experience
     * based on completed tasks
     * 
     * @return View
     */
    public function showTasklist(): View
    {
        if (!Auth::check()) return redirect()->route('login');

        $amountOfCompletedTasks = TaskModel::where('user_id', Auth::user()->id)
            ->where('status', 'completed')
            ->count();

        ['lvl' => $lvl, 'exp' => $exp] = Task::getLevelAndExp($amountOfCompletedTasks);

        $data = [
            'title' => 'Lista de tarefas',
            'user_name' => Auth::user()->name,
            'user_level' => $lvl,
            'user_experience' => $exp,
            'lists' => Tasklist::getLists(),
        ];

        return view('pages.tasklist', $data);
    }

    /**
     * Store a new task list
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function storeTasklist(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|unique:tasklists|min:3|max:200',
            'description' => 'nullable|max:1000',
        ], [
            'name.required' => 'O campo é obrigatório.',
            'name.unique' => 'A lista de tarefas já existe.',
            'name.min' => 'Mínimo :min caracteres.',
            'name.max' => 'Máximo :max caracteres.',
            'description.max' => 'Máximo :max caracteres.',
        ]);

        TasklistModel::create([
            'user_id' => Auth::user()->id,
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'created_at' => now(),
        ]);

        return redirect()->route('tasklist.show');
    }

    /**
     * Edit a task list
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function editTasklist(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'min:3|max:200',
            'description' => 'max:255|nullable',
        ], [
            'name.min' => 'O campo deve ter no mínimo :min caracteres.',
            'name.max' => 'O campo deve ter no máximo :max caracteres.',
            'description.max' => 'O campo deve ter no máximo :max caracteres.',
        ]);

        // get form data
        $id = $request->get('id');
        $name = $request->get('name');
        $description = $request->get('description');

        $tasklist = TasklistModel::find($id);
        $tasklist->name = $name;
        $tasklist->description = $description;
        $tasklist->updated_at = date('Y-m-d H:i:s');
        $tasklist->save();

        // $tasklists = Tasklist::getLists();

        return redirect()->route('tasklist.show');
    }

    /**
     * delete a list
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function deleteTasklist(int $id): RedirectResponse
    {
        TaskModel::where('tasklist_id', $id)->delete();
        TasklistModel::where('id', $id)->delete();
        return back();
    }

    /**
     * Search task lists
     *
     * @param string|null $search
     * @return View
     */
    public function searchTasklist(?string $search = null): View
    {
        $amountOfCompletedTasks = TaskModel::where('user_id', Auth::user()->id)
            ->where('status', 'completed')
            ->count();

        ['lvl' => $lvl, 'exp' => $exp] = Task::getLevelAndExp($amountOfCompletedTasks);

        if ($search) {
            $tasklists = TasklistModel::where('user_id', Auth::user()->id)
                ->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                        ->orWhere(DB::raw('lower(description)'), 'like', '%' . strtolower($search) . '%');
                })
                ->orderBy('created_at', 'DESC')
                ->whereNull('deleted_at')
                ->get()
                ->map(fn($list) => [
                    'id' => $list->id,
                    'name' => $list->name, 
                    'description' => $list->description
                ])
                ->toArray();
        } else {
            $tasklists = self::getLists();
        }

        return view('pages.tasklist', [
            'title' => 'Listas de Tarefas',
            'user_name' => Auth::user()->name,
            'lists' => $tasklists,
            'user_level' => $lvl,
            'user_experience' => $exp,
        ]);
    }
    /**
     * Get all task lists for the authenticated user
     *
     * @return array Returns array containing task lists with id, name and description
     */
    private static function getLists(): array
    {
        return TasklistModel::where('user_id', Auth::user()->id)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->map(fn($list) => [
                'id' => $list->id,
                'name' => $list->name,
                'description' => $list->description,
            ])
            ->toArray();
    }
}
