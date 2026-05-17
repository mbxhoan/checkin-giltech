export const handleBtnToggleShow = (btnId, textElement, localKey) => {
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
    var anyHidden = $(`${textElement}`).is(':hidden');

    if (anyHidden) {
      $(`${textElement}`).show();
      localStorage.setItem(STORAGE_KEY, 'shown');
    } else {
      $(`${textElement}`).hide();
      localStorage.setItem(STORAGE_KEY, 'hidden');
    }
  });
};
