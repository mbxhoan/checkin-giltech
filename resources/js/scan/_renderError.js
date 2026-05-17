import { playSound } from "./_playSound";

export const renderError = (fields = null, msg) => {
  $('.overlay-layer').hide();

  if (fields !== null) {
    if (fields.length > 0) {
      $.each(fields, function (key, item) {
        if ($.isArray(item) || typeof item == 'object') {
          let text = '';
          let values = []; // Array to store values

          $.each(item, function (index, value) {
            if (typeof value === 'object' && value !== null) {
              // If the value is an object, stringify it
              values.push(JSON.stringify(value));
            } else {
              values.push(value);
            }
          });

          text = values.join(', '); // Join values with commas

          $(`#field-${key}`).text(text); // Set as text, not HTML
        } else {
          $(`#field-${key}`).text(item);
        }
        console.log(key);

        $(`#field-${key}`).show();
      });
    }
  }

  if ($('#msg-failed').hasClass("show-image")) {

  } else {
    $('#msg-failed').html(msg);
  }

  $('#msg-failed').show();
  playSound("sound_fail");
}
