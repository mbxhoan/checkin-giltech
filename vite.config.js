import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'

export default defineConfig({
  // server: {
  //   host: true,
  //   hmr: {
  //     host: 'scan.checkin.delfi.local', // your custom domain
  //     protocol: 'http', // if using http (not https)
  //     port: 5173, // Vite dev server port
  //   },
  //   cors: true, // Allow CORS
  // },

  server: {
    // Add this server configuration
    cors: {
      origin: '*', // Allow requests from any origin during development
      // Or specify the exact origin of your Laravel app:
      // origin: 'http://scan.checkin.delfi.local:8000',
      methods: ['GET', 'POST', 'PUT', 'DELETE'], // Allowed methods
      allowedHeaders: ['Content-Type', 'Authorization', 'X-Requested-With'], // Allowed headers
    }
    // If you have other server options, keep them here
    // host: 'scan.checkin.delfi.local', // You might already have this
    // port: 5173, // Or specify your dev server port
  },

  plugins: [
    laravel([
      'resources/sass/scan.scss',
      'resources/sass/app.scss',
      'resources/sass/admin.scss',
      'resources/sass/web.scss',
      'resources/js/app.js',
      'resources/js/admin.js',
      'resources/js/web.js',

      'resources/js/scan.js',
      'resources/js/scan/cameraLoginByQrcode.js',

      'resources/js/admin/dashboard/detail.js',
      'resources/js/admin/labels/detail.js',
      'resources/js/admin/cards/detail.js',
      'resources/js/admin/cards/aim.js',
      'resources/js/admin/campaigns/detail.js',
      'resources/js/admin/emails/history.js',
      'resources/js/admin/companys/detail.js',
      'resources/js/admin/clients/detail.js',
      'resources/js/admin/clients/index.js',
      'resources/js/admin/clients/import.js',
      'resources/js/admin/events/detail.js',
      'resources/js/admin/events/index.js',
      'resources/js/admin/reports/detail.js',
      'resources/js/admin/users/index.js',
      'resources/js/admin/users/detail.js',
      'resources/js/admin/checkins/config.js',
      'resources/js/admin/landing_pages/detail.js',
      'resources/js/admin/lucky_draws/detail.js',
      'resources/js/web/landing_pages/register.js',
      'resources/js/web/landing_pages/success.js',
      'resources/js/scan/scan.js',

    ])
  ]
})
