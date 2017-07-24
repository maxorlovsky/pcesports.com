const dynamicTemplates = {
    header: document.createElement('script'),
    footer: document.createElement('script'),
    eventItem: document.createElement('script'),
    ga: document.createElement('script'),
    leftSideMenu: document.createElement('script'),
    rightSideMenu: document.createElement('script'),
    login: document.createElement('script'),
    seo: document.createElement('script'),
    register: document.createElement('script'),
    forgotPassword: document.createElement('script')
};

const pce = {
    version: '%version%',
    canRunAds: false,
    loggedIn: false,
    loginCheckError: false,
    apiUrl: 'https://api.pcesports.com',
    env: '',
    // Local storage
    storage: (func, key, ...args) => {
        let timeoutSeconds = 1800000;

        if (args[1]) {
            timeoutSeconds = args[1];
        }

        // setItem
        if (func === 'set') {
            // If any parameter is empty, we don't do anything
            if (!func || !key || !args[0]) {
                console.log('false');
                return false;
            }

            let saveData = {
                data: args[0],
                time: (new Date().getTime() + timeoutSeconds)
            };

            localStorage.setItem(key, JSON.stringify(saveData));
        } else if (func === 'get') {
            // If it's a development environment, we ignoring localStorage cache
            if (location.href.indexOf('dev') !== -1 && key !== 'token') {
                return false;
            }

            // Check if there is something for specified key
            if (!localStorage.getItem(key)) {
                return false;
            }

            let returnValue = JSON.parse(localStorage.getItem(key));

            // If more than 30 min, cleanup
            if (returnValue.time <= new Date().getTime()) {
                pce.storage('remove', key);
            }

            return returnValue.data;
        } else if (func === 'remove') {
            localStorage.removeItem(key);
        } else {
            return false;
        }

        return true;
    },
    storageCacheBuster: () => {
        let storagesKeys = ['structure-data', 'blogs-posts'];

        // If version was bumped, we might still use outdated localStorage data, doing full cleanup
        if (localStorage.getItem('version') !== pce.version) {
            localStorage.clear();
            // Saving version to not cleaup everything again until the next bump
            localStorage.setItem('version', pce.version);
        }

        return true;
    },
    getGameData: (name) => {
        const game = {};

        switch(name) {
            case 'smite':
                game.abbriviature = 'smite';
                game.name = 'Smite';
                game.link = 'smite';
                game.regions = {
                    'all': 'All',
                    'na': 'North America',
                    'eu': 'Europe'
                };
            break;
            case 'dota':
                game.abbriviature = 'dota';
                game.name = 'Dota 2';
                game.link = 'dota';
                game.regions = {
                    'all': 'All',
                    'na': 'North America',
                    'euw': 'Europe West',
                    'eune': 'Europe East'
                };
            break;
            case 'cs':
            case 'counter-strike':
                game.abbriviature = 'cs';
                game.name = 'Counter Strike:GO';
                game.link = 'counter-strike';
                game.regions = {
                    'all': 'All',
                    'na': 'North America',
                    'eu': 'Europe'
                };
            break;
            case 'rl':
            case 'rocket-league':
                game.abbriviature = 'rl';
                game.name = 'Rocket League';
                game.link = 'rocket-league';
                game.regions = {
                    'all': 'All',
                    'na': 'North America',
                    'eu': 'Europe'
                };
            break;
            case 'hots':
            case 'heroes-of-the-storm':
                game.abbriviature = 'hots';
                game.name = 'Heroes of the Storm';
                game.link = 'heroes-of-the-storm';
                game.regions = {
                    'all': 'All',
                    'na': 'North America',
                    'eu': 'Europe'
                };
            break;
            case 'lol':
            case 'league-of-legends':
                game.abbriviature = 'lol';
                game.name = 'League of Legends';
                game.link = 'league-of-legends';
                game.regions = {
                    'all': 'All',
                    'na': 'North America',
                    'euw': 'Europe West',
                    'eune': 'Europe East'
                };
            break;
            case 'hs':
            case 'hearthstone':
                game.abbriviature = 'hs';
                game.name = 'Hearthstone';
                game.link = 'hearthstone';
                game.regions = {
                    'all': 'All',
                    'na': 'North America',
                    'eu': 'Europe'
                };
            break;
            case 'ow':
            case 'overwatch':
                game.abbriviature = 'ow';
                game.name = 'Overwatch';
                game.link = 'overwatch';
                game.regions = {
                    'all': 'All',
                    'na': 'North America',
                    'eu': 'Europe'
                };
            break;
            default:
                game.abbriviature = 'lol';
                game.name = 'League of Legends';
                game.link = 'league-of-legends';
                game.regions = {
                    'all': 'All',
                    'na': 'North America',
                    'euw': 'Europe West',
                    'eune': 'Europe East'
                };
            break;
        }

        return game;
    },
    checkUserAuth: () => {
        const token = pce.storage('get', 'token');

        if (token.sessionToken) {
            pce.loggedIn = true;
            axios.defaults.headers.common.sessionToken = token.sessionToken;
        }

        return pce.loggedIn;
    },
    prepareMenu: function(menu) {
        const returnMenu = {};

        for (let value of menu) {
            if (value.menu_item_parent === '0') {
                returnMenu['link-' + value.ID] = {
                    'title': value.title,
                    'url': (value.url?value.url:''),
                    'css_classes': value.classes.join(' '),
                    'target': (value.target?value.target:''),
                    'slug': value.post_name,
                    'sublinks': {}
                };
            } else {
                returnMenu['link-' + value.menu_item_parent].sublinks['sublink-' + value.ID] = {
                    'title': value.title,
                    'url': value.url.replace('http://', ''),
                    'css_classes': value.classes.join(' '),
                    'target': value.target,
                    'slug': value.post_name,
                };
            }
        }

        return returnMenu;
    }
};

pce.storageCacheBuster();

if (location.host.indexOf('dev') === 0) {
    //pce.apiUrl = 'http://dev.api.pcesports.com';
    pce.env = 'dev';
}