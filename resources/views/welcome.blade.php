<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Task Manager | Cytonn Assessment</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <script src="https://cdn.tailwindcss.com"></script>
        <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

        <style>
            body { font-family: 'Instrument Sans', sans-serif; background-color: #FDFDFC; }
            [v-cloak] { display: none; }
        </style>
    </head>
    <body class="antialiased text-[#1B1B18]">
        <div id="app" v-cloak class="max-w-4xl mx-auto p-6 lg:mt-10">
            
            <header class="flex justify-between items-end mb-8 border-b pb-6">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Task Dashboard</h1>
                    <p class="text-gray-500">Managing tasks for {{ date('F j, Y') }}</p>
                </div>
                <div class="flex gap-4">
                    <div class="text-center">
                        <span class="block text-2xl font-bold">@{{ tasks.length }}</span>
                        <span class="text-xs uppercase tracking-wider text-gray-400">Total</span>
                    </div>
                </div>
            </header>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="lg:col-span-1">
                    <div class="bg-white p-6 rounded-xl border shadow-sm">
                        <h2 class="font-semibold mb-4">Create New Task</h2>
                        <form @submit.prevent="createTask" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Title</label>
                                <input v-model="form.title" type="text" required class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-black outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Due Date</label>
                                <input v-model="form.due_date" type="date" required class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-black outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Priority</label>
                                <select v-model="form.priority" class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-black outline-none">
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>
                            <button type="submit" :disabled="loading" class="w-full bg-black text-white rounded-md py-2 font-medium hover:bg-gray-800 transition">
                                @{{ loading ? 'Saving...' : 'Add Task' }}
                            </button>
                        </form>
                    </div>
                </div>

                <div class="lg:col-span-2 space-y-4">
                    <div class="flex justify-between items-center mb-2">
                        <h2 class="font-semibold text-lg">Your Tasks</h2>
                        <select v-model="filterStatus" @change="fetchTasks" class="text-sm border rounded px-2 py-1 outline-none">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="done">Done</option>
                        </select>
                    </div>

                    <div v-if="tasks.length === 0" class="text-center py-12 border-2 border-dashed rounded-xl text-gray-400">
                        No tasks found. Start by adding one!
                    </div>

                    <div v-for="task in tasks" :key="task.id" class="bg-white border rounded-xl p-4 shadow-sm flex justify-between items-center transition hover:border-gray-400">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span :class="priorityClass(task.priority)" class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-widest">
                                    @{{ task.priority }}
                                </span>
                                <span class="text-xs text-gray-400">@{{ task.due_date }}</span>
                            </div>
                            <h3 class="font-medium text-[#1B1B18]">@{{ task.title }}</h3>
                        </div>

                        <div class="flex items-center gap-3">
                            <button v-if="task.status !== 'done'" 
                                    @click="advanceStatus(task)"
                                    class="text-sm font-semibold px-4 py-1.5 rounded-md border border-black hover:bg-gray-100 transition">
                                @{{ task.status === 'pending' ? 'Start' : 'Finish' }}
                            </button>

                            <button v-if="task.status === 'done'"
                                    @click="deleteTask(task.id)"
                                    class="text-red-500 hover:bg-red-50 p-2 rounded-md transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>

                            <span v-if="task.status === 'done'" class="text-green-600 font-bold text-sm">✓ Done</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            const { createApp, ref, onMounted } = Vue;

            createApp({
                setup() {
                    const tasks = ref([]);
                    const filterStatus = ref('');
                    const loading = ref(false);
                    const form = ref({
                        title: '',
                        due_date: new Date().toISOString().split('T')[0],
                        priority: 'medium'
                    });

                    const fetchTasks = async () => {
                        const url = filterStatus.value ? `/api/tasks?status=${filterStatus.value}` : '/api/tasks';
                        const response = await fetch(url);
                        const result = await response.json();
                        tasks.value = result.data;
                    };

                    const createTask = async () => {
                        loading.value = true;
                        try {
                            const response = await fetch('/api/tasks', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                                body: JSON.stringify(form.value)
                            });
                            if (response.ok) {
                                form.value.title = '';
                                fetchTasks();
                            } else {
                                const err = await response.json();
                                alert(err.message || "Validation Error");
                            }
                        } finally {
                            loading.value = false;
                        }
                    };

                    const advanceStatus = async (task) => {
                        const nextStatus = task.status === 'pending' ? 'in_progress' : 'done';
                        await fetch(`/api/tasks/${task.id}/status`, {
                            method: 'PATCH',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                            body: JSON.stringify({ status: nextStatus })
                        });
                        fetchTasks();
                    };

                    const deleteTask = async (id) => {
                        if (!confirm('Are you sure?')) return;
                        await fetch(`/api/tasks/${id}`, { method: 'DELETE' });
                        fetchTasks();
                    };

                    const priorityClass = (p) => {
                        if (p === 'high') return 'bg-red-100 text-red-700';
                        if (p === 'medium') return 'bg-amber-100 text-amber-700';
                        return 'bg-blue-100 text-blue-700';
                    };

                    onMounted(fetchTasks);

                    return { tasks, form, loading, filterStatus, fetchTasks, createTask, advanceStatus, deleteTask, priorityClass };
                }
            }).mount('#app');
        </script>
    </body>
</html>