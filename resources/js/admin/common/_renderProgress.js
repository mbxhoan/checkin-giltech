
export const renderProgress = () => {
  const $progressBar = $('#progress-bar');
  if (!$progressBar.length) return; // Exit if it doesn't exist
  let time = $progressBar.data('time') || 5; // fallback to 5 seconds if not set

  setInterval(() => {
    getProgress();
  }, time*1000);
}

const getProgress = () => {
  $.ajax({
    url: $('#progress-bar').data('url'),
    type: 'GET',
    success: function (response) {
      if (response.status === 'success') {
        let html = response.data.html;
        let ele = $('#progress-bar').data('ele');
        $(ele).html(html);

        if (response.data.redirect) {
          window.location.href = response.data.redirect;
        }

        if (response.data.reload) {
          location.reload(true);
        }

        return

        let total = response.data.total;
        let completed = response.data.completed;

        const percent = total > 0
            ? Math.round((completed / total) * 100)
            : 0;

        const $bar = $('.progress-bar');
        $bar.css('width', percent + '%');
        $bar.attr('aria-valuenow', percent);
        $bar.text(`${percent}% (${completed}/${total})`);
      }
    },
    error: function (e) {
      // Handle errors during the AJAX request
      if (e.responseJSON.message) {
        console.error(e.responseJSON.message);
        return;
      }

      console.error(e);
    }
  });
}
