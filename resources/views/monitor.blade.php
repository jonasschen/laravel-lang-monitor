<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Lang Monitor UI - by Jonas Schen</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    </head>
<body>
    <div class="container" id="lm-app" style="padding:16px;max-width:1100px;margin:0 auto;">
        <h1 class="h3">Lang Monitor UI</h1>

        <div class="card" style="padding:16px;margin-bottom:16px;">
            <div id="dropzone" style="border:2px dashed #bbb;padding:20px;border-radius:8px;text-align:center;">
                Cole aqui o conteúdo JSON ou PHP.<br>
                Ou arraste seu arquivo aqui.<br>
                Ou
                <label style="color:#0a58ca;cursor:pointer;">
                    <u>clique aqui</u>
                    <input type="file" id="file-input" accept=".json,.php,application/json,text/x-php" style="display:none">
                </label>
                para selecionar um arquivo do seu computador.
            </div>

            <div style="margin-top:12px;display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                <input type="text" id="search" class="form-control" placeholder="Buscar chave ou valor" style="max-width:280px;">
                <label style="display:flex;gap:6px;align-items:center;">
                    <input type="checkbox" id="missingOnly"> Somente faltantes
                </label>
                <label style="display:flex;gap:6px;align-items:center;">
                    <input type="checkbox" id="dupOnly"> Somente duplicadas
                </label>
                <label style="display:flex;gap:6px;align-items:center;">
                    Formato
                    <select id="file-format" class="form-control" style="width:auto;">
                        <option value="json" selected>JSON (.json)</option>
                        <option value="php">PHP (.php)</option>
                    </select>
                </label>
                <button class="btn btn-sm btn-primary" id="btn-download">Baixar JSON atualizado</button>
                <button class="btn btn-sm btn-success" id="btn-save">Salvar…</button>
                <button class="btn btn-sm btn-outline-primary" id="btn-copy">Copiar para a área de transferência</button>
            </div>
        </div>

        <div id="lm-stats" style="margin:8px 0 12px; display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
            <span id="lm-badge-missing" class="badge bg-warning text-dark" style="padding:.4rem .6rem; border-radius:.5rem;">
                Faltantes: 0 / 0
            </span>
            <span id="lm-badge-dup" class="badge bg-danger" style="padding:.4rem .6rem; border-radius:.5rem;">
                Duplicadas: 0
            </span>
        </div>

        <div class="card" style="padding:16px;margin-bottom:16px;">
            <button class="btn btn-sm btn-secondary" id="btn-add-row">+ Nova linha</button>
            <button class="btn btn-sm btn-outline-secondary" id="btn-sort">Ordenar A→Z</button>
        </div>

        <div class="table-responsive" style="max-height:60vh;overflow:auto;border:1px solid #eee;border-radius:8px;">
            <table class="table table-striped table-hover" id="translations-table" style="width:100%;">
                <thead>
                <tr>
                    <th style="width:50%;">Chave</th>
                    <th style="width:50%;">Valor</th>
                    <th style="width:110px;">Ações</th>
                </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <div id="lm-pager" style="display:flex;align-items:center;gap:10px;justify-content:flex-end;margin-top:10px;flex-wrap:wrap;">
            <label style="display:flex;align-items:center;gap:6px;">
                Itens por página
                <select id="page-size" class="form-select form-select-sm" style="width:auto;">
                    <option>25</option>
                    <option selected>50</option>
                    <option>100</option>
                    <option>200</option>
                </select>
            </label>

            <button class="btn btn-sm btn-outline-secondary" id="btn-prev">← Anterior</button>
            <span id="page-info" style="min-width:120px;text-align:center;">Página 1 de 1</span>
            <button class="btn btn-sm btn-outline-secondary" id="btn-next">Próxima →</button>
        </div>
    </div>

    <script src="{{asset('vendor/lang-monitor/lang-monitor.js')}}"></script>
    <script>
        window.LANG_MONITOR_SAVE_URL = @json(route('lang-monitor.save'));
        window.LANG_MONITOR_CSRF = @json(csrf_token());
    </script>
</body>
</html>
