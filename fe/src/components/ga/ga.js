Vue.component('adsense', {
    template: dynamicTemplates.ga,
    data: function() {
        return {
            canRunAds: pce.canRunAds
        };
    },
    mounted() {
        (window.adsbygoogle = window.adsbygoogle || []).push({});
    }
});