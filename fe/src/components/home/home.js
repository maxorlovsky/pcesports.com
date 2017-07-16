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
        const self = this;

        this.loading = true;

        const checkStorage = pce.storage('get', 'blogs-posts');
        
        if (checkStorage) {
            this.posts = checkStorage;
            this.loading = false;
        }
        else {
            fetch('https://api.pcesports.com/wp/wp-json/wp/v2/posts/?per_page=4',{
                method: 'GET',
                headers: new Headers(),
                mode: 'cors'
            })
            .then((response) => response.json())
            .then((response) => {
                for (let i = 0; i < response.length; ++i) {
                    let date = (new Date(response[i].date));
                    response[i].date = date.toLocaleString('en-us', { month: "short" })+'<br />'+date.getDate();
                }

                self.posts = response;

                pce.storage('set', 'blogs-posts', self.posts);

                self.loading = false;
            });
        }

        document.title = document.title + ' - Home';
    }
};