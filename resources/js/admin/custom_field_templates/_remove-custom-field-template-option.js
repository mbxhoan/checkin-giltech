"use-strict";

import { submitFormOnChange } from "../common/submitFormOnChange";

export const handleRemoveRowOption = () => {
  $('.btn-remove-option').on('click', function (e) {
    e.preventDefault();
    let formId = $(this).data('id');
    let id = $(this).attr('id');

    console.log(id);

    $(`#${id}`).remove();
    submitFormOnChange(formId);
  })
}
