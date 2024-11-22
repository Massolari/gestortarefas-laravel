<!-- Modal do Cronômetro -->
<div class="modal fade" id="modal_timer" tabindex="-1" aria-labelledby="modal_timer_label">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="text-info"><strong>{{ $list_name }}</strong> - <em>@{{ task.name }}</em></h4>
                <button :disabled="time.started_at" @click="editClock" class="btn btn-secondary btn-lg"><i class="fas fa-pencil"></i></button>
            </div>
            <div class="p-3 modal-body">
                <div class="d-flex flex-column justify-content-center">
                    <div class="d-flex justify-content-center">
                        <p class="fs-2" v-if="!isEditing">@{{ clock }}</p>
                    </div>
                    <div>
                        <p v-if="errorMessage" class="text-center text-danger">
                            @{{ errorMessage }}
                        </p>
                    </div>
                    <div class="d-flex justify-content-center mb-4">
                        <div class="input-group w-50" v-if="isEditing">
                            <input class="form-control" type="time" step="2" name="inputTimerDisplay_modal_timer" v-model="clockInput">
                            <button class="btn btn-success" @click="submitEditedElapsedTimeOfDisplay">
                                <i class="fas fa-save"></i>
                            </button>
                            <button class="btn btn-danger" @click="cancelEditDisplayElapsedTime">
                                <i class="fas fa-cancel"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-center" role="group" aria-label="Timer Controls">
                    <div class="btn-group">
                        <button @click="startTask" :disabled="time.started_at || isEditing" class="btn btn-success btn-lg"><i class="fas fa-play"></i></button>
                        <button @click="pauseTask" :disabled="!time.started_at || isEditing" class="btn btn-danger btn-lg"><i class="fas fa-pause"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>