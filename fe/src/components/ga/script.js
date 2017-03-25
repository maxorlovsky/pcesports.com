Vue.component('adsense', {
    template: dynamicTemplates.ga,
    data: function() {
        return {
            
        };
    },
    mounted() {
        (window.adsbygoogle = window.adsbygoogle || []).push({})
    }
});