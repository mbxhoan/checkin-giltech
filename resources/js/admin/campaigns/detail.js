"use-strict";

import { renderSendMailTable } from "../emails/_renderSendMailTable";
import { handleChangeStatus } from "../emails/_handleChangeStatus";

$(document).ready(function () {
  handleChangeStatus();
  renderSendMailTable('#table-send-mail');

  $('#from_email').select2();
  $('#template_id').select2();
});

$(document).on('draw.dt', function (e, settings) {
  $('.btn-quick-edit').on('click', function (e) {
    e.preventDefault();
    let id = $(this).data('id');
    let field = $(this).data('field');

    // Example: prompt for new value
    let newValue = prompt(`Nhập thay đổi:`);
    if (newValue !== null) {
      updateField(id, field, newValue);
    }
  });
});

// Function to update field via POST to controller
function updateField(id, field, value) {
  $.ajax({
    url: '/admin/campaign_details/update-field', // Adjust URL to your controller route
    type: 'POST',
    data: {
      id: id,
      field: field,
      value: value,
      _token: $('meta[name="csrf-token"]').attr('content') // If using Laravel CSRF
    },
    success: function (response) {
      console.log('Field updated successfully!');
      $(`#${field}-${id}`).text(value);
      // Optionally, refresh table or update UI
    },
    error: function (xhr) {
      alert('Error updating field.');
    }
  });
}
