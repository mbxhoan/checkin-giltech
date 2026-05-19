import 'trumbowyg'
import svgPath from 'trumbowyg/dist/ui/icons.svg'
// Tooltip dùng global window.bootstrap (loaded từ offline bundle trong admin layout)
const Tooltip = window.bootstrap?.Tooltip

/* common */
import { btnSubmitLoading } from './common/btnSubmit';
import { handleRowClick } from './common/table';

const showLoadingOverlay = (message) => {
  window.GiltechLoadingOverlay?.show(message);
};

const hideLoadingOverlay = () => {
  window.GiltechLoadingOverlay?.hide();
};

const bindSidebar = () => {
  const body = document.body;
  const openButton = document.getElementById('adminSidebarToggle');
  const closeButton = document.getElementById('adminSidebarClose');
  const backdrop = document.getElementById('adminSidebarBackdrop');

  const closeSidebar = () => body.classList.remove('sidebar-open');
  const openSidebar = () => body.classList.add('sidebar-open');

  openButton?.addEventListener('click', () => openSidebar());
  closeButton?.addEventListener('click', () => closeSidebar());
  backdrop?.addEventListener('click', () => closeSidebar());

  window.addEventListener('resize', () => {
    if (window.innerWidth >= 992) {
      closeSidebar();
    }
  });
};

const bindQuickSearch = () => {
  const input = document.getElementById('adminNavSearch');
  const results = document.getElementById('adminNavSearchResults');

  if (!input || !results) {
    return;
  }

  const links = Array.from(document.querySelectorAll('[data-nav-search-label]'));
  let highlightedIndex = -1;

  const closeResults = () => {
    results.innerHTML = '';
    results.classList.add('d-none');
    highlightedIndex = -1;
  };

  const render = (keyword) => {
    const normalizedKeyword = keyword.trim().toLowerCase();

    if (!normalizedKeyword) {
      closeResults();
      return;
    }

    const matches = links
      .filter(link => link.dataset.navSearchLabel?.includes(normalizedKeyword))
      .slice(0, 6);

    if (!matches.length) {
      results.innerHTML = '<div class="px-3 py-2 text-muted small">Không tìm thấy module phù hợp.</div>';
      results.classList.remove('d-none');
      highlightedIndex = -1;
      return;
    }

    results.innerHTML = matches
      .map((link, index) => `
        <a
          href="${link.href}"
          class="${index === 0 ? 'is-highlighted' : ''}"
          data-loading-nav="${link.dataset.loadingNav || 'Đang chuyển trang...'}"
        >
          <i class="fa-solid fa-arrow-up-right-from-square fa-fw"></i>
          <span>${link.textContent.trim()}</span>
        </a>
      `)
      .join('');

    results.classList.remove('d-none');
    highlightedIndex = 0;
  };

  input.addEventListener('input', (event) => render(event.target.value));

  input.addEventListener('keydown', (event) => {
    const items = Array.from(results.querySelectorAll('a'));

    if (!items.length) {
      if (event.key === 'Enter') {
        event.preventDefault();
      }

      return;
    }

    if (event.key === 'ArrowDown') {
      event.preventDefault();
      highlightedIndex = (highlightedIndex + 1) % items.length;
    }

    if (event.key === 'ArrowUp') {
      event.preventDefault();
      highlightedIndex = (highlightedIndex - 1 + items.length) % items.length;
    }

    if (event.key === 'Enter' && highlightedIndex >= 0) {
      event.preventDefault();
      items[highlightedIndex].click();
      return;
    }

    items.forEach((item, index) => {
      item.classList.toggle('is-highlighted', index === highlightedIndex);
    });
  });

  document.addEventListener('click', (event) => {
    if (!results.contains(event.target) && event.target !== input) {
      closeResults();
    }
  });

  document.addEventListener('keydown', (event) => {
    const isTypingContext = ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName);

    if (!isTypingContext && event.key === '/') {
      event.preventDefault();
      input.focus();
    }

    if (event.key === 'Escape') {
      closeResults();
      input.blur();
    }
  });
};

const bindLoadingLinks = () => {
  document.addEventListener('click', (event) => {
    const link = event.target.closest('a[data-loading-nav]');

    if (!link || event.defaultPrevented) {
      return;
    }

    if (link.target === '_blank' || link.hasAttribute('download')) {
      return;
    }

    if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
      return;
    }

    showLoadingOverlay(link.dataset.loadingNav || 'Đang chuyển trang...');
  });
};

document.addEventListener('DOMContentLoaded', () => {
  const $ = window.jQuery;

  if ($) {
    $('.trumbowyg-form').trumbowyg({
      svgPath: svgPath
    });

    btnSubmitLoading();
  }

  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(tooltipTriggerEl => {
    new Tooltip(tooltipTriggerEl, {
      template: '<div class="tooltip navbar-sidenav-tooltip" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
    });
  });

  bindSidebar();
  bindQuickSearch();
  bindLoadingLinks();
  handleRowClick();
  hideLoadingOverlay();
});
