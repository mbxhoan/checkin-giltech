
import { handleClickClearOfflineCheckin } from "./_clearOfflineCheckin";
import { handleBtnToggleShow } from "./_handleBtnToggleShow";
import { handleCamera } from "./_handleCamera";
import { handleToggleOpacity } from "./_handleToggleOpacity";
import { inputQrcodeByChange } from "./_inputQrcodeByChange";
import { renderCheckinOfflineTable } from "./_renderCheckinOfflineTable";
import { handleClickSyncOfflineCheckin } from "./_syncOfflineCheckin";

$(document).ready(function () {
  handleBtnToggleShow('btn-show-fields', '.custom-field-box', 'custom-field-box_visibility');
  handleBtnToggleShow('btn-show-messages', '.custom-message', 'custom-message_visibility');
  handleToggleOpacity('btn-show-input', 'input#qrcode', 'input_qrcode');

  /* input types */
  inputQrcodeByChange();

  /* open camera */
  // handleBtnToggleShow('btn-show-camera', '#cameraBtns', 'camera_visibility');
  handleCamera();

  /* render checkin offline table to cache */
  renderCheckinOfflineTable();

  /* sync multi offline checkins */
  handleClickSyncOfflineCheckin();

  /* sync multi offline checkins */
  handleClickClearOfflineCheckin();
});
