<template>
    <AppLayout :in-progress="inProgress">
        <div class="space-y-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold">Atividades</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Todas as atividades registradas</p>
                </div>
                <button @click="showFilterModal = true"
                    class="flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-lg border transition-colors shrink-0"
                    :class="activeFilterCount > 0
                        ? 'bg-gray-900 dark:bg-white text-white dark:text-gray-900 border-gray-900 dark:border-white'
                        : 'bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800'">
                    <svg class="w-4 h-4 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="3 3 21 3 14 12 14 21 10 18 10 12 3 3"/>
                    </svg>
                    <span class="hidden sm:inline">Filtros</span>
                    <span v-if="activeFilterCount > 0"
                        class="min-w-[20px] h-5 px-1 text-xs font-bold rounded-full flex items-center justify-center"
                        :class="activeFilterCount > 0
                            ? 'bg-white/20 dark:bg-gray-900/20 text-white dark:text-white'
                            : 'bg-gray-900 dark:bg-white text-white dark:text-gray-900'">
                        {{ activeFilterCount }}
                    </span>
                </button>
            </div>

            <Modal :show="showFilterModal" @close="showFilterModal = false" max-width="lg">
                <div class="p-6 space-y-5">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Filtros</h2>
                        <button @click="showFilterModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1">
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        </button>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1.5">Período</label>
                        <div class="grid grid-cols-2 gap-3">
                            <input v-model="filters.date_from" type="date"
                                class="w-full text-sm bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-gray-100 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-white" />
                            <input v-model="filters.date_to" type="date"
                                class="w-full text-sm bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-gray-100 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-white" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1.5">Buscar</label>
                        <div class="space-y-2">
                            <input v-model="filters.search" type="text" placeholder="Título..."
                                class="w-full text-sm bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-gray-100 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-white placeholder-gray-400" />
                            <input v-model="filters.description" type="text" placeholder="Descrição..."
                                class="w-full text-sm bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-gray-100 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-white placeholder-gray-400" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1.5">Categoria</label>
                        <div class="relative" ref="categoryRef">
                            <input v-model="categoryQuery" type="text" placeholder="Buscar categorias..."
                                @focus="categoryOpen = true" @input="categoryOpen = true" @keydown.escape="categoryOpen = false"
                                class="w-full text-sm bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-gray-100 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 pl-9 outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-white placeholder-gray-400" />
                            <svg class="absolute left-2.5 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <div v-if="selectedCategories.length" class="flex flex-wrap gap-1.5 mt-2">
                                <span v-for="cat in selectedCategories" :key="cat.id"
                                    class="inline-flex items-center gap-1 px-2 py-0.5 text-xs rounded-full bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                    <span class="w-1.5 h-1.5 rounded-full shrink-0" :style="{ backgroundColor: cat.color }" />
                                    {{ cat.name }}
                                    <button type="button" @click="toggleCategory(cat)" class="hover:text-gray-900 dark:hover:text-white">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </span>
                            </div>
                            <div v-if="categoryOpen && filteredCategories.length"
                                class="absolute left-0 right-0 top-full mt-1 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-50 max-h-48 overflow-y-auto">
                                <button v-for="cat in filteredCategories" :key="cat.id" type="button"
                                    @mousedown.prevent="toggleCategory(cat)"
                                    class="w-full text-left px-3 py-2 text-sm transition-colors flex items-center gap-2"
                                    :class="selectedCategoryIds.has(cat.id) ? 'bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800'">
                                    <span class="w-2 h-2 rounded-full shrink-0" :style="{ backgroundColor: cat.color }" />
                                    <span class="flex-1">{{ cat.name }}</span>
                                    <svg v-if="selectedCategoryIds.has(cat.id)" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 12 5 5L20 7"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1.5">Projeto</label>
                            <div class="relative" ref="projectRef">
                                <input v-model="projectQuery" type="text" placeholder="Buscar projetos..."
                                    @focus="projectOpen = true" @input="projectOpen = true" @keydown.escape="projectOpen = false"
                                    class="w-full text-sm bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-gray-100 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 pl-9 outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-white placeholder-gray-400" />
                                <svg class="absolute left-2.5 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                <div v-if="selectedProjects.length" class="flex flex-wrap gap-1.5 mt-2">
                                    <span v-for="proj in selectedProjects" :key="proj.id"
                                        class="inline-flex items-center gap-1 px-2 py-0.5 text-xs rounded-full bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                        <span class="w-1.5 h-1.5 rounded-full shrink-0" :style="{ backgroundColor: proj.color }" />
                                        {{ proj.name }}
                                        <button type="button" @click="toggleProject(proj)" class="hover:text-gray-900 dark:hover:text-white">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </span>
                                </div>
                                <div v-if="projectOpen && filteredProjects.length"
                                    class="absolute left-0 right-0 top-full mt-1 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-50 max-h-48 overflow-y-auto">
                                    <button v-for="proj in filteredProjects" :key="proj.id" type="button"
                                        @mousedown.prevent="toggleProject(proj)"
                                        class="w-full text-left px-3 py-2 text-sm transition-colors flex items-center gap-2"
                                        :class="selectedProjectIds.has(proj.id) ? 'bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800'">
                                        <span class="w-2 h-2 rounded-full shrink-0" :style="{ backgroundColor: proj.color }" />
                                        <span class="flex-1">{{ proj.name }}</span>
                                        <svg v-if="selectedProjectIds.has(proj.id)" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 12 5 5L20 7"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1.5">Módulo</label>
                            <div class="relative" ref="moduleRef">
                                <input v-model="moduleQuery" type="text" placeholder="Buscar módulos..."
                                    @focus="moduleOpen = true" @input="moduleOpen = true" @keydown.escape="moduleOpen = false"
                                    class="w-full text-sm bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-gray-100 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 pl-9 outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-white placeholder-gray-400" />
                                <svg class="absolute left-2.5 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                <div v-if="selectedModules.length" class="flex flex-wrap gap-1.5 mt-2">
                                    <span v-for="mod in selectedModules" :key="mod.id"
                                        class="inline-flex items-center gap-1 px-2 py-0.5 text-xs rounded-full bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                        <span class="w-1.5 h-1.5 rounded-full shrink-0" :style="{ backgroundColor: mod.color }" />
                                        {{ mod.name }}
                                        <button type="button" @click="toggleModule(mod)" class="hover:text-gray-900 dark:hover:text-white">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </span>
                                </div>
                                <div v-if="moduleOpen && filteredModules.length"
                                    class="absolute left-0 right-0 top-full mt-1 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-50 max-h-48 overflow-y-auto">
                                    <button v-for="mod in filteredModules" :key="mod.id" type="button"
                                        @mousedown.prevent="toggleModule(mod)"
                                        class="w-full text-left px-3 py-2 text-sm transition-colors flex items-center gap-2"
                                        :class="selectedModuleIds.has(mod.id) ? 'bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800'">
                                        <span class="w-2 h-2 rounded-full shrink-0" :style="{ backgroundColor: mod.color }" />
                                        <span class="flex-1">{{ mod.name }}</span>
                                        <svg v-if="selectedModuleIds.has(mod.id)" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 12 5 5L20 7"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1.5">Prioridade</label>
                            <select v-model="filters.priority"
                                class="w-full text-sm bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-gray-100 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-white">
                                <option value="">Todas</option>
                                <option value="low">Baixa</option>
                                <option value="normal">Normal</option>
                                <option value="medium">Média</option>
                                <option value="high">Alta</option>
                                <option value="critical">Crítica</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1.5">Dificuldade</label>
                            <select v-model="filters.energy_level"
                                class="w-full text-sm bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-gray-100 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-white">
                                <option value="">Todas</option>
                                <option :value="1">1</option>
                                <option :value="2">2</option>
                                <option :value="3">3</option>
                                <option :value="4">4</option>
                                <option :value="5">5</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1.5">Status</label>
                            <select v-model="filters.status"
                                class="w-full text-sm bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-gray-100 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-white">
                                <option value="">Todos</option>
                                <option value="in_progress">Andamento</option>
                                <option value="paused">Pausado</option>
                                <option value="completed">Concluído</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button @click="clearFilters"
                            class="text-xs text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 underline underline-offset-2">
                            Limpar filtros
                        </button>
                        <div class="flex gap-2">
                            <button @click="showFilterModal = false"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                Fechar
                            </button>
                            <button @click="handleApply"
                                class="px-4 py-2 text-sm font-medium bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-lg hover:bg-gray-800 dark:hover:bg-gray-100 transition-colors">
                                Aplicar
                            </button>
                        </div>
                    </div>
                </div>
            </Modal>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div v-for="activity in activities.data" :key="activity.id"
                    @click="viewDetail(activity)"
                    class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 space-y-2 cursor-pointer hover:border-gray-300 dark:hover:border-gray-700 transition-colors">

                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-2 min-w-0 flex-1">
                            <span class="text-xs px-2 py-0.5 rounded-full shrink-0 font-medium whitespace-nowrap"
                                :class="statusClass(activity.status)">
                                {{ statusLabel(activity.status) }}
                            </span>
                            <span class="text-sm font-semibold truncate text-gray-900 dark:text-gray-100" :title="activity.title">
                                {{ activity.title }}
                            </span>
                            <span v-if="activity.type === 'interruption'"
                                class="shrink-0 text-xs px-1.5 py-0.5 rounded bg-red-100 dark:bg-red-950/30 text-red-600 dark:text-red-400 font-medium">
                                Int
                            </span>
                        </div>
                        <div class="flex items-center gap-1 shrink-0">
                            <span class="text-sm font-mono text-gray-500 dark:text-gray-400">{{ formatDuration(activity.duration_minutes) }}</span>
                            <div class="flex items-center gap-0.5" @click.stop>
                                <button @click="edit(activity)"
                                    class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                                    </svg>
                                </button>
                                <button v-if="activity.status === 'paused'" @click="resume(activity)"
                                    class="p-1.5 rounded-lg text-yellow-500 hover:text-yellow-600 dark:hover:text-yellow-400 hover:bg-yellow-50 dark:hover:bg-yellow-950/20 transition-colors"
                                    title="Resumir">
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                </button>
                                <button v-if="activity.status === 'completed'" @click="reopen(activity)"
                                    class="p-1.5 rounded-lg text-gray-400 hover:text-green-500 dark:hover:text-green-400 hover:bg-green-50 dark:hover:bg-green-950/20 transition-colors"
                                    title="Reabrir">
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                                </button>
                                <button @click="confirmDelete(activity)"
                                    class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/20 transition-colors">
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-1.5 text-xs text-gray-400 dark:text-gray-500 font-mono">
                        <span>{{ formatDate(activity.started_at) }}</span>
                        <span class="text-gray-300 dark:text-gray-600">→</span>
                        <span>{{ formatDate(activity.ended_at) }}</span>
                    </div>

                    <div v-if="activity.category || activity.project || activity.context"
                        class="flex flex-wrap items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                        <span v-if="activity.category" class="inline-flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full shrink-0" :style="{ backgroundColor: activity.category.color }" />
                            {{ activity.category.name }}
                        </span>
                        <span v-if="activity.category && (activity.project || activity.context)" class="text-gray-300 dark:text-gray-600">·</span>
                        <span v-if="activity.project">{{ activity.project.name }}</span>
                        <span v-if="activity.project && activity.context" class="text-gray-300 dark:text-gray-600">·</span>
                        <span v-if="activity.context">{{ activity.context.name }}</span>
                    </div>

                    <div v-if="activity.priority || activity.energy_level" class="flex items-center gap-2 text-xs">
                        <span v-if="activity.priority"
                            class="text-xs px-1.5 py-0.5 rounded-full font-medium"
                            :class="priorityClass(activity.priority)">
                            {{ priorityLabel(activity.priority) }}
                        </span>
                        <span v-if="activity.energy_level" class="text-gray-400 dark:text-gray-500">
                            Dificuldade: {{ difficultyLabel(activity.energy_level) }}
                        </span>
                    </div>

                    <div v-if="activity.description" :title="activity.description"
                        class="text-xs text-gray-400 dark:text-gray-500 truncate leading-relaxed">
                        {{ activity.description }}
                    </div>
                </div>

                <div v-if="activities.data?.length === 0"
                    class="col-span-full text-center py-12 text-sm text-gray-500">
                    Nenhuma atividade encontrada.
                </div>
            </div>

            <div class="flex items-center justify-between px-1">
                <div class="flex items-center gap-2 text-sm text-gray-500">
                    <span class="hidden sm:inline">Por página:</span>
                    <select v-model="filters.per_page" @change="applyFilters"
                        class="text-xs bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-gray-100 border border-gray-200 dark:border-gray-700 rounded px-2 py-1 outline-none focus:ring-1 focus:ring-gray-900 dark:focus:ring-white">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                <div v-if="activities.total > activities.per_page" class="flex gap-2">
                    <Link v-for="link in activities.links" :key="link.label"
                        :href="link.url || '#'"
                        v-html="link.label"
                        class="px-3 py-1.5 text-sm rounded-lg border transition-colors"
                        :class="link.active
                            ? 'bg-gray-900 dark:bg-white text-white dark:text-gray-900 border-gray-900 dark:border-white'
                            : 'border-gray-200 dark:border-gray-700 text-gray-500 hover:border-gray-400 dark:hover:border-gray-500'" />
                </div>
            </div>
        </div>

        <EditActivityModal v-if="editingActivity" :activity="editingActivity" @close="closeEdit" @saved="closeEdit" />
        <ConfirmDeleteModal v-if="deletingActivity" :activity="deletingActivity" @close="deletingActivity = null" @confirm="(e) => doDelete(e.mode)" />
        <ActivityDetailModal v-if="viewingActivity" :activity="viewingActivity" @close="viewingActivity = null" />
    </AppLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Modal from '@/Components/Modal.vue'
