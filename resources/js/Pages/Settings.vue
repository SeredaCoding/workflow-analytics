<template>
    <AppLayout :in-progress="inProgress">
        <div class="max-w-5xl mx-auto space-y-8">
            <div>
                <h2 class="text-2xl font-bold">Configurações</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Destinatário e template do relatório mensal</p>
            </div>

            <form @submit.prevent="save" class="space-y-6">
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 space-y-4">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Destinatário</h3>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Nome</label>
                        <input v-model="form.user_name" placeholder="João"
                            class="w-full text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-white placeholder-gray-400" />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">E-mail do destinatário</label>
                        <input v-model="form.boss_email" type="email" placeholder="destinatario@empresa.com"
                            class="w-full text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-white placeholder-gray-400" />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Assunto do E-mail</label>
                        <input v-model="form.report_subject" placeholder="Relatório Mensal - {{month}}"
                            class="w-full text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-white placeholder-gray-400" />
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 space-y-4">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Horário de Almoço</h3>
                    <p class="text-xs text-gray-400">Se configurado, o intervalo de almoço será automaticamente descontado da duração das atividades.</p>
                    <div class="flex gap-4">
                        <div class="flex-1">
                            <label class="block text-xs text-gray-500 mb-1">Início</label>
                            <input v-model="form.lunch_start" type="time"
                                class="w-full text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-white" />
                        </div>
                        <div class="flex-1">
                            <label class="block text-xs text-gray-500 mb-1">Fim</label>
                            <input v-model="form.lunch_end" type="time"
                                class="w-full text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-white" />
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 space-y-4">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Template do Relatório</h3>
                    <p class="text-xs text-gray-400">HTML puro. Placeholders disponíveis:
                        <code class="text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 px-1 rounded">&#123;&#123;month&#125;&#125;</code>,
                        <code class="text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 px-1 rounded">&#123;&#123;total_hours&#125;&#125;</code>,
                        <code class="text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 px-1 rounded">&#123;&#123;interruptions&#125;&#125;</code>,
                        <code class="text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 px-1 rounded">&#123;&#123;avg_focus&#125;&#125;</code>,
                        <code class="text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 px-1 rounded">&#123;&#123;meeting_hours&#125;&#125;</code>,
                        <code class="text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 px-1 rounded">&#123;&#123;category_distribution&#125;&#125;</code>,
                        <code class="text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 px-1 rounded">&#123;&#123;month_breakdown&#125;&#125;</code>,
                        <code class="text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 px-1 rounded">&#123;&#123;top_activities&#125;&#125;</code>,
                        <code class="text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 px-1 rounded">&#123;&#123;weekly_summary&#125;&#125;</code>,
                        <code class="text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 px-1 rounded">&#123;&#123;csv_note&#125;&#125;</code>,
                        <code class="text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 px-1 rounded">&#123;&#123;user_name&#125;&#125;</code>
                    </p>
                    <div class="space-y-4">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs text-gray-500">HTML</label>
                                <button type="button" @click="templateEditable = !templateEditable"
                                    class="text-xs font-medium text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                                    {{ templateEditable ? '🔒 Bloquear' : '✏️ Editar' }}
                                </button>
                            </div>
                            <textarea v-model="form.report_template" rows="12" :disabled="!templateEditable" :readonly="!templateEditable"
                                class="w-full text-sm font-mono bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-white resize-none placeholder-gray-400"
                                :class="!templateEditable ? 'opacity-60 cursor-not-allowed' : ''"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Preview</label>
                            <div class="w-full min-h-[300px] max-h-[600px] overflow-auto bg-white border border-gray-200 dark:border-gray-700 rounded-lg p-4"
                                v-html="previewHtml" />
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <span v-if="flashSuccess" class="text-sm text-green-500">{{ flashSuccess }}</span>
                    <span v-if="flashError" class="text-sm text-red-500">{{ flashError }}</span>
                    <button type="submit"
                        class="px-6 py-2.5 text-sm font-medium bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-lg hover:bg-gray-800 dark:hover:bg-gray-100 transition-colors">
                        Salvar Configurações
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const page = usePage()

const props = defineProps({
    settings: Object,
    inProgress: Object,
})

const templateEditable = ref(false)

const flashSuccess = computed(() => page.props.flash?.success)
const flashError = computed(() => page.props.flash?.error)

const form = reactive({
    user_name: props.settings?.user_name || '',
    boss_email: props.settings?.boss_email || '',
    report_subject: props.settings?.report_subject || 'Relatório Mensal - {{month}}',
    report_template: props.settings?.report_template || defaultTemplate,
    lunch_start: props.settings?.lunch_start || '',
    lunch_end: props.settings?.lunch_end || '',
})

