$(document).ready(function(){
    var seoWrap = document.createElement('div');
    seoWrap.className = 'd2f_multidomain__seo_wrap';
    seoWrap.id = 'd2fMultidomainSeo';

    var divImg = document.createElement('div');
    // img.src = '/bitrix/images/dev2fun.multidomain/seo.png';
    divImg.className = 'd2f_multidomain__seo_image';

    seoWrap.append(divImg);
    document.body.append(document.createComment('dev2fun.multidomain start'));
    // document.body.append(seoWrap);
    $('body').append($(seoWrap)).ready(function(){
        $('#d2fMultidomainSeo').magnificPopup({
            type: 'ajax',
            preloader: true,
            // key: 'seos'+location.pathname,
            // focus: '',
            ajax: {
                settings : {
                    type : 'post',
                    url : '/bitrix/admin/dev2fun_subdomain_seo_form.php',
                    data : {
                        m_seo_host : location.host,
                        m_seo_page : location.pathname
                    },
                    success : function(data){
                        $.magnificPopup.open({
                            key: 'seo'+location.pathname,
                            items: {
                                src: data, // can be a HTML string, jQuery object, or CSS selector
                                type: 'inline'
                            }
                        });
                    }
                }
            }
        });
    });
    window.onSaveEditSeoD2FForm = function(el){
        d2fSeoFormInit($(el));
    };
    document.body.append(document.createComment('dev2fun.multidomain end'));
});


function d2fSeoForm() {
    BX.ajax({
        url : '/bitrix/admin/dev2fun_subdomain_seo_form.php',
        method : 'post',
        data : {
            m_seo_host : location.host,
            m_seo_path : location.pathname
        },
        dataType: 'html',
        processData: true,
        async: true,
        start: true,
        cache: false,
        onsuccess: function(data){
            $.magnificPopup.open({
                items: {
                    src: data,
                    type: 'inline'
                }
            });
        },
        onfailure: function(){
            alert('error!');
        }
    });
}

function d2fSeoFormInit(form) {
    BX.ajax({
        url : '/bitrix/admin/dev2fun_subdomain_seo_form.php',
        method : 'post',
        data : form.serialize(),
        dataType: 'json',
        processData: true,
        async: true,
        start: true,
        cache: false,
        onsuccess: function(data){
            $.magnificPopup.open({
                items: {
                    src: data.content,
                    type: 'inline'
                }
            });
        },
        onfailure: function(){
            alert('error!');
        }
    });
}