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
            },
            {
                gameName: 'Dota 2',
                cssClass: 'game-dota',
                link: '../events/dota'
            },
            {
                gameName: 'Counter-Strike: GO',
                cssClass: 'game-cs',
                link: '../events/counter-strike'
            },
            {
                gameName: 'Smite',
                cssClass: 'game-smite',
                link: '../events/smite'
            }
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