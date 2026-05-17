
import { loadScript } from "../common/_loadScript";

export const handleDraggableCheckin = async (container = '.background-container', draggables = '#backgroundContainer .draggable', inContainer = false) => {
  // const loadScript = (src) => {
  //   return new Promise((resolve, reject) => {
  //     if (document.querySelector(`script[src="${src}"]`)) {
  //       return resolve(); // already loaded
  //     }

  //     const script = document.createElement("script");
  //     script.src = src;
  //     script.onload = () => resolve();
  //     script.onerror = () => reject(`Failed to load script: ${src}`);
  //     document.body.appendChild(script);
  //   });
  // };

  try {
    await loadScript("https://code.jquery.com/ui/1.14.1/jquery-ui.js");
    console.log("Draggable initialized.");
    draggableCheckin(container, draggables, inContainer);
  } catch (error) {
    console.error(error);
  }
}

const draggableCheckin = (container = '.background-container', draggables = '#backgroundContainer .draggable', inContainer = false) => {
  const $container = $(container);
  const $draggables = $(draggables);

  // $draggables.draggable('destroy');
  $draggables.each(function () {
    const $el = $(this);

    // ✅ Only destroy if already initialized
    if ($el.data("ui-draggable")) {
      $el.draggable('destroy');
    }
  });

  $draggables.draggable({
    containment: inContainer ? $container : null,
    drag: function (event, ui) {
      const containerWidth = $container.width();
      const containerHeight = $container.height();
      const draggablePosition = ui.position;
      const percentageLeft = containerWidth > 0 ? (draggablePosition.left / containerWidth) * 100 : 0;
      const percentageTop = containerHeight > 0 ? (draggablePosition.top / containerHeight) * 100 : 0;

      let pos_x = $(this).data('target-pos_x');
      let pos_y = $(this).data('target-pos_y');
      const $inputLeft = $(pos_x);
      const $inputTop = $(pos_y);

      $inputLeft.val(percentageLeft.toFixed(2));
      $inputTop.val(percentageTop.toFixed(2));
    },
    stop: function (event, ui) {
      const containerWidth = $container.width();
      const containerHeight = $container.height();
      const draggablePosition = ui.position;
      const percentageLeft = containerWidth > 0 ? (draggablePosition.left / containerWidth) * 100 : 0;
      const percentageTop = containerHeight > 0 ? (draggablePosition.top / containerHeight) * 100 : 0;

      let pos_x = $(this).data('target-pos_x');
      let pos_y = $(this).data('target-pos_y');
      const $inputLeft = $(pos_x);
      const $inputTop = $(pos_y);

      $inputLeft.val(percentageLeft.toFixed(2));
      $inputTop.val(percentageTop.toFixed(2)).trigger('change');
      console.log('Stopped dragging - Left:', percentageLeft.toFixed(2) + '%', 'Top:', percentageTop.toFixed(2) + '%');
    }
  });
}
