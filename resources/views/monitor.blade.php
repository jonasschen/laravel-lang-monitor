<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Lang Monitor UI - by Jonas Schen</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="{{ asset('vendor/lang-monitor/css/lang-monitor.css') }}">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&icon_names=add_row_below,backup_table,delete_forever,download,keyboard_arrow_left,keyboard_arrow_right,plagiarism,save,sort_by_alpha,variable_remove" />
    </head>
    <body>
        <div class="container" id="lm-app">
            <div class="header">
                <img src="{{ asset('vendor/lang-monitor/images/lang_monitor_logo_small.png') }}" alt="Lang Monitor Logo" width="32" height="32" class="me-2">
                <h1 class="h3">
                    Lang Monitor UI
                </h1>
            </div>

            <div class="card">
                <div id="dropzone">
                    Paste here the JSON or PHP content.<br>
                    Or drag and drop your file here.<br>
                    Or
                    <label>
                        <u>click here</u>
                        <input type="file" id="file-input" accept=".txt,.json,.php,text/plain,application/json,text/x-php" style="display:none">
                    </label>
                    to select a file from your device.
                </div>
            </div>

            <div id="import-notification" class="card notification-area" title="Click to close" style="display: none"></div>

            <div class="card export">
                <div class="w-full">
                    Exporting
                </div>
                <select id="file-format" class="form-control">
                    <option value="json" selected>JSON (.json)</option>
                    <option value="php">PHP (.php)</option>
                    <option value="txt">TEXT (.txt)</option>
                </select>

                <button class="btn btn-sm btn-primary" id="btn-download">
                    <span class="material-symbols-outlined">
                        download
                    </span>
                    Download
                </button>
                <button class="btn btn-sm btn-success" id="btn-save">
                    <span class="material-symbols-outlined">
                        save
                    </span>
                    Save to file
                </button>
                <button class="btn btn-sm btn-yellow" id="btn-copy">
                    <span class="material-symbols-outlined">
                        backup_table
                    </span>
                    Copy to clipboard
                </button>
            </div>

            <div id="export-notification" class="card notification-area" title="Click to close" style="display: none"></div>

            <div class="card toolbar">
                <div class="toolbar-controls">
                    <div class="toolbar-actions">
                        <button class="btn btn-sm btn-secondary" id="btn-scan-project">
                            <span class="material-symbols-outlined">
                                plagiarism
                            </span>
                            Scan project
                        </button>
                        <button class="btn btn-sm btn-secondary" id="btn-add-row" title="[Ctrl+Enter]">
                            <span class="material-symbols-outlined">
                                add_row_below
                            </span>
                            New line
                        </button>
                        <button class="btn btn-sm btn-secondary" id="btn-sort">
                            <span class="material-symbols-outlined">
                                sort_by_alpha
                            </span>
                            Sort
                        </button>
                        <button class="btn btn-sm btn-secondary" id="btn-clear-all">
                            <span class="material-symbols-outlined">
                                delete_forever
                            </span>
                            Clear all
                        </button>
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

            <div id="toolbar-notification" class="card notification-area" title="Click to close" style="display: none"></div>

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

                <button class="btn btn-sm btn-secondary" id="btn-prev" title="[Ctrl + ←]">
                    <span class="material-symbols-outlined">
                        keyboard_arrow_left
                    </span>
                    Previous
                </button>
                <span id="page-info">Page 1 of 1</span>
                <button class="btn btn-sm btn-secondary" id="btn-next" title="[Ctrl + →]">
                    Next
                    <span class="material-symbols-outlined">
                        keyboard_arrow_right
                    </span>
                </button>
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
