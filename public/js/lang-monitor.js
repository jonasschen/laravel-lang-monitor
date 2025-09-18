(function() {
    // -----------------------
    // STATE
    // -----------------------
    const state = {
        rows: [],
        filteredRows: [],
        page: 1,
        pageSize: 50,
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
        btnScanProject: document.getElementById('btn-scan-project'),
        btnAddRow: document.getElementById('btn-add-row'),
        btnSort: document.getElementById('btn-sort'),
        statsMissing: document.getElementById('lm-badge-missing'),
        statsDup: document.getElementById('lm-badge-dup'),
        btnPrev: document.getElementById('btn-prev'),
        btnNext: document.getElementById('btn-next'),
        pageInfo: document.getElementById('page-info'),
        pageSizeSel: document.getElementById('page-size'),
        fileFormat: document.getElementById('file-format'),
        btnCopy: document.getElementById('btn-copy'),
        exportNotification: document.getElementById('export-notification'),
        importNotification: document.getElementById('import-notification'),
        toolbarNotification: document.getElementById('toolbar-notification'),
    };

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
        if (!Object.keys(map).length) throw new Error('None pair key => value found on PHP.');

        return map;
    }

    function parseTextLang(text) {
        return text
            .split("\n")
            .map(line => line.trim())
            .filter(line => line)
            .reduce((newObj, line) => {
                newObj[line] = "";

                return newObj;
            }, {});
    }

    function setRowsFromMap(map){
        const arr = [];
        if (map && typeof map === 'object' && !Array.isArray(map)) {
            let i = 0;
            Object.keys(map).forEach(k=>{
                arr.push({ id:`row_${Date.now()}_${i++}`, key:String(k), value: map[k]==null?'':String(map[k]) });
            });
        } else {
            showImportNotification('Invalid format. Waiting { "key": "value" }.', true);

            return;
        }

        arr.sort((a,b)=> a.key.localeCompare(b.key));
        state.rows = arr;
        applyFilters();
    }

    function importFromText(raw, filenameHint=''){
        let isPhp = false;
        const isObject = (typeof raw == 'object');
        let map;
        if (isObject) {
            map = raw;
        } else {
            const s = String(raw || '');
            isPhp = filenameHint.toLowerCase().endsWith('.php') || s.trimStart().startsWith('<?php');

            if (isPhp) {
                map = parsePhpLang(s);
            } else {
                try {
                    map = JSON.parse(s);
                } catch {
                    map = parseTextLang(s);
                }
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
                showImportNotification('Invalid file. Send JSON, PHP or TXT formats.', true);
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
        const seen = new Map();

        rows.forEach(r => {
            const k = (r.key || '').trim();
            if (!k) {
                emptyKeys.add(r.id);

                return;
            }

            if (seen.has(k)) {
                dupKeys.add(r.id); dupKeys.add(seen.get(k));
            } else {
                seen.set(k, r.id);
            }
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
            inputEl.title = isEmpty ? 'Empty key' : 'Duplicated key';
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
            el.statsMissing.textContent = `Missings: ${missing} / ${total}`;
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
            el.statsDup.textContent = `Duplicated: ${dups}`;
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

        recomputeValidation();

        state.filteredRows = state.rows.filter(r => {
            const keyText = (r.key || '');
            const valText = (r.value || '');
            const inQuery =
                !q ||
                keyText.toLowerCase().includes(q) ||
                valText.toLowerCase().includes(q);

            if (!inQuery) return false;

            const keyEmpty = !(keyText.trim().length);
            const valEmpty = !(valText.trim().length);
            const isMissing = keyEmpty || valEmpty;

            const isDup = state._dupKeys?.has(r.id);

            if (missingOnly && dupOnly) return isMissing || isDup;

            if (missingOnly) return isMissing;

            if (dupOnly) return isDup;

            return true;
        });

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
        el.pageInfo.textContent = `Page ${state.page} of ${totalPages()}`;
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

    function autoResizeRow(tr) {
        const minHeight = 42;
        const areas = tr.querySelectorAll('textarea');
        let target = minHeight;

        areas.forEach(a => {
            a.style.height = 'auto';
            target = Math.max(target, (a.scrollHeight - 5) || 0, minHeight);
        });

        areas.forEach(a => {
            a.style.height = target + 'px';
        });
    }

    function renderBody() {
        if (!el.tbody) {
            el.tbody = el.table?.querySelector('tbody');
        }

        if (!el.tbody) return;

        el.tbody.innerHTML = '';

        recomputeValidation();

        const pageRows = getPageSlice();

        pageRows.forEach((row, idxOnPage) => {
            const tr = document.createElement('tr');

            // --- KEY ---
            const tdKey = document.createElement('td');
            const keyInput = document.createElement('textarea');
            keyInput.value = row.key || '';
            keyInput.style.minHeight = '42px';
            keyInput.id = row.id;
            keyInput.dataset.id = row.id;
            keyInput.placeholder = '— key —';

            paintKeyValidation(keyInput, row.id);

            keyInput.addEventListener('input', (e) => {
                autoResizeRow(tr);

                const rid = e.target.dataset.id;
                const baseIdx = state.rows.findIndex(r => r.id === rid);
                if (baseIdx >= 0) state.rows[baseIdx].key = e.target.value;

                const globalIdx = (state.page - 1) * state.pageSize + idxOnPage;
                if (state.filteredRows[globalIdx]) state.filteredRows[globalIdx].key = e.target.value;

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

            tdKey.appendChild(keyInput);
            tr.appendChild(tdKey);

            // --- VALUE ---
            const tdVal = document.createElement('td');
            const valInput = document.createElement('textarea');
            valInput.value = row.value || '';
            valInput.style.minHeight = '42px';
            valInput.dataset.id = row.id;

            valInput.addEventListener('input', (e) => {
                autoResizeRow(tr);

                const rid = e.target.dataset.id;
                const baseIdx = state.rows.findIndex(r => r.id === rid);
                if (baseIdx >= 0) state.rows[baseIdx].value = e.target.value;

                const globalIdx = (state.page - 1) * state.pageSize + idxOnPage;
                if (state.filteredRows[globalIdx]) state.filteredRows[globalIdx].value = e.target.value;

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
                valInput.placeholder = '— missing —';
            }
            tdVal.appendChild(valInput);
            tr.appendChild(tdVal);

            // --- ACTIONS ---
            const tdActions = document.createElement('td');
            tdActions.style.whiteSpace = 'nowrap';

            const delIcon = document.createElement('span');
            delIcon.className = 'material-symbols-outlined text-danger';
            delIcon.textContent = 'variable_remove';

            const delBtn = document.createElement('button');
            delBtn.type = 'button';
            delBtn.className = 'btn btn-sm btn-secondary btn-outline-danger';
            delBtn.title = '[Ctrl+Del]';
            delBtn.dataset.id = row.id;
            delBtn.append(delIcon, ' Remove');
            delBtn.addEventListener('click', (e) => {
                const rid = e.currentTarget.dataset.id;
                if (confirm('Remove this line?')) removeRowById(rid);
            });

            tdActions.appendChild(delBtn);
            tr.appendChild(tdActions);

            el.tbody.appendChild(tr);

            autoResizeRow(tr);
        });
    }

    // -----------------------
    // ADD / REMOVE / SORT
    // -----------------------
    function addBlankRow() {
        const newId = `row_${Date.now()}_${Math.floor(Math.random()*1e6)}`;
        state.rows.push({ id: newId, key: '', value: '' });

        applyFilters();
        state.page = totalPages();
        goToPage(state.page);
        updatePager();
        updateStats();

        setTimeout(() => document.getElementById(newId).focus(), 0);
    }

    function removeRowById(rowId) {
        const ix = state.rows.findIndex(r => r.id === rowId);
        if (ix >= 0) state.rows.splice(ix, 1);

        applyFilters();
        state.page = clampPage(state.page);
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

            if (aEmpty) return 1;

            if (bEmpty) return -1;

            return collator.compare(ak, bk);
        });
        applyFilters();
    }

    function closeNotification() {
        this.classList.remove('error');
        this.classList.remove('success');
        this.style.display = 'none';
    }

    // -----------------------
    // EXPORT / SAVE
    // -----------------------
    function downloadTranslations() {
        recomputeValidation();
        if (state._emptyKeys.size || state._dupKeys.size) {
            showExportNotification('Fix the empty/duplicated keys before download.', true);

            return;
        }

        const format = el.fileFormat?.value;
        const obj = {};
        state.rows.forEach(r => { obj[(r.key || '').trim()] = r.value || ''; });

        const phpEscape = s => String(s).replace(/\\/g,'\\\\').replace(/'/g,"\\'");
        const toPhp = (map) => {
            const keys = Object.keys(map).sort((a,b)=> a.localeCompare(b));
            const body = keys.map(k => `    '${phpEscape(k)}' => '${phpEscape(map[k] ?? '')}',`).join('\n');

            return `<?php\n\nreturn [\n${body}\n];\n`;
        };

        let dataStr, mime, ext;

        switch (format) {
            case 'php':
                dataStr = toPhp(obj);
                mime = 'text/x-php';
                ext = '.php';
                break;
            case 'json':
                dataStr = JSON.stringify(obj, null, 2);
                mime = 'application/json';
                ext = '.json';
                break;
            case 'txt':
                dataStr = Object.keys(obj).map(k => `${k}: ${obj[k]}`).join('\n');
                mime = 'text/plain';
                ext = '.txt';
                break;
        }
        const filename = `exported_translations_${Date.now()}${ext}`;

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
            showExportNotification('Fix the empty/duplicated keys before save.', true);

            return;
        }

        const format = el.fileFormat?.value;
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
            body: JSON.stringify({ data: obj, strategy, format: format })
        });

        if (res.ok && res.status === 200) {
            const jsonData = await res.json();
            if (jsonData.success) {
                showExportNotification(`Successfully saved!<br>File location: ${jsonData.path}`);
            }

            return;
        }

        const t = await res.text().catch(() => '');
        showExportNotification('Error saving: ' + t, true);
    }

    async function scanProject() {
        if (state.rows.length > 0 && !confirm('Are you sure you want to scan the project and lose unsaved translations?')) {
            return;
        }

        const res = await fetch(window.LANG_MONITOR_SCAN_PROJECT, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.LANG_MONITOR_CSRF,
                'Accept': 'application/json',
            },
        });

        if (res.ok && res.status === 200) {
            const jsonData = await res.json();

            const missedKeys = Object.values(jsonData.keys_not_found).reduce((newObj, value) => {
                newObj[value] = '';

                return newObj;
            }, {});

            importFromText(missedKeys);

            showToolbarNotification('Project successfully scanned!');

            return;
        }

        const t = await res.text().catch(() => '');
        showToolbarNotification('Error scaning project: ' + t, true);
    }

    async function copyToClipboard() {
        recomputeValidation();
        if (state._emptyKeys.size || state._dupKeys.size) {
            showExportNotification('Fix the empty/duplicated key before copy.', true);

            return;
        }

        const format = el.fileFormat.value;
        const obj = {};
        state.rows.forEach(r => { obj[(r.key || '').trim()] = r.value || ''; });

        const phpEscape = s => String(s).replace(/\\/g,'\\\\').replace(/'/g,"\\'");
        const toPhp = (map) => {
            const keys = Object.keys(map).sort((a,b)=> a.localeCompare(b));
            const body = keys.map(k => `    '${phpEscape(k)}' => '${phpEscape(map[k] ?? '')}',`).join('\n');

            return `<?php\n\nreturn [\n${body}\n];\n`;
        };

        let dataStr;
        switch (format) {
            case 'php':
                dataStr = toPhp(obj);
                break;
            case 'json':
                dataStr = JSON.stringify(obj, null, 2);
                break;
            case 'txt':
                dataStr = Object.keys(obj).map(k => `${k}: ${obj[k]}`).join('\n');
                break;
        }

        try {
            if (navigator.clipboard?.writeText) {
                await navigator.clipboard.writeText(dataStr);
            } else {
                const ta = document.createElement('textarea');
                ta.value = dataStr;
                ta.style.position = 'fixed';
                ta.style.opacity = '0';
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
            }
            showExportNotification('Content successfully copied to clipboard!');
        } catch (e) {
            console.error(e);
            showExportNotification('Unable to copy. Check your browser permissions.', true);
        }
    }

    // -----------------------
    // WIRE UI
    // -----------------------
    function wireUI() {
        el.search.addEventListener('input', applyFilters);
        el.missingOnly.addEventListener('change', applyFilters);
        el.dupOnly && el.dupOnly.addEventListener('change', applyFilters);

        if (el.dropzone) {
            ['dragenter','dragover'].forEach(evt => {
                el.dropzone.addEventListener(evt, e => {
                    el.dropzone.classList.add('is-dragover');
                    e.preventDefault();
                    e.stopPropagation();
                });
            });

            ['dragleave','drop'].forEach(evt => {
                el.dropzone.addEventListener(evt, e => {
                    el.dropzone.classList.remove('is-dragover');
                    e.preventDefault();
                    e.stopPropagation();
                });
            });
            el.dropzone.addEventListener('drop', e => {
                el.dropzone.classList.remove('is-dragover');
                const file = e.dataTransfer.files?.[0];
                if (file) readFile(file);
            });
        }

        el.fileInput.addEventListener('change', () => {
            const file = el.fileInput.files?.[0];
            if (file) readFile(file);
        });

        el.btnDownload.addEventListener('click', downloadTranslations);
        el.btnSave.addEventListener('click', saveBackend);
        el.btnScanProject.addEventListener('click', scanProject);
        el.btnCopy?.addEventListener('click', copyToClipboard);
        el.btnAddRow.addEventListener('click', addBlankRow);
        el.btnSort.addEventListener('click', sortByKeyAsc);
        el.exportNotification.addEventListener('click', closeNotification);
        el.importNotification.addEventListener('click', closeNotification);
        el.toolbarNotification.addEventListener('click', closeNotification);

        el.btnPrev.addEventListener('click', () => goToPage(state.page - 1));
        el.btnNext.addEventListener('click', () => goToPage(state.page + 1));
        el.pageSizeSel.addEventListener('change', () => {
            const n = parseInt(el.pageSizeSel.value, 10) || 50;
            state.pageSize = n;
            state.page = 1;
            renderBody();
            updatePager();
        });

        el.dropzone?.addEventListener('paste', (e)=>{
            const text = e.clipboardData?.getData('text') || '';
            if (text.trim()) {
                e.preventDefault();
                try {
                    importFromText(text);
                } catch (err) {
                    console.error(err);
                    showImportNotification('Invalid content pasted.', true);
                }
            }
        });

        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') addBlankRow();
            if ((e.ctrlKey || e.metaKey) && e.key === 'Delete') {
                const active = document.activeElement;

                if (!active) return;

                const rid = active.dataset && active.dataset.id;
                if (rid && confirm('Remove the current line?')) removeRowById(rid);
            }

            if (e.altKey && e.key === 'ArrowRight') goToPage(state.page + 1);
            if (e.altKey && e.key === 'ArrowLeft')  goToPage(state.page - 1);
        });
    }

    // -----------------------
    // NOTIFICATIONS
    // -----------------------
    function showExportNotification(message, isError = false) {
        const notificationCard = document.getElementById('export-notification');
        handleNotification(notificationCard, message, isError);
    }

    function showImportNotification(message, isError = false) {
        const notificationCard = document.getElementById('import-notification');
        handleNotification(notificationCard, message, isError);
    }

    function showToolbarNotification(message, isError = false) {
        const notificationCard = document.getElementById('toolbar-notification');
        handleNotification(notificationCard, message, isError);
    }

    function handleNotification(notificationCard, message, isError) {
        if (!notificationCard) return;

        notificationCard.innerHTML = message;
        if (isError) {
            notificationCard.classList.add('error');
            notificationCard.classList.remove('success');
        } else {
            notificationCard.classList.add('success');
            notificationCard.classList.remove('error');
        }
        notificationCard.style.display = 'flex';
    }

    // -----------------------
    // INIT
    // -----------------------
    function init() {
        if (el.pageSizeSel) {
            const n = parseInt(el.pageSizeSel.value, 10);
            if (!isNaN(n)) state.pageSize = n;
        }
        wireUI();

        updateStats();
        updatePager();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else { init(); }
})();
