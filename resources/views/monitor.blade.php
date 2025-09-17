<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Lang Monitor UI - by Jonas Schen</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="{{ asset('vendor/lang-monitor/css/lang-monitor.css') }}">
    </head>
    <body>
        <div class="container" id="lm-app">
            <h1 class="h3">Lang Monitor UI</h1>

            <div class="card">
                <div id="dropzone">
                    Paste here the JSON or PHP content.<br>
                    Or drag and drop your file here.<br>
                    Or
                    <label>
                        <u>click here</u>
                        <input type="file" id="file-input" accept=".json,.php,application/json,text/x-php" style="display:none">
                    </label>
                    to select a file from your device.
                </div>
            </div>

            <div class="card export">
                <div class="w-full">
                    Exporting
                </div>
                <select id="file-format" class="form-control">
                    <option value="json" selected>JSON (.json)</option>
                    <option value="php">PHP (.php)</option>
                </select>

                <button class="btn btn-sm btn-primary" id="btn-download">Download</button>
                <button class="btn btn-sm btn-success" id="btn-save">Save</button>
                <button class="btn btn-sm btn-yellow" id="btn-copy">Copy to clipboard</button>
            </div>

            <div class="card toolbar">
                <div class="toolbar-controls">
                    <div class="toolbar-actions">
                        <button class="btn btn-sm btn-secondary" id="btn-scan-project">Scan project</button>
                        <button class="btn btn-sm btn-secondary" id="btn-add-row" title="[Ctrl+Enter]">+ New line</button>
                        <button class="btn btn-sm btn-secondary" id="btn-sort">Sort A→Z</button>
                    </div>
                    <div class="toolbar-labels">
                        <span id="lm-badge-missing" class="badge bg-warning text-dark">
                            Missing: 0 / 0
                        </span>
                        <span id="lm-badge-dup" class="badge bg-danger">
                            Duplicated: 0
                        </span>
                    </div>
                </div>
                <div class="card-search">
                    <input type="text" id="search" class="form-control w-full" placeholder="Search by key or value">
                </div>
                <div class="">
                    Filters:
                </div>
                <div class="card-filters">
                    <label>
                        <input type="checkbox" id="missingOnly"> Only missing
                    </label>
                    <label>
                        <input type="checkbox" id="dupOnly"> Only duplicated
                    </label>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover w-full" id="translations-table">
                    <thead>
                        <tr>
                            <th class="w-half">Key</th>
                            <th class="w-half">Value</th>
                            <th style="width:110px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <div id="lm-pager">
                <label>
                    Items per page
                    <select id="page-size" class="form-select form-select-sm w-auto">
                        <option>25</option>
                        <option selected>50</option>
                        <option>100</option>
                        <option>200</option>
                    </select>
                </label>

                <button class="btn btn-sm btn-secondary" id="btn-prev" title="[Ctrl + ←]">← Previous</button>
                <span id="page-info">Page 1 of 1</span>
                <button class="btn btn-sm btn-secondary" id="btn-next" title="[Ctrl + →]">Next →</button>
            </div>
        </div>

        <script src="{{asset('vendor/lang-monitor/js/lang-monitor.js')}}"></script>
        <script>
            window.LANG_MONITOR_SCAN_PROJECT = @json(route('lang-monitor.scan'));
            window.LANG_MONITOR_SAVE_URL = @json(route('lang-monitor.save'));
            window.LANG_MONITOR_CSRF = @json(csrf_token());
        </script>
    </body>
</html>
