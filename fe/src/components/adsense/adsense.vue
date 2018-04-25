<template>
<div v-if="canRunAds"
    class="adsense"
>
    <!-- <ins class="adsbygoogle"
        data-ad-client="ca-pub-9080099154901572"
        data-ad-slot="1135872653"></ins>
    <div v-else class="alert alert-danger">
        We really hate ADs too. We would like to get rid of ADs completely. But unfortunatelly ADs the only way for website to be online. To help us grow and get rid of ADs, <a href="https://www.patreon.com/pcesports" target="_blank">consider becoming a patron.</a>
    </div> -->
</div>
</template>

<script>
export default {
    name: 'adsense',
    data: function() {
        return {
            canRunAds: pce.canRunAds
        };
    },
    mounted() {
        (window.adsbygoogle = window.adsbygoogle || []).push({
            google_ad_client: 'ca-pub-9080099154901572',
            enable_page_level_ads: true
        });
    }
}
</script>