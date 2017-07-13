let router = new VueRouter({
    mode: 'history',
    routes: [
        {
            path: '/',
            component: Home,
            meta: {
                template: 'home'
            },
        },
        {
            path: '/blog',
            component: Blog,
            meta: {
                title: 'Blog',
                template: 'blog'
            },
            children: [
                {
                    path: 'page/:page',
                    meta: {
                        title: 'Blog',
                        template: 'blog'
                    }
                }
            ]
        },
        {
            path: '/article/:post',
            component: Article,
            meta: {
                title: 'Article',
                template: 'article'
            }
        },
        {
            path: '/events',
            component: Events,
            meta: {
                title: 'Events List',
                template: 'events'
            },
            children: [
                {
                    path: ':game',
                    meta: {
                        title: 'Events List',
                        template: 'events'
                    }
                }
            ]
        },
        {
            path: '/events/:game/:event',
            component: EventDetails,
            meta: {
                title: 'Event Details',
                template: 'event-details'
            }
        },
        { path: '/404', component: PageNotFound, meta: { title: 'Page not found', template: '404' } },
        { path: '*', redirect: '/404' }
    ]
});

router.beforeEach((to, from, next) => {
    window.scrollTo(0, 0);
    
    document.title = 'PC eSports';
    if (to.meta.title) {
        document.title += ' - ' + to.meta.title;
    }
    
    // Loading html template for component
    let element = document.getElementById('template-holder');

    axios.get('/dist/html/' + to.meta.template + '.html')
    .then((template) => {
        element.innerHTML = template.data;

        next();
    });
});

const checkStorage = pce.storage('get', 'structure-data');

if (checkStorage) {
    dynamicTemplates.header.appendChild(document.createTextNode(checkStorage.templates.header));
    dynamicTemplates.footer.appendChild(document.createTextNode(checkStorage.templates.footer));
    dynamicTemplates.eventItem.appendChild(document.createTextNode(checkStorage.templates.eventItem));
    dynamicTemplates.ga.appendChild(document.createTextNode(checkStorage.templates.ga));
    dynamicTemplates.leftSideMenu.appendChild(document.createTextNode(checkStorage.templates.leftSideMenu));
    dynamicTemplates.rightSideMenu.appendChild(document.createTextNode(checkStorage.templates.rightSideMenu));
    dynamicTemplates.login.appendChild(document.createTextNode(checkStorage.templates.login));
    dynamicTemplates.seo.appendChild(document.createTextNode(checkStorage.templates.seo));
    dynamicTemplates.register.appendChild(document.createTextNode(checkStorage.templates.register));

    loadApp(checkStorage.menu);
}
else {
    axios.all([
        axios.get('/dist/html/header.html'),
        axios.get('/dist/html/footer.html'),
        axios.get('/dist/html/event-item.html'),
        //axios.get('/dist/html/events-filters.html'),
        axios.get('/dist/html/ga.html'),
        axios.get('/dist/html/left-side-menu.html'),
        axios.get('/dist/html/right-side-menu.html'),
        axios.get('/dist/html/login.html'),
        axios.get('/dist/html/seo.html'),
        axios.get('/dist/html/register.html'),
        axios.get('https://api.pcesports.com/wp/wp-json/pce-api/menu')
    ])
    .then(axios.spread((
        headerTemplate,
        footerTemplate,
        eventItemTemplate,
        gaTemplate,
        leftSideMenuTemplate,
        rightSideMenuTemplate,
        loginTemplate,
        seoTemplate,
        registerTemplate,
        menuData
    ) => {
        dynamicTemplates.header.appendChild(document.createTextNode(headerTemplate.data));
        dynamicTemplates.footer.appendChild(document.createTextNode(footerTemplate.data));
        dynamicTemplates.eventItem.appendChild(document.createTextNode(eventItemTemplate.data));
        //dynamicTemplates.eventsFilters.appendChild(document.createTextNode(eventsFiltersTemplate.data));
        dynamicTemplates.ga.appendChild(document.createTextNode(gaTemplate.data));
        dynamicTemplates.leftSideMenu.appendChild(document.createTextNode(leftSideMenuTemplate.data));
        dynamicTemplates.rightSideMenu.appendChild(document.createTextNode(rightSideMenuTemplate.data));
        dynamicTemplates.login.appendChild(document.createTextNode(loginTemplate.data));
        dynamicTemplates.seo.appendChild(document.createTextNode(seoTemplate.data));
        dynamicTemplates.register.appendChild(document.createTextNode(registerTemplate.data));

        let returnMenu = {};
        if (menuData.data) {
            returnMenu = pce.prepareMenu(menuData.data);
        }

        let store = {
            templates: {
                header: headerTemplate.data,
                footer: footerTemplate.data,
                eventItem: eventItemTemplate.data,
                ga: gaTemplate.data,
                leftSideMenu: leftSideMenuTemplate.data,
                rightSideMenu: rightSideMenuTemplate.data,
                login: loginTemplate.data,
                seo: seoTemplate.data,
                register: registerTemplate.data
            },
            menu: returnMenu
        };

        pce.storage('set', 'structure-data', store);

        loadApp(returnMenu);
    }))
    .catch((error) => {
        console.log('Error fetching main resources: ' + error);
    });
}

