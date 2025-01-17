<template>
    <div class="max-w-7xl flex-1 mx-auto flex flex-col overflow-auto sm:px-6 lg:px-8">
        <div class="w-full mb-6 flex">

            <Teleport to="body">
                <generic-modal :show="kanban.creatingTask" @close="kanban.creatingTask = false" key="createTaskModal">
                    <div>
                        <div class="mt-3 sm:mt-2">

                            <div v-if="errors.length > 0" class="text-danger text-center">
                                <span v-for="error in kanban.errors">
                                {{ error }}<br>
                                </span>
                            </div>

                            <DialogTitle as="h3" class="mb-6 text-base font-semibold leading-6 text-gray-900">
                                Create a new task
                            </DialogTitle>
                            <div>
                                <label for="name" class="block text-sm font-medium leading-6 text-gray-900">Task description</label>
                                <div class="relative mt-2">
                                    <input type="text" v-model="kanban.creatingTaskProps.name" id="name"
                                        class="peer block w-full border-0 bg-gray-50 py-1.5 text-gray-900 focus:ring-0 sm:text-sm sm:leading-6"
                                        placeholder="Make it productive, but also fun!" />
                                    <p v-if="hasError('name')"
                                        class="mt-2 text-sm text-red-600"
                                        id="name-error">{{ getError('name') }}
                                    </p>
                                    <div class="absolute inset-x-0 bottom-0 border-t border-gray-300 peer-focus:border-t-2 peer-focus:border-blue-600" aria-hidden="true" />
                                </div>
                            </div>

                            <Listbox as="div" v-model="kanban.creatingTaskProps.user_id" class="mt-8">
                                <ListboxLabel class="block text-sm font-medium leading-6 text-gray-900">Assigned to</ListboxLabel>
                                <div class="relative mt-2">
                                    <ListboxButton
                                        class="relative w-full cursor-default rounded-md bg-white py-1.5 pl-3 pr-10 text-left text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 sm:text-sm sm:leading-6">
                                        <span class="flex items-center">
                                            <img :src="getAvatar(kanban.users[kanban.creatingTaskProps.user_id || kanban.self.id])"
                                                 alt=""
                                                 class="h-5 w-5 flex-shrink-0 rounded-full"
                                            />
                                            <span class="ml-3 block truncate">
                                                {{ kanban.users[kanban.creatingTaskProps.user_id || kanban.self.id].name }}
                                            </span>
                                        </span>
                                        <span
                                            class="pointer-events-none absolute inset-y-0 right-0 ml-3 flex items-center pr-2">
                                            <ChevronUpDownIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                                        </span>
                                    </ListboxButton>

                                    <transition leave-active-class="transition ease-in duration-100"
                                        leave-from-class="opacity-100" leave-to-class="opacity-0">
                                        <ListboxOptions
                                            class="absolute z-20 mt-1 max-h-56 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm">
                                            <ListboxOption as="template" v-for="person in kanban.users" :key="person.id"
                                                :value="person.id" v-slot="{ active, selected }">
                                                <li :class="[active ? 'bg-blue-600 text-white' : 'text-gray-900', 'relative cursor-default select-none py-2 pl-3 pr-9']">
                                                    <div class="flex items-center">
                                                        <img :src="getAvatar(person)" alt="{{ person.name }}"
                                                            class="h-5 w-5 flex-shrink-0 rounded-full" />
                                                        <span :class="[selected ? 'font-semibold' : 'font-normal', 'ml-3 block truncate']">
                                                            {{ person.name }}
                                                        </span>
                                                    </div>

                                                    <span v-if="selected"
                                                        :class="[active ? 'text-white' : 'text-blue-600', 'absolute inset-y-0 right-0 flex items-center pr-4']">
                                                        <CheckIcon class="h-5 w-5" aria-hidden="true" />
                                                    </span>
                                                </li>
                                            </ListboxOption>
                                        </ListboxOptions>
                                    </transition>
                                </div>
                            </Listbox>

                            <div class="mt-8">
                                <label for="taskPhase" class="block text-sm font-medium leading-6 text-gray-900">Phase</label>
                                <select v-model="kanban.creatingTaskProps.phase_id" id="taskPhase" class="mt-2 block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-blue-600 sm:text-sm sm:leading-6">
                                    <option v-for="phase in kanban.phases" :key="phase.id" :value="phase.id">{{ phase.name }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-5 sm:mt-6">
                            <button type="button"
                                class="inline-flex w-full justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
                                @click="addCard()">Add the card!</button>
                        </div>
                    </div>
                </generic-modal>
            </Teleport>

        </div>

        <div id="kanban-container" class="flex-1 flex overflow-auto scrollbar-hide shadow-lg">
            <div class="text-gray-900">
                <div class="h-full flex overflow-x-auto overflow-y-auto space-x-4">
                    <task-column v-for="col in kanban.phases" :col="col"></task-column>
                </div>
            </div>
        </div>

        <!-- Modal to View the selected card -->
        <Teleport to="body">
            <generic-modal v-if="kanban.hasSelectedTask()" @close="closeModal()">

                <div v-if="editTask === false" class="relative">
                    <TrashIcon class="w-6 h-6 absolute top-0 right-0 hover:cursor-pointer" title="Delete"
                               @click="deleteCard(kanban.selectedTask.id)" />
                    <PencilSquareIcon class="w-6 h-6 absolute top-0 hover:cursor-pointer" title="edit"
                                      @click="toggleEditTask()" />

                    <div class="flex justify-center">
                        <img class="w-16 h-16 shadow-lg rounded-full border-2 border-blue-800"
                            :src="getAvatar(kanban.selectedTask.user)"
                             :alt="kanban.selectedTask.user.name" />
                    </div>

                    <div class="mt-3 sm:mt-5">
                        <DialogTitle as="h3" class="text-base font-semibold leading-6 text-gray-900">
                            {{ kanban.selectedTask.name }}
                        </DialogTitle>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                In column {{ kanban.phases[kanban.selectedTask.phase_id].name }}</p>
                            <p class="text-sm text-gray-500">Assigned to {{ kanban.selectedTask.user.name }}</p>
                        </div>
                    </div>
                </div>

                <div v-else>
                    <div>
                        <div class="sm:mt-2 mr-2">

                            <div v-if="errors.length > 0" class="text-danger text-center">
                                <span v-for="error in errors">
                                {{ error }}<br>
                                </span>
                            </div>

                            <svg v-if="kanban.loading"
                                 class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                                 xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>

                            <EyeIcon class="mt-2 w-6 h-6 absolute top-0 right-0 hover:cursor-pointer" @click="editTask = false" />

                            <DialogTitle as="h3" class="mb-6 text-base font-semibold leading-6 text-gray-900">
                                Edit task
                            </DialogTitle>
                            <div>
                                <label for="name" class="block text-sm font-medium leading-6 text-gray-900">Task description</label>
                                <div class="relative mt-2">
                                    <input type="text" v-model="updateForm.name" id="name"
                                           class="peer block w-full border-0 bg-gray-50 py-1.5 text-gray-900 focus:ring-0 sm:text-sm sm:leading-6"
                                           placeholder="Make it productive, but also fun!" />
                                    <p v-if="hasError('name')"
                                       class="mt-2 text-sm text-red-600"
                                       id="name-error">{{ getError('name') }}
                                    </p>
                                    <div class="absolute inset-x-0 bottom-0 border-t border-gray-300 peer-focus:border-t-2 peer-focus:border-blue-600" aria-hidden="true" />
                                </div>
                            </div>

                            <Listbox as="div" v-model="updateForm.user_id" class="mt-8">
                                <ListboxLabel class="block text-sm font-medium leading-6 text-gray-900">Assigned to</ListboxLabel>
                                <div class="relative mt-2">
                                    <ListboxButton
                                        class="relative w-full cursor-default rounded-md bg-white py-1.5 pl-3 pr-10 text-left text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 sm:text-sm sm:leading-6">
                                        <span class="flex items-center">
                                            <img :src="getAvatar(updateForm.user)"
                                                 alt="" class="h-5 w-5 flex-shrink-0 rounded-full" />
                                            <span class="ml-3 block truncate">
                                                {{ updateForm.user.name }}
                                            </span>
                                        </span>
                                        <span
                                            class="pointer-events-none absolute inset-y-0 right-0 ml-3 flex items-center pr-2">
                                            <ChevronUpDownIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                                        </span>
                                    </ListboxButton>

                                    <transition leave-active-class="transition ease-in duration-100"
                                                leave-from-class="opacity-100" leave-to-class="opacity-0">
                                        <ListboxOptions
                                            class="absolute z-20 mt-1 max-h-56 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm">
                                            <ListboxOption as="template" v-for="person in kanban.users" :key="person.id"
                                                           :value="person.id" v-slot="{ active, selected }">
                                                <li :class="[active ? 'bg-blue-600 text-white' : 'text-gray-900', 'relative cursor-default select-none py-2 pl-3 pr-9']">
                                                    <div class="flex items-center">
                                                        <img :src="getAvatar(person)" alt="{{ person.name }}"
                                                             class="h-5 w-5 flex-shrink-0 rounded-full" />
                                                        <span :class="[selected ? 'font-semibold' : 'font-normal', 'ml-3 block truncate']">
                                                            {{ person.name }}</span>
                                                    </div>

                                                    <span v-if="selected"
                                                          :class="[active ? 'text-white' : 'text-blue-600', 'absolute inset-y-0 right-0 flex items-center pr-4']">
                                                        <CheckIcon class="h-5 w-5" aria-hidden="true" />
                                                    </span>
                                                </li>
                                            </ListboxOption>
                                        </ListboxOptions>
                                    </transition>
                                </div>
                            </Listbox>

                            <div class="mt-8">
                                <label for="taskPhase" class="block text-sm font-medium leading-6 text-gray-900">Phase</label>
                                <select v-model="updateForm.phase_id" id="taskPhase"
                                        class="mt-2 block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-blue-600 sm:text-sm sm:leading-6">
                                    <option v-for="phase in kanban.phases" :key="phase.id" :value="phase.id">{{ phase.name }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-5 sm:mt-6">
                            <button type="button"
                                    class="inline-flex w-full justify-center rounded-md bg-amber-400 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-400"
                                    @click="updateTask(kanban.selectedTask.id)">Update Card</button>
                        </div>
                    </div>
                </div>

                <div class="mt-5 sm:mt-6">
                    <button type="button"
                        class="inline-flex w-full justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
                        @click="closeModal()">Close</button>
                </div>
            </generic-modal>
        </Teleport>

    </div>
</template>

<script setup>
import {ref, onMounted, onUnmounted, nextTick, reactive} from 'vue';
import { useKanbanStore } from '../stores/kanban';
import { DialogTitle, Listbox, ListboxButton, ListboxLabel, ListboxOption, ListboxOptions } from '@headlessui/vue';
import { CheckIcon, ChevronUpDownIcon, TrashIcon, PencilSquareIcon, EyeIcon} from '@heroicons/vue/20/solid';
import { sha256 } from 'js-sha256';

const kanban = useKanbanStore();
const selected = ref(null);
const editTask = ref(false);
const errors = ref([]);
const loading = ref(false);

const updateForm = reactive({
    name: "",
    user_id: "",
    phase_id: "",
    user: ""
});

const getAvatar = function (user) {
    if (user.profile_picture_url !== null) {
        return user.profile_picture_url;
    } else {
        return ("https://www.gravatar.com/avatar/" + sha256(String(user.email).trim().toLowerCase()) + "?size=400");
    }
}

const getError = function (field) {
    if (errors.value && errors.value[field]) {
        return errors.value[field][0].message;
    }
    return null;
}

const hasError = function (field) {
    return !!(errors.value && errors.value[field]);

}

// Define these functions outside of onMounted so they're in the component's scope
let pos = { top: 0, left: 0, x: 0, y: 0 };
let ele;

const mouseDownHandler = function (e) {
    ele.style.cursor = 'grabbing';
    ele.style.userSelect = 'none';

    pos = {
        left: ele.scrollLeft,
        top: ele.scrollTop,
        x: e.clientX,
        y: e.clientY,
    };

    document.addEventListener('mousemove', mouseMoveHandler);
    document.addEventListener('mouseup', mouseUpHandler);
};

const mouseMoveHandler = function (e) {
    const dx = e.clientX - pos.x;
    const dy = e.clientY - pos.y;

    ele.scrollTop = pos.top - dy;
    ele.scrollLeft = pos.left - dx;
};

const mouseUpHandler = function () {
    ele.style.cursor = 'grab';
    ele.style.removeProperty('user-select');

    document.removeEventListener('mousemove', mouseMoveHandler);
    document.removeEventListener('mouseup', mouseUpHandler);
};

const refreshTasks = async () => {
    try {
        const response = await axios.get('/api/tasks');
        const originalTasks = response.data;
        kanban.phases = originalTasks.reduce((acc, cur) => {
            acc[cur.id] = cur;
            return acc;
        }, {});
    } catch (error) {
        console.error('There was an error fetching the tasks!', error);
    }
}

const refreshUsers = async () => {
    try {
        const response = await axios.get('/api/users');
        const originalUsers = response.data;
        // rekey originalUsers to use the id property in the objects as the array key
        kanban.users = originalUsers.reduce((acc, cur) => {
            acc[cur.id] = cur;
            return acc;
        }, {});
    } catch (error) {
        console.error('There was an error fetching the users!', error);
    }
}

const getSelf = async () => {
    try {
        const response = await axios.get('/api/user');
        kanban.self = response.data;
        if (kanban.creatingTaskProps.user_id === null) {
            kanban.creatingTaskProps.user_id = kanban.self.id;
        }
        if (kanban.self.profile_picture_url === null) {
            kanban.self.profile_picture_url = getAvatar(kanban.self)
        }
    } catch (error) {
        console.error('There was an error fetching the logged in user!', error);
    }
}

const addCard = async () => {
    kanban.loading = true;
    try {
        const response = await axios.post('/api/tasks', kanban.creatingTaskProps);
        kanban.creatingTask = false;
        kanban.creatingTaskProps = {
            name: null,
            phase_id: null,
            user_id: null
        };
        kanban.loading = false;
        await refreshTasks();
    } catch (error) {
        if (error.response.status === 422) {
            errors.value = error.response.data.errors;
        }
    }
}

const toggleEditTask = async () => {
    editTask.value = true;
    errors.value

    updateForm.name = kanban.selectedTask.name;
    updateForm.user_id = kanban.selectedTask.user_id;
    updateForm.phase_id = kanban.selectedTask.phase_id;
    updateForm.user = kanban.selectedTask.user;
}

const closeModal = () => {
    kanban.unselectTask();
    editTask.value = false;
}

const updateTask = (id) => {
    kanban.loading = true;
    axios.put('/api/task/update', updateForm, {
        params: {
            id: id
        }

    }).then((response) => {
        if(response.data.success === true){
            console.log(response.data);
            errors.value = [];
        }else{
            errors.value = response.data.errors;
            console.log(response.data.errors);
        }

    }).catch((error) => {
        console.log(error);
    });
    kanban.loading = false;
}

const deleteCard = async (id) => {
    kanban.loading = true;
    try {
        const response = await axios.delete('/api/tasks/' + id);
        await refreshTasks();
        kanban.unselectTask();
    } catch (error) {
        console.error('There was an error deleting the task!', error);
    }
    kanban.loading = false;
}

onMounted(async () => {

    await refreshTasks();
    await refreshUsers();
    await getSelf();

    await nextTick();

    ele = document.getElementById('kanban-container');
    if (ele) {
        ele.style.cursor = 'grab';
        ele.addEventListener('mousedown', mouseDownHandler);
    }

})

onUnmounted(() => {
    if (ele) {
        // Clean up the event listener when the component is unmounted.
        ele.removeEventListener('mousedown', mouseDownHandler);
    }
})
</script>

<style scoped>/* For Webkit-based browsers (Chrome, Safari and Opera) */
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}

/* For IE, Edge and Firefox */
.scrollbar-hide {
    -ms-overflow-style: none;
    /* IE and Edge */
    scrollbar-width: none;
    /* Firefox */
}

.text-danger{
    color: red;
}
</style>
