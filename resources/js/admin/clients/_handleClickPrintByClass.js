
export const handleClickPrintByClass = () => {
  $(document).off("click.printByClass", ".btn-print");

  $(document).on("click.printByClass", ".btn-print", function (e) {
    e.preventDefault();

    const modalId = $(this).data("modal_id");
    const printTarget = document.querySelector(`#${modalId} #to-print`);

    if (!printTarget) {
      return;
    }

    const printContents = printTarget.innerHTML;
    closeModal(modalId);
    printByHiddenFrame(printContents);
  });
}

const closeModal = (modalId) => {
  const modalElement = document.getElementById(modalId);

  if (!modalElement || !window.bootstrap) {
    return;
  }

  const modal = window.bootstrap.Modal.getOrCreateInstance(modalElement);
  modal.hide();
}

const printByHiddenFrame = (printContents) => {
  const customStyleElement = document.getElementById("style")?.innerHTML || "";
  const fontLinkHtml = document.getElementById("font-link")?.innerHTML || "";

  const iframe = document.createElement("iframe");
  iframe.style.position = "fixed";
  iframe.style.right = "0";
  iframe.style.bottom = "0";
  iframe.style.width = "0";
  iframe.style.height = "0";
  iframe.style.border = "0";
  iframe.setAttribute("aria-hidden", "true");
  document.body.appendChild(iframe);

  const doc = iframe.contentWindow.document;
  doc.open();
  doc.write(`
    <html>
      <head>
        <title>In tem</title>
        ${fontLinkHtml}
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">
        <style>${customStyleElement}</style>
      </head>
      <body>${printContents}</body>
    </html>
  `);
  doc.close();

  setTimeout(() => {
    iframe.contentWindow.focus();
    iframe.contentWindow.print();
  }, 250);

  const removeIframe = () => {
    if (iframe && iframe.parentNode) {
      iframe.parentNode.removeChild(iframe);
    }
  };

  iframe.contentWindow.onafterprint = removeIframe;
  setTimeout(removeIframe, 3000);
}