import EditActivityModal from '@/Components/EditActivityModal.vue'
import ConfirmDeleteModal from '@/Components/ConfirmDeleteModal.vue'
import ActivityDetailModal from '@/Components/ActivityDetailModal.vue'

const page = usePage()
const categories = page.props.categories
const projects = page.props.projects
const modules = page.props.modules

const props = defineProps({
    activities: Object,
    inProgress: Object,
    filters: Object,
})

const filters = reactive({
    search: props.filters?.search || '',
    category_ids: props.filters?.category_ids || [],
    project_ids: props.filters?.project_ids || [],
    context_ids: props.filters?.context_ids || [],
    status: props.filters?.status || '',
    date_from: props.filters?.date_from || '',
    date_to: props.filters?.date_to || '',
    description: props.filters?.description || '',
    priority: props.filters?.priority || '',
    energy_level: props.filters?.energy_level || '',
    per_page: props.filters?.per_page || '50',
})

const editingActivity = ref(null)
const deletingActivity = ref(null)
const viewingActivity = ref(null)

const showFilterModal = ref(false)

const categoryQuery = ref('')
const projectQuery = ref('')
const moduleQuery = ref('')
const categoryOpen = ref(false)
const projectOpen = ref(false)
const moduleOpen = ref(false)

const selectedCategoryIds = ref(new Set(filters.category_ids))
const selectedProjectIds = ref(new Set(filters.project_ids))
const selectedModuleIds = ref(new Set(filters.context_ids))

