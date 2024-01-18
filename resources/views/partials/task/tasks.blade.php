<div class="container">

    <div class="container-fluid justify-content-center mt-5">

        <div class="row p-3 bg-dark shadow">

            <div class="col-12 col-lg-5 col-md-6 col-sm-6">
                <div class="row input-group justify-content-center ms-0 my-2 ">
                    <input type="text" name="text_search" id="text_search" class="col-md form-control "
                        placeholder="Pesquisar">
                    <button type="submit" class="col-auto btn btn-outline-primary" onclick="searchTasks()"><i
                            class="bi bi-search"></i></button>
                </div>
            </div>

            <div class="col-12 col-lg-5 col-md-3 col-sm-3">
                <select name="filter" id="filter" class="form-select my-2">
                    <option value="all" @php echo (!empty($filter) && $filter == 'all') ? 'selected' : '' @endphp>
                        Todos
                    </option>
                    <option value="new" @php echo (!empty($filter) && $filter == 'new') ? 'selected' : '' @endphp>
                        Novas
                    </option>
                    <option value="in_progress"
                        @php echo (!empty($filter) && $filter == 'in_progress') ? 'selected' : '' @endphp>Em progresso
                    </option>
                    <option value="cancelled"
                        @php echo (!empty($filter) && $filter == 'cancelled') ? 'selected' : '' @endphp>Canceladas
                    </option>
                    <option value="completed"
                        @php echo (!empty($filter) && $filter == 'completed') ? 'selected' : '' @endphp>Concluídas
                    </option>
                </select>
            </div>

            <div class="col-12 col-lg-2 col-md-3 col-sm-3">
                <button type="button" class="btn btn-outline-info my-2 w-100" data-bs-toggle="modal"
                    data-bs-target="#new_task">
                    <i class="bi bi-plus-circle"></i>
                    <span class="hidden-md">Nova</span>
                </button>

                @include('partials.task.form_task', [
                    'route' => 'task.new',
                    'modal_id' => 'new_task',
                    'form_title' => 'Nova Tarefa',
                    'task_id' => '',
                    'task_name' => '',
                    'task_description' => '',
                    'task_status' => '',
                ])
            </div>
        </div>
    </div>

    {{-- <div class="col text-end">
                    <a href="{{ route('task.new') }}" class="btn btn-primary"><i class="bi bi-plus-square me-2"></i>Nova
                        tarefa</a> --}}

    <div class="row">
        <div class="col">
            @if (count($tasks) != 0)
                <table class="table table-dark table-striped-columns w-100 shadow shadow-md" id="table_tasks">
                    <thead class="table-outline-dark">
                        <tr>
                            <th class="w-75 text-center">Tarefas</th>
                            <th class="w-20 text-center">Status</th>
                            <th class="text-center">Opções</th>
                        </tr>
                    </thead>
                    <tbody class="text-light table-group-divider">
                        @foreach ($tasks as $task)
                            <tr>
                                <td>
                                    <p class="task-title ms-2 mt-2 mb-0">{{ $task['task_name'] }}
                                    </p>
                                    <p class="opacity-75 ms-2">{{ $task['task_description'] }}</p>
                                </td>
                                <td class="text-center align-middle">
                                    <span
                                        class="{{ $task['task_status_style'] }} fs-6 shadow shadow-md">{{ $task['task_status'] }}</span>
                                </td>
                                <td class="text-center align-middle">

                                    <button type="button" class="btn btn-secondary m-2 shadow shadow-md"
                                        data-bs-toggle="modal" data-bs-target="#edit_task-{{ $task['task_id'] }}"><i
                                            class="bi bi-pencil"></i></button>

                                    <button type="button" class="btn btn-danger m-2 shadow shadow-md"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalDeleteConfirm-{{ $task['task_id'] }}"><i
                                            class="bi bi-trash"></i></button>
                                </td>
                            </tr>

                            {{-- modal delete --}}
                            <div class="modal fade" id="modalDeleteConfirm-{{ $task['task_id'] }}" tabindex="-1"
                                aria-labelledby="modalDeleteConfirm-{{ $task['task_id'] }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h1 class="modal-title fs-5" id="exampleModalLabel">Excluir tarefa</h1>
                                        </div>
                                        <div class="modal-body">
                                            <h4 class="text-info">{{ $task['task_name'] }}</h4>
                                            <p class="opacity-50">{{ $task['task_description'] }}</p>
                                            <p class="mt-5 text-center">Deseja excluir esta tarefa?</p>

                                            <div class="row mt-3 text-center">
                                                <hr>
                                                <div class="col">
                                                    <a href="{{ route('task.index') }}"
                                                        class="btn btn-secondary font shadow shadow-md"><i
                                                            class="bi bi-cancel me-2"
                                                            data-bs-dismiss="modal"></i>Cancelar</a>
                                                </div>
                                                <div class="col">
                                                    <a href="{{ route('task.delete', ['id' => Crypt::encrypt($task['task_id'])]) }}"
                                                        class="btn btn-danger shadow shadow-md"><i
                                                            class="bi bi-thrash me-2"
                                                            data-bs-dismiss="modal"></i>Confirmar</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @include('partials.task.form_task', [
                                'route' => 'task.edit',
                                'modal_id' => 'edit_task-' . $task['task_id'],
                                'form_title' => 'Editar Tarefa',
                                'task_id' => $task['task_id'],
                                'task_name' => $task['task_name'],
                                'task_description' => $task['task_description'],
                                'task_status' => $task['task_status'],
                            ])
                        @endforeach

                    </tbody>
                </table>
            @else
                <p class="text-center opacity-50 my-5">Não existem tarefas registradas</p>
            @endif
        </div>
    </div>
</div>

<script>
    // $(document).ready(function() {
    //     $('#table_tasks').DataTable({
    //         data: @json($tasks),
    //         columns: [{
    //                 data: 'task_name'
    //             },
    //             {
    //                 data: 'task_status',
    //                 className: 'text-center align-middle'
    //             },
    //             {
    //                 data: 'task_actions',
    //                 className: 'text-center align-middle'
    //             },
    //         ]
    //     })
    // })

    const filter = document.querySelector('#filter')

    filter.addEventListener('change', () => {
        window.location.href = `/${filter.value}`
    })

    const searchTasks = () => {
        const inputSearch = document.querySelector('#text_search')
        window.location.href = `/search/${inputSearch.value}`
    }
</script>
