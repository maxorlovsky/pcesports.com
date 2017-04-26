const Home = {
    template: '#home-template',
    data: function() {
        let games = [];
        const gameOrder = ['lol', 'hs', 'ow', 'hots', 'rl', 'dota', 'cs', 'smite'];

        for (game of gameOrder) {
            game = pce.getGameData(game);
            
            games.push({
                gameName: game.name,
                cssClass: 'game-' + game.abbriviature,
                link: '../events/' + game.link
            });
        }
        
        return {
            loading: true,
            posts: 0,
            games: games
        };
    },
    created: function() {
        var self = this;

        this.loading = true;

        const checkStorage = pce.storage('get', 'blogs-posts');
        
        if (checkStorage) {
            this.posts = checkStorage;
            this.loading = false;
        }
        else {
            axios.get('https://api.pcesports.com/wp/wp-json/wp/v2/posts/?per_page=4')
            .then(function (response) {
                self.posts = response.data;

                for (let i = 0; i < self.posts.length; ++i) {
                    let date = (new Date(self.posts[i].date));
                    self.posts[i].date = date.toLocaleString('en-us', { month: "short" })+'<br />'+date.getDate();
                }

                pce.storage('set', 'blogs-posts', self.posts);

                self.loading = false;
            });
        }

        document.title = document.title + ' - Home';
    }
};