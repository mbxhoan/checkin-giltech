"use-strict";

import { renderEventSelect2 } from "./_renderEventSelect2";

$(document).ready(function () {
  $('#company_id').select2();
  $('#event_id').select2();

  changeCompanySelect();
  changeEmail();
});

const changeCompanySelect = () => {
  $('select#company_id').on('change', function () {
    let companyId = $(this).val();
    renderEventSelect2(companyId, true);
    return true;
  });

  let companyId = $('#company_id').val();
  renderEventSelect2(companyId, true);
  return true;
}

const changeEmail = () => {
  $('#email').on('input', function () {
      const email = $(this).val();
      const username = email.split('@')[0]; // Get part before @

      if (username) {
          $('#username').val(username);
      } else {
          $('#username').val('');
      }
  });
}
