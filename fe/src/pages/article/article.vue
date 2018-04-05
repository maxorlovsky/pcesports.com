<template>
<div class="article">
    <div class="block">
        <loading v-if="loading"></loading>

        <div v-if="!loading && !pageError" class="block-content blog-block semi-widths">
            <h2>{{post.title}}</h2>

            <div class="date" v-html="post.date"></div>
            <div class="image-holder" v-html="post.image"></div>
            <div class="text" v-html="post.content"></div>

            <router-link role="button"
                class="btn btn-info"
                v-bind:to="'/blog'">Back to blog</router-link>

            <div class="comments">
                <vue-disqus shortname="https-www-pcesports-com" url="https://www.pcesports.com"></vue-disqus>
            </div>

            <router-link role="button"
                class="btn btn-info"
                v-bind:to="'/blog'">Back to blog</router-link>
        </div>

        <div class="maintenance block-content" v-if="pageError">
            <h2>{{pageError}}</h2>
            <img src="/dist/assets/images/maintenance.png" />
        </div>
    </div>
</div>
</template>

<script>
// 3rd party libs
import axios from 'axios';
import vueDisqus from 'vue-disqus';

// Global functions
import { functions } from '../../functions.js';

// Components
import loading from '../../components/loading/loading.vue';

const articlePage = {
    components: {
        loading,
        vueDisqus
    },
    data: function () {
        return {
            loading: true,
            pageError: '',
            post: {}
        };
    },
    created: function() {
        this.loading = true;

        axios.get(`${pce.wpApiUrl}/wp/v2/posts/?slug=${this.$route.params.post}`)
        .then((response) => {
            if (response.data === []) {
                return false;
            }
            let post = response.data[0];

            let date = (new Date(post.date));
            post.date = date.toLocaleString('en-us', { month: "short" })+'<br />'+date.getDate();
            post.title = post.title.rendered;
            post.content = post.content.rendered;

            // Set custom made meta description
            const cutDownDescription = post.excerpt.rendered.substring(0, 100);
            const metaDescription = `${post.title} | ${cutDownDescription}...`;

            functions.setUpCustomMeta(post.title, metaDescription);

            this.post = post;
            this.loading = false;
        })
        .catch((error) => {
            this.pageError = 'Blog post not found';
            this.loading = false;
        });
    }
};

// Routing
pce.routes.push({
    path: '/article/:post',
    component: articlePage,
    meta: {
        title: '',
        description: ''
    }
});

export default articlePage;
</script>