<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }

            /* Preloader styles */
            #app-preloader {
                position: fixed;
                inset: 0;
                z-index: 99999;
                display: flex;
                align-items: center;
                justify-content: center;
                background: rgba(255, 255, 255, 0.7);
                backdrop-filter: blur(2px);
                transition: opacity 0.3s ease;
            }

            html.dark #app-preloader {
                background: rgba(23, 23, 23, 0.7);
            }

            #app-preloader.hidden {
                opacity: 0;
                pointer-events: none;
            }

            .preloader-spinner {
                width: 40px;
                height: 40px;
                border: 3px solid rgba(0, 0, 0, 0.1);
                border-top-color: #3b82f6;
                border-radius: 50%;
                animation: spin 0.8s linear infinite;
            }

            html.dark .preloader-spinner {
                border-color: rgba(255, 255, 255, 0.1);
                border-top-color: #3b82f6;
            }

            @keyframes spin {
                to { transform: rotate(360deg); }
            }
        </style>

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        {{-- Preloader - shows before Vue mounts --}}
        <div id="app-preloader">
            <div class="preloader-spinner"></div>
        </div>

        @inertia

        {{-- Hide preloader when Vue mounts --}}
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Hide preloader after Vue mounts (small delay to ensure render)
                setTimeout(function() {
                    const preloader = document.getElementById('app-preloader');
                    if (preloader) {
                        preloader.classList.add('hidden');
                        setTimeout(function() {
                            preloader.style.display = 'none';
                        }, 300);
                    }
                }, 500);
            });
        </script>
    </body>
</html>
