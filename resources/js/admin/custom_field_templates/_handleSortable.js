"use-strict";

import { loadScript } from "../common/_loadScript";

export const handleSortable = async (container = '#sortable-wrapper', items = '.sortable-item') => {
  try {
    await loadScript("https://code.jquery.com/ui/1.14.1/jquery-ui.js");
    console.log("Sortable initialized.");
    sortable(container, items);
  } catch (error) {
    console.error(error);
  }
}

const sortable = (container, items) => {
  $(container).sortable({
    update: function () {
      $(items).each(function (index) {
        $(this).find('input[name="order[]"]').val(index + 1);
      });

      // Prepare data to send
      let sortedData = $(items).map(function () {
        let id = $(this).data('id');
        let order = $(this).find('input[name="order[]"]').val();
        $(`#order-${id}`).text(order);

        return {
          id: id,
          order: $(this).find('input[name="order[]"]').val()
        };
      }).get();

      // Send AJAX request
      $.ajax({
        url: '/admin/custom_field_templates/update-orders',
        type: 'POST',
        data: {
          _token: $('meta[name="csrf-token"]').attr('content'),
          items: sortedData
        },
        success: function (response) {
          console.log('Order updated!');
        },
        error: function (xhr) {
          console.error('Update failed', xhr.responseText);
          toastr.error(xhr.responseText);
        }
      });
    }
  });
}
