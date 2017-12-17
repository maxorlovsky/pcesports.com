const Article = {
    template: '#article-template',
    data: function () {
        return {
            loading: true,
            pageError: '',
            post: {}
        };
    },
    created: function() {
        var self = this;

        this.loading = true;

        axios.get(`${pce.apiUrl}/wp/wp-json/wp/v2/posts/?slug=${this.$route.params.post}`)
        .then(function (response) {
            if (response.data === []) {
                return false;
            }
            let post = response.data[0];

            let date = (new Date(post.date));
            post.date = date.toLocaleString('en-us', { month: "short" })+'<br />'+date.getDate();
            post.title = post.title.rendered;
            post.content = post.content.rendered;

            // Set custom made meta title
            document.title += ' - '+ post.title;

            // Set custom made meta description
            const cutDownDescription = post.excerpt.rendered.substring(0, 100);
            const metaDescription = `${post.title} | ${cutDownDescription}...`;
            document.querySelector('meta[name="description"]').setAttribute("content", metaDescription);

            self.post = post;
            self.loading = false;
        })
        .catch(function (error) {
            self.pageError = 'Blog post not found';
            self.loading = false;
        });
    }
};

// Routing
pce.routes.push({
    path: '/article/:post',
    component: Article,
    meta: {
        title: 'Article',
        template: 'article'
    }
});