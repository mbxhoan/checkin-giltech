
export const handleChangeLabelId = (eleId) => {
  $(eleId).on('change', function (event) {
    let value = $(this).val();
    let url = new URL($(this).data('url'), window.location.origin);
    window.location.href = url.toString() + `admin/labels/edit/${value}`;
    return;

    const params = new URLSearchParams(url.search);
    params.set('label_id', value);
    url.search = params.toString();
    window.location.href = url.toString();
  });
}
