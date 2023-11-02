<template>
<div v-if="!phase_deleted" :class="[is_completion === 1 ? 'bg-amber-500' : 'bg-sky-950']" class="w-[300px] rounded-lg shadow-lg">
    <div class="p-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg text-zinc-100 font-black mb-3">
                {{ kanban.phases[col.id].name }} - ({{ kanban.phases[col.id].tasks.length }})
            </h2>
            <div class="inline-flex float-right">
                <PlusIcon
                    @click="createTask()"
                    class="mb-3 h-6 w-6 text-white hover:cursor-pointer"
                    aria-hidden="true" />

                <TrashIcon
                    @click="deletePhase(col.id)"
                    class="mb-3 h-6 w-6 text-white hover:cursor-pointer"
                    aria-hidden="true" />

                <svg v-if="loading"
                     class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>

               <div v-if="!kanban.loading" class="text-xs text-white inline-flex">
                    <CheckBadgeIcon
                        @click="toggleSetCompletion(col.id)"
                        class="mb-3 h-6 w-6 text-white hover:cursor-pointer"
                        :class="[is_completion === 1 ? 'text-amber-400': '']"
                        aria-hidden="true" />

                   <span @click="toggleSetCompletion(col.id)"
                         v-if="is_completion === 1">Completion</span>
                   <span v-else>Mark as completion</span>
               </div>

            </div>

        </div>
        <task-card
            v-if="kanban.phases[col.id].tasks.length > 0"
            v-for="task in kanban.phases[col.id].tasks"
            :task="task"
        />

        <!-- A card to create a new task -->
        <div class="w-full flex items-center justify-between bg-white text-gray-900 hover:cursor-pointer shadow-md rounded-lg p-3 relative"
            @click="createTask()">
            <span>Create a new task</span>
            <PlusIcon class="h-6 w-6" aria-hidden="true" />
        </div>

    </div>
</div>
</template>

<script setup>
// get the props
import { useKanbanStore } from '../stores/kanban'
import { PlusIcon, CheckBadgeIcon, TrashIcon } from '@heroicons/vue/20/solid'
import {ref} from "vue";

const props = defineProps({
    col: {
        type: Object,
        required: true
    },
});

const kanban = useKanbanStore();
const col = ref(props.col);
const is_completion = ref(props.col.is_completion);
const loading = ref(false);
const errors = ref([]);
const phase_deleted = ref(false);

const createTask = function () {
    kanban.creatingTask = true;
    kanban.creatingTaskProps.phase_id = props.phase_id;
}

const updateTask = function () {
    kanban.updatingTask = true;
    kanban.creatingTaskProps.phase_id = props.phase_id;
}

const toggleSetCompletion = function (id) {
    loading.value = true;
    let data = {
        is_completion: is_completion.value === 1 ? 0 : 1
    }
    axios.put('/api/phases/update', data, {
        params: {
            id: id
        }
    }).then((response) => {
        if(response.data.success === true){
            is_completion.value = response.data.phase.is_completion
            errors.value = [];
        }else{
            errors.value = response.data.errors;
        }
    }).catch((error) => {
        console.log(error);

    }).finally(() => {
        loading.value = false;
    });
}

const deletePhase = function (id) {
    loading.value = true;
    axios.delete('/api/phases/delete', {
        params: {
            id: id
        }
    }).then((response) => {
        if(response.data.success === true){
            phase_deleted.value = true;
        }
    }).catch((error) => {
        console.log(error);

    }).finally(() => {
        loading.value = false;
    });
}
</script>

<style>

</style>
