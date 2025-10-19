// Configura CSRF token do Laravel para requisições jQuery.ajax
(function(){
    try{
        var tokenMeta = document.querySelector('meta[name="csrf-token"]');
        var token = tokenMeta ? tokenMeta.getAttribute('content') : null;
        if (token && window.jQuery) {
            window.jQuery.ajaxSetup({ headers: { 'X-CSRF-TOKEN': token } });
        }
    }catch(e){
        // ignore
    }
})();
