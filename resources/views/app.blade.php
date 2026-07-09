<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>FLTech ERP</title>
    @if(file_exists(public_path('hot')))
        @php($viteUrl = trim(file_get_contents(public_path('hot'))))
        <script type="module">
            import RefreshRuntime from "{{ $viteUrl }}/@react-refresh"
            RefreshRuntime.injectIntoGlobalHook(window)
            window.$RefreshReg$ = () => {}
            window.$RefreshSig$ = () => (type) => type
            window.__vite_plugin_react_preamble_installed__ = true
        </script>
    @endif
    @vite(['resources/css/app.css', 'resources/js/main.jsx'])
</head>
<body class="antialiased">
    <div id="app"></div>
</body>
</html>
