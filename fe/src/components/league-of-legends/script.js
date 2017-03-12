const LolTournaments = {
    template: '#lol-template',
    data: function() {
        return {
            loading: true,
            games: {}
        };
    },
    created: function() {
        var self = this;

        axios.get('https://api.pcesports.com/wp/wp-json/pce-api/tournaments/?game=lol&limit=20')
        .then(function (response) {
            self.games = response.data;

            /*for (let i = 0; i < self.posts.length; ++i) {
                let date = (new Date(self.posts[i].date));
                self.posts[i].date = date.toLocaleString('en-us', { month: "short" })+'<br />'+date.getDate();
            }*/

            self.loading = false;
        });
    }
};