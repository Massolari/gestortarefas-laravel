<!-- Modal do Cronômetro -->
<div class="modal fade" id="{{ $modal_id }}" tabindex="-1" aria-labelledby="{{ $modal_id }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="text-info"><strong>{{ $list_name }}</strong> - <em>{{ $task_name }}</em></h4>
                <button id="editTimer_{{ $modal_id }}" class="btn btn-secondary btn-lg"><i class="fas fa-pencil"></i></button>
            </div>
            <div class="p-3 modal-body">
                <div class="d-flex flex-column justify-content-center"">
                    <div class="d-flex justify-content-center">
                        <p id="timerDisplay_{{ $modal_id }}" class="fs-2">00:00:00</p> <!-- Exibição do cronômetro -->
                    </div>
                    <div id="messageContainer_{{ $modal_id }}"></div>
                    <div class="d-flex justify-content-center mb-4">
                        <div class="input-group w-50" id="updateTimeContainer_{{ $modal_id }}" style="display: none">
                            <input class="form-control" type="time" step="2" name="inputTimerDisplay_{{ $modal_id }}" id="inputTimerDisplay_{{ $modal_id }}">
                            <button  class="btn btn-success" id="updateTimeButton_{{ $modal_id }}">
                                <i class="fas fa-save"></i>
                            </button>
                            <button  class="btn btn-danger" id="cancelUpdateTimeButton_{{ $modal_id }}">
                                <i class="fas fa-cancel"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-center" role="group" aria-label="Timer Controls">
                    <div class="btn-group">
                        <button id="playButton_{{ $modal_id }}" class="btn btn-success btn-lg"><i class="fas fa-play"></i></button>
                        <button id="pauseButton_{{ $modal_id }}" class="btn btn-danger btn-lg" disabled><i class="fas fa-pause"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // A lógica agora será carregada apenas quando o modal for mostrado
    document.getElementById("{{ $modal_id }}").addEventListener('show.bs.modal', function () {
        const modalTaskId = "{{ $modal_id }}";
        const listName = "{{ $list_name }}"
        const listId = "{{ $list_id }}"
        const taskName = "{{ $task_name }}"
        const taskId = "{{ $task_id }}"

        const taskStatus = "task_status-{{ $task['id'] }}"

        const getTaskUrl = `{{ route('getTask') }}?task_id=${taskId}`
        const checkForStartedTaskUrl = `{{ route('checkForStartedTask') }}?task_id=${taskId}`
        const startTaskUrl = `{{ route('startTask') }}`
        const pauseTaskUrl = `{{ route('pauseTask') }}`
        const updateElapsedTimeTaskUrl = `{{ route('updateElapsedTime') }}`

        let elapsedTimeOfDisplay = document.getElementById(`timerDisplay_${modalTaskId}`)

        const playButton = document.getElementById("playButton_{{ $modal_id }}")
        const pauseButton = document.getElementById("pauseButton_{{ $modal_id }}")
        const editTimerButton = document.getElementById("editTimer_{{ $modal_id }}")
        const updateTimeContainer = document.getElementById("updateTimeContainer_{{ $modal_id }}")
        const inputTimerDisplay = document.getElementById("inputTimerDisplay_{{ $modal_id }}")
        const updateTimeButton = document.getElementById("updateTimeButton_{{ $modal_id }}")
        const cancelUpdateTimeButton = document.getElementById("cancelUpdateTimeButton_{{ $modal_id }}")

        const actualTaskRunningContainer = document.getElementById("actual_task_running_container")
        const actualTaskRunningMessage = document.getElementById("actual_task_running_message")

        const existsAnotherTaskErrorMessage = document.getElementById("messageContainer_{{ $modal_id }}")

        let timer

        function init() {
            axios.get(getTaskUrl, {'task_id': taskId}).then(response => {
                const startedAt = response.data.started_at
                const elapsedTime = response.data.elapsed_time
                const now = moment().utc().format('YYYY-MM-DD HH:mm:ss')
                const diffSeconds = diffOfDatesInSeconds(startedAt, now)
                const totalElapsedTime = diffSeconds + elapsedTime
                
                const taskSituation = startedAt ? "started" : "paused"

                updateDisplay(elapsedTime)

                if (startedAt) {
                    startTimer(totalElapsedTime)
                }

            }).catch(error => {
                console.error('Erro ao fazer requisição:', error.response ? error.response.data : error.message)
                console.error('Erro: ', error)
            })
        }
        
        // Função para atualizar o banco de dados com o tempo
        function updateStartedTask() {
            axios.post(startTaskUrl, { 
                    task_id: taskId,
                })
                .catch(error => {
                    console.error(error.response ? error.response.data : error.message)
                })
        }

        // Calcula a diferença entre duas datas e retorna ela em segundos
        function diffOfDatesInSeconds(startDate, endDate) {
            const dateObj1 = new Date(startDate)
            const dateObj2 = new Date(endDate)

            // Verifica se as datas foram corretamente criadas
            if (isNaN(dateObj1.getTime()) || isNaN(dateObj2.getTime())) {
                console.error("Erro: Uma das datas não é válida.")
                return null
            }

            // Calcula a diferença em milissegundos e converte para segundos
            const diffInMilliseconds = dateObj2 - dateObj1
            const diffInSeconds = Math.floor(diffInMilliseconds / 1000)

            return diffInSeconds
        }

        // Função para atualizar o display do cronômetro
        function updateDisplay(elapsedTime) {
            const hours = Math.floor(elapsedTime / 3600);
            const minutes = Math.floor((elapsedTime % 3600) / 60);
            const seconds = elapsedTime % 60;
            elapsedTimeOfDisplay.textContent = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`
        }

        // Função para verificar se algum outro cronômetro está em execução
        async function existsAnotherTaskIsStarted() {
            const response = await axios.get(checkForStartedTaskUrl)
            console.log(response.data.is_started)
            return response.data.is_started
        }

        // Função para iniciar a contagem do timer
        function startTimer(elapsedTime) {
            timer = setInterval(function() {
                elapsedTime++
                updateDisplay(elapsedTime)
            }, 1000);

            playButton.disabled = true
            editTimerButton.disabled = true
            pauseButton.disabled = false
        }

        // Função para iniciar o cronômetro
        async function startTask() {
            if (await existsAnotherTaskIsStarted()) {
                existsAnotherTaskErrorMessage.innerHTML = `<div id="messageError_{{ $modal_id }}">
                        <p class="text-center text-danger">
                            Existe uma tarefa já iniciada
                        </p>
                    </div>`
                return
            }

            const body = {'task_id': taskId} 
            const header = {'Content-Typpe': 'application/json'}

            axios.post(
                startTaskUrl, 
                body,
                header
            ).then(response => {
                const elapsedTime = response.data.elapsed_time

                startTimer(elapsedTime)
                buttonsHandler("started")
                taskMessageHandler("started")
                taskStatusHandler("started")

            }).catch(error => {
                console.error(error.response ? error.response.data : error.message)
            });
        }
        
        // Função para pausar o cronômetro
        function pauseTask() {
            const body = {'task_id': taskId} 
            const header = {'Content-Typpe': 'application;json'}

            axios.post(
                pauseTaskUrl, 
                body,
                header
            ).then(response => {
                clearInterval(timer)
                buttonsHandler("paused")
                taskMessageHandler("paused")
                taskStatusHandler("paused")
            }).catch(error => {
                console.error(error.response ? error.response.data : error.message)
            })
        }

        // Função para mostrar ou esconder a mensagem
        function taskMessageHandler(taskStatus) {
            
            if (taskStatus === "paused") {
                actualTaskRunningContainer.style.setProperty('display', 'none', 'important');
                return
            }

            actualTaskRunningMessage.innerHTML = `
                <a href=${listId ? "{{ route('taskWithList.search', $listIdGlobal) }}" : "{{ route('task.show') }}"} 
                    class="btn btn-success py-1 pe-3 rounded shadow" 
                    id="btnMessageRunningTask">
                    <i class="bi bi-stopwatch me-1"></i>
                    <em><strong>${taskName}</strong>${listId ? " da lista <strong> " + listName : ""}</strong></em>
                </a>`
            
            actualTaskRunningContainer.style.display = 'flex'
        }

        // Função para gerenciar os botões do modal
        function buttonsHandler(taskStatus) {
            playButton.disabled = taskStatus === "started" ? true : false
            editTimerButton.disabled = taskStatus === "started" ? true : false
            pauseButton.disabled = taskStatus === "started" ? false : true
        }

        // função para gerenciar os status
        function taskStatusHandler(taskStatus) {

            const isStarted = taskStatus === "started" ? true : false

            const classesToRemove = isStarted ? ["bg-primary", "bg-success", "bg-danger"] : ["bg-info", "bg-success", "bg-danger"]
            const classToAdd = isStarted ? "bg-info" : "bg-primary"
            const statusLabel = isStarted ? "Em progresso" : "Não iniciada"
                        
            document.getElementById("in_progress_{{ $task_id }}").disabled = isStarted ? false : true
            document.getElementById("in_progress_{{ $task_id }}").selected = isStarted ? true : false

            document.getElementById("not_started_{{ $task_id }}").selected = isStarted ? false : true
            document.getElementById("not_started_{{ $task_id }}").disabled = isStarted ? true : false

            document.getElementById("cancelled_{{ $task_id }}").disabled = isStarted ? true : false
            document.getElementById("completed_{{ $task_id }}").disabled = isStarted ? true : false

            document.getElementById("task_status-{{ $task['id'] }}").classList.remove(...classesToRemove)
            document.getElementById("task_status-{{ $task['id'] }}").classList.add(classToAdd)
            document.getElementById("task_status-{{ $task['id'] }}").innerHTML = statusLabel
        } 

        // Edição do tempo do cronomêtro       
        function editDisplayElapsedTime () {
            elapsedTimeOfDisplay.style.display = "none"
            updateTimeContainer.style.display = "flex"
            inputTimerDisplay.value = elapsedTimeOfDisplay.textContent
        }
        
        // Cancela a edição do cronomêtro
        function cancelEditDisplayElapsedTime () {
            elapsedTimeOfDisplay.style.display = "flex"
            updateTimeContainer.style.display = "none"
        }
        
        // Atualiza o tempo da tarefa com o tempo novo
        function submitEditedElapsedTimeOfDisplay() {
            if (inputTimerDisplay.value === "") {
                existsAnotherTaskErrorMessage.innerHTML = `<p id="messageError_{{ $modal_id }}" class="text-center text-danger">Valor informado é inválido!... ${timeLess}</p>`
            }

            let [h, m, s] = inputTimerDisplay.value.split(":")

            h = parseInt(h)
            m = parseInt(m)
            s = parseInt(s)

            let total = h * 60 *60 + m * 60 + s

            elapsedTime = total

            const body = {
                'task_id': taskId,
                'elapsed_time': elapsedTime
            }

            const head = {'Content-Type': 'application/json'}

            axios.post(updateElapsedTimeTaskUrl, body, head).then(response => {
                if (response.data.status === 'success') {
                    updateDisplay(elapsedTime)
                    elapsedTimeOfDisplay.style.display = "flex"
                    updateTimeContainer.style.display = "none"
                }
            }).catch(error => {
                console.error(error)
            })
        }

        // Adiciona os eventos aos botões
        playButton.addEventListener("click", startTask)
        pauseButton.addEventListener("click", pauseTask)
        updateTimeButton.addEventListener("click", submitEditedElapsedTimeOfDisplay)
        cancelUpdateTimeButton.addEventListener("click", cancelEditDisplayElapsedTime)
        editTimerButton.addEventListener("click", editDisplayElapsedTime)

        // Quando o modal for fechado, limpamos a lógica
        document.getElementById('{{ $modal_id }}').addEventListener('hidden.bs.modal', function () {
            clearInterval(timer)
            existsAnotherTaskErrorMessage.innerHTML = ''
        })

        // Inicializa o display do cronômetro
        init()
    });
</script>