const categoryRef = ref(null)
const projectRef = ref(null)
const moduleRef = ref(null)

const filteredCategories = computed(() => {
    const q = categoryQuery.value.toLowerCase().trim()
    if (!q) return categories
    return categories.filter(c => c.name.toLowerCase().includes(q))
})

const filteredProjects = computed(() => {
    const q = projectQuery.value.toLowerCase().trim()
    if (!q) return projects
    return projects.filter(p => p.name.toLowerCase().includes(q))
})

const filteredModules = computed(() => {
    const q = moduleQuery.value.toLowerCase().trim()
    if (!q) return modules
    return modules.filter(m => m.name.toLowerCase().includes(q))
})

const selectedCategories = computed(() =>
    categories.filter(c => selectedCategoryIds.value.has(c.id))
)

const selectedProjects = computed(() =>
    projects.filter(p => selectedProjectIds.value.has(p.id))
)

const selectedModules = computed(() =>
    modules.filter(m => selectedModuleIds.value.has(m.id))
)

const activeFilterCount = computed(() => {
    let count = 0
    if (filters.search) count++
    if (filters.description) count++
    if (filters.date_from) count++
    if (filters.date_to) count++
    if (filters.priority) count++
    if (filters.energy_level) count++
    if (filters.status) count++
    if (filters.category_ids?.length) count++
    if (filters.project_ids?.length) count++
    if (filters.context_ids?.length) count++
    return count
})

