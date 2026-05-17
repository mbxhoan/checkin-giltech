
/* custom field templates */
import { handleDelCustomFieldTemplate } from "../custom_field_templates/_del-custom-field-template";
import { handleEditChangeField } from "../custom_field_templates/_edit-change-field";
import { handleRemoveRowOption } from "../custom_field_templates/_remove-custom-field-template-option";
import { handleAddRowOption } from "../custom_field_templates/_add-custom-field-template-option";

/* settings */
import { handleEditSetting } from "./_edit-setting";
import { handleSyncSetting } from "./_handleSyncSetting";

/* datas */
import { handleGenerateClients } from "./_handleGenerateClients";

import { handleToggleCollapses } from "./_handleToggleCollapses";
import { handleSortable } from "../custom_field_templates/_handleSortable";

$(document).ready(function() {
  $('#company_id').select2();
  $('#province_id').select2();

  handleToggleCollapses();

  /* datas */
  handleGenerateClients();

  /* settings */
  handleEditSetting();
  handleSyncSetting();

  /* custom field templates */
  handleEditChangeField();
  handleDelCustomFieldTemplate();
  handleAddRowOption();
  handleRemoveRowOption();

  /* sortable */
  handleSortable();
});
