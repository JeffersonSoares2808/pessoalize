<?php
/**
 * Pessoalize - Gerenciar Participantes de Treinamento
 */
$db = Database::getInstance();

if (!$id || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php?module=treinamentos');
}

validateCsrf();

$acao = $_POST['acao'] ?? '';

switch ($acao) {
    case 'adicionar':
        $funcionarioId = (int)($_POST['funcionario_id'] ?? 0);
        if (!$funcionarioId) {
            setFlash('error', 'Selecione um funcionário.');
            break;
        }

        // Verificar se o treinamento existe
        $treinamento = $db->fetch("SELECT id FROM treinamentos WHERE id = ?", [$id]);
        if (!$treinamento) {
            setFlash('error', 'Treinamento não encontrado.');
            redirect('index.php?module=treinamentos');
        }

        // Verificar se o funcionário existe e está ativo
        $funcionario = $db->fetch("SELECT id FROM funcionarios WHERE id = ? AND status = 'ativo'", [$funcionarioId]);
        if (!$funcionario) {
            setFlash('error', 'Funcionário não encontrado ou inativo.');
            break;
        }

        // Verificar duplicidade
        $existe = $db->fetch(
            "SELECT id FROM treinamento_participantes WHERE treinamento_id = ? AND funcionario_id = ?",
            [$id, $funcionarioId]
        );
        if ($existe) {
            setFlash('error', 'Este funcionário já está inscrito neste treinamento.');
            break;
        }

        try {
            $db->insert('treinamento_participantes', [
                'treinamento_id' => $id,
                'funcionario_id' => $funcionarioId,
                'status' => 'inscrito',
            ]);
            setFlash('success', 'Participante adicionado com sucesso!');
        } catch (Exception $e) {
            setFlash('error', 'Erro ao adicionar participante.');
        }
        break;

    case 'atualizar':
        $participanteId = (int)($_POST['participante_id'] ?? 0);
        if (!$participanteId) {
            setFlash('error', 'Participante não identificado.');
            break;
        }

        $nota = $_POST['nota'] ?? '';
        $nota = $nota !== '' ? (float)str_replace(',', '.', $nota) : null;

        $data = [
            'status' => $_POST['status'] ?? 'inscrito',
            'nota' => $nota,
            'data_conclusao' => $_POST['data_conclusao'] ?: null,
            'observacoes' => trim($_POST['observacoes'] ?? ''),
        ];

        try {
            $db->update('treinamento_participantes', $data, 'id = ? AND treinamento_id = ?', [$participanteId, $id]);
            setFlash('success', 'Participante atualizado com sucesso!');
        } catch (Exception $e) {
            setFlash('error', 'Erro ao atualizar participante.');
        }
        break;

    case 'remover':
        $participanteId = (int)($_POST['participante_id'] ?? 0);
        if (!$participanteId) {
            setFlash('error', 'Participante não identificado.');
            break;
        }

        try {
            // Remover certificado físico
            $part = $db->fetch(
                "SELECT certificado_arquivo FROM treinamento_participantes WHERE id = ? AND treinamento_id = ?",
                [$participanteId, $id]
            );
            if ($part && $part['certificado_arquivo']) {
                $filePath = UPLOADS_PATH . 'certificados/' . $part['certificado_arquivo'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            $db->delete('treinamento_participantes', 'id = ? AND treinamento_id = ?', [$participanteId, $id]);
            setFlash('success', 'Participante removido com sucesso.');
        } catch (Exception $e) {
            setFlash('error', 'Erro ao remover participante.');
        }
        break;

    default:
        setFlash('error', 'Ação inválida.');
}

redirect("index.php?module=treinamentos&action=view&id={$id}");
