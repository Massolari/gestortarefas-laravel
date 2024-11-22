// @ts-check

// Declarando variáveis globais
/** @type {any} */
// @ts-ignore
const axios = window.axios;

/** @type {any} */
// @ts-ignore
const moment = window.moment;


// @ts-ignore
import { createApp } from "vue";

/**
 * @typedef {Object} Task
 * @property {number} id - Task ID
 * @property {string} name - Task name
 * @property {string} description - Task description
 * @property {string} status - Task status
 * @property {string} status_style - Task status style
 * @property {number|null} tasklist_id - ID of the tasklist this task belongs to
 * @property {number} user_id - ID of task owner
 * @property {string} commentary - Task commentary
 */

/**
 * @typedef {Object} Time
 * @property {number} elapsed_time - The total elapsed time in seconds
 * @property {string|null} started_at - ISO datetime string when timer was started, or null if not started
 */

const actualTaskRunningMessage = document.getElementById(
    "actual_task_running_message"
);
const actualTaskRunningContainer = document.getElementById(
    "actual_task_running_container"
);

const existsAnotherTaskErrorMessage = "Existe uma tarefa já iniciada";
const invalidTimeErrorMessage = "Valor informado é inválido!";

/**
 * @param {number} elapsedTime
 * @returns {string}
 */
function formatElapsedTime(elapsedTime) {
    const hours = Math.floor(elapsedTime / 3600);
    const minutes = Math.floor((elapsedTime % 3600) / 60);
    const seconds = elapsedTime % 60;
    return `${String(hours).padStart(2, "0")}:${String(minutes).padStart(
        2,
        "0"
    )}:${String(seconds).padStart(2, "0")}`;
}

/**
 * @param {string} startDate
 * @param {string} endDate
 * @returns {number}
 */
function diffOfDatesInSeconds(startDate, endDate) {
    const dateObj1 = new Date(startDate);
    const dateObj2 = new Date(endDate);

    // Verifica se as datas foram corretamente criadas
    if (isNaN(dateObj1.getTime()) || isNaN(dateObj2.getTime())) {
        console.error("Erro: Uma das datas não é válida.");
        return 0;
    }

    // Calcula a diferença em milissegundos e converte para segundos
    const diffInMilliseconds = dateObj2.getTime() - dateObj1.getTime();
    const diffInSeconds = Math.floor(diffInMilliseconds / 1000);

    return diffInSeconds;
}

/**
 * @param {Time} time
 * @returns {number}
 */
function calculateElapsedTime(time) {
    const now = moment().utc().format("YYYY-MM-DD HH:mm:ss");
    if (!time.started_at) {
        return time.elapsed_time;
    }
    const diffSeconds = diffOfDatesInSeconds(time.started_at, now);
    return diffSeconds + time.elapsed_time;
}

/**
 * @param {string} clock
 * @returns {number}
 */
function clockToSeconds(clock) {
    const [h, m, s] = clock.split(":");
    return parseInt(h) * 60 * 60 + parseInt(m) * 60 + parseInt(s);
}

/**
 * @type {Task}
 */
const emptyTask = {
    id: -1,
    name: "",
    description: "",
    status: "",
    status_style: "",
    tasklist_id: null,
    user_id: -1,
    commentary: "",
};

/**
 * @type {Time}
 */
const emptyTime = {
    elapsed_time: 0,
    started_at: null,
};

/**
 * @typedef {Object} State
 * @property {Task} task - The current task object
 * @property {Time} time - Time tracking object
 * @property {string} clock - Current time display in HH:mm:ss format
 * @property {number|undefined|NodeJS.Timeout} timer - Timer interval ID
 * @property {string} errorMessage - Error message to display
 * @property {boolean} isEditing - Whether clock is in edit mode
 * @property {string} clockInput - Input value when editing clock
 */

/**
 * @typedef {Object} Methods
 * @property {(task: Task) => void} openModal
 * @property {() => void} startTimer
 * @property {() => Promise<void>} startTask
 * @property {() => Promise<boolean>} existsAnotherTaskIsStarted
 * @property {() => void} pauseTask
 * @property {() => void} editClock
 * @property {() => void} submitEditedElapsedTimeOfDisplay
 * @property {() => void} cancelEditDisplayElapsedTime
 * @property {() => void} reset
 */

/**
 * @param {Object} options
 * @param {Object} options.endpoint
 * @param {string} options.listName
 */
