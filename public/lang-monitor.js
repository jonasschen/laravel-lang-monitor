// public/lang-monitor.js
(function() {
    // -----------------------
    // STATE
    // -----------------------
    const state = {
        rows: [],            // [{ id, key, value }]
        filteredRows: [],
        lastAddedId: null,

        // paginação
        page: 1,
        pageSize: 50,

        // validação cache
        _emptyKeys: new Set(),
        _dupKeys: new Set(),
    };

    // -----------------------
    // ELEMENTS
    // -----------------------
    const el = {
        table: document.getElementById('translations-table'),
        tbody: null,
        search: document.getElementById('search'),
        missingOnly: document.getElementById('missingOnly'),
        dupOnly: document.getElementById('dupOnly'),
        dropzone: document.getElementById('dropzone'),
        fileInput: document.getElementById('file-input'),
        btnDownload: document.getElementById('btn-download'),
        btnSave: document.getElementById('btn-save'),
        btnAddRow: document.getElementById('btn-add-row'),
        btnSort: document.getElementById('btn-sort'),

        // badges
        statsMissing: document.getElementById('lm-badge-missing'),
        statsDup: document.getElementById('lm-badge-dup'),

        // paginação
        btnPrev: document.getElementById('btn-prev'),
        btnNext: document.getElementById('btn-next'),
        pageInfo: document.getElementById('page-info'),
        pageSizeSel: document.getElementById('page-size'),

        fileFormat: document.getElementById('file-format'),
        btnCopy: document.getElementById('btn-copy'),
    };

    function syncDownloadLabel(){
        el.btnDownload.textContent =
            (el.fileFormat?.value === 'php') ? 'Baixar PHP atualizado' : 'Baixar JSON atualizado';
    }

    // -----------------------
    // LOAD / PARSE
    // -----------------------
    function parsePhpLang(text){
        text = String(text || '').replace(/^\uFEFF/, '');
        const re = /(['"])(.*?)\1\s*=>\s*(['"])((?:\\.|(?!\3).)*?)\3/gs;
        const map = {}; let m;
        while ((m = re.exec(text)) !== null) {
            const key = m[2];
            let val = m[4];
            val = val
                .replace(/\\\\/g, '\\')
                .replace(/\\'/g, "'")
                .replace(/\\"/g, '"')
                .replace(/\\n/g, '\n').replace(/\\r/g, '\r').replace(/\\t/g, '\t');
            map[key] = val;
        }
        if (!Object.keys(map).length) throw new Error('Nenhum par key => value encontrado no PHP.');
        return map;
    }

    function setRowsFromMap(map){
        const arr = [];
        if (map && typeof map === 'object' && !Array.isArray(map)) {
            let i = 0;
            Object.keys(map).forEach(k=>{
                arr.push({ id:`row_${Date.now()}_${i++}`, key:String(k), value: map[k]==null?'':String(map[k]) });
            });
        } else { alert('Formato inválido. Esperado { "chave": "valor" }.'); return; }
        arr.sort((a,b)=> a.key.localeCompare(b.key));
        state.rows = arr;
        applyFilters();
    }

    function importFromText(raw, filenameHint=''){
        const s = String(raw || '');
        const looksPhp = filenameHint.toLowerCase().endsWith('.php') || s.trimStart().startsWith('<?php');
        const looksJson = !looksPhp; // tentativa padrão

        let map;
        if (looksPhp) {
            map = parsePhpLang(s);
        } else {
            try { map = JSON.parse(s); }
            catch {
                // fallback: pode ser PHP sem tag inicial
                map = parsePhpLang(s);
            }
        }
        setRowsFromMap(map);
    }

    function readFile(file) {
        const reader = new FileReader();
        reader.onload = () => {
            try {
                const raw = String(reader.result || '');
                importFromText(raw, file?.name || '');
            } catch (e) {
                console.error(e);
                alert('Arquivo inválido. Envie JSON ou PHP de lang.');
            }
        };

        reader.readAsText(file, 'utf-8');
    }

    // -----------------------
    // VALIDATION
    // -----------------------
    function validateRows(rows) {
        const emptyKeys = new Set();
        const dupKeys = new Set();
        const seen = new Map(); // key -> id

        rows.forEach(r => {
            const k = (r.key || '').trim();
            if (!k) { emptyKeys.add(r.id); return; }
            if (seen.has(k)) { dupKeys.add(r.id); dupKeys.add(seen.get(k)); }
            else { seen.set(k, r.id); }
        });

        return { emptyKeys, dupKeys };
    }

    function recomputeValidation() {
        const { emptyKeys, dupKeys } = validateRows(state.rows);
        state._emptyKeys = emptyKeys;
        state._dupKeys = dupKeys;
    }

    function paintKeyValidation(inputEl, rowId) {
        const isEmpty = state._emptyKeys?.has(rowId);
        const isDup   = state._dupKeys?.has(rowId);

        if (isEmpty || isDup) {
            inputEl.style.borderColor = '#dc3545';
            inputEl.style.background = '#fff5f5';
            inputEl.title = isEmpty ? 'Chave vazia' : 'Chave duplicada';
        } else {
            inputEl.style.borderColor = '';
            inputEl.style.background = '';
            inputEl.title = '';
        }
    }

    function countMissingValues(rows) {
        let c = 0;
        rows.forEach(r => {
            const keyEmpty = !(r.key && r.key.trim().length);
            const valEmpty = !(r.value && r.value.trim().length);
            if (keyEmpty || valEmpty) c++;
        });
        return c;
    }

    // -----------------------
    // STATS BADGES
    // -----------------------
    function updateStats() {
        const total = state.rows.length;
        const missing = countMissingValues(state.rows);
        const { dupKeys } = validateRows(state.rows);
        const dups = dupKeys.size;

        if (el.statsMissing) {
            el.statsMissing.textContent = `Faltantes: ${missing} / ${total}`;
            if (missing === 0) {
                el.statsMissing.style.opacity = '.6';
                el.statsMissing.classList?.remove('bg-warning','text-dark');
                el.statsMissing.classList?.add('bg-secondary');
            } else {
                el.statsMissing.style.opacity = '1';
                el.statsMissing.classList?.remove('bg-secondary');
                el.statsMissing.classList?.add('bg-warning','text-dark');
            }
        }

        if (el.statsDup) {
            el.statsDup.textContent = `Duplicadas: ${dups}`;
            if (dups === 0) {
                el.statsDup.style.opacity = '.6';
                el.statsDup.classList?.remove('bg-danger');
                el.statsDup.classList?.add('bg-secondary');
            } else {
                el.statsDup.style.opacity = '1';
                el.statsDup.classList?.remove('bg-secondary');
                el.statsDup.classList?.add('bg-danger');
            }
        }
    }

    // -----------------------
    // FILTER
    // -----------------------
    function applyFilters() {
        const q = (el.search?.value || '').toLowerCase();
        const missingOnly = !!el.missingOnly?.checked;
        const dupOnly     = !!el.dupOnly?.checked;

        // precisamos do estado de duplicadas antes de filtrar
        recomputeValidation();

        state.filteredRows = state.rows.filter(r => {
            const keyText = (r.key || '');
            const valText = (r.value || '');
            const inQuery =
                !q ||
                keyText.toLowerCase().includes(q) ||
                valText.toLowerCase().includes(q);

            if (!inQuery) return false;

            // regra "faltantes": chave OU valor vazios
            const keyEmpty = !(keyText.trim().length);
            const valEmpty = !(valText.trim().length);
            const isMissing = keyEmpty || valEmpty;

            // regra "duplicadas": chave aparece mais de uma vez
            const isDup = state._dupKeys?.has(r.id);

            // combinar switches:
            // - se ambos marcados, exige (faltante OU duplicada)
            // - se só um marcado, aplica o respectivo
            if (missingOnly && dupOnly) return isMissing || isDup;
            if (missingOnly)            return isMissing;
            if (dupOnly)                return isDup;

            return true;
        });

        // sempre que filtra, volta pra primeira página
        state.page = 1;
        renderBody();
        updatePager();
        updateStats();
    }

    // -----------------------
    // PAGINATION
    // -----------------------
    function totalPages() {
        const n = Math.ceil((state.filteredRows?.length || 0) / state.pageSize);
        return Math.max(1, n || 1);
    }
    function clampPage(p) {
        return Math.min(Math.max(1, p), totalPages());
    }

    function getPageSlice() {
        const start = (state.page - 1) * state.pageSize;
        return state.filteredRows.slice(start, start + state.pageSize);
    }

    function updatePager() {
        el.pageInfo.textContent = `Página ${state.page} de ${totalPages()}`;
        el.btnPrev.disabled = state.page <= 1;
        el.btnNext.disabled = state.page >= totalPages();
    }

    function goToPage(p) {
        state.page = clampPage(p);
        renderBody();
        updatePager();
        const scrollBox = el.table?.parentElement;
        if (scrollBox?.scrollTo) scrollBox.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function autoResizeTextarea(el) {
        const minHeight = 34; // altura mínima em px
        el.style.height = 'auto';
        el.style.height = Math.max(el.scrollHeight, minHeight) + 'px';
    }

    // -----------------------
    // RENDER
    // -----------------------
    function renderBody() {
        if (!el.tbody) { el.tbody = el.table?.querySelector('tbody'); }
        if (!el.tbody) return;
        el.tbody.innerHTML = '';

        // (re)calcula validação antes de pintar
        recomputeValidation();

        const pageRows = getPageSlice();

        pageRows.forEach((row, idxOnPage) => {
            const tr = document.createElement('tr');

            // --- CHAVE ---
            const tdKey = document.createElement('td');
            const keyInput = document.createElement('textarea');
            keyInput.value = row.key || '';
            keyInput.style.width = '100%';
            keyInput.style.fontFamily = 'monospace';
            keyInput.style.resize = 'none';
            keyInput.style.minHeight = '34px'; // garante mínimo via CSS
            keyInput.dataset.id = row.id;
            keyInput.placeholder = '— chave —';

            paintKeyValidation(keyInput, row.id);
            autoResizeTextarea(keyInput);

            keyInput.addEventListener('input', (e) => {
                autoResizeTextarea(e.target);

                const rid = e.target.dataset.id;
                const baseIdx = state.rows.findIndex(r => r.id === rid);
                if (baseIdx >= 0) state.rows[baseIdx].key = e.target.value;

                // reflete também na coleção filtrada correspondente
                const globalIdx = (state.page - 1) * state.pageSize + idxOnPage;
                if (state.filteredRows[globalIdx]) state.filteredRows[globalIdx].key = e.target.value;

                // revalida só esse input
                recomputeValidation();
                paintKeyValidation(e.target, rid);
                updateStats();
            });

            keyInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    const inputs = el.tbody.querySelectorAll('input, textarea');
                    const i = Array.prototype.indexOf.call(inputs, e.currentTarget);
                    if (i >= 0 && i + 1 < inputs.length) inputs[i + 1].focus();
                }
            });

            if (state.lastAddedId && row.id === state.lastAddedId) {
                setTimeout(() => keyInput.focus(), 0);
            }
            tdKey.appendChild(keyInput);
            tr.appendChild(tdKey);

            // --- VALOR ---
            const tdVal = document.createElement('td');
            const valInput = document.createElement('textarea');
            valInput.value = row.value || '';
            valInput.style.width = '100%';
            valInput.style.fontFamily = 'monospace';
            valInput.style.resize = 'none';
            valInput.style.minHeight = '34px'; // garante mínimo via CSS
            valInput.dataset.id = row.id;

            autoResizeTextarea(valInput);

            valInput.addEventListener('input', (e) => {
                autoResizeTextarea(e.target);

                const rid = e.target.dataset.id;
                const baseIdx = state.rows.findIndex(r => r.id === rid);
                if (baseIdx >= 0) state.rows[baseIdx].value = e.target.value;

                const globalIdx = (state.page - 1) * state.pageSize + idxOnPage;
                if (state.filteredRows[globalIdx]) state.filteredRows[globalIdx].value = e.target.value;

                if ((e.target.value || '').trim().length) {
                    tdVal.style.background = '';
                } else {
                    tdVal.style.background = '#fff7f7';
                }
                updateStats();
            });

            valInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    const inputs = el.tbody.querySelectorAll('input, textarea');
                    const i = Array.prototype.indexOf.call(inputs, e.currentTarget);
                    if (i >= 0 && i + 1 < inputs.length) inputs[i + 1].focus();
                }
            });

            if (!row.value) {
                valInput.placeholder = '— faltante —';
                tdVal.style.background = '#fff7f7';
            }
            tdVal.appendChild(valInput);
            tr.appendChild(tdVal);

            // --- AÇÕES ---
            const tdActions = document.createElement('td');
            tdActions.style.whiteSpace = 'nowrap';

            const delBtn = document.createElement('button');
            delBtn.type = 'button';
            delBtn.textContent = 'Remover';
            delBtn.className = 'btn btn-sm btn-outline-danger';
            delBtn.dataset.id = row.id;
            delBtn.addEventListener('click', (e) => {
                const rid = e.currentTarget.dataset.id;
                if (confirm('Remover esta linha?')) removeRowById(rid);
            });

            tdActions.appendChild(delBtn);
            tr.appendChild(tdActions);

            el.tbody.appendChild(tr);
        });
    }

    // -----------------------
    // ADD / REMOVE / SORT
    // -----------------------
    function addBlankRow() {
        const newId = `row_${Date.now()}_${Math.floor(Math.random()*1e6)}`;
        state.rows.push({ id: newId, key: '', value: '' });
        state.lastAddedId = newId;

        // mostra na última página
        applyFilters();                 // recalcula filtrados e reseta page=1
        state.page = totalPages();      // vai pra última
        goToPage(state.page);
        updatePager();
        updateStats();
    }

    function removeRowById(rowId) {
        const ix = state.rows.findIndex(r => r.id === rowId);
        if (ix >= 0) state.rows.splice(ix, 1);
        if (state.lastAddedId === rowId) state.lastAddedId = null;

        applyFilters();
        state.page = clampPage(state.page); // se a página esvaziou, recua
        goToPage(state.page);
        updateStats();
    }

    function sortByKeyAsc() {
        const collator = new Intl.Collator('pt-BR', { sensitivity: 'base', numeric: false });
        state.rows.sort((a, b) => {
            const ak = (a.key || '').trim();
            const bk = (b.key || '').trim();
            const aEmpty = ak.length === 0;
            const bEmpty = bk.length === 0;
            if (aEmpty && bEmpty) return 0;
            if (aEmpty) return 1;          // vazias por último
            if (bEmpty) return -1;
            return collator.compare(ak, bk);
        });
        applyFilters(); // recalcula, vai pra 1ª página, renderiza e atualiza pager + stats
    }

    // -----------------------
    // EXPORT / SAVE
    // -----------------------
    function downloadJSON() {
        recomputeValidation();
        if (state._emptyKeys.size || state._dupKeys.size) {
            alert('Corrija as chaves vazias/duplicadas antes de baixar.');
            return;
        }

        const fmt = (el.fileFormat?.value === 'php') ? 'php' : 'json';
        const obj = {};
        state.rows.forEach(r => { obj[(r.key || '').trim()] = r.value || ''; });

        // helpers inline para evitar mexer no resto do arquivo
        const phpEscape = s => String(s).replace(/\\/g,'\\\\').replace(/'/g,"\\'");
        const toPhp = (map) => {
            const keys = Object.keys(map).sort((a,b)=> a.localeCompare(b));
            const body = keys.map(k => `    '${phpEscape(k)}' => '${phpEscape(map[k] ?? '')}',`).join('\n');
            return `<?php\n\nreturn [\n${body}\n];\n`;
        };

        let dataStr, mime, filename;
        if (fmt === 'php') {
            dataStr = toPhp(obj);
            mime = 'text/x-php';
            filename = 'translations_updated.php';
        } else {
            dataStr = JSON.stringify(obj, null, 2);
            mime = 'application/json';
            filename = 'translations_updated.json';
        }

        const blob = new Blob([dataStr], {type: mime});
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = filename;
        a.click();
        URL.revokeObjectURL(a.href);
    }

    async function saveBackend() {
        recomputeValidation();
        if (state._emptyKeys.size || state._dupKeys.size) {
            alert('Corrija as chaves vazias/duplicadas antes de salvar.');
            return;
        }

        const fmt = (el.fileFormat?.value === 'php') ? 'php' : 'json';
        const obj = {};
        state.rows.forEach(r => { obj[(r.key || '').trim()] = r.value || ''; });

        const strategyEl = document.getElementById('save-strategy');
        const strategy = strategyEl ? strategyEl.value : 'store-only';

        const res = await fetch(window.LANG_MONITOR_SAVE_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.LANG_MONITOR_CSRF,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ data: obj, strategy, format: fmt })
        });

        if (!res.ok) {
            const t = await res.text().catch(() => '');
            alert('Falha ao salvar: ' + t);
        } else {
            alert('Salvo com sucesso!');
        }
    }

    async function copyToClipboard() {
        // validação
        recomputeValidation();
        if (state._emptyKeys.size || state._dupKeys.size) {
            alert('Corrija as chaves vazias/duplicadas antes de copiar.');
            return;
        }

        const fmt = (el.fileFormat?.value === 'php') ? 'php' : 'json';
        const obj = {};
        state.rows.forEach(r => { obj[(r.key || '').trim()] = r.value || ''; });

        // helpers locais (iguais aos do download)
        const phpEscape = s => String(s).replace(/\\/g,'\\\\').replace(/'/g,"\\'");
        const toPhp = (map) => {
            const keys = Object.keys(map).sort((a,b)=> a.localeCompare(b));
            const body = keys.map(k => `    '${phpEscape(k)}' => '${phpEscape(map[k] ?? '')}',`).join('\n');
            return `<?php\n\nreturn [\n${body}\n];\n`;
        };

        const dataStr = (fmt === 'php')
            ? toPhp(obj)
            : JSON.stringify(obj, null, 2);

        try {
            if (navigator.clipboard?.writeText) {
                await navigator.clipboard.writeText(dataStr);
            } else {
                // fallback
                const ta = document.createElement('textarea');
                ta.value = dataStr;
                ta.style.position = 'fixed';
                ta.style.opacity = '0';
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
            }
            alert('Conteúdo copiado para a área de transferência!');
        } catch (e) {
            console.error(e);
            alert('Não foi possível copiar. Verifique permissões do navegador.');
        }
    }

    // -----------------------
    // WIRE UI
    // -----------------------
    function wireUI() {
        // busca / filtro
        el.search.addEventListener('input', applyFilters);
        el.missingOnly.addEventListener('change', applyFilters);
        el.dupOnly && el.dupOnly.addEventListener('change', applyFilters);

        // dropzone
        if (el.dropzone) {
            ['dragenter','dragover'].forEach(evt => {
                el.dropzone.addEventListener(evt, e => {
                    e.preventDefault(); e.stopPropagation();
                    el.dropzone.style.background='#f7fbff';
                });
            });
            ['dragleave','drop'].forEach(evt => {
                el.dropzone.addEventListener(evt, e => {
                    e.preventDefault(); e.stopPropagation();
                    el.dropzone.style.background='';
                });
            });
            el.dropzone.addEventListener('drop', e => {
                const file = e.dataTransfer.files?.[0];
                if (file) readFile(file);
            });
        }

        el.fileInput.addEventListener('change', () => {
            const file = el.fileInput.files?.[0];
            if (file) readFile(file);
        });

        // ações
        el.btnDownload.addEventListener('click', downloadJSON);
        el.btnSave.addEventListener('click', saveBackend);
        el.btnCopy?.addEventListener('click', copyToClipboard);
        el.btnAddRow.addEventListener('click', addBlankRow);
        el.btnSort.addEventListener('click', sortByKeyAsc);

        // paginação
        el.btnPrev.addEventListener('click', () => goToPage(state.page - 1));
        el.btnNext.addEventListener('click', () => goToPage(state.page + 1));
        el.pageSizeSel.addEventListener('change', () => {
            const n = parseInt(el.pageSizeSel.value, 10) || 50;
            state.pageSize = n;
            state.page = 1;
            renderBody();
            updatePager();
        });

        el.fileFormat?.addEventListener('change', syncDownloadLabel);

        // permitir colar direto na dropzone
        el.dropzone?.addEventListener('paste', (e)=>{
            const text = e.clipboardData?.getData('text') || '';
            if (text.trim()) {
                e.preventDefault();
                try { importFromText(text); }
                catch (err){ console.error(err); alert('Conteúdo inválido colado.'); }
            }
        });

        // atalhos (opcionais)
        document.addEventListener('keydown', (e) => {
            // Ctrl/Cmd+Enter => nova linha
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') addBlankRow();
            if ((e.ctrlKey || e.metaKey) && e.key === 'Delete') {
                const active = document.activeElement;
                if (!active) return;
                const rid = active.dataset && active.dataset.id;
                if (rid && confirm('Remover a linha atual?')) removeRowById(rid);
            }

            // Alt+←/→ => paginação
            if (e.altKey && e.key === 'ArrowRight') goToPage(state.page + 1);
            if (e.altKey && e.key === 'ArrowLeft')  goToPage(state.page - 1);
        });
    }

    // -----------------------
    // INIT
    // -----------------------
    function init() {
        // page size default from select
        if (el.pageSizeSel) {
            const n = parseInt(el.pageSizeSel.value, 10);
            if (!isNaN(n)) state.pageSize = n;
        }
        wireUI();
        syncDownloadLabel();

        // estado inicial dos badges/pager (lista vazia)
        updateStats();
        updatePager();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else { init(); }
})();