function toggleCategory(cat) {
    const set = selectedCategoryIds.value
    if (set.has(cat.id)) {
        set.delete(cat.id)
    } else {
        set.add(cat.id)
    }
    selectedCategoryIds.value = new Set(set)
}

function toggleProject(proj) {
    const set = selectedProjectIds.value
    if (set.has(proj.id)) {
        set.delete(proj.id)
    } else {
        set.add(proj.id)
    }
    selectedProjectIds.value = new Set(set)
}

function toggleModule(mod) {
    const set = selectedModuleIds.value
    if (set.has(mod.id)) {
        set.delete(mod.id)
    } else {
        set.add(mod.id)
    }
    selectedModuleIds.value = new Set(set)
}

function handleClickOutside(e) {
    if (categoryRef.value && !categoryRef.value.contains(e.target)) categoryOpen.value = false
    if (projectRef.value && !projectRef.value.contains(e.target)) projectOpen.value = false
    if (moduleRef.value && !moduleRef.value.contains(e.target)) moduleOpen.value = false
}

onMounted(() => document.addEventListener('click', handleClickOutside))
onUnmounted(() => document.removeEventListener('click', handleClickOutside))

function edit(activity) {
    editingActivity.value = activity
}

function closeEdit() {
    editingActivity.value = null
}

