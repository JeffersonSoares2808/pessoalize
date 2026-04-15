<?php
/**
 * Pessoalize - Concluir Lembrete
 */
$db = Database::getInstance();

if (!$id) redirect('index.php?module=agenda');

$lembrete = $db->fetch("SELECT * FROM lembretes WHERE id = ?", [$id]);
if (!$lembrete) {
    setFlash('error', 'Lembrete não encontrado.');
    redirect('index.php?module=agenda');
}

try {
    $db->update('lembretes', ['status' => 'concluido'], 'id = ?', [$id]);

    // Se recorrente, criar próxima ocorrência
    if ($lembrete['recorrencia'] !== 'nenhuma') {
        $nextDate = $lembrete['data_lembrete'];
        switch ($lembrete['recorrencia']) {
            case 'diaria':
                $nextDate = date('Y-m-d', strtotime($nextDate . ' +1 day'));
                break;
            case 'semanal':
                $nextDate = date('Y-m-d', strtotime($nextDate . ' +1 week'));
                break;
            case 'mensal':
                $nextDate = date('Y-m-d', strtotime($nextDate . ' +1 month'));
                break;
            case 'anual':
                $nextDate = date('Y-m-d', strtotime($nextDate . ' +1 year'));
                break;
        }

        $db->insert('lembretes', [
            'titulo' => $lembrete['titulo'],
            'descricao' => $lembrete['descricao'],
            'tipo' => $lembrete['tipo'],
            'data_lembrete' => $nextDate,
            'hora_lembrete' => $lembrete['hora_lembrete'],
            'recorrencia' => $lembrete['recorrencia'],
            'prioridade' => $lembrete['prioridade'],
            'status' => 'pendente',
            'funcionario_id' => $lembrete['funcionario_id'],
            'conta_id' => $lembrete['conta_id'],
            'observacoes' => $lembrete['observacoes'],
            'criado_por' => $_SESSION['user_id'] ?? null,
        ]);

        setFlash('success', 'Lembrete concluído! Próxima ocorrência criada para ' . date('d/m/Y', strtotime($nextDate)) . '.');
    } else {
        setFlash('success', 'Lembrete concluído com sucesso!');
    }
} catch (Exception $e) {
    setFlash('error', 'Erro ao concluir lembrete.');
}

redirect('index.php?module=agenda');
