export const initQrScanner = (options) => {
  const cameraBtn = $(options.cameraBtnSelector);
  const stopBtn = $(options.stopBtnSelector);
  const textInput = $(options.textInputSelector);
  const qrReaderDiv = $(options.qrReaderSelector);
  const placeholderDiv = $('#camera-placeholder');
  const cameraBtnsDiv = $('#cameraBtns');

  const qrReaderId = options.qrReaderId;
  const onScanCallback = options.onScanCallback;
  const qrboxWidth = 250;

  let html5QrCode = null;
  let isScannerRunning = false;

  // Original camera button (inside panel)
  cameraBtn.on('click', function (e) {
    e.preventDefault();
    if (!isScannerRunning) {
      startScanner();
    } else {
      stopScanner();
    }
  });

  stopBtn.on('click', function (e) {
    e.preventDefault();
    stopScanner();
  });

  textInput.on('focus', function (e) {
    if (isScannerRunning) {
      e.preventDefault();
      this.blur();
      suppressVirtualKeyboard();
    }
  });

  // Direct camera button (bottom icon) - start camera immediately
  $('#btn-show-camera').on('click', function (e) {
    e.preventDefault();

    if (isScannerRunning) {
      // Camera running -> stop and hide
      stopScanner();
      cameraBtnsDiv.hide();
      localStorage.setItem('camera_visibility', 'hidden');
    } else {
      // Camera not running -> show panel and start immediately
      cameraBtnsDiv.show();
      placeholderDiv.hide();
      cameraBtn.hide(); // Hide "Mở camera" button immediately
      localStorage.setItem('camera_visibility', 'shown');
      startScanner();
    }
  });

  /**
   * Starts the QR code scanner (prevents Android from toggling visual keyboard).
   *
   * On many Android browsers, focusing an <input> or sometimes even showing certain
   * overlays can cause the virtual keyboard to appear, depending on the DOM state.
   * To prevent the keyboard from showing:
   *   - Ensure no input fields are automatically focused or selected here.
   *   - Explicitly blur the text input used for QR results.
   *   - Avoid focusing/triggering any other editable element.
   */
  function startScanner() {
    suppressVirtualKeyboard(); // 🔥 quan trọng

    if (textInput && textInput.length) {
      textInput.blur();
    }

    cameraBtn.addClass('disabled');
    cameraBtn.html('<i class="fa-solid fa-spinner fa-spin"></i>');
    qrReaderDiv.show();
    qrReaderDiv.html('<i class="fa-solid fa-spinner fa-spin"></i>');
    placeholderDiv.hide();

    html5QrCode = new Html5Qrcode(qrReaderId);

    Html5Qrcode.getCameras().then(cameras => {
      if (cameras && cameras.length) {
        html5QrCode.start(
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

      cameraBtn.hide();
      stopBtn.show();
    }).catch(err => {
      console.error("Camera error: ", err);
      cameraBtn.removeClass('disabled');
      cameraBtn.html('<i class="fa-solid fa-camera fa-fw"></i> Mở camera');
      cameraBtn.show();
      placeholderDiv.show();
    });
  }

  function stopScanner() {
    suppressVirtualKeyboard();
    
    if (html5QrCode && isScannerRunning) {
      html5QrCode.stop().then(() => {
        qrReaderDiv.hide();
        isScannerRunning = false;

        cameraBtn.removeClass('disabled');
        cameraBtn.html('<i class="fa-solid fa-camera fa-fw"></i> Mở camera');
        cameraBtn.show();
        stopBtn.hide();
        placeholderDiv.show();
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
}

function suppressVirtualKeyboard() {
  try {
    // Blur mọi element đang focus
    if (document.activeElement) {
      document.activeElement.blur();
    }

    // Android Chrome mới (Virtual Keyboard API)
    if (navigator.virtualKeyboard && navigator.virtualKeyboard.hide) {
      navigator.virtualKeyboard.hide();
    }
  } catch (e) {}
}
