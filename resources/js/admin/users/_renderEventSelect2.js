
export const renderEventSelect2 = (companyId, defaultNull = false) => {
  if (companyId) {
    $.ajax({
      url: `/admin/events/data/get-list-by/${companyId}`,
      type: 'GET',
      success: function (response) {
        let selected = $('#event_id').val();
        let datas = response.data.list;

        $('#event_id').empty();

        if (defaultNull) {
          $('#event_id').append('<option value=""> - </option>');
        }

        $.each(datas, function (index, item) {
          $('#event_id').append(`<option value="${item.id}">${item.code} - ${item.name}</option>`);
        });

        $('#event_id').val(selected);
      },
      error: function (xhr, status, error) {
        console.error('Error fetching info:', error);
      }
    });
  } else {
    $('#event_id').empty();
    $('#event_id').append('<option value="">-</option>');
  }
}
