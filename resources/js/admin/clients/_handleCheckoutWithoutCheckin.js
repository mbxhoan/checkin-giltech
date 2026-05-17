"use-strict";

export const handleCheckoutWithoutCheckin = () => {
  $(document).off('submit.checkoutWithoutCheckin', '.js-checkout-form');

  $(document).on('submit.checkoutWithoutCheckin', '.js-checkout-form', function (e) {
    const hasCheckin = Number($(this).data('has-checkin')) === 1;

    if (hasCheckin) {
      return true;
    }

    e.preventDefault();
    const form = this;

    Swal.fire({
      title: 'Chưa checkin',
      text: 'Khách này chưa checkin, bạn có muốn checkout?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#0d6efd',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Checkout',
      cancelButtonText: 'Huỷ',
    }).then((result) => {
      if (result.isConfirmed) {
        form.submit();
      }
    });

    return false;
  });
}
