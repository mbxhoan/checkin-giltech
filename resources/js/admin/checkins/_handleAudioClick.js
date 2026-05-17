
export const handleAudioClick = () => {
  $('.audio-play').on('click', function (e) {
    e.preventDefault();
    const audioId = $(this).data('id');
    const audio = document.getElementById(audioId);
    audio.pause(); // Reset if already playing
    audio.currentTime = 0;
    audio.play();
  });
}
