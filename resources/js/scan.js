import 'trumbowyg'
import svgPath from 'trumbowyg/dist/ui/icons.svg'
import { Tooltip } from 'bootstrap'

import toastr from 'toastr';
import 'toastr/build/toastr.min.css';

window.toastr = toastr;

$('.trumbowyg-form').trumbowyg({
  svgPath: svgPath
})

// Configure tooltips for collapsed side navigation
document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(tooltipTriggerEl => {
  new Tooltip(tooltipTriggerEl, {
    template: '<div class="tooltip navbar-sidenav-tooltip" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
  })
})
