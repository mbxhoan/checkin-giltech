
export const handleClickPrint = (offAfterPrint = true) => {
  $("#btn-print").click(function (e) {
    e.preventDefault();
    let printContents = document.getElementById("to-print").innerHTML;
    let currentUrl = window.location.href;
    // let printPage = window.open('', '_blank');
    let printPage = window.open(currentUrl, '_blank', "toolbar=yes,scrollbars=yes,resizable=yes,top=0,left=0,width=400,height=400");
    let style = printPage.document.createElement('style');
    let script = printPage.document.createElement('script');
    let customStyleElement = document.getElementById("style").innerHTML;
    printPage.document.write('<html>');
    printPage.document.write('<head>');

    let fontLinkHtml = document.getElementById("font-link").innerHTML;
    style.innerHTML = customStyleElement;

    if (offAfterPrint) {
      script.innerHTML = `
          window.onafterprint = function() {
              window.close();
          };
      `;
    }

    printPage.document.write('<title>Print Page</title>');
    printPage.document.write(fontLinkHtml);
    printPage.document.write('<meta charset="UTF-8">');
    printPage.document.write('<meta name=description content="">');
    printPage.document.write('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
    printPage.document.write('<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">');
    printPage.document.head.appendChild(style);
    printPage.document.write('<body >');
    writeLoadingHtml(printPage);
    setTimeout(() => {
      removeLoadingHtml(printPage);
      printPage.document.write(printContents);
      printPage.document.body.appendChild(script);
      printPage.document.close();
      printPage.print();
    }, 500);
  });
}

const writeLoadingHtml = (printPage) => {
  let style = printPage.document.createElement('style');
  printPage.document.write('<div class="spinner"></div>');
  style.innerHTML = `
        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid rgba(0, 0, 0, 0.1);
            border-top: 4px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    `;
  printPage.document.head.appendChild(style);
}

const removeLoadingHtml = (printPage) => {
  let spinner = printPage.document.querySelector('.spinner');
  let style = printPage.document.querySelector('style');

  if (spinner) {
    spinner.remove();
  }

  if (style) {
    style.remove();
  }
}