const sampleData = {
    user_name: 'João',
    month: 'Junho/2026',
    total_hours: '87.2',
    interruptions: '12',
    avg_focus: '45',
    meeting_hours: '12.5',
    category_distribution: `
        <ul style="list-style:none;padding:0;margin:0">
            <li style="padding:4px 0">Desenvolvimento: 52h (60%)</li>
            <li style="padding:4px 0">Suporte: 12h (14%)</li>
            <li style="padding:4px 0">Reunião: 12h (14%)</li>
            <li style="padding:4px 0">Investigação: 6h (7%)</li>
            <li style="padding:4px 0">Bug: 5h (6%)</li>
        </ul>
    `.trim(),
    month_breakdown: `
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:13px;border-collapse:collapse">
            <tr>
                <th style="padding:6px 4px;text-align:center;border-bottom:2px solid #e5e7eb;color:#111;font-size:11px;font-weight:600">Seg</th>
                <th style="padding:6px 4px;text-align:center;border-bottom:2px solid #e5e7eb;color:#111;font-size:11px;font-weight:600">Ter</th>
                <th style="padding:6px 4px;text-align:center;border-bottom:2px solid #e5e7eb;color:#111;font-size:11px;font-weight:600">Qua</th>
                <th style="padding:6px 4px;text-align:center;border-bottom:2px solid #e5e7eb;color:#111;font-size:11px;font-weight:600">Qui</th>
                <th style="padding:6px 4px;text-align:center;border-bottom:2px solid #e5e7eb;color:#111;font-size:11px;font-weight:600">Sex</th>
                <th style="padding:6px 4px;text-align:center;border-bottom:2px solid #e5e7eb;color:#111;font-size:11px;font-weight:600">Sáb</th>
                <th style="padding:6px 4px;text-align:center;border-bottom:2px solid #e5e7eb;color:#111;font-size:11px;font-weight:600">Dom</th>
                <th style="padding:6px 4px;text-align:right;border-bottom:2px solid #e5e7eb;color:#111;font-size:11px;font-weight:600">Total</th>
            </tr>
            <tr>
                <td style="padding:5px 3px;text-align:center;border-bottom:1px solid #f3f4f6;background:#f9fafb;font-size:13px;color:#333;line-height:1.3">1<br><span style="font-size:10px;color:#666">8.0h</span></td>
                <td style="padding:5px 3px;text-align:center;border-bottom:1px solid #f3f4f6;background:#f9fafb;font-size:13px;color:#333;line-height:1.3">2<br><span style="font-size:10px;color:#666">7.5h</span></td>
                <td style="padding:5px 3px;text-align:center;border-bottom:1px solid #f3f4f6;background:#f9fafb;font-size:13px;color:#333;line-height:1.3">3<br><span style="font-size:10px;color:#666">6.0h</span></td>
                <td style="padding:5px 3px;text-align:center;border-bottom:1px solid #f3f4f6;background:#f9fafb;font-size:13px;color:#333;line-height:1.3">4<br><span style="font-size:10px;color:#666">7.5h</span></td>
                <td style="padding:5px 3px;text-align:center;border-bottom:1px solid #f3f4f6;background:#f9fafb;font-size:13px;color:#333;line-height:1.3">5<br><span style="font-size:10px;color:#666">5.0h</span></td>
                <td style="padding:5px 3px;text-align:center;border-bottom:1px solid #f3f4f6;background:#f9fafb;font-size:13px;color:#ccc;line-height:1.3">-</td>
                <td style="padding:5px 3px;text-align:center;border-bottom:1px solid #f3f4f6;background:#f9fafb;font-size:13px;color:#ccc;line-height:1.3">-</td>
                <td style="padding:5px 3px;text-align:right;border-bottom:1px solid #f3f4f6;background:#f9fafb;font-size:13px;color:#333;font-weight:600;vertical-align:middle">34.0h</td>
            </tr>
            <tr>
                <td style="padding:5px 3px;text-align:center;border-bottom:1px solid #f3f4f6;background:#ffffff;font-size:13px;color:#333;line-height:1.3">8<br><span style="font-size:10px;color:#666">7.0h</span></td>
                <td style="padding:5px 3px;text-align:center;border-bottom:1px solid #f3f4f6;background:#ffffff;font-size:13px;color:#333;line-height:1.3">9<br><span style="font-size:10px;color:#666">6.5h</span></td>
                <td style="padding:5px 3px;text-align:center;border-bottom:1px solid #f3f4f6;background:#ffffff;font-size:13px;color:#333;line-height:1.3">10<br><span style="font-size:10px;color:#666">5.0h</span></td>
                <td style="padding:5px 3px;text-align:center;border-bottom:1px solid #f3f4f6;background:#ffffff;font-size:13px;color:#333;line-height:1.3">11<br><span style="font-size:10px;color:#666">4.5h</span></td>
                <td style="padding:5px 3px;text-align:center;border-bottom:1px solid #f3f4f6;background:#ffffff;font-size:13px;color:#ccc;line-height:1.3">-</td>
                <td style="padding:5px 3px;text-align:center;border-bottom:1px solid #f3f4f6;background:#ffffff;font-size:13px;color:#333;line-height:1.3">13<br><span style="font-size:10px;color:#666">5.0h</span></td>
                <td style="padding:5px 3px;text-align:center;border-bottom:1px solid #f3f4f6;background:#ffffff;font-size:13px;color:#ccc;line-height:1.3">-</td>
                <td style="padding:5px 3px;text-align:right;border-bottom:1px solid #f3f4f6;background:#ffffff;font-size:13px;color:#333;font-weight:600;vertical-align:middle">28.0h</td>
            </tr>
            <tr>
                <td style="padding:5px 3px;text-align:center;border-bottom:1px solid #f3f4f6;background:#f9fafb;font-size:13px;color:#333;line-height:1.3">15<br><span style="font-size:10px;color:#666">6.0h</span></td>
                <td style="padding:5px 3px;text-align:center;border-bottom:1px solid #f3f4f6;background:#f9fafb;font-size:13px;color:#333;line-height:1.3">16<br><span style="font-size:10px;color:#666">4.0h</span></td>
                <td style="padding:5px 3px;text-align:center;border-bottom:1px solid #f3f4f6;background:#f9fafb;font-size:13px;color:#333;line-height:1.3">17<br><span style="font-size:10px;color:#666">5.5h</span></td>
                <td style="padding:5px 3px;text-align:center;border-bottom:1px solid #f3f4f6;background:#f9fafb;font-size:13px;color:#ccc;line-height:1.3">-</td>
                <td style="padding:5px 3px;text-align:center;border-bottom:1px solid #f3f4f6;background:#f9fafb;font-size:13px;color:#333;line-height:1.3">19<br><span style="font-size:10px;color:#666">3.0h</span></td>
                <td style="padding:5px 3px;text-align:center;border-bottom:1px solid #f3f4f6;background:#f9fafb;font-size:13px;color:#333;line-height:1.3">20<br><span style="font-size:10px;color:#666">2.0h</span></td>
                <td style="padding:5px 3px;text-align:center;border-bottom:1px solid #f3f4f6;background:#f9fafb;font-size:13px;color:#ccc;line-height:1.3">-</td>
                <td style="padding:5px 3px;text-align:right;border-bottom:1px solid #f3f4f6;background:#f9fafb;font-size:13px;color:#333;font-weight:600;vertical-align:middle">20.5h</td>
            </tr>
            <tr>
                <td style="padding:5px 3px;text-align:center;border-bottom:1px solid #f3f4f6;background:#ffffff;font-size:13px;color:#333;line-height:1.3">22<br><span style="font-size:10px;color:#666">4.5h</span></td>
                <td style="padding:5px 3px;text-align:center;border-bottom:1px solid #f3f4f6;background:#ffffff;font-size:13px;color:#333;line-height:1.3">23<br><span style="font-size:10px;color:#666">3.0h</span></td>
                <td style="padding:5px 3px;text-align:center;border-bottom:1px solid #f3f4f6;background:#ffffff;font-size:13px;color:#333;line-height:1.3">24<br><span style="font-size:10px;color:#666">2.5h</span></td>
                <td style="padding:5px 3px;text-align:center;border-bottom:1px solid #f3f4f6;background:#ffffff;font-size:13px;color:#ccc;line-height:1.3">-</td>
                <td style="padding:5px 3px;text-align:center;border-bottom:1px solid #f3f4f6;background:#ffffff;font-size:13px;color:#ccc;line-height:1.3">-</td>
                <td style="padding:5px 3px;text-align:center;border-bottom:1px solid #f3f4f6;background:#ffffff;font-size:13px;color:#ccc;line-height:1.3">-</td>
                <td style="padding:5px 3px;text-align:center;border-bottom:1px solid #f3f4f6;background:#ffffff;font-size:13px;color:#ccc;line-height:1.3">-</td>
                <td style="padding:5px 3px;text-align:right;border-bottom:1px solid #f3f4f6;background:#ffffff;font-size:13px;color:#333;font-weight:600;vertical-align:middle">10.0h</td>
            </tr>
            <tr>
                <td style="padding:5px 3px;text-align:center;border-bottom:1px solid #f3f4f6;background:#f9fafb;font-size:13px;color:#333;line-height:1.3">29<br><span style="font-size:10px;color:#666">4.0h</span></td>
                <td style="padding:5px 3px;text-align:center;border-bottom:1px solid #f3f4f6;background:#f9fafb;font-size:13px;color:#333;line-height:1.3">30<br><span style="font-size:10px;color:#666">2.0h</span></td>
                <td style="padding:5px 3px;text-align:center;border-bottom:1px solid #f3f4f6;background:#f9fafb;font-size:13px;color:#ccc;line-height:1.3">-</td>
                <td style="padding:5px 3px;text-align:center;border-bottom:1px solid #f3f4f6;background:#f9fafb;font-size:13px;color:#ccc;line-height:1.3">-</td>
                <td style="padding:5px 3px;text-align:center;border-bottom:1px solid #f3f4f6;background:#f9fafb;font-size:13px;color:#ccc;line-height:1.3">-</td>
                <td style="padding:5px 3px;text-align:center;border-bottom:1px solid #f3f4f6;background:#f9fafb;font-size:13px;color:#ccc;line-height:1.3">-</td>
                <td style="padding:5px 3px;text-align:center;border-bottom:1px solid #f3f4f6;background:#f9fafb;font-size:13px;color:#ccc;line-height:1.3">-</td>
                <td style="padding:5px 3px;text-align:right;border-bottom:1px solid #f3f4f6;background:#f9fafb;font-size:13px;color:#333;font-weight:600;vertical-align:middle">6.0h</td>
            </tr>
            <tr>
                <td colspan="7" style="padding:8px 4px;text-align:right;border-top:2px solid #e5e7eb;font-size:13px;color:#111;font-weight:600">Total do Mês</td>
                <td style="padding:8px 4px;text-align:right;border-top:2px solid #e5e7eb;font-size:13px;color:#111;font-weight:700">87.2h</td>
            </tr>
        </table>
    `.trim(),
    top_activities: `
        <table style="width:100%;border-collapse:collapse;font-size:13px">
            <tr style="background:#f3f4f6">
                <th style="padding:6px 8px;text-align:left">#</th>
                <th style="padding:6px 8px;text-align:left">Atividade</th>
                <th style="padding:6px 8px;text-align:left">Categoria</th>
                <th style="padding:6px 8px;text-align:right">Duração</th>
                <th style="padding:6px 8px;text-align:right">%</th>
            </tr>
            <tr><td style="padding:6px 8px;color:#666">1°</td><td style="padding:6px 8px">Dashboard de Vendas</td><td style="padding:6px 8px;color:#666">Desenvolvimento</td><td style="padding:6px 8px;text-align:right;font-weight:600">24.0h</td><td style="padding:6px 8px;text-align:right;color:#666">28%</td></tr>
            <tr><td style="padding:6px 8px;color:#666">2°</td><td style="padding:6px 8px">Refatorar API</td><td style="padding:6px 8px;color:#666">Desenvolvimento</td><td style="padding:6px 8px;text-align:right;font-weight:600">16.0h</td><td style="padding:6px 8px;text-align:right;color:#666">18%</td></tr>
            <tr><td style="padding:6px 8px;color:#666">3°</td><td style="padding:6px 8px">Code Review</td><td style="padding:6px 8px;color:#666">Desenvolvimento</td><td style="padding:6px 8px;text-align:right;font-weight:600">8.0h</td><td style="padding:6px 8px;text-align:right;color:#666">9%</td></tr>
            <tr><td style="padding:6px 8px;color:#666">4°</td><td style="padding:6px 8px">Suporte Cliente X</td><td style="padding:6px 8px;color:#666">Suporte</td><td style="padding:6px 8px;text-align:right;font-weight:600">6.5h</td><td style="padding:6px 8px;text-align:right;color:#666">7%</td></tr>
            <tr><td style="padding:6px 8px;color:#666">5°</td><td style="padding:6px 8px">Daily Standup</td><td style="padding:6px 8px;color:#666">Reunião</td><td style="padding:6px 8px;text-align:right;font-weight:600">5.0h</td><td style="padding:6px 8px;text-align:right;color:#666">6%</td></tr>
        </table>
    `.trim(),
    csv_note: '<p style="font-size:13px;color:#555;margin:12px 0">📎 Segue em anexo a lista completa de atividades do mês em formato CSV.</p>',
    weekly_summary: `
        <table style="width:100%;border-collapse:collapse;font-size:13px">
            <tr style="background:#f3f4f6">
                <th style="padding:6px 8px;text-align:left">Semana</th>
                <th style="padding:6px 8px;text-align:left">Dias</th>
                <th style="padding:6px 8px;text-align:right">Total</th>
                <th style="padding:6px 8px;text-align:right">Interrupções</th>
            </tr>
            <tr><td style="padding:6px 8px;font-weight:600">Semana 1</td><td style="padding:6px 8px;color:#666;font-size:12px">01 Jun - 07 Jun</td><td style="padding:6px 8px;text-align:right">34.0h</td><td style="padding:6px 8px;text-align:right">5</td></tr>
            <tr><td style="padding:6px 8px;font-weight:600">Semana 2</td><td style="padding:6px 8px;color:#666;font-size:12px">08 Jun - 14 Jun</td><td style="padding:6px 8px;text-align:right">28.0h</td><td style="padding:6px 8px;text-align:right">3</td></tr>
            <tr><td style="padding:6px 8px;font-weight:600">Semana 3</td><td style="padding:6px 8px;color:#666;font-size:12px">15 Jun - 21 Jun</td><td style="padding:6px 8px;text-align:right">20.5h</td><td style="padding:6px 8px;text-align:right">4</td></tr>
            <tr style="font-weight:700"><td colspan="2" style="padding:8px;border-top:2px solid #e5e7eb">Total do Mês</td><td style="padding:8px;text-align:right;border-top:2px solid #e5e7eb">87.2h</td><td style="padding:8px;text-align:right;border-top:2px solid #e5e7eb">12</td></tr>
        </table>
    `.trim(),
}

