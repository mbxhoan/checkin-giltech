"use-strict";

// const clearText = (type, ele) => {
//     if (type !== null) {
//         switch (type) {
//             case 'input':
//                 $(ele).val('');
//                 break;
//             case 'textarea':
//                 break;
//         }
//     }
// };

const getBtnWaiting = (btn) => {
    $(btn).html('<i class="fa-solid fa-spinner fa-spin-pulse"></i> Loading')
    $(btn).addClass('disabled')
    return true;
};

const getBtn = (btn, html) => {
    $(btn).html(html)
    $(btn).removeClass('disabled')
    return true;
};

module.exports = { getBtn, getBtnWaiting };

