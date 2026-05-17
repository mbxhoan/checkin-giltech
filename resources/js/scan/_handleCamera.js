import { initQrScanner } from "./_camera";
import { checkinOnline } from "./_checkinOnline";

export const handleCamera = () => {
  /* camera */
  initQrScanner({
    cameraBtnSelector: '#cameraBtn',
    stopBtnSelector: '#stopBtn',
    serialInputSelector: '#qrcode',
    textInputSelector: '#qrcode',
    qrReaderSelector: '#camera-qrcode-reader',
    qrReaderId: 'camera-qrcode-reader',
    onScanCallback: function (decodedText) {
      checkinOnline(decodedText, true); // your custom function
      // $('#cameraBtns').css('opacity', 0);
      $('#cameraBtns').hide();
    }
  });
}
