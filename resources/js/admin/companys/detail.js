"use-strict";

import { handleSyncSetting } from "../events/_handleSyncSetting";

$(document).ready(function () {
  /* settings */
  handleSyncSetting();

  $('.checkbox-all-settings').each(function () {
    let group = $(this).attr('id');
    let allChecked = $('.checkbox-' + group + ':not(:checked)').length === 0;
    $(this).prop('checked', allChecked);
  });

  $('.checkbox-settings').on('change', function () {
    let group = $(this).data('group');
    let allChecked = $('.checkbox-' + group + ':not(:checked)').length === 0;
    $(`#${group}.checkbox-all-settings`).prop('checked', allChecked);
  });

  // When .checkbox-all-settings is clicked
  $('.checkbox-all-settings').on('change', function () {
    let group = $(this).attr('id');
    $('.checkbox-' + group).prop('checked', $(this).is(':checked'));
  });
});
