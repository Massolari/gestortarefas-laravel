<?php

namespace App\Http\Controllers;

use App\Models\TasklistModel;
use App\Models\TaskModel;
use App\Models\UserModel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectReportController extends Controller
{
    /**
     * format task elapsed time form humans
     *
     * @param integer $seconds
     * @return void
     */
    private function formatTaskTime(int $seconds)
    {
        $hours = floor($seconds / 3600);
        $secondsLeft = $seconds % 3600;

        $minutes = floor($secondsLeft / 60);

        $seconds = $secondsLeft % 60;

        $minutes = $minutes < 10 ? "0$minutes" : $minutes;
        $hours = $hours < 10 ? "0$hours" : $hours;
        $seconds = $seconds < 10 ? "0$seconds" : $seconds;

        return "$hours:$seconds:$seconds";
    }

    /**
     * Download PDF of report
     *
     * @param Request $request
     * @return void
     */
    public function downloadPDF(Request $request)
    {
        $request->validate([
            "client_name" => "required|min:5",
        ]);

        $listIdOrNull = $request->input('list_id') ?? null;

        $tasks = TaskModel::where(
            'tasklist_id',
            $listIdOrNull,
        )->orderBy('created_at','desc')->get();

        $user = UserModel::find(Auth::user()->id);
        
        $totalTimeWorked = 0;

        foreach ($tasks as $task) {
            $totalTimeWorked += $task->elapsed_time;
        }

        $totalTimeWorked = $this->formatTaskTime($totalTimeWorked);
        
        foreach ($tasks as $task) {
            $task->elapsed_time = $this->formatTaskTime($task->elapsed_time);
            $task->status = $this->getStatusInPortuguesse($task->status);
        }

        $data = [
            'title' => 'Resumo do relatório',
            'client_name' => $request->input('client_name'),
            'list_id' => $request->input('list_id'),
            'project_description' => $request->input('project_description'),
            'tasks' => $tasks ?? [],
            'total_time_worked' => $totalTimeWorked,
            'username' => $user->name,
            'lastName' => $user->lastName,
        ];

        // Gera o PDF a partir da view e passa os dados
        $pdf = Pdf::loadView('pages.project_report_resume', $data);

        // Retorna o PDF para download
        return $pdf->download('relatorio_' . $request->input('client_name') . '.pdf');
    }

    /**
     * translate staus of tasks to portuguese
     *
     * @param string $status
     * @return void
     */
    private function getStatusInPortuguesse(string $status)
    {
        $status_collection = [
            'new' => 'Não iniciada',
            'in_progress' => 'Em progresso',
            'not_started' => 'Não concluída',
            'cancelled' => 'Cancelada',
            'completed' => 'Concluída',
        ];

        if (key_exists($status, $status_collection)) {
            return $status_collection[$status];
        } else {
            return 'Desconhecido';
        }
    }
}
