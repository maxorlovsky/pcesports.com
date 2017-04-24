const dynamicTemplates = {
    header: document.createElement('script'),
    footer: document.createElement('script'),
    eventItem: document.createElement('script'),
    //eventsFilters: document.createElement('script')
    ga: document.createElement('script'),
    sideMenu: document.createElement('script')
};

const pce = {
    version: '2.4.0',
    // Local storage
    storage: (func, key, json) => {
        // setItem
        if (func === 'set') {
            // If any parameter is empty, we don't do anything
            if (!func || !key || !json) {
                return false;
            }

            let saveData = {
                data: json,
                time: new Date().getTime()
            };

            localStorage.setItem(key, JSON.stringify(saveData));
        } else if (func === 'get') {
            // If it's a development environment, we ignoring localStorage cache
            if (location.href.indexOf('dev') !== -1) {
                return false;
            }

            // Check if there is something for specified key
            if (!localStorage.getItem(key)) {
                return false;
            }

            let returnValue = JSON.parse(localStorage.getItem(key));

            // If more than 30 min, cleanup
            if ((returnValue.time * 1800000) <= new Date().getTime()) {
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
    }
};

pce.storageCacheBuster();