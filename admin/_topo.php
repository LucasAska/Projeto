<?php
// Cabeçalho comum das telas de administração.
// A página deve definir $titulo e, opcionalmente, $larguraClasse e $secaoAtual.
$titulo        = $titulo ?? 'Administração';
$larguraClasse = $larguraClasse ?? 'container-admin';
$secaoAtual    = $secaoAtual ?? '';
$nomeAdmin     = e($_SESSION['usuario_nome'] ?? 'Administrador');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titulo) ?> | Milka Mics</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
    <style>
        /* Ajustes das telas de administração, guardados aqui para não depender de outros arquivos */

        .voltar-link {
            display: inline-block;
            margin-bottom: 18px;
            color: var(--cor-principal);
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
        }

        .voltar-link:hover {
            text-decoration: underline;
        }

        select {
            width: 100%;
            height: 44px;
            padding: 0 13px;
            border: 1px solid var(--cor-borda-campo);
            border-radius: 6px;
            background: var(--cor-branco);
            color: var(--cor-texto);
            font-size: 14px;
            font-family: inherit;
            outline: none;
            cursor: pointer;
        }

        select:focus {
            border-color: var(--cor-principal);
            box-shadow: 0 0 0 3px rgba(163, 79, 112, 0.12);
        }

        input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin: 0;
            accent-color: var(--cor-principal);
            cursor: pointer;
        }

        input[type="file"] {
            height: auto;
            padding: 9px 10px;
            font-size: 13px;
            cursor: pointer;
        }

        .opcao {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 6px 0 18px;
            font-size: 14px;
            font-weight: normal;
            color: var(--cor-label);
            cursor: pointer;
        }

        .confirmacao {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 22px;
        }

        .aviso-texto {
            color: #856404;
            font-size: 13px;
        }

        .card-estreito {
            max-width: 560px;
            margin: 0 auto;
        }

        .miniatura {
            width: 56px;
            height: 56px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid var(--cor-borda-campo);
            display: block;
        }

        .miniatura-grande {
            width: 180px;
            height: 180px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid var(--cor-borda-campo);
            display: block;
            margin-bottom: 4px;
        }

        .imagem-atual {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 6px;
            min-height: 44px;
            justify-content: center;
        }

        .tabela-admin td {
            vertical-align: middle;
        }

        .tag-ativo {
            background: #d4edda;
            color: #155724;
        }

        .tag-inativo {
            background: #f1f1f1;
            color: #777;
            border: 1px solid #ddd;
        }

        .alerta-erro {
            background: #f8d7da;
            color: #721c24;
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .btn-perigo {
            background: #c92a2a;
            color: #fff;
            border: none;
        }

        .btn-perigo:hover {
            background: #a61e1e;
        }
    </style>
    <link rel="stylesheet" href="/assets/css/cabecalho.css">
</head>
<body>

<?php $cabecalhoModo = 'admin'; require __DIR__ . '/../includes/cabecalho.php'; ?>

<main>
    <div class="<?= e($larguraClasse) ?>">
        <a class="voltar-link" href="/admin.php">&larr; Voltar ao painel</a>