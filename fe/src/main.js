let router = new VueRouter({
    mode: 'history',
    routes: [
        {
            path: '/',
            component: Home,
            meta: {
                template: 'home',
                description: 'PC Esports is a catalog of events for online competitive gaming.'
            },
        },
        {
            path: '/blog',
            component: Blog,
            meta: {
                title: 'Blog',
                template: 'blog',
                description: 'PC Esports blog, know about new features, development, thought on eSports and just news about the project from the blog'
            },
            children: [
                {
                    path: 'page/:page',
                    meta: {
                        title: 'Blog',
                        template: 'blog',
                        description: 'PC Esports blog, know about new features, development, thought on eSports and just news about the project from the blog'
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
                template: 'events',
                description: 'Find all competitive gaming tournaments around North America and Europe. List include games like League of Legends, Hearthstone, Overwatch, Rocket League, Heroes of the Storm, Dota 2, Counter-Strike: Global Offensive, Smite, full list of what gamer might need'
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
        {
            path: '/registration/:code',
            component: RegistrationApproval,
            meta: {
                title: 'Registration Approval',
                template: 'registration-approval',
                description: 'Complete your registration process on PC Esports website'
            }
        },
        {
            path: '/profile',
            component: Profile,
            props: true,
            meta: {
                loggedIn: true,
                title: 'Profile',
                template: 'profile',
                description: 'User profile'
            }
        },
        {
            path: '/profile/change-password',
            component: ChangePassword,
            props: true,
            meta: {
                loggedIn: true,
                title: 'Change Password - Profile',
                template: 'change-password',
                description: 'User page to change password'
            }
        },
        {
            path: '/profile/settings',
            component: Settings,
            props: true,
            meta: {
                loggedIn: true,
                title: 'Settings - Profile',
                template: 'settings',
                description: 'User page to change password and personal settings'
            }
        },
        {
            path: '/user/:name',
            component: User,
            meta: {
                template: 'user',
            }
        },
        { path: '/404', component: PageNotFound, meta: { title: 'Page not found', template: '404' } },
        { path: '*', redirect: '/404' }
    ]
});

router.beforeEach((to, from, next) => {
    pce.loginCheckError = false;
    if (to.meta.loggedIn && !pce.loggedIn) {
        pce.loginCheckError = true;
    }
    window.scrollTo(0, 0);
    
    // Set up meta title
    document.title = 'PC Esports';
    if (to.meta.title) {
        document.title += ' - ' + to.meta.title;
    }

    // Set up meta description
    // Default description used on home page
    let metaDescription = 'PC Esports is a catalog of events for online competitive gaming.';
    // If there is meta description in a router, we update it
    if (to.meta.description) {
        metaDescription = to.meta.description;
    }
    document.querySelector('meta[name="description"]').setAttribute("content", metaDescription);
    
    // Loading html template for component
    let element = document.getElementById('template-holder');

    axios.get('/dist/html/' + to.meta.template + '.html')
    .then((template) => {
        element.innerHTML = template.data;

        next();
    });
});

router.afterEach((to, from) => {
    if (pce.loginCheckError) {
        // Displaying error message to the user
        router.app.authRequired();
        return false;
    }
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
    dynamicTemplates.forgotPassword.appendChild(document.createTextNode(checkStorage.templates.forgotPassword));

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
        axios.get('/dist/html/forgot-password.html'),
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
        forgotPasswordTemplate,
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
        dynamicTemplates.forgotPassword.appendChild(document.createTextNode(forgotPasswordTemplate.data));

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
                register: registerTemplate.data,
                forgotPassword: forgotPasswordTemplate.data
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
            userData: {},
            floatingMessage: {}
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
                this.$router.push('/');
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
                        axios.get(`${pce.apiUrl}/user-data`)
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
                        if (pce.env !== 'dev') {
                            self.logout();
                        }
                        self.displayMessage('Error, logging out', 'danger');
                        console.log('Error fetching user resources: ' + error);
                    });
                }
            },
            // When updating user data in profile forms, we need to recache user data
            recacheLoggedInData: function() {
                let self = this;

                axios.get(`${pce.apiUrl}/user-data`)
                .then((profileData) => {
                    const store = pce.storage('get', 'structure-user-data');
                    //store.userProfileData = profileData.data;
                    //pce.storage('set', 'structure-user-data', store);

                    self.userData = profileData.data;
                })
                .catch((error) => {
                    // If catched error, that means that user is probably not authorized.
                    // Trigger logout
                    if (pce.env !== 'dev') {
                        self.logout();
                    }
                    self.displayMessage('Error, logging out (2)', 'danger');
                    console.log('Error fetching user resources: ' + error);
                });
            },
            displayMessage: function(message, type) {
                if (!type) {
                    type = 'info';
                }

                this.floatingMessage = {
                    message: message,
                    type: type
                };
            },
            authRequired: function() {
                // Displaying error message to the user
                this.displayMessage('You must be logged in to enter this page', 'danger');

                // Redirect to home page
                this.$router.push('/');
            }
        }
    });

    document.getElementById('pre-loading').remove();
}