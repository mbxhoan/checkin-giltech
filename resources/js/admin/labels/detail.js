"use-strict";

import { handleDraggableCheckin } from "../checkins/_handleDraggableCheckin";
import { renderLabel } from "../label_details/_renderLabel";
import { handleEditChangeField } from "../custom_field_templates/_edit-change-field";
import { handleClickPrint } from "./_print";
import { handleUpdateLabel } from "./_handleUpdateLabel";
import { handleChangeLabelId } from "./_handleChangeLabelId";
import { handleClickMultiPrint } from "./_multiPrint";

$(document).ready(function () {
  handleEditChangeField(true, [renderLabel]);
  handleDraggableCheckin('#ms-label', '.draggable', true);
  handleClickPrint(false);
  handleClickMultiPrint(false);
  handleUpdateLabel();
  handleChangeLabelId('#label_id');
});
