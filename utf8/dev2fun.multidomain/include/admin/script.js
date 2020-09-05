document.addEventListener('DOMContentLoaded', function() {
    if (typeof $ === "function") {
        $('.nav-tabs .nav-link').click(function (e) {
            e.preventDefault();
            var el = $(this);
            $('.nav-tabs .nav-link').removeClass('active');
            el.addClass('active');
            $('#Dev2funLangDomainId').val(el.attr('data-id'));
            var tabContent = $('.tab-content');
            tabContent.find('.tab-pane.active').removeClass('active');
            tabContent.find(el.attr('href')).addClass('active');
            return false;
        });
    } else {
        console.warn('jQuery is not found!');
    }
});