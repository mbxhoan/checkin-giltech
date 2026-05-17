export const playSound = (elementId) => {
  // document.getElementById(elementId).play();

  const audio = document.getElementById(elementId);
  if (audio && typeof audio.play === 'function') {
    audio.pause();           // stop current if playing
    audio.currentTime = 0;   // rewind
    audio.play();
  } else {
    console.warn(`Audio element with ID "${elementId}" not found or is not playable.`);
  }
};
