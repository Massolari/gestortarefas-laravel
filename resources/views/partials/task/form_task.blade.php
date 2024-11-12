{{-- modal edit --}}
<div class="modal fade" id="{{ $modal_id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 text-info" id="taskEditTitle">{{ $form_title }}</h1>
            </div>
            <div class="p-3 modal-body">
                <form action="{{ route($route) }}" method="POST" id="form-new-edit-post">
                    @csrf
                    <input type="hidden" name="task_id" value="{{ $task_id ?? null }}">
                    {{-- task name --}}
                    <div class="mb-3 form-floating">
                        <input type="text" name="name" id="name" class="mb-2 form-control"
                            placeholder="Nome da tarefa" value="{{ old('name', $name) }}">
                        <label for="name" class="form-label">Nome da
                            Tarefa</label>
                        @error('name')
                            <div class="text-warning">
                                {{ $errors->get('name')[0] }}
                            </div>
                            <script>
                                document.addEventListener("DOMContentLoaded", (event) => {
                                    const myModal = new bootstrap.Modal({{ $modal_id }}, {
                                        keyboard: true,
                                        dispose: true
                                    })
                                    myModal.show({{ $modal_id }})
                                })
                            </script>
                        @enderror
                    </div>

                    {{-- task description --}}
                    <div class="mb-3 form-floating">
                        <textarea name="description" id="description" style="height: 250px" class="pt-4 form-control"
                            placeholder="Conteúdo da tarefa">{{ old('description', $description) }}</textarea>
                        <label for="description" class="form-label">Descrição da Tarefa</label>
                        @error('description')
                            <div class="text-warning">{{ $errors->get('description')[0] }}</div>
                            <script>
                                document.addEventListener("DOMContentLoaded", (event) => {
                                    const myModal = new bootstrap.Modal({{ $modal_id }}, {
                                        keyboard: true,
                                        dispose: true
                                    })
                                    myModal.show({{ $modal_id }})
                                })
                            </script>
                        @enderror
                    </div>
                    {{-- task list choice --}}

                    {{-- alimentando a lista de tarefas --}}
                    <div hx-get="{{ route('tasklist.get', $task_id) }}" hx-trigger="load"
                        hx-target="#get-lists-response-{{ $task_id }}" hx-swap="innerHTML">

                        <label for="get-lists-response-{{ $task_id }}">Escolha uma lista:</label>

                        <input type="hidden" name="list_choice" value="{{ $list_id ?? null }}">

                        @if ($status === 'new')
                            @if (empty($list_id))
                                <select class="form-select" disabled name="list_choice">
                                    <option selected>
                                        Sem lista.
                                    </option>
                                </select>
                            @else
                                <select class="form-select" name="list_choice" disabled>
                                    <option value="{{ $list_id }}" selected>
                                        {{ $list_name }}
                                    </option>
                                </select>
                            @endif
                        @else
                            <select class="form-select" id="get-lists-response-{{ $task_id }}" name="list_choice"></select>
                        @endif
                        
                    </div>


                    {{-- task status --}}
                    <label class="mt-3" for="status">Escolha um status:</label>
                    @if ($type == 'edit')

                        <input type="hidden" name="status" id="status_hidden" value="{{ old('status', $status) }}">
                        <div>
                            <select name="status" id="status" class="form-select">
                                <option value="new" {{ old('status', $status) == 'Nova' ? 'selected' : '' }} disabled>
                                    Nova
                                </option>
                                
                                <option value="in_progress" 
                                        id="in_progress_{{ $task_id }}"
                                        {{ old('status', $status) == 'Em progresso' ? 'selected' : '' }} 
                                        {{ $status == 'Em progresso' ? '' : 'disabled' }}
                                >Em progresso</option>
                                
                                <option value="not_started" 
                                        id="not_started_{{ $task_id }}"
                                        {{ old('status', $status) == 'Não iniciada' ? 'selected' : '' }} 
                                        {{ $status == 'Em progresso' ? 'disabled' : '' }}
                                >Não iniciada</option>
                                
                                <option value="cancelled" 
                                        id="cancelled_{{ $task_id }}"
                                        {{ old('status', $status) == 'Cancelada' ? 'selected' : '' }} 
                                        {{ $status == 'Em progresso' || $status == 'Concluída' ? 'disabled' : '' }}
                                >Cancelada</option>
                                
                                <option value="completed" 
                                        id="completed_{{ $task_id }}"
                                        {{ old('status', $status) == 'Concluída' ? 'selected' : '' }} 
                                        {{ $status == 'Em progresso' || $status == 'Cancelada' ? 'disabled' : '' }}
                                >Concluída</option>
                            </select>
                    
                            @error('status')
                                <div class="text-warning">{{ $errors->get('status')[0] }}</div>
                            @enderror
                        </div>
                    @else
                        <div>
                            <select name="status" id="status" class="form-select" disabled>
                                <option value="new" selected>
                                    Nova
                                </option>
                            </select>
                        </div>
                    @endif

                    <hr class="mt-3">

                    {{-- cancel or submit --}}
                    <div class="d-flex justify-content-end">
                        <button type="button" class="mx-2 shadow shadow-md btn btn-secondary"
                            title="Cancele as mudanças" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-2"></i>
                            Cancelar
                        </button>
                        <button type="submit" class="shadow shadow-md btn btn-success" title="Guarde as mudanças">
                            <i class="bi bi-floppy me-2"></i>
                            Guardar
                        </button>
                    </div>
                </form>


                @if (session()->has('task_error'))
                    <div class="p-1 text-center alert alert-danger"> {{ session()->get('task_error') }} </div>
                    <script>
                        document.addEventListener("DOMContentLoaded", (event) => {
                            const myModal = new bootstrap.Modal({{ $modal_id }}, {
                                keyboard: true,
                                dispose: true
                            })
                            myModal.show({{ $modal_id }})
                        })
                    </script>
                @endif

            </div>
        </div>
    </div>
</div>
