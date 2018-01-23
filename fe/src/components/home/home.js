const Home = {
    template: '#home-template',
    data: function() {
        let games = [];
        const gameOrder = ['lol', 'hs', 'ow', 'hots', 'rl', 'dota', 'cs', 'smite'];

        for (let game of gameOrder) {
            game = pce.getGameData(game);
            
            games.push({
                gameName: game.name,
                cssClass: 'game-' + game.abbriviature,
                link: '../events/' + game.link,
                abbriviature: game.abbriviature
            });
        }
        
        return {
            loading: true,
            posts: [],
            games: games
        };
    },
    created: function() {
        const self = this;

        this.loading = true;

        const checkStorage = pce.storage('get', 'home-data');
        if (checkStorage) {
            this.posts = checkStorage.posts;

            for(const game of this.games) {
                game.count = checkStorage.tournamentsCount[game.abbriviature];
            }

            this.loading = false;
        }
        else {
            axios.all([
                axios.get(`${pce.apiUrl}/wp/wp-json/wp/v2/posts/?per_page=4`),
                axios.get(`${pce.apiUrl}/tournaments/count`)
            ])
            .then(axios.spread((
                postsData,
                tournamentsCountData
            ) => {
                for (let i = 0; i < postsData.data.length; ++i) {
                    let date = (new Date(postsData.data[i].date));
                    postsData.data[i].date = date.toLocaleString('en-us', { month: "short" })+'<br />'+date.getDate();
                }

                self.posts = postsData.data;

                for(const game of self.games) {
                    game.count = tournamentsCountData.data[game.abbriviature];
                }

                const homeData = {
                    posts: self.posts,
                    tournamentsCount: tournamentsCountData.data
                };

                pce.storage('set', 'home-data', homeData);

                self.loading = false;
            }));
        }
    }
};

// Routing
pce.routes.push({
    path: '/',
    component: Home,
    meta: {
        title: 'Choose a game, League of Legends, Hearthstone, Dota2, CS:GO, Rocket League, Overwatch',
        template: 'home',
        description: 'PC Esports is a catalog of events for online competitive gaming.'
    },
});