function loadApp(menu) {
    new Vue({
        el: '#app',
        router: router,
        data: {
            menu: menu,
            userMenu: {},
            leftSideMenu: false,
            rightSideMenu: false,
            rightSideMenuForm: '',
            loggedIn: pce.checkUserAuth(),
            userData: {}
        },
        mounted() {
            let self = this;
            
            // If back button is clicked and menu is open, we need to close menu first
            window.addEventListener("hashchange", this.checkMenu);

            delete Hammer.defaults.cssProps.userSelect;

            Hammer(document.getElementById('app'))
            .on('swiperight', function(ev) {
                if (ev.pointerType !== 'touch') {
                    return false;
                }
                
                if (self.leftSideMenu === false) {
                    self.burgerMenu();
                }
            })
            .on('swipeleft', function(ev) {
                if (ev.pointerType !== 'touch') {
                    return false;
                }

                if (self.leftSideMenu === true) {
                    self.burgerMenu();
                }
            });

            if (this.loggedIn) {
                this.fetchLoggedInData();
            }

            //setTimeout(() => {
            //    console.log('should run at the end');
            //    window.prerenderReady = true;
            //}, 3000);
        },
        methods: {
            burgerMenu: function() {
                if (this.leftSideMenu) {
                    this.leftSideMenu = false;
                    document.querySelector('body').className = document.querySelector('body').className.replace('open left', '').trim();
                } else {
                    this.leftSideMenu = true;
                    window.location.hash = '#side-menu-open';
                    document.querySelector('body').className = document.querySelector('body').className + ' open left'.trim();
                }
            },
            openRightMenu: function(test) {
                //console.log(test);
                if (this.rightSideMenu) {
                    this.rightSideMenu = false;
                    document.querySelector('body').className = document.querySelector('body').className.replace('open right', '').trim();
                } else {
                    this.rightSideMenu = true;
                    window.location.hash = '#right-side-menu-open';
                    document.querySelector('body').className = document.querySelector('body').className + ' open right'.trim();
                }
            },
            checkMenu: function(e) {
                if (this.leftSideMenu === true && e.oldURL.indexOf('#side-menu-open') !== -1) {
                    this.burgerMenu();
                }
            },
            login: function() {
                this.loggedIn = pce.checkUserAuth();
                this.fetchLoggedInData();
            },
            logout: function() {
                pce.storage('remove', 'token');
                pce.storage('remove', 'structure-user-data');
                delete(axios.defaults.headers.common.sessionToken);
                pce.loggedIn = false;
                this.loggedIn = false;
            },
            fetchLoggedInData: function() {
                const checkStorage = pce.storage('get', 'structure-user-data');
                let self = this;

                if (checkStorage) {
                    //dynamicTemplates.header.appendChild(document.createTextNode(checkStorage.templates.header));
                    this.userMenu = checkStorage.menu;
                    this.userData = checkStorage.userProfileData;
                } else {
                    axios.all([
                        axios.get('https://api.pcesports.com/wp/wp-json/pce-api/user-menu'),
                        axios.get('http://dev.api.pcesports.com/user-data')
                    ])
                    .then(axios.spread((
                        userMenuData,
                        profileData
                    ) => {
                        let returnMenu = {};

                        if (userMenuData.data) {
                            returnMenu = pce.prepareMenu(userMenuData.data);
                        }

                        let store = {
                            templates: {
                                //header: response[0],
                            },
                            menu: returnMenu,
                            userProfileData: profileData.data
                        };

                        pce.storage('set', 'structure-user-data', store);

                        this.userMenu = returnMenu;
                        this.userData = profileData.data;
                    }))
                    .catch((error) => {
                        // If catched error, that means that user is probably not authorized.
                        // Trigger logout
                        self.logout();
                        alert('logout');
                        console.log('Error fetching user resources: ' + error);
                    });
                }
            }
        }
    });

    document.getElementById('pre-loading').remove();
}