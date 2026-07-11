<!DOCTYPE html>
<html lang="en" class="h-full">
    <head>
        <meta charset="utf-8">
        @include('partials.theme-init')
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title') &middot; {{ config('app.name') }} Admin</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=cairo:400,500,600,700,900|jetbrains-mono:400,500,600" rel="stylesheet" />
        <link rel="icon" href="/brand/logo_icon.png" type="image/png">
        <meta name="color-scheme" content="light dark">
        <script src="https://unpkg.com/@phosphor-icons/web"></script>

        @vite(['resources/css/app.css', 'resources/js/admin-app.js'])
    </head>
    <body data-page="@yield('page')" class="h-full font-sans antialiased bg-zinc-50 text-zinc-900 dark:bg-zinc-950 dark:text-zinc-50">
        <div class="flex min-h-[100dvh]">
            <!-- Mobile backdrop -->
            <div id="sidebar-backdrop" class="fixed inset-0 z-40 hidden bg-zinc-950/50 backdrop-blur-sm lg:hidden" data-sidebar-backdrop></div>

            <!-- Sidebar -->
            <aside
                id="admin-sidebar"
                class="fixed inset-y-0 left-0 z-50 w-64 flex-shrink-0 -translate-x-full flex-col border-r border-zinc-200/70 bg-white transition-transform duration-300 ease-out dark:border-white/10 dark:bg-zinc-900 lg:static lg:flex lg:translate-x-0"
            >
                <div class="flex items-center gap-3 px-6 py-5">
                    <a href="/admin/dashboard" class="flex flex-col gap-1">
                        <img src="/brand/logo_colored.png" alt="KhidmaNow Logo" class="h-8 w-auto dark:hidden object-contain" />
                        <img src="/brand/logo_white.png" alt="KhidmaNow Logo" class="h-8 w-auto hidden dark:block object-contain" />
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 pl-0.5">Admin Portal</p>
                    </a>
                    <button type="button" data-sidebar-close class="ml-auto rounded-lg p-1.5 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 lg:hidden dark:hover:bg-zinc-800" aria-label="Close menu">
                        <i class="ph ph-x text-lg"></i>
                    </button>
                </div>

                <nav class="flex-1 space-y-4 overflow-y-auto px-3 py-4">
                    <div>
                        <a href="/admin/dashboard" class="nav-link {{ request()->is('admin/dashboard') ? 'nav-link-active' : '' }}">
                            <i class="ph ph-squares-four text-lg"></i> Dashboard
                        </a>
                    </div>

                    <div>
                        <p class="px-3 pb-1 text-xs font-semibold uppercase tracking-wide text-zinc-400 dark:text-zinc-500">Users</p>
                        <a href="/admin/users/clients" class="nav-link {{ request()->is('admin/users/clients') ? 'nav-link-active' : '' }}">
                            <i class="ph ph-users text-lg"></i> Clients
                        </a>
                        <a href="/admin/users/providers" class="nav-link {{ request()->is('admin/users/providers') ? 'nav-link-active' : '' }}">
                            <i class="ph ph-briefcase text-lg"></i> Service Providers
                        </a>
                    </div>

                    <div>
                        <p class="px-3 pb-1 text-xs font-semibold uppercase tracking-wide text-zinc-400 dark:text-zinc-500">Catalog</p>
                        <a href="/admin/categories" class="nav-link {{ request()->is('admin/categories') ? 'nav-link-active' : '' }}">
                            <i class="ph ph-squares-four text-lg"></i> Categories
                        </a>
                    </div>

                    <div>
                        <p class="px-3 pb-1 text-xs font-semibold uppercase tracking-wide text-zinc-400 dark:text-zinc-500">Communication</p>
                        <a href="/admin/chats" class="nav-link {{ request()->is('admin/chats') ? 'nav-link-active' : '' }}">
                            <i class="ph ph-chats-circle text-lg"></i> Chats
                        </a>
                        <a href="/admin/support-tickets" class="nav-link {{ request()->is('admin/support-tickets') ? 'nav-link-active' : '' }}">
                            <i class="ph ph-lifebuoy text-lg"></i> Support Tickets
                        </a>
                        <a href="/admin/notifications/send" class="nav-link {{ request()->is('admin/notifications/send') ? 'nav-link-active' : '' }}">
                            <i class="ph ph-paper-plane-tilt text-lg"></i> Send Notification
                        </a>
                        <a href="/admin/notifications" class="nav-link {{ request()->is('admin/notifications') && !request()->is('admin/notifications/send') ? 'nav-link-active' : '' }}">
                            <i class="ph ph-bell text-lg"></i> Notifications
                        </a>
                    </div>

                    <div>
                        <p class="px-3 pb-1 text-xs font-semibold uppercase tracking-wide text-zinc-400 dark:text-zinc-500">Locations</p>
                        <a href="/admin/locations/countries" class="nav-link {{ request()->is('admin/locations/countries') ? 'nav-link-active' : '' }}">
                            <i class="ph ph-flag text-lg"></i> Countries
                        </a>
                        <a href="/admin/locations/cities" class="nav-link {{ request()->is('admin/locations/cities') ? 'nav-link-active' : '' }}">
                            <i class="ph ph-buildings text-lg"></i> Cities
                        </a>
                        <a href="/admin/locations/areas" class="nav-link {{ request()->is('admin/locations/areas') ? 'nav-link-active' : '' }}">
                            <i class="ph ph-map-pin text-lg"></i> Areas
                        </a>
                    </div>

                    <div>
                        <p class="px-3 pb-1 text-xs font-semibold uppercase tracking-wide text-zinc-400 dark:text-zinc-500">CMS</p>
                        <a href="/admin/cms/intro-screens" class="nav-link {{ request()->is('admin/cms/intro-screens') ? 'nav-link-active' : '' }}">
                            <i class="ph ph-images text-lg"></i> Intro Screens
                        </a>
                        <a href="/admin/cms/terms" class="nav-link {{ request()->is('admin/cms/terms') ? 'nav-link-active' : '' }}">
                            <i class="ph ph-file-text text-lg"></i> Terms & Conditions
                        </a>
                        <a href="/admin/cms/privacy-policy" class="nav-link {{ request()->is('admin/cms/privacy-policy') ? 'nav-link-active' : '' }}">
                            <i class="ph ph-shield-check text-lg"></i> Privacy Policy
                        </a>
                        <a href="/admin/cms/faqs" class="nav-link {{ request()->is('admin/cms/faqs') ? 'nav-link-active' : '' }}">
                            <i class="ph ph-question text-lg"></i> FAQs
                        </a>
                    </div>
                </nav>

                <div class="border-t border-zinc-200/70 px-3 py-4 dark:border-white/10">
                    <button id="logout-button" type="button" class="nav-link w-full text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-950/30">
                        <i class="ph ph-sign-out text-lg"></i> Sign out
                    </button>
                </div>
            </aside>

            <!-- Main -->
            <div class="flex min-w-0 flex-1 flex-col">
                <header class="sticky top-0 z-30 flex items-center justify-between border-b border-zinc-200/70 glass-panel px-4 py-4 sm:px-6 dark:border-white/10">
                    <div class="flex items-center gap-3">
                        <button type="button" data-sidebar-open class="rounded-lg p-2 text-zinc-500 hover:bg-zinc-100 hover:text-zinc-700 lg:hidden dark:text-zinc-400 dark:hover:bg-zinc-800" aria-label="Open menu">
                            <i class="ph ph-list text-xl"></i>
                        </button>
                        <h1 class="text-lg font-semibold tracking-tight text-zinc-900 dark:text-zinc-50">@yield('title')</h1>
                    </div>
                    <div class="flex items-center gap-3 text-sm text-zinc-600 dark:text-zinc-300">
                        <span class="hidden items-center gap-1.5 rounded-full border border-zinc-200/70 px-2.5 py-1 text-xs font-medium text-zinc-500 sm:flex dark:border-white/10 dark:text-zinc-400">
                            <span class="relative flex h-1.5 w-1.5">
                                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-brand-green-400 opacity-75"></span>
                                <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-brand-green-500"></span>
                            </span>
                            Live
                        </span>
                        <button
                            type="button"
                            id="theme-toggle"
                            aria-label="Toggle color theme"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-zinc-200/70 text-zinc-500 transition-colors duration-150 hover:text-accent-600 dark:border-white/10 dark:text-zinc-400 dark:hover:text-accent-400"
                        >
                            <i class="ph ph-sun text-base dark:hidden"></i>
                            <i class="ph ph-moon hidden text-base dark:block"></i>
                        </button>
                        <div class="flex items-center gap-2 rounded-full border border-zinc-200/70 py-1 pl-1 pr-3 dark:border-white/10">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-accent-600 text-xs font-semibold text-white">
                                <i class="ph ph-user text-sm"></i>
                            </span>
                            <span id="topbar-user-name" class="font-medium">Admin</span>
                        </div>
                    </div>
                </header>

                <main class="relative flex-1 overflow-y-auto p-4 sm:p-6">
                    <div id="page-banner" class="hidden" role="alert"></div>
                    <div class="animate-fade-up">
                        @yield('content')
                    </div>
                </main>
            </div>
        </div>
    </body>
</html>
