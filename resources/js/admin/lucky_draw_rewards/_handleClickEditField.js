
import validated from '../../common/validated';
import helper from '../../common/helper';

export const handleClickEditField = () => {
    $('.btn-edit-field').on('click', function(e) {
        e.preventDefault();
        let id = $(this).attr('id');

        let inputBlock = `.input-block#${id}`;
        let editBlock = `.edit-block#${id}`;

        $(inputBlock).show();
        $(editBlock).hide();

        // handleClickUpdateField(`btn-update-${id}`);
        handleClickUpdateField(`${id}`);
        handleClickCancel(id);
    });
}

const handleClickUpdateField = (attrId) => {
    $('.btn-update-field').on('click', function(e) {
        e.preventDefault();
        if (attrId == '') {
            attrId = $(this).attr('id');
        }

        let id = $(this).data('id');
        let field = $(this).data('field');
        // let value = $(`input#edit-${attrId}`).val();
        let value = $(`input#edit-${field}-${id}`).val();
        let url = $(this).data('url');
        let token = helper.getToken();
        let btnHtml = $(this).html();

        // let editBlock = `.edit-block#${attrId}`;
        // let inputBlock = `.input-block#${attrId}`;
        let editBlock = `.edit-block#${field}-${id}`;
        let inputBlock = `.input-block#${field}-${id}`;

        // let cancelBtn = `#${attrId}.btn-cancel`;
        let cancelBtn = `#${field}-${id}.btn-cancel`;
        let updateBtn = `.btn-update-field#${attrId}`;
        // let updateBtn = `#${attrId}`;
        $(cancelBtn).off('click');

        let data = {
            'id': id,
            'field': field,
            'value': value,
            '_token': token
        };

        $.ajax({
            method: 'POST',
            url: url,
            data: data,
            dataType: 'json',
            beforeSend: function() {

            }
        })
        .fail(function(jqXHR, textStatus) {
            let attrs = new Object();
            attrs.code = jqXHR.status;
            attrs.errors = jqXHR.responseJSON.message;
            validated.showErrorMsg(attrs);
            toastr.error(result.data.message);

            $(inputBlock).hide();
            $(editBlock).show();
        })
        .done(function(result) {
            toastr.success(result.data.message);
            let spanDiv = $(`${editBlock} span.value`);
            let label = spanDiv.data('label');
            console.log(label);

            if (label !== '' && label !== undefined) {
                $(`${editBlock} span.value`).text(`${label}: ${value}`);
            } else {
                $(`${editBlock} span.value`).text(value);
            }

            $(inputBlock).hide();
            $(editBlock).show();

            console.log(updateBtn);
            $(updateBtn).off('click');
        });

        $(this).off('click');
    });
}

const handleClickCancel = (id) => {
    $(`#${id}.btn-cancel`).on('click', function(e) {
        e.preventDefault();
        let inputBlock = `.input-block#${id}`;
        let editBlock = `.edit-block#${id}`;
        let updateBtn = `.btn-update-field#${id}`;
        // let updateBtn = `#btn-update-${id}`;
        // let cancelBtn = `#${id}.btn-cancel`;

        $(inputBlock).hide();
        $(editBlock).show();
        $(updateBtn).off('click');
        // $(updateBtn).off('click');
        // $(this).off('click');
        // $(`#${id}`).off('click');
        console.log(updateBtn);
        // console.log($(updateBtn).data('id'));
    });
}
