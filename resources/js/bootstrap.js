/**
 * We'll load jQuery and the Bootstrap jQuery plugin which provides support
 * for JavaScript based Bootstrap features such as modals and tabs. This
 * code may be modified to fit the specific needs of your application.
 */
// Bootstrap JS loaded as offline global in admin layout (offlines/offline-js/5.3.5-bootstrap.bundle.min.js)
// window.bootstrap is available globally via that script
import Clipboard from 'clipboard'
// import jquery from 'jquery'
import Echo from 'laravel-echo'
import 'pusher-js'
import * as Turbo from '@hotwired/turbo'

Turbo.session.drive = false

// window.$ = window.jQuery = jquery

new Clipboard('[data-clipboard-target]')

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

window.Echo = new Echo({
  broadcaster: 'pusher',
  key: import.meta.env.VITE_PUSHER_APP_KEY,
  cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
  encrypted: true
})
