<?php

namespace App\Http\Controllers;

use App\Models\TasklistModel;
use App\Models\TaskModel;
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
            'name' => Auth::user()->name,
            'tasklists' => Tasklist::getLists(),
        ];

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

        return redirect()->route('tasklist.index');
    }

    public function deleteTasklist($id)
    {
        try {
            TaskModel::where('tasklist_id', $id)
                ->delete();

            TasklistModel::where('id', $id)
                ->delete();
        } catch (\Throwable $th) {
            throw $th;
        }

        return back();
    }

    private static function getLists()
    {
        // return UserModel::find(Auth::user()->id);
        $tasklists = [];
        $allTasklists = TasklistModel::where('user_id', Auth::user()->id)->get();

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