const previewHtml = computed(() => {
    let html = form.report_template || ''
    for (const [key, value] of Object.entries(sampleData)) {
        html = html.replaceAll(`{{${key}}}`, value)
    }
    const bodyMatch = html.match(/<body[^>]*>([\s\S]*)<\/body>/i)
    if (bodyMatch) return bodyMatch[1]
    return html
})

function save() {
    router.post('/settings', form)
}
</script>

<script>
export const defaultTemplate = `<!--[if mso]>
<table role="presentation" width="600" align="center" cellpadding="0" cellspacing="0" style="width:600px"><tr><td>
<![endif]-->
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;margin:0 auto">
<tr><td style="padding:24px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;color:#333;font-size:14px;line-height:1.5">
    <h2 style="margin:0 0 20px;font-size:22px;color:#111">Relatório de {{user_name}} - {{month}}</h2>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:20px">
    <tr>
        <td style="padding:16px;background:#f3f4f6;text-align:center" bgcolor="#f3f4f6">
            <div style="font-size:28px;font-weight:700;color:#111">{{total_hours}}h</div>
            <div style="font-size:12px;color:#666">Horas Trabalhadas</div>
        </td>
        <td style="width:12px;font-size:1px;line-height:1px" bgcolor="#ffffff">&nbsp;</td>
        <td style="padding:16px;background:#f3f4f6;text-align:center" bgcolor="#f3f4f6">
            <div style="font-size:28px;font-weight:700;color:#111">{{interruptions}}</div>
            <div style="font-size:12px;color:#666">Interrupções</div>
        </td>
        <td style="width:12px;font-size:1px;line-height:1px" bgcolor="#ffffff">&nbsp;</td>
        <td style="padding:16px;background:#f3f4f6;text-align:center" bgcolor="#f3f4f6">
            <div style="font-size:28px;font-weight:700;color:#111">{{avg_focus}}min</div>
            <div style="font-size:12px;color:#666">Méd. Foco</div>
        </td>
    </tr>
    </table>

    <h3 style="margin:20px 0 10px;font-size:16px;color:#111">Distribuição por Categoria</h3>
    {{category_distribution}}

    <h3 style="margin:20px 0 10px;font-size:16px;color:#111">Mês</h3>
    {{month_breakdown}}

    <h3 style="margin:20px 0 10px;font-size:16px;color:#111">Resumo Semanal</h3>
{{weekly_summary}}

    <h3 style="margin:20px 0 10px;font-size:16px;color:#111">Top 5 Atividades</h3>
{{top_activities}}

    <hr style="border:none;border-top:1px solid #e5e7eb;margin:20px 0">

    {{csv_note}}

    <hr style="border:none;border-top:1px solid #e5e7eb;margin:20px 0">
    <p style="font-size:12px;color:#999">Relatório gerado automaticamente pelo WorkFlow Analytics</p>
</td></tr></table>
<!--[if mso]>
</td></tr></table>
<![endif]-->`
</script>
