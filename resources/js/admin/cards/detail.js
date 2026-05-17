"use-strict";

import { renderBackground } from "../checkins/_renderBackground";
import { renderProgress } from "../common/_renderProgress";
import { handleEditChangeField } from "../custom_field_templates/_edit-change-field";
import { handleChangeCardId } from "./_handleChangeCardId";

$(document).ready(function () {
  handleEditChangeField(true, [renderBackground]);
  renderProgress();
  // handleDraggableCheckin('.background-container', '#backgroundContainer .draggable', true);
  handleChangeCardId('#card_id', 'admin/cards/edit');
});
