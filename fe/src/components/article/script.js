const Article = {
    template: '#article-template',
    data: function () {
        return {
            loading: true,
            post: {}
        };
    },
    created: function() {
        var self = this;

        this.loading = true;

        axios.get('https://api.pcesports.com/wp/wp-json/wp/v2/posts/?slug='+this.$route.params.post)
        .then(function (response) {
            let post = response.data[0];

            let date = (new Date(post.date));
            post.date = date.toLocaleString('en-us', { month: "short" })+'<br />'+date.getDate();
            post.title = post.title.rendered;
            post.content = post.content.rendered;

            document.title = post.title + ' | Blog | PC eSports';

            self.post = post;
            self.loading = false;
        });
    }
};