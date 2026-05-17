"use-strict";

import { renderBackground } from "../checkins/_renderBackground";
import { renderProgress } from "../common/_renderProgress";
import { handleEditChangeField } from "../custom_field_templates/_edit-change-field";
import { handleChangeCardId } from "./_handleChangeCardId";

$(document).ready(function () {
  handleEditChangeField(true, [renderBackground]);
  handleChangeCardId('#card_id', 'admin/cards/edit');
  // // handleDraggableCheckin('.background-container', '#backgroundContainer .draggable', true);
  renderProgress();

  toggleOffcanvasPosition();

  // const imageUrl = "http://localhost:8000/storage/medias/75/3.png";
  // getImageAspectRatio(imageUrl, function (aspectRatio, width, height) {
  //   console.log(`Aspect Ratio: ${aspectRatio}`);
  //   console.log(`Image Dimensions: ${width}x${height}`);

  //   const container = document.querySelector('.background-container');
  //   if (container && aspectRatio) {
  //     container.style.aspectRatio = `${width} / ${height}`;
  //   }
  // });
});

// function getImageAspectRatio(imageUrl, callback) {
//   const img = new Image();
//   img.src = imageUrl;

//   img.onload = function () {
//     const aspectRatio = img.width / img.height;
//     callback(aspectRatio, img.width, img.height);
//   };

//   img.onerror = function () {
//     console.error('Failed to load image:', imageUrl);
//     callback(null);
//   };
// }

// window.setBackgroundAspectRatio = function () {
//   const imageUrl = "http://localhost:8000/storage/medias/75/3.png";
//   function getImageAspectRatio(imageUrl, callback) {
//     const img = new Image();
//     img.src = imageUrl;

//     img.onload = function () {
//       const aspectRatio = img.width / img.height;
//       callback(aspectRatio, img.width, img.height);
//     };

//     img.onerror = function () {
//       console.error('Failed to load image:', imageUrl);
//       callback(null);
//     };
//   }

//   getImageAspectRatio(imageUrl, function (aspectRatio, width, height) {
//     console.log(`Aspect Ratio: ${aspectRatio}`);
//     console.log(`Image Dimensions: ${width}x${height}`);

//     const container = document.querySelector('.background-container');
//     if (container && aspectRatio) {
//       container.style.aspectRatio = `${width} / ${height}`;
//     }
//   });
// };

function toggleOffcanvasPosition() {
  $('.offcanvas_position').each(function () {
    const $select = $(this);
    const modelId = $select.attr('id');
    const canvasId = 'offcanvasFieldsConfig'; // Adjust if different per model
    const $offcanvas = $('#' + canvasId);
    console.log(modelId);


    // Load cached position
    const cached = localStorage.getItem(`offcanvas-position-${modelId}`);
    console.log(cached);

    if (cached) {
      $offcanvas.removeClass('offcanvas-start offcanvas-end offcanvas-top offcanvas-bottom')
        .addClass(`offcanvas-${cached}`);
      $select.val(cached);
      $select.show();
      $select.removeClass('d-none');
    }

    // On change, update class and cache
    $select.on('change', function () {
      const pos = $(this).val();
      console.log(pos);

      localStorage.setItem(`offcanvas-position-${modelId}`, pos);
      $offcanvas.removeClass('offcanvas-start offcanvas-end offcanvas-top offcanvas-bottom')
        .addClass(`offcanvas-${pos}`);
    });
  });
}
