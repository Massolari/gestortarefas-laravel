<?php

namespace App\Http\Controllers;

use App\Models\TasklistModel;
use App\Models\TaskModel;
use App\Models\UserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Tasklist extends Controller
{

    public function index()
    {

        if (!Auth::check()) {
            return redirect()->route('login');
        }

        return redirect()->route('tasklist');
    }

    public function lists()
    {
        $data = [
            'title' => 'Lista de tarefas',
            'datatables' => false,
            'user_name' => Auth::user()->name,
            'user_level' => Auth::user()->level,
            'user_experience' => Auth::user()->experience,
            'tasklists' => Tasklist::getLists(),
        ];

        // dd($data);

        return view('pages.tasklist', $data);
    }

    public function storeTasklist(Request $request)
    {
        $request->validate([
            'name' => 'required|min:3|max:200',
            'description' => 'required|min:3|max:1000',
        ], [
            'name.required' => 'O campo é obrigatório.',
            'name.min' => 'O campo deve ter no mínimo :min caracteres.',
            'name.max' => 'O campo deve ter no máximo :max caracteres.',
            'description.required' => 'O campo é obrigatório',
            'description.min' => 'O campo deve ter no mínimo :min caracteres.',
            'description.max' => 'O campo deve ter no máximo :max caracteres.',
        ]);

        // get form data
        $name = $request->input('name');
        $description = $request->input('description');

        // check if there is already another task with same name for the same user
        $tasklist = TasklistModel::where('user_id', Auth::user()->id)
            ->where('name', $name)
            ->whereNull('deleted_at')
            ->first();

        if ($tasklist) {
            return redirect()
                ->route('tasklist.index')
                ->withInput()
                ->with('tasklist_error', 'Já existe uma lista com este nome');
        }

        TasklistModel::create([
            'user_id' => Auth::user()->id,
            'name' => $name,
            'description' => $description,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $user = UserModel::where('id', Auth::user()->id)->first();
        UserModel::where('id', )->update([
            'list_created_count' => $user->list_created_count += 1,
        ]);

        return redirect()->route('tasklist.index');
    }

    public function editTasklist(Request $request)
    {
        $request->validate([
            'name' => 'required|min:3|max:200',
            'description' => 'required|min:3|max:1000',
        ], [
            'name.required' => 'O campo é obrigatório.',
            'name.min' => 'O campo deve ter no mínimo :min caracteres.',
            'name.max' => 'O campo deve ter no máximo :max caracteres.',
            'description.required' => 'O campo é obrigatório',
            'description.min' => 'O campo deve ter no mínimo :min caracteres.',
            'description.max' => 'O campo deve ter no máximo :max caracteres.',
        ]);

        // get form data
        $id = $request->get('id');
        $name = $request->get('name');
        $description = $request->get('description');

        // check if there is already another task with same name for the same user
        $tasklist = TasklistModel::where('user_id', Auth::user()->id)
            ->where('name', $name)
            ->whereNull('deleted_at')
            ->first();

        if ($tasklist) {
            return redirect()
                ->route('tasklist.index')
                ->withInput()
                ->with('tasklist_error', 'Já existe uma lista com este nome');
        }

        TasklistModel::where('id', $id)
            ->update([
                'user_id' => Auth::user()->id,
                'name' => $name,
                'description' => $description,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        $tasklist = Tasklist::getLists();

        return view('tasklist.index', $tasklist);
    }

    public function deleteTasklist($id)
    {
        try {
            TaskModel::where('tasklist_id', $id)
                ->delete();

            TasklistModel::where('id', $id)
                ->delete();

            $user = UserModel::where('id', Auth::user()->id)->first();
            UserModel::where('id', )->update([
                'list_created_count' => $user->list_created_count += 1,
            ]);

        } catch (\Throwable $th) {
            throw $th;
        }

        return back();
    }

    public function searchTasklist($search = null)
    {
        $tasklist = [];

        // get tasks
        if ($search) {
            $allTasklists = TasklistModel::where('user_id', Auth::user()->id)
                ->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%')
                        ->orderBy('created_at', 'DESC');
                })->whereNull('deleted_at')
                ->get();

            foreach ($allTasklists as $list) {

                $tasklist[] = [
                    'id' => $list['id'],
                    'name' => $list['name'],
                    'description' => $list['description'],
                ];
            }

        } else {
            $tasklist = Tasklist::getLists();
        }

        $data = [
            'title' => 'Listas de Tarefas',
            'datatables' => false,
            'user_name' => Auth::user()->name,
            'tasklists' => $tasklist,
        ];

        return view('pages.tasklist', $data);

    }

    private static function getLists()
    {
        // return UserModel::find(Auth::user()->id);
        $tasklists = [];
        $allTasklists = TasklistModel::where('user_id', Auth::user()->id)
            ->orderBy('created_at', 'DESC')->get();

        foreach ($allTasklists as $list) {
            $tasklists[] = [
                'id' => $list->id,
                'name' => $list->name,
                'description' => $list->description,
            ];
        }

        return $tasklists;
    }
}
