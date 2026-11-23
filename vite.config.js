import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // Global CSS & SCSS
                'resources/sass/app.scss',
                'resources/css/app.css',

                // Page-specific CSS
                'resources/css/pages/menus.css',
                'resources/css/pages/itsm-sla.css',
                'resources/css/pages/kpi.css',
                'resources/css/pages/penilaian360.css',
                'resources/css/pages/layout-setting.css',

                // Global JS
                'resources/js/app.js',

                // Page-specific JS
                'resources/js/pages/menus.js',
                'resources/js/pages/dashboard.js',
                'resources/js/pages/itsm-sla.js',
                'resources/js/pages/office-dashboard.js',
                'resources/js/pages/layout-setting.js',

                // KPI Module JS
                'resources/js/pages/kpi/kpiGrade.js',
                'resources/js/pages/kpi/kpiIndex.js',
                'resources/js/pages/kpi/dataTarget.js',
                'resources/js/pages/kpi/kpiOverview.js',
                'resources/js/pages/kpi/kpiPersonal.js',

                // Penilaian 360 Module JS
                'resources/js/pages/penilaian360/dashboard.js',
                'resources/js/pages/penilaian360/dataFormPenilaian.js',
                'resources/js/pages/penilaian360/detail.js',
                'resources/js/pages/penilaian360/editform-penilaian.js',
                'resources/js/pages/penilaian360/formkategori.js',
                'resources/js/pages/penilaian360/index.js',
                'resources/js/pages/penilaian360/personal-penilaian.js',
            ],
            refresh: true,
        }),
    ],
    css: {
        preprocessorOptions: {
            scss: {
                quietDeps: true,
                silenceDeprecations: ['import', 'global-builtin', 'color-functions'],
            },
        },
    },
    build: {
        chunkSizeWarningLimit: 600,
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes('node_modules')) {
                        if (id.includes('pusher-js') || id.includes('laravel-echo')) {
                            return 'vendor-realtime';
                        }
                        if (id.includes('axios')) {
                            return 'vendor-http';
                        }
                        if (id.includes('bootstrap') || id.includes('@popperjs')) {
                            return 'vendor-bootstrap';
                        }
                        if (id.includes('docx-preview') || id.includes('jszip')) {
                            return 'vendor-docx';
                        }
                        return 'vendor';
                    }
                },
            },
        },
    },
});
