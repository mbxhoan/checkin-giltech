
import { handleClickMultiPrint } from "../labels/_multiPrint";
import { handleCheckoutWithoutCheckin } from "./_handleCheckoutWithoutCheckin";
import { handleClickPrintByClass } from "./_handleClickPrintByClass";
import { handleToggleModal } from "./_handleToggleModal";

$(document).ready(function() {
  handleClickMultiPrint(false);
  handleCheckoutWithoutCheckin();
});

$(document).on('draw.dt', function(e, settings) {
    handleClickPrintByClass();
    handleToggleModal();
    handleCheckoutWithoutCheckin();
});
