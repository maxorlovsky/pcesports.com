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

        axios.get('https://api.pcesports.com/wp/wp-json/wp/v2/posts/?slug='+this.$route.params.post)
        .then(function (response) {
            if (response.data === []) {
                return false;
            }
            let post = response.data[0];

            let date = (new Date(post.date));
            post.date = date.toLocaleString('en-us', { month: "short" })+'<br />'+date.getDate();
            post.title = post.title.rendered;
            post.content = post.content.rendered;

            document.title = post.title + ' | Blog | PC eSports';

            self.post = post;
            self.loading = false;
        })
        .catch(function (error) {
            self.pageError = 'Blog post not found';
            self.loading = false;
        });
    }
};