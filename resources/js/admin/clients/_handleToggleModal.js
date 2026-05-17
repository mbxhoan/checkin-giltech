"use strict";

import { handleChangeLabelId } from "./_handleChangeLabelId";

export const handleToggleModal = () => {
  $(".btn-toggle-modal").on('click', function () {
      const modalId = $(this).data('modal_id')
      handleChangeLabelId(`#${modalId}`, `#${modalId} #label_id`, `#${modalId} #printContainer`);
    });
}