function viewDetail(activity) {
    viewingActivity.value = activity
}

function confirmDelete(activity) {
    deletingActivity.value = activity
}

function resume(activity) {
    router.post(`/api/activities/${activity.id}/resume`, {}, {
        preserveState: false,
    })
}

function reopen(activity) {
    router.post(`/api/activities/${activity.id}/reopen`, {}, {
        preserveState: false,
    })
}

function doDelete(mode) {
    if (!deletingActivity.value) return
    router.delete(`/activities/${deletingActivity.value.id}?mode=${mode}`, {
        preserveState: false,
        onFinish: () => { deletingActivity.value = null },
    })
}

function applyFilters() {
    const params = {}
    for (const [key, value] of Object.entries(filters)) {
        if (value && (typeof value === 'string' || Array.isArray(value))) {
            if (Array.isArray(value) && value.length > 0) {
                params[key] = value
            } else if (typeof value === 'string' && value) {
                params[key] = value
            }
        }
    }
    router.get('/activities', params, { preserveState: true, preserveScroll: true })
}

function handleApply() {
    filters.category_ids = [...selectedCategoryIds.value]
    filters.project_ids = [...selectedProjectIds.value]
    filters.context_ids = [...selectedModuleIds.value]
    showFilterModal.value = false
    applyFilters()
}

function clearFilters() {
    filters.search = ''
    filters.category_ids = []
    filters.project_ids = []
    filters.context_ids = []
    filters.status = ''
    filters.date_from = ''
    filters.date_to = ''
    filters.description = ''
    filters.priority = ''
    filters.energy_level = ''
    selectedCategoryIds.value = new Set()
    selectedProjectIds.value = new Set()
    selectedModuleIds.value = new Set()
    showFilterModal.value = false
    applyFilters()
}

function formatDate(date) {
    if (!date) return '-'
    return new Date(date).toLocaleString('pt-BR', { day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit' })
}

function formatDuration(minutes) {
    if (!minutes && minutes !== 0) return '-'
    if (minutes < 60) return `${minutes}m`
    const h = Math.floor(minutes / 60)
    const m = minutes % 60
    return `${h}h${m > 0 ? m + 'm' : ''}`
}

function priorityClass(priority) {
    const map = {
        low: 'bg-blue-100 dark:bg-blue-950/30 text-blue-700 dark:text-blue-400',
        normal: 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400',
        medium: 'bg-yellow-100 dark:bg-yellow-950/30 text-yellow-700 dark:text-yellow-400',
        high: 'bg-orange-100 dark:bg-orange-950/30 text-orange-700 dark:text-orange-400',
        critical: 'bg-red-100 dark:bg-red-950/30 text-red-700 dark:text-red-400',
    }
    return map[priority] || ''
}

function priorityLabel(priority) {
    const map = {
        low: 'Baixa',
        normal: 'Normal',
        medium: 'Média',
        high: 'Alta',
        critical: 'Crítica',
    }
    return map[priority] || priority
}

function statusClass(status) {
    const map = {
        in_progress: 'bg-green-100 dark:bg-green-950/30 text-green-700 dark:text-green-400',
        paused: 'bg-yellow-100 dark:bg-yellow-950/30 text-yellow-700 dark:text-yellow-400',
        completed: 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400',
    }
    return map[status] || ''
}

function statusLabel(status) {
    const map = {
        in_progress: 'Em andamento',
        paused: 'Pausado',
        completed: 'Concluído',
    }
    return map[status] || status
}

function difficultyLabel(level) {
    const map = {
        1: 'Muito Fácil',
        2: 'Fácil',
        3: 'Normal',
        4: 'Difícil',
        5: 'Muito Difícil',
    }
    return map[level] || level
}
</script>
