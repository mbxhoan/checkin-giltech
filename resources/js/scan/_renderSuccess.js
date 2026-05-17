import { playSound } from "./_playSound";

export const renderSuccess = (fields, msg, ele = '#msg-success', isOnline = true) => {
  $('.overlay-layer').hide();

  if (fields !== null) {
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
        let fieldBox = $(`#field-${key}`);
        let prefix = "";

        if (fieldBox.hasClass("show-fix-text")) {
          return;
        }

        if (fieldBox.hasClass("show-prefix")) {
          prefix = fieldBox.data('prefix');
          item = `${prefix} ${item}`;
        }

        if (fieldBox.hasClass("show-image-link")) {
          if (item) {
            if (isOnline) {
              // fieldBox.html(`<img src="${item}" alt="${key}" style="max-width: 100%; width: 100%; height: auto; border: 2px solid orange; border-radius: 10px;">`);
              // fieldBox.html(`<img src="${item}" alt="${key}" style="width: 350px; height: 350px; object-fit: cover; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 6px rgba(0,0,0,.2);" />`);
              fieldBox.html(`<img src="${item}" alt="${key}" style="width: 70%; aspect-ratio: 1 / 1; object-fit: cover; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 6px rgba(0,0,0,.2);" />`);
            } else {
              let imageHtml = $(`#client-${fields.id}-${key}`).html();
              fieldBox.html(imageHtml);
            }
          }
          return; // Skip to next iteration in $.each
        }

        fieldBox.text(item);
      }
    });

    $('.custom-field-box').show();
    $('.show-fix-text').show();
  }

  if ($(ele).hasClass("show-image")) {

  } else {
    $(ele).text(msg);
  }

  $(ele).show();
  playSound("sound_success");
}
