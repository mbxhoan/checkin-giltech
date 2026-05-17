export const handleToggleOpacity = (btnId, component, localKey) => {
  const STORAGE_KEY = localKey;

  // Function to apply the saved state
  function applySavedState() {
    const savedState = localStorage.getItem(STORAGE_KEY);
    if (savedState === 'shown') {
      $(`${textElement}`).show();
    } else if (savedState === 'hidden') {
      $(`${textElement}`).hide();
    }
  }

  // Call it once when page loads
  applySavedState();

  $(`#${btnId}`).on("click", function (e) {
    e.preventDefault();

    const $elements = $(`${component}`);
    let anyInvisible = false;

    $elements.each(function() {
      const opacity = parseFloat($(this).css('opacity'));
      if (opacity === 0) {
        anyInvisible = true;
        return false; // exit loop early
      }
    });

    if (anyInvisible) {
      $elements.css('opacity', 1);
    } else {
      $elements.css('opacity', 0);
    }
  });
};
