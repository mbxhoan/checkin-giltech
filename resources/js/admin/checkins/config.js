"use-strict";

import { loadScript } from "../common/_loadScript";
/* custom field templates */
import { handleDelCustomFieldTemplate } from "../custom_field_templates/_del-custom-field-template";
import { handleEditChangeField } from "../custom_field_templates/_edit-change-field";
import { handleEditSetting } from "../events/_edit-setting";
import { handleAudioClick } from "./_handleAudioClick";
import { handleDraggableCheckin } from "./_handleDraggableCheckin";

import { renderBackground } from "./_renderBackground";

$(document).ready(function () {
  // renderBackground();
  handleAudioClick();

  /* settings */
  handleEditSetting();

  /* custom field templates */
  handleEditChangeField(true, [
    renderBackground
  ]);
  handleDelCustomFieldTemplate();

  /* draggable */
  handleDraggableCheckin();

  /* sortable */
  // handleSortableCheckin();
});

const handleSortableCheckin = async () => {
   try {
      await loadScript("https://code.jquery.com/ui/1.14.1/jquery-ui.js");
      console.log("Draggable initialized.");

      $("#sortable").sortable({
        update: function () {
          console.log($(this).attr('id'));

          // $('.field-data .row-attr').each(function (index, value) {
          //   let indexRow = index + 1;
          //   let currentRow = parseInt($(this).attr('data-rowid'));
          //   updateSortedRow($(this), currentRow, indexRow);
          //   $(this).attr('id', 'row_' + indexRow);
          // });
          // updateOrderCustomFields();
        }
      });
    } catch (error) {
      console.error(error);
    }


}
