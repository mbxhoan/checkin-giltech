
export const handleMultiLanguage = () => {
    $('.change-language').each(function(index) {
        $(this).on('click', function(e) {
            e.preventDefault();
            let url = $(this).data('url');
            let route = $(this).data('route');
            let params = $(this).data('params');

            $.get(url, function() {
                console.log('Redirecting...');
                // location.reload(true);
            }).done(function (locale) {
                if (locale != '') {
                    params.locale = locale;
                }

                // console.log(locale);
                // return false;

                let urlParams = $.param(params);
                $(location).prop('href', `${route}?${urlParams}`);
            });
        });
    });
}

export default function setDefaultLanguage() {
    let defaultLang = $('.language .init-default-language input#init-lang').val();

    if (defaultLang !== null) {
        let url = $(this).data('url');

        $.get(url, function(responseData) {
            console.log(`Setting language to ${defaultLang}...`);
        }).done(function (locale) {
            // console.log(`Default language already set to ${locale}`);
        });
    }
};
