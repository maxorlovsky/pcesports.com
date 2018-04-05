// 3rd party libs
import axios from 'axios';

const functions = {
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
                time: (new Date().getTime() + timeoutSeconds),
                version: pce.version
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

            if (
                // If older than 30 min
                (returnValue.time <= new Date().getTime()) ||
                // Or if version is now different, ignoring session token
                (returnValue.version !== pce.version && key !== 'token')
               ) {
                // Cleanup
                functions.storage('remove', key);
                return false;
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
        const storagesKeys = ['structure-data', 'blogs-posts', 'structure-user-data'];

        // If version was bumped, we might still use outdated localStorage data, doing full cleanup
        if (localStorage.getItem('version') !== pce.version) {
            for (let value of storagesKeys) {
                localStorage.removeItem(value);
            }
            // Saving version to not cleaup everything again until the next bump
            localStorage.setItem('version', pce.version);
        }

        return true;
    },
    getGameData: (name) => {
        const game = {};

        switch(name) {
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
        }

        return game;
    },
    checkUserAuth: () => {
        const token = functions.storage('get', 'token');

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
    },
    setUpCustomMeta: (title, description) => {
        // Set document meta title
        document.title = (title + ' ' + document.title).replace(/<\/?[^>]+(>|$)/g, '');
        document.querySelector('meta[property="og:title"]').content = document.title;

        // Set document meta description
        document.querySelector('meta[name="description"]').content = description.replace(/<\/?[^>]+(>|$)/g, '');
        document.querySelector('meta[property="og:description"]').content = document.querySelector('meta[name="description"]').content;
    }
};

export { functions };