export const handleRowClick = () => {
  const rows = document.querySelectorAll('tr[data-href]');

  rows.forEach(row => {
    // console.log(123444);
    row.addEventListener('click', function (e) {
      // e.preventDefault();
      // console.log(123);
      // console.log(e);
      // console.log(e.target);
      // console.log(e.target.tagName);
      // console.log(e.target.tagName === 'a');
      // console.log(e.target.tagName === 'BUTTON');
      // return
      
      // Prevent navigation if a button or link inside the row is clicked
      if ((e.target.tagName === 'A' || e.target.tagName === 'I') || e.target.tagName === 'BUTTON') {
        return;
      }

      window.location.href = this.dataset.href;
    });
  });
}
