"use-strict";

$(document).ready(function () {
  handleBtnToggleShow('btn-open-camera', '#cameraBtns', 'camera_visibility');
});

const handleBtnToggleShow = (btnId, textElement, localKey) => {
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
  // applySavedState();

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

    setTimeout(() => {
      initQrScanner({
        cameraBtnSelector: '#cameraBtn',
        stopBtnSelector: '#stopBtn',
        serialInputSelector: '#qrcode',
        qrReaderSelector: '#camera-qrcode-reader',
        qrReaderId: 'camera-qrcode-reader',
        onScanCallback: function (decodedText) {
          // Example: assuming your QR code text includes an "id" and "code"
          // If not, you can parse or define them as needed
          // scanForClient(decodedText, 98147);
          scanForClient(decodedText);
          playSound("sound_success");
          $('#cameraBtns').hide();
        }
      });
    }, 100);
  });
};

const scanForClient = (code) => {
  const csrf = $('meta[name="csrf-token"]').attr('content');
  const url = $("#camera-qrcode-reader").data('url');

  console.log(code);
  console.log(url);
  console.log(csrf);

  // Post to Laravel route
  $.ajax({
      url: url,
      type: 'POST',
      data: {
          code: code,
          _token: csrf // Always include CSRF for Laravel
      },
      beforeSend: function () {
        // ✅ Code runs right before sending the request
        $('.overlay-layer').show();
        $('#score-count').html('<i class="fa-solid fa-spinner fa-spin"></i>');
        console.log('Sending request...');
      },
      success: function (response) {
        $('.overlay-layer').hide();
        console.log('Success: ', response);
        window.location.href = response.data.redirectTo;
        // toastr.info(response.message);
      },
      error: function (xhr) {
        $('.overlay-layer').hide();
        console.error('Error:', xhr.responseJSON.message);
        alert('Error: ' + xhr.responseJSON.message);
        // toastr.error(xhr.responseJSON.message);
      }
  });
}

const initQrScanner = (options) => {
  const cameraBtn = $(options.cameraBtnSelector);
  const stopBtn = $(options.stopBtnSelector);
  const textInput = $(options.textInputSelector);
  const qrReaderDiv = $(options.qrReaderSelector);
  const placeholderDiv = $('#camera-placeholder'); // get the placeholder link
  const placeholderUrl = qrReaderDiv.data('placeholder'); // get the placeholder link
  const imgTag = $('<img>', {
    src: placeholderUrl,
    alt: 'Placeholder',
    style: 'width: 100%; max-width: 100%; height: auto;' // optional: make it responsive
  });

  const qrReaderId = options.qrReaderId;
  const onScanCallback = options.onScanCallback;
  const qrReaderWidth = qrReaderDiv.width();
  const qrboxWidth = 250;

  let reader = null;
  let isScannerRunning = false;

  cameraBtn.addClass('disabled');
  cameraBtn.html('<i class="fa-solid fa-spinner fa-spin"></i>');
  hidePlaceholderImage();
  startScanner();

  // cameraBtn.on('click', function (e) {
  //   e.preventDefault();
  //   cameraBtn.addClass('disabled');
  //   cameraBtn.html('<i class="fa-solid fa-spinner fa-spin"></i>');
  //   hidePlaceholderImage();

  //   if (!isScannerRunning) {
  //     startScanner();
  //   } else {
  //     stopScanner();
  //   }
  // });

  stopBtn.on('click', function (e) {
    e.preventDefault();
    cameraBtn.show();
    cameraBtn.removeClass('disabled');
    cameraBtn.html('<i class="fa-solid fa-camera fa-fw"></i> Mở camera');
    stopScanner();
    stopBtn.hide();
    showPlaceholderImage();
    $('#cameraBtns').hide();
  });

  function startScanner() {
    qrReaderDiv.show();
    qrReaderDiv.html('<i class="fa-solid fa-spinner fa-spin"></i>');

    reader = new Html5Qrcode(qrReaderId);

    Html5Qrcode.getCameras().then(cameras => {
      if (cameras && cameras.length) {
        reader.start(
          { facingMode: "environment" },
          {
            fps: 15,
            qrbox: qrboxWidth,
            aspectRatio: 1.0
          },
          onScanSuccess,
          onScanFailure
        ).then(() => {
          isScannerRunning = true;
        });
      }

      // cameraBtn.html('<i class="fa-solid fa-camera fa-fw"></i> Mở camera');
      cameraBtn.hide();
      stopBtn.show();
    }).catch(err => {
      console.error("Camera error: ", err);
      cameraBtn.removeClass('disabled');
      cameraBtn.html('<i class="fa-solid fa-camera fa-fw"></i> Mở camera');
      showPlaceholderImage();
    });
  }

  function stopScanner() {
    if (reader && isScannerRunning) {
      reader.stop().then(() => {
        // DO NOT call html5QrCode.clear();
        qrReaderDiv.hide();
        isScannerRunning = false;

        cameraBtn.removeClass('disabled');
        cameraBtn.html('<i class="fa-solid fa-camera fa-fw"></i> Mở camera');
        cameraBtn.show();
        stopBtn.hide();
        showPlaceholderImage(); // safe to modify content here
      }).catch(err => {
        console.error("Failed to stop scanner: ", err);
      });
    }
  }

  function onScanSuccess(decodedText, decodedResult) {
    textInput.val(decodedText);
    if (typeof onScanCallback === 'function') {
      onScanCallback(decodedText);
    }
    textInput.val("");
    stopScanner();
  }

  function onScanFailure(error) {
    // Optional: Ignore or log errors
  }

  function showPlaceholderImage() {
    // qrReaderDiv.empty();
    // qrReaderDiv.append(imgTag);
    placeholderDiv.show();
  }

  function hidePlaceholderImage() {
    placeholderDiv.hide();
  }
}

const playSound = (elementId) => {
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