export const main = ({ endpoint, listName }) => {
    const app = createApp({
        /**
         * @returns {State}
         */
        data() {
            return {
                task: emptyTask,
                time: emptyTime,
                clock: "00:00:00",
                timer: undefined,
                errorMessage: "",
                isEditing: false,
                clockInput: "00:00:00",
            };
        },
        /**
         * @type {Methods}
         */
        methods: {
            /**
             * @param {Task} task
             * @this {State & Methods}
             */
            openModal(task) {
                this.task = task;

                axios
                    .get(endpoint.getTaskTime(task.id))
                    .then(({ data: time }) => {
                        this.time = time;

                        const elapsedTime = time.started_at
                            ? (this.startTimer(),
                              calculateElapsedTime(this.time))
                            : time.elapsed_time;

                        this.clock = formatElapsedTime(elapsedTime);
                    })
                    .catch((error) => {
                        console.error(
                            "Erro ao fazer requisição:",
                            error.response ? error.response.data : error.message
                        );
                        console.error("Erro: ", error);
                    });
            },
            /**
             * @this {State & Methods}
             */
            startTimer() {
                this.timer = setInterval(() => {
                    this.clock = formatElapsedTime(
                        calculateElapsedTime(this.time)
                    );
                }, 1000);
            },
            /**
             * @this {State & Methods}
             */
            async startTask() {
                this.errorMessage = "";
                if (await this.existsAnotherTaskIsStarted()) {
                    this.errorMessage = existsAnotherTaskErrorMessage;
                    return;
                }

                const body = { task_id: this.task.id };
                const header = { "Content-Typpe": "application/json" };

                axios
                    .post(endpoint.startTask, body, header)
                    .then(({ data: time }) => {
                        this.time = time;
                        this.startTimer();

                        if (actualTaskRunningMessage) {
                            actualTaskRunningMessage.innerHTML = `
                            <a href=${
                                this.task.tasklist_id
                                    ? endpoint.taskWithListSearch
                                    : endpoint.taskShow
                            } 
                        class="btn btn-success py-1 pe-3 rounded shadow" 
                        id="btnMessageRunningTask">
                        <i class="bi bi-stopwatch me-1"></i>
                        <em><strong>${this.task.name}</strong>${
                                this.task.tasklist_id
                                    ? " da lista <strong> " + listName
                                    : ""
                            }</strong></em>
                    </a>`;

                            if (actualTaskRunningContainer) {
                                actualTaskRunningContainer.classList.add(
                                    "d-flex"
                                );
                            }
                        }
                    })
                    .catch((error) => {
                        console.error(
                            error.response ? error.response.data : error.message
                        );
                    });
            },
            /**
             * @this {State & Methods}
             * @returns {Promise<boolean>}
             */
            async existsAnotherTaskIsStarted() {
                try {
                    const response = await axios.get(
                        endpoint.checkForStartedTask(this.task.id)
                    );
                    return response.data.is_started;
                } catch (error) {
                    console.error(
                        error.response ? error.response.data : error.message
                    );
                    return true;
                }
            },
            /**
             * @this {State & Methods}
             */
            pauseTask() {
                const body = { task_id: this.task.id };
                const header = { "Content-Typpe": "application;json" };

                axios
                    .post(endpoint.pauseTask, body, header)
                    .then(({ data: time }) => {
                        this.time = time;
                        clearInterval(this.timer);
                        this.timer = undefined;
                        if (actualTaskRunningContainer) {
                            actualTaskRunningContainer.classList.add("d-none");
                        }
                    })
                    .catch((error) => {
                        console.error(
                            error.response ? error.response.data : error.message
                        );
                    });
            },
            /**
             * @this {State & Methods}
             */
            editClock() {
                this.errorMessage = "";
                this.isEditing = true;
                this.clockInput = this.clock;
            },
            /**
             * @this {State & Methods}
             */
            submitEditedElapsedTimeOfDisplay() {
                this.errorMessage = "";
                if (this.clockInput === "") {
                    this.errorMessage = invalidTimeErrorMessage;
                    return;
                }
                const total = clockToSeconds(this.clockInput);

                const body = {
                    task_id: this.task.id,
                    elapsed_time: total,
                };

                const head = { "Content-Type": "application/json" };

                axios
                    .post(endpoint.updateElapsedTimeTask, body, head)
                    .then((response) => {
                        if (response.data.status === "success") {
                            this.clock = formatElapsedTime(total);
                            this.isEditing = false;
                        }
                    })
                    .catch((error) => {
                        console.error(error);
                    });
            },
            /**
             * @this {State & Methods}
             */
            cancelEditDisplayElapsedTime() {
                this.isEditing = false;
                this.errorMessage = "";
            },
            /**
             * @this {State & Methods}
             */
            reset() {
                clearInterval(this.timer);
                this.timer = undefined;
                this.clock = "00:00:00";
                this.task = emptyTask;
                this.time = emptyTime;
                this.errorMessage = "";
                this.isEditing = false;
            },
        },
    }).mount("#vue-app");

    document
        .getElementById("modal_timer")
        ?.addEventListener("hidden.bs.modal", () => app.reset());
};