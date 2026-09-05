<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Error | MahdizadehMMG</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @keyframes pulse-red {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .animate-pulse-red { animation: pulse-red 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
    </style>
</head>
<body class="bg-slate-950 text-slate-200 flex items-center justify-center min-h-screen p-6">

    <div class="max-w-2xl w-full">
        <!-- Icon Section -->
        <div class="flex justify-center mb-8">
            <div class="relative">
                <div class="absolute inset-0 bg-red-500 blur-2xl opacity-20 animate-pulse"></div>
                <div class="relative bg-slate-900 p-6 rounded-full border border-red-500/30 shadow-2xl">
                    <i class="fa-solid fa-database text-red-500 text-5xl animate-pulse-red"></i>
                </div>
            </div>
        </div>

        <!-- Content Section -->
        <div class="text-center space-y-4">
            <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl">
                Connection <span class="text-red-500">Lost</span>
            </h1>
            <p class="text-lg text-slate-400">
                Hey <span class="text-indigo-400 font-mono">MSC</span>, your database engine is currently unreachable.
            </p>
        </div>

        <!-- Error Details Card -->
        <div class="mt-10 bg-slate-900/50 border border-slate-800 rounded-xl overflow-hidden backdrop-blur-sm shadow-xl">
            <div class="px-6 py-4 border-b border-slate-800 bg-slate-900/80 flex justify-between items-center">
                <span class="text-sm font-semibold uppercase tracking-wider text-slate-500">Error Diagnostics</span>
                <span class="px-2 py-1 text-xs font-medium text-red-400 bg-red-400/10 rounded-full">Critical</span>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <!-- Error Message -->
                    <div class="flex items-start space-x-3">
                        <i class="fa-solid fa-circle-exclamation text-red-500 mt-1"></i>
                        <div>
                            <p class="text-sm font-medium text-slate-300">Exception Message</p>
                            <p class="text-sm font-mono text-red-400 mt-1 break-words bg-red-500/5 p-2 rounded">
                                {{ $error }}
                            </p>
                        </div>
                    </div>

                    <!-- Quick Fix Checklist -->
                    <div class="pt-4 border-t border-slate-800">
                        <p class="text-sm font-medium text-slate-300 mb-3">Suggested Checklist:</p>
                        <ul class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <li class="flex items-center text-sm text-slate-400">
                                <i class="fa-solid fa-check-circle text-green-500 mr-2"></i> Check .env credentials
                            </li>
                            <li class="flex items-center text-sm text-slate-400">
                                <i class="fa-solid fa-check-circle text-green-500 mr-2"></i> Verify MySQL/PostgreSQL service
                            </li>
                            <li class="flex items-center text-sm text-slate-400">
                                <i class="fa-solid fa-check-circle text-green-500 mr-2"></i> Check DB_PORT and DB_HOST
                            </li>
                            <li class="flex items-center text-sm text-slate-400">
                                <i class="fa-solid fa-check-circle text-green-500 mr-2"></i> Review network/firewall
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer/Action -->
        <div class="mt-8 text-center">
            <button onclick="window.location.reload()" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all">
                <i class="fa-solid fa-rotate mr-2"></i> Retry Connection
            </button>
            <p class="mt-6 text-xs text-slate-600">
                Project: <span class="font-mono">MahdizadehMMG</span> &bull; Dev Environment
            </p>
        </div>
    </div>

</body>
</html>
