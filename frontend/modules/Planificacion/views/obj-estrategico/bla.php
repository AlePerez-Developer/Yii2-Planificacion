<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Objetivos Estratégicos Institucionales</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f0f7ff',
                            100: '#e0effe',
                            500: '#2563eb',
                            600: '#1d4ed8',
                            700: '#1e40af',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
        }
        /* Custom smooth hover transitions */
        .card-transition {
            transition: all 0.2s ease-in-out;
        }
        .card-transition:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
        }
        /* Custom Tooltip Styling */
        [data-tooltip] {
            position: relative;
        }
        [data-tooltip]:before {
            content: attr(data-tooltip);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            margin-bottom: 6px;
            padding: 4px 8px;
            border-radius: 6px;
            background-color: #1e293b;
            color: #fff;
            font-size: 11px;
            font-weight: 500;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            z-index: 20;
        }
        [data-tooltip]:hover:before {
            opacity: 1;
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800 p-4 md:p-8">

<div class="max-w-7xl mx-auto space-y-6">

    <!-- Header Section -->
    <header class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Objetivos Estratégicos Institucionales</h1>
                <p class="text-sm text-slate-500 mt-1">Gestión de Metas, Políticas e Indicadores Institucionales</p>
            </div>
            <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Gestión Activa: 2026 - 2030
                    </span>
            </div>
        </div>

        <!-- Toolbar Controls -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 pt-4 border-t border-slate-100">
            <!-- Search Box -->
            <div class="flex items-center gap-2 flex-1 max-w-md">
                <label for="searchInput" class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Buscar:</label>
                <div class="relative flex-1">
                    <input
                        type="text"
                        id="searchInput"
                        placeholder="Buscar por código, título, área o política..."
                        class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all"
                        onkeyup="filterCards()"
                    >
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <button
                    onclick="resetSearch()"
                    data-tooltip="Actualizar lista"
                    class="p-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl shadow-sm transition-all focus:ring-2 focus:ring-blue-300 active:scale-95 flex items-center justify-center shrink-0"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </button>
            </div>

            <!-- Page Rows Selector -->
            <div class="flex items-center justify-end gap-3 text-xs text-slate-600">
                <span>Mostrar</span>
                <select id="recordsPerPage" class="bg-slate-50 border border-slate-200 rounded-lg px-3 py-1.5 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
                <span>registros</span>
            </div>
        </div>
    </header>

    <main id="cardsContainer" class="space-y-4">

        <!-- CARD 1 -->
        <article class="objective-card card-transition bg-white rounded-2xl p-5 md:p-6 shadow-sm border border-slate-200/90 flex flex-col lg:flex-row lg:items-start justify-between gap-6">
            <!-- Left Details Content -->
            <div class="flex-1 space-y-4">

                <!-- Card Header / Title -->
                <div class="flex items-start gap-3">
                        <span class="flex items-center justify-center w-7 h-7 bg-blue-600 text-white font-bold text-xs rounded-full shrink-0 mt-0.5 shadow-sm">
                            1
                        </span>
                    <div class="space-y-1">
                        <div class="flex items-center gap-2 flex-wrap">
                                <span class="bg-cyan-100 text-cyan-800 text-xs font-bold px-2.5 py-0.5 rounded-md border border-cyan-200">
                                    Objetivo: 132
                                </span>
                        </div>
                        <h2 class="text-base md:text-lg font-bold text-slate-900 leading-snug">
                            Ampliar el acceso y cobertura a la educación superior universitaria en diversas áreas del conocimiento, con enfoque inclusivo y equitativo, alcanzando el crecimiento programado, y contribuir al desarrollo económico y social del país
                        </h2>
                    </div>
                </div>

                <!-- Meta Pill Indicators -->
                <div class="flex flex-wrap items-center gap-y-2 gap-x-6 text-xs text-slate-600 bg-slate-50 p-3 rounded-xl border border-slate-100">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        <span class="font-semibold text-slate-700">PRODUCTO:</span>
                        <span class="text-slate-900 font-medium">producto</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="font-semibold text-slate-700">Gestión:</span>
                        <span class="text-slate-900 font-medium">2026 - 2030</span>
                    </div>
                    <div class="flex items-center gap-1.5 text-slate-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="italic text-[11px]">(descripción - fórmula)</span>
                    </div>
                </div>

                <!-- Clean Structured Layout for Area & Policy -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-1">
                    <!-- Area Block -->
                    <div class="bg-white p-3 rounded-xl border border-slate-200/70 hover:border-blue-300 transition-colors">
                        <div class="flex items-center gap-1.5 mb-1">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">ÁREA</span>
                            <span class="bg-blue-50 text-blue-700 font-mono text-[11px] px-1.5 py-0.2 rounded font-bold border border-blue-200">&lt;1&gt;</span>
                        </div>
                        <p class="text-xs font-medium text-slate-700">GESTIÓN DE LA FORMACIÓN DE GRADO Y POSGRADO</p>
                    </div>

                    <!-- Policy Block -->
                    <div class="bg-white p-3 rounded-xl border border-slate-200/70 hover:border-blue-300 transition-colors">
                        <div class="flex items-center gap-1.5 mb-1">
                            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">POLÍTICA</span>
                            <span class="bg-indigo-50 text-indigo-700 font-mono text-[11px] px-1.5 py-0.2 rounded font-bold border border-indigo-200">&lt;3&gt;</span>
                        </div>
                        <p class="text-xs font-medium text-slate-700">CONTRIBUCIÓN AL DESARROLLO ECONÓMICO, SOCIAL Y TECNOLÓGICO, PARA ELEVAR LA PRODUCTIVIDAD Y COMPETITIVIDAD NACIONAL CON CAPITAL HUMANO AVANZADO</p>
                    </div>
                </div>

            </div>

            <!-- Right Action Buttons -->
            <div class="flex items-center lg:flex-col lg:items-end gap-2.5 shrink-0 pt-4 lg:pt-0 border-t lg:border-t-0 border-slate-100">
                <!-- Program Button -->
                <button class="flex-1 lg:flex-none flex items-center justify-center gap-2 bg-sky-100 hover:bg-sky-200 text-sky-800 font-semibold text-xs px-4 py-2.5 rounded-xl border border-sky-300/60 shadow-xs transition-all active:scale-95">
                    <svg class="w-4 h-4 text-sky-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>Programar</span>
                </button>

                <div class="flex items-center gap-2">
                    <!-- Edit Icon Button -->
                    <button data-tooltip="Editar Objetivo" class="p-2.5 bg-amber-100 hover:bg-amber-200 text-amber-700 rounded-xl border border-amber-300/60 shadow-xs transition-all active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                        </svg>
                    </button>

                    <!-- Delete Icon Button -->
                    <button data-tooltip="Eliminar Registro" class="p-2.5 bg-rose-100 hover:bg-rose-200 text-rose-700 rounded-xl border border-rose-300/60 shadow-xs transition-all active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        </article>

        <!-- CARD 2 -->
        <article class="objective-card card-transition bg-white rounded-2xl p-5 md:p-6 shadow-sm border border-slate-200/90 flex flex-col lg:flex-row lg:items-start justify-between gap-6">
            <div class="flex-1 space-y-4">
                <div class="flex items-start gap-3">
                        <span class="flex items-center justify-center w-7 h-7 bg-blue-600 text-white font-bold text-xs rounded-full shrink-0 mt-0.5 shadow-sm">
                            2
                        </span>
                    <div class="space-y-1">
                        <div class="flex items-center gap-2 flex-wrap">
                                <span class="bg-cyan-100 text-cyan-800 text-xs font-bold px-2.5 py-0.5 rounded-md border border-cyan-200">
                                    Objetivo: 133
                                </span>
                        </div>
                        <h2 class="text-base md:text-lg font-bold text-slate-900 leading-snug">
                            Asegurar la calidad de la formacion profesional en diversas areas del conocimiento, con oferta academica de carreras de grado de alto nivel
                        </h2>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-y-2 gap-x-6 text-xs text-slate-600 bg-slate-50 p-3 rounded-xl border border-slate-100">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        <span class="font-semibold text-slate-700">PRODUCTO:</span>
                        <span class="text-slate-900 font-medium">producto</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="font-semibold text-slate-700">Gestión:</span>
                        <span class="text-slate-900 font-medium">2026 - 2030</span>
                    </div>
                    <div class="flex items-center gap-1.5 text-slate-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="italic text-[11px]">(descripción - fórmula)</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-1">
                    <div class="bg-white p-3 rounded-xl border border-slate-200/70 hover:border-blue-300 transition-colors">
                        <div class="flex items-center gap-1.5 mb-1">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">ÁREA</span>
                            <span class="bg-blue-50 text-blue-700 font-mono text-[11px] px-1.5 py-0.2 rounded font-bold border border-blue-200">&lt;1&gt;</span>
                        </div>
                        <p class="text-xs font-medium text-slate-700">GESTIÓN DE LA FORMACIÓN DE GRADO Y POSGRADO</p>
                    </div>

                    <div class="bg-white p-3 rounded-xl border border-slate-200/70 hover:border-blue-300 transition-colors">
                        <div class="flex items-center gap-1.5 mb-1">
                            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">POLÍTICA</span>
                            <span class="bg-indigo-50 text-indigo-700 font-mono text-[11px] px-1.5 py-0.2 rounded font-bold border border-indigo-200">&lt;3&gt;</span>
                        </div>
                        <p class="text-xs font-medium text-slate-700">CONTRIBUCIÓN AL DESARROLLO ECONÓMICO, SOCIAL Y TECNOLÓGICO, PARA ELEVAR LA PRODUCTIVIDAD Y COMPETITIVIDAD NACIONAL CON CAPITAL HUMANO AVANZADO</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center lg:flex-col lg:items-end gap-2.5 shrink-0 pt-4 lg:pt-0 border-t lg:border-t-0 border-slate-100">
                <button class="flex-1 lg:flex-none flex items-center justify-center gap-2 bg-sky-100 hover:bg-sky-200 text-sky-800 font-semibold text-xs px-4 py-2.5 rounded-xl border border-sky-300/60 shadow-xs transition-all active:scale-95">
                    <svg class="w-4 h-4 text-sky-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>Programar</span>
                </button>

                <div class="flex items-center gap-2">
                    <button data-tooltip="Editar Objetivo" class="p-2.5 bg-amber-100 hover:bg-amber-200 text-amber-700 rounded-xl border border-amber-300/60 shadow-xs transition-all active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                        </svg>
                    </button>
                    <button data-tooltip="Eliminar Registro" class="p-2.5 bg-rose-100 hover:bg-rose-200 text-rose-700 rounded-xl border border-rose-300/60 shadow-xs transition-all active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        </article>

        <!-- CARD 3 -->
        <article class="objective-card card-transition bg-white rounded-2xl p-5 md:p-6 shadow-sm border border-slate-200/90 flex flex-col lg:flex-row lg:items-start justify-between gap-6">
            <div class="flex-1 space-y-4">
                <div class="flex items-start gap-3">
                        <span class="flex items-center justify-center w-7 h-7 bg-blue-600 text-white font-bold text-xs rounded-full shrink-0 mt-0.5 shadow-sm">
                            3
                        </span>
                    <div class="space-y-1">
                        <div class="flex items-center gap-2 flex-wrap">
                                <span class="bg-cyan-100 text-cyan-800 text-xs font-bold px-2.5 py-0.5 rounded-md border border-cyan-200">
                                    Objetivo: 212
                                </span>
                        </div>
                        <h2 class="text-base md:text-lg font-bold text-slate-900 leading-snug">
                            Consolidar a la Universidad como un referente nacional e internacional en investigacion e innovacion, mediante el fortalecimiento de la cultura investigativa, generacion de conocimiento de alto impacto y transferencia de resultados a la sociedad, contribuyendo asi al desarrollo sostenible y al bienestar del pais
                        </h2>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-y-2 gap-x-6 text-xs text-slate-600 bg-slate-50 p-3 rounded-xl border border-slate-100">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        <span class="font-semibold text-slate-700">PRODUCTO:</span>
                        <span class="text-slate-900 font-medium">producto</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="font-semibold text-slate-700">Gestión:</span>
                        <span class="text-slate-900 font-medium">2026 - 2030</span>
                    </div>
                    <div class="flex items-center gap-1.5 text-slate-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="italic text-[11px]">(descripción - fórmula)</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-1">
                    <div class="bg-white p-3 rounded-xl border border-slate-200/70 hover:border-blue-300 transition-colors">
                        <div class="flex items-center gap-1.5 mb-1">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">ÁREA</span>
                            <span class="bg-blue-50 text-blue-700 font-mono text-[11px] px-1.5 py-0.2 rounded font-bold border border-blue-200">&lt;2&gt;</span>
                        </div>
                        <p class="text-xs font-medium text-slate-700">GESTIÓN DE LA INVESTIGACIÓN, CIENCIA, TECNOLOGÍA E INNOVACIÓN</p>
                    </div>

                    <div class="bg-white p-3 rounded-xl border border-slate-200/70 hover:border-blue-300 transition-colors">
                        <div class="flex items-center gap-1.5 mb-1">
                            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">POLÍTICA</span>
                            <span class="bg-indigo-50 text-indigo-700 font-mono text-[11px] px-1.5 py-0.2 rounded font-bold border border-indigo-200">&lt;1&gt;</span>
                        </div>
                        <p class="text-xs font-medium text-slate-700">CONTRIBUCIÓN AL DESARROLLO ECONÓMICO, SOCIAL, CULTURAL Y TECNOLÓGICO NACIONAL CON INCREMENTO DEL CAPITAL CIENTÍFICO</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center lg:flex-col lg:items-end gap-2.5 shrink-0 pt-4 lg:pt-0 border-t lg:border-t-0 border-slate-100">
                <button class="flex-1 lg:flex-none flex items-center justify-center gap-2 bg-sky-100 hover:bg-sky-200 text-sky-800 font-semibold text-xs px-4 py-2.5 rounded-xl border border-sky-300/60 shadow-xs transition-all active:scale-95">
                    <svg class="w-4 h-4 text-sky-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>Programar</span>
                </button>

                <div class="flex items-center gap-2">
                    <button data-tooltip="Editar Objetivo" class="p-2.5 bg-amber-100 hover:bg-amber-200 text-amber-700 rounded-xl border border-amber-300/60 shadow-xs transition-all active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                        </svg>
                    </button>
                    <button data-tooltip="Eliminar Registro" class="p-2.5 bg-rose-100 hover:bg-rose-200 text-rose-700 rounded-xl border border-rose-300/60 shadow-xs transition-all active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        </article>

        <!-- No results box -->
        <div id="noResults" class="hidden bg-white rounded-2xl p-8 text-center border border-slate-200">
            <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm font-semibold text-slate-700">No se encontraron registros coincidentes</p>
            <p class="text-xs text-slate-400 mt-1">Intenta con otros términos de búsqueda.</p>
        </div>

    </main>

    <!-- Footer / Pagination -->
    <footer class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200/80 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
        <span id="counterText">Mostrando 1 a 3 de 3 registros</span>

        <div class="inline-flex items-center -space-x-px rounded-xl shadow-2xs">
            <button class="px-3 py-1.5 rounded-l-lg border border-slate-200 bg-white text-slate-400 hover:bg-slate-50 font-medium transition-colors">
                Anterior
            </button>
            <button class="px-3 py-1.5 border border-slate-200 bg-blue-50 text-blue-600 font-semibold">
                1
            </button>
            <button class="px-3 py-1.5 rounded-r-lg border border-slate-200 bg-white text-slate-400 hover:bg-slate-50 font-medium transition-colors">
                Siguiente
            </button>
        </div>
    </footer>

</div>

<script>
    function filterCards() {
        const query = document.getElementById('searchInput').value.toLowerCase().trim();
        const cards = document.querySelectorAll('.objective-card');
        let visibleCount = 0;

        cards.forEach(card => {
            const text = card.textContent.toLowerCase();
            if (text.includes(query)) {
                card.classList.remove('hidden');
                visibleCount++;
            } else {
                card.classList.add('hidden');
            }
        });

        const noResults = document.getElementById('noResults');
        if (visibleCount === 0) {
            noResults.classList.remove('hidden');
        } else {
            noResults.classList.add('hidden');
        }

        document.getElementById('counterText').textContent = `Mostrando ${visibleCount} de ${cards.length} registros`;
    }

    function resetSearch() {
        document.getElementById('searchInput').value = '';
        filterCards();
    }
</script>
</body>
</html>
