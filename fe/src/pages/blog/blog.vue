<template>
<div class="blog">
    <div class="block">
        <loading v-if="loading"></loading>

        <div class="block-content blog-block semi-widths"
            :key="post.slug"
            v-for="post in posts"
        >
            <div class="date" v-html="post.date"></div>
            <div class="image-holder"
                v-html="post.image"></div>
            <h3 :href="'blog/'+post.slug" class="title">{{post.title.rendered}}</h3>
            <div class="text" v-html="post.excerpt.rendered"></div>

            <router-link role="button"
                class="btn btn-info"
                v-bind:to="'/article/'+post.slug">Read more</router-link>
        </div>

        <div class="pages semi-widths">
            <pagination :page="page"
                :amount="amount"
                :amount-per-page="5"
                page-url="blog"></pagination>
        </div>
    </div>
</div>
</template>

<script>
// 3rd party libs
import axios from 'axios';

// Components
import loading from '../../components/loading/loading.vue';
import pagination from '../../components/pagination/pagination.vue';

const blogPage = {
    components: {
        loading,
        pagination
    },
    data: function () {
        if (!this.$route.params.page) {
            this.$route.params.page = 1;
        }

        return {
            loading: true,
            amount: 0,
            posts: {},
            page: 0
        };
    },
    created: function() {
        return this.fetchData();
    },
    watch: {
        $route: 'fetchData'
    },
    methods: {
        fetchData: function() {
            if (this.$route.params.page) {
                this.page = parseInt(this.$route.params.page);
            }

            axios.all([
                axios.get(`${pce.wpApiUrl}/pce-api/post-count`),
                axios.get(`${pce.wpApiUrl}/wp/v2/posts/?per_page=5&page=${this.page}`)
            ])
            .then(axios.spread((amount, posts) => {
                this.amount = parseInt(amount.data.publish);

                posts = posts.data;

                for (let i = 0; i < posts.length; ++i) {
                    let date = (new Date(posts[i].date));
                    posts[i].date = date.toLocaleString('en-us', { month: "short" })+'<br />'+date.getDate();
                }
                
                this.posts = posts;
                this.loading = false;
            }));
        }
    }
};

// Routing
pce.routes.push({
    path: '/blog',
    component: blogPage,
    meta: {
        title: 'Blog',
        description: 'PC Esports blog, know about new features, development, thought on eSports and just news about the project from the blog'
    },
    children: [
        {
            path: 'page/:page',
            meta: {
                title: 'Blog',
                template: 'blog',
                description: 'PC Esports blog, know about new features, development, thought on eSports and just news about the project from the blog'
            }
        }
    ]
});

export default blogPage;
</script>