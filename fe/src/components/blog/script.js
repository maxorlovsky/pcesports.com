const Blog = {
    template: '#blog-template',
    data: function () {
        if (!this.$route.params.page) {
            this.$route.params.page = 1;
        }

        return {
            loading: true,
            amount: 0,
            posts: {},
            page: this.$route.params.page,
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
            var self = this;
            
            axios.all([
                axios.get('https://api.pcesports.com/wp/wp-json/pce-api/post-count'),
                axios.get('https://api.pcesports.com/wp/wp-json/wp/v2/posts/?per_page=5&page='+this.$route.params.page)
            ])
            .then(axios.spread(function (amount, posts) {
                self.amount = amount.data.publish;

                posts = posts.data;

                for (let i = 0; i < posts.length; ++i) {
                    let date = (new Date(posts[i].date));
                    posts[i].date = date.toLocaleString('en-us', { month: "short" })+'<br />'+date.getDate();
                }
                
                self.posts = posts;
                self.loading = false;
            }));

            this.page = this.$route.params.page;
        }
    }
};