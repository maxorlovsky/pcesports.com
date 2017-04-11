const Home = {
    template: '#home-template',
    data: function() {
        const games = [
            {
                gameName: 'League of Legends',
                cssClass: 'game-lol',
                link: '../events/league-of-legends/'
            },
            {
                gameName: 'Hearthstone',
                cssClass: 'game-hs',
                link: '../events/hearthstone/'
            },
            {
                gameName: 'Overwatch',
                cssClass: 'game-ow',
                link: '../events/overwatch/'
            },
            {
                gameName: 'Heroes of the Storm',
                cssClass: 'game-hots',
                link: '../events/heroes-of-the-storm'
            },
            {
                gameName: 'Rocket League',
                cssClass: 'game-rl',
                link: '../events/rocket-league'
            }
            /*{
                gameName: 'Counter-Strike: GO',
                cssClass: 'game-csgo',
                tournamentsNumbers: 0
            },
            {
                gameName: 'Smite',
                cssClass: 'game-smite',
                tournamentsNumbers: 0
            }*/
        ];

        return {
            loading: true,
            posts: 0,
            games: games
        };
    },
    created: function() {
        var self = this;

        this.loading = true;

        axios.get('https://api.pcesports.com/wp/wp-json/wp/v2/posts/?per_page=4')
        .then(function (response) {
            self.posts = response.data;

            for (let i = 0; i < self.posts.length; ++i) {
                let date = (new Date(self.posts[i].date));
                self.posts[i].date = date.toLocaleString('en-us', { month: "short" })+'<br />'+date.getDate();
            }

            self.loading = false;
        });

        document.title = document.title + ' - Home';
    }
};