const Home = {
    template: '#home-template',
    data: function() {
        return {
            loading: true,
            posts: 0
        };
    },
    created: function() {
        var self = this;

        this.loading = true;

        axios.get('https://api.pcesports.com/wp/wp-json/wp/v2/posts/?per_page=3')
        .then(function (response) {
            self.posts = response.data;

            for (let i = 0; i < self.posts.length; ++i) {
                let date = (new Date(self.posts[i].date));
                self.posts[i].date = date.toLocaleString('en-us', { month: "short" })+'<br />'+date.getDate();
            }

            self.loading = false;
        });
    }
};