<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\FaqTopic;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $topics = [
            'Dashboard' => [
                ['question' => 'Como ver minhas horas de trabalho hoje?', 'answer' => 'No Dashboard, o card "Horas Trabalhadas" mostra o total de horas registradas hoje. Abaixo, a Linha do Tempo exibe todas as atividades do dia agrupadas por horário.'],
                ['question' => 'O que significa o card "Desenvolvimento" no Dashboard?', 'answer' => 'O card "Desenvolvimento" mostra o total de horas gastas em atividades classificadas como "development". As categorias definem se uma atividade é development, support ou meeting.'],
                ['question' => 'Como funciona a Linha do Tempo no Dashboard?', 'answer' => 'A Linha do Tempo mostra as atividades dos últimos 3 dias, agrupadas por data e horário. Atividades em andamento aparecem com um indicador verde pulsante.'],
            ],
            'Atividades' => [
                ['question' => 'Como iniciar uma atividade?', 'answer' => 'Clique no campo "O que você vai fazer?" na barra superior ou use o atalho Ctrl+K para abrir a paleta de comandos. Digite o título, selecione uma categoria e clique em "Iniciar".'],
                ['question' => 'Como pausar e retomar uma atividade?', 'answer' => 'Na barra superior, clique no botão de pausa ao lado da atividade em andamento. Para retomar, clique no botão de play. Você também pode pausar automaticamente ao iniciar outra atividade.'],
                ['question' => 'O que é uma interrupção?', 'answer' => 'Uma interrupção é uma pausa não planejada na sua atividade atual (ex: reunião urgente, telefonema). Ao registrar uma interrupção, a atividade atual é pausada automaticamente.'],
                ['question' => 'Como registrar uma atividade manualmente?', 'answer' => 'Use o atalho Ctrl+K e selecione "Registrar entrada manual", ou clique no botão de registro manual na barra superior. Preencha o título, categoria, horário de início e fim.'],
                ['question' => 'Como editar ou excluir uma atividade?', 'answer' => 'Na página de Atividades, clique em uma atividade para ver os detalhes. Use os botões de ação (editar, excluir) na coluna Ações. Você pode editar título, categoria, projeto, módulo, horários e prioridade.'],
                ['question' => 'O que significam os status das atividades?', 'answer' => 'Os status são: "Em andamento" (atividade ativa), "Pausado" (atividade temporariamente parada) e "Concluído" (atividade finalizada). Você pode reabrir uma atividade concluída se necessário.'],
            ],
            'Categorias' => [
                ['question' => 'O que são categorias?', 'answer' => 'Categorias classificam suas atividades por tipo (ex: Desenvolvimento, Suporte, Reunião). Cada categoria tem uma cor e pode ser configurada como global, por setor ou por usuário.'],
                ['question' => 'Como criar uma nova categoria?', 'answer' => 'Ao iniciar ou editar uma atividade, clique em "+ Nova Categoria". Digite o nome e confirme. A categoria será criada automaticamente como "usuário" (visível apenas para você).'],
            ],
            'Projetos vs Módulos' => [
                ['question' => 'Qual a diferença entre Projeto e Módulo?', 'answer' => 'O Módulo representa a camada do sistema onde a atividade foi realizada (ex: ERP, API, CRM). O Projeto representa um conjunto de etapas a serem seguidas (ex: "Melhorias e Correções - Compras"). Módulos são criados rapidamente ao registrar atividades; Projetos são gerenciados por supervisores e administradores.'],
                ['question' => 'Como criar um novo módulo?', 'answer' => 'Ao iniciar ou editar uma atividade, digite o nome do módulo no campo "Módulo". Se não existir, a opção "+ Criar" aparecerá. O módulo é criado automaticamente.'],
                ['question' => 'O que são Projetos com visibilidade?', 'answer' => 'Projetos podem ser: Global (visível para todos), Setor (visível apenas para um setor específico) ou Usuário (visível apenas para usuários específicos). Isso permite controlar quem pode associar atividades a cada projeto.'],
            ],
            'Estatísticas' => [
                ['question' => 'Como vejo minhas estatísticas mensais?', 'answer' => 'Acesse a página de Estatísticas para ver gráficos de distribuição por categoria, projeto, módulo, além do resumo diário e semanal do mês. Navegue entre os meses usando os botões de seta.'],
                ['question' => 'O que os gráficos de pizza mostram?', 'answer' => 'Os gráficos de pizza (Doughnut) mostram a distribuição do seu tempo entre categorias, projetos e módulos. Passe o mouse sobre cada fatia para ver o detalhe em horas e percentual.'],
                ['question' => 'Como enviar o relatório mensal por e-mail?', 'answer' => 'Na página de Estatísticas, clique em "Enviar Relatório". O relatório será enviado para o destinatário configurado nas Configurações. Você pode personalizar o template do relatório.'],
            ],
            'Supervisor' => [
                ['question' => 'O que um supervisor pode gerenciar?', 'answer' => 'Supervisores podem gerenciar projetos do seu setor, visualizar relatórios dos usuários do setor e atribuir usuários a projetos. O menu "Meu Setor" no sidebar dá acesso a essas funcionalidades.'],
                ['question' => 'Como criar um projeto como supervisor?', 'answer' => 'Acesse "Meu Setor > Projetos" e clique em "Novo Projeto". Defina nome, descrição, cor e visibilidade. Projetos de setor ficam visíveis apenas para usuários do setor selecionado.'],
            ],
            'Administração' => [
                ['question' => 'Como gerenciar setores?', 'answer' => 'Acesse "Admin > Setores". Você pode criar, editar e excluir setores. Cada setor pode ter um supervisor responsável e uma descrição.'],
                ['question' => 'Como gerenciar categorias globalmente?', 'answer' => 'Acesse "Admin > Categorias". Você pode criar categorias com visibilidade global, por setor ou por usuário, definir cores e ordem de exibição.'],
                ['question' => 'Como gerenciar usuários?', 'answer' => 'Acesse "Admin > Usuários". Você pode editar o perfil dos usuários, alterar setor e papel (user, supervisor, admin, dev, suporte).'],
                ['question' => 'Como gerenciar o FAQ?', 'answer' => 'Acesse "Admin > FAQ". Você pode criar, editar, reordenar e desativar perguntas, além de gerenciar tópicos. O FAQ fica disponível para todos os usuários através do botão "?" no canto inferior direito.'],
            ],
            'Reports' => [
                ['question' => 'Como reportar um problema?', 'answer' => 'Clique no seu nome no canto superior direito e selecione "Reportar Erro". Descreva o problema, selecione a gravidade e revise os detalhes técnicos capturados automaticamente (URL, navegador, resolução).'],
                ['question' => 'O que acontece depois que eu reporto um problema?', 'answer' => 'O relatório é enviado para a equipe de administração. O status do report pode ser: Aberto, Em andamento ou Resolvido. Você pode acompanhar o status consultando a equipe responsável.'],
            ],
            'Conta' => [
                ['question' => 'Como alterar meus dados de perfil?', 'answer' => 'Clique no seu nome no canto superior direito e selecione "Perfil". Lá você pode alterar nome, e-mail e senha.'],
                ['question' => 'Como configurar o horário de almoço?', 'answer' => 'Acesse "Configurações" no sidebar. No campo "Horário de Almoço", defina os horários de início e fim. O sistema desconsiderará esse período no cálculo de horas trabalhadas.'],
                ['question' => 'Como configurar o template do relatório?', 'answer' => 'Acesse "Configurações". Você pode editar o template HTML do relatório mensal e ver o preview ao vivo. Use variáveis como {{total_hours}}, {{category_distribution}}, etc.'],
                ['question' => 'Quais atalhos de teclado estão disponíveis?', 'answer' => 'Ctrl+K (ou Cmd+K no Mac): abre a paleta de comandos. Pelo paleta você pode iniciar atividade rápida, registrar entrada manual, registrar interrupção e acessar outras funções.'],
            ],
        ];

        $order = 0;

        foreach ($topics as $topicName => $faqs) {
            $order += 10;
            $topic = FaqTopic::create([
                'name' => $topicName,
                'slug' => \Illuminate\Support\Str::slug($topicName),
                'sort_order' => $order,
                'is_active' => true,
            ]);

            foreach ($faqs as $i => $faqData) {
                Faq::create([
                    'question' => $faqData['question'],
                    'answer' => $faqData['answer'],
                    'sort_order' => ($i + 1) * 10,
                    'is_active' => true,
                    'faq_topic_id' => $topic->id,
                ]);
            }
        }
    }
}
