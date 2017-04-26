Vue.component('adsense', {
    template: dynamicTemplates.ga,
    data: function() {
        return {
            canRunAds: canRunAds
        };
    },
    mounted() {
        (window.adsbygoogle = window.adsbygoogle || []).push({});
    }
});