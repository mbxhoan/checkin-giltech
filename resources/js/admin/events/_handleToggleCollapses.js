
// import 'bootstrap';
// import $ from 'jquery';

export const handleToggleCollapses = () => {
  const storageKeyPrefix = 'collapse_state_';

  // Load and restore collapse state
  const restoreCollapseState = () => {
    $('.collapse').each(function () {
      const collapseId = $(this).attr('id');
      const state = localStorage.getItem(storageKeyPrefix + collapseId);

      if (state === 'open') {
        $(this).collapse('show');
      } else {
        $(this).collapse('hide');
      }
    });
  };

  // Save collapse state
  const saveCollapseState = (collapseId, state) => {
    localStorage.setItem(storageKeyPrefix + collapseId, state);
  };

  // Event on toggle button
  $('#btn-toggle-collapses').on('click', function (e) {
    e.preventDefault();

    let isCollapsed = $('.collapse.show').length === 0;

    if (isCollapsed) {
      $('.collapse').each(function () {
        $(this).collapse('show');
        const collapseId = $(this).attr('id');
        saveCollapseState(collapseId, 'open');
      });
    } else {
      $('.collapse').each(function () {
        $(this).collapse('hide');
        const collapseId = $(this).attr('id');
        saveCollapseState(collapseId, 'closed');
      });
    }
  });

  // Save state on individual collapse toggle
  $('.collapse').on('shown.bs.collapse', function () {
    const collapseId = $(this).attr('id');
    saveCollapseState(collapseId, 'open');
  });

  $('.collapse').on('hidden.bs.collapse', function () {
    const collapseId = $(this).attr('id');
    saveCollapseState(collapseId, 'closed');
  });

  // Call restore on load
  restoreCollapseState();

  $('a[data-bs-toggle="collapse"]').on('click', function (e) {
    // Prevent default link behavior (if needed)
    // e.preventDefault();

    let collapseId = $(this).attr('href');
    let target = $(collapseId); // e.g., #collapseExample

    target.on('shown.bs.collapse', function () {
      console.log('Collapse is now shown');
      // $(this).removeClass('collapsed');
      // target.removeClass('show');
    });

    target.on('hidden.bs.collapse', function () {
      console.log('Collapse is now hidden');
    });
  });
};
