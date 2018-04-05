// Globals functions
import { functions } from './functions.js';

// VUE
import Vue from 'vue';
import VueRouter from 'vue-router';
import VueAnalytics from 'vue-analytics';

// 3rd party libs
import axios from 'axios';
import Hammer from 'hammerjs';

// Components
import headerComponent from './components/header/header.vue';
import footerComponent from './components/footer/footer.vue';
import leftSideMenuComponent from './components/left-side-menu/left-side-menu.vue';
import rightSideMenuComponent from './components/right-side-menu/right-side-menu.vue';
import floatMessage from './components/float-message/float-message.vue';

// Pages
/* jshint ignore:start */
import articlePage from './pages/article/article.vue';
import blogPage from './pages/blog/blog.vue';
import changePasswordPage from './pages/change-password/change-password.vue';
import eventAddPage from './pages/event-add/event-add.vue';
import eventDetailsPage from './pages/event-details/event-details.vue';
import eventsPage from './pages/events/events.vue';
import homePage from './pages/home/home.vue';
import pageNotFound from './pages/404/404.vue';
import userPage from './pages/user/user.vue';
import unsubscribePage from './pages/unsubscribe/unsubscribe.vue';
import tournamentsPage from './pages/tournaments/tournaments.vue';
import settingsPage from './pages/settings/settings.vue';
import registrationApprovalPage from './pages/registration-approval/registration-approval.vue';
import profilePage from './pages/profile/profile.vue';
/* jshint ignore:end */

functions.storageCacheBuster();

if (location.host.indexOf('dev') === 0) {
    pce.env = 'dev';
}

// Add <any> URLs to router, to push to /404
pce.routes.push({
    path: '*',
    redirect: '/404'
});

// Initiate the router
const router = new VueRouter({
    mode: 'history',
    routes: pce.routes
});

Vue.use(VueRouter);

// If it's not prerender, enabling google analytics
if (window.navigator.userAgent.toLowerCase().indexOf('prerender') === -1) {
    Vue.use(VueAnalytics, {
        id: 'UA-47216717-1',
        router
    });
}

router.beforeEach((to, from, next) => {
    window.scrollTo(0, 0);
    
    if (to.meta.loggedIn && !pce.loggedIn) {
        console.log('Authentication failure');
        next('/');
        return false;
    }

    // Set up meta title
    document.title = '';

    let metaTitle = ' | PC Esports';
    if (to.meta.title) {
        metaTitle = `${to.meta.title} ${metaTitle}`;
    }

    // Set up meta description
    // Default description used on home page
    let metaDescription = '';
    // If there is meta description in a router, we update it
    if (to.meta.description) {
        metaDescription = to.meta.description;
    }

    functions.setUpCustomMeta(metaTitle, metaDescription);
    
    next();
});

const checkStorage = functions.storage('get', 'structure-data');

if (checkStorage) {
    loadApp(checkStorage);
}
else {
    axios.all([
        axios.get(`${pce.wpApiUrl}/pce-api/menu`)
    ])
    .then(axios.spread((
        menuData
    ) => {
        let returnMenu = {};

        if (menuData.data) {
            returnMenu = functions.prepareMenu(menuData.data);
        }

        let store = {
            menu: returnMenu
        };

        functions.storage('set', 'structure-data', store);

        pce.app = loadApp(store);
    }))
    .catch((error) => {
        console.log('Error fetching main resources: ' + error);
    });
}

function loadApp(storage) {
    new Vue({
        el: '#app',
        router: router,
        components: {
            headerComponent,
            footerComponent,
            leftSideMenuComponent,
            rightSideMenuComponent,
            floatMessage
        },
        data: {
            menu: storage.menu,
            userMenu: {},
            leftSideMenu: false,
            rightSideMenu: false,
            rightSideMenuForm: '',
            loggedIn: functions.checkUserAuth(),
            userData: {},
            floatingMessage: {}
        },
        mounted() {
            // If back button is clicked and menu is open, we need to close menu first
            window.addEventListener("hashchange", this.checkMenu);

            delete Hammer.defaults.cssProps.userSelect;

            Hammer(document.getElementById('app'))
            .on('swiperight', (ev) => {
                if (ev.pointerType !== 'touch') {
                    return false;
                }
                
                if (this.leftSideMenu === false) {
                    this.burgerMenu();
                }
            })
            .on('swipeleft', (ev) => {
                if (ev.pointerType !== 'touch') {
                    return false;
                }

                if (this.leftSideMenu === true) {
                    this.burgerMenu();
                }
            });

            document.getElementById('pre-loading').remove();

            if (this.loggedIn) {
                this.fetchLoggedInData();
            }
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
            openRightMenu: function() {
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
                this.loggedIn = functions.checkUserAuth();
                this.fetchLoggedInData();
            },
            logout: function() {
                functions.storage('remove', 'token');
                functions.storage('remove', 'structure-user-data');
                delete(axios.defaults.headers.common.sessionToken);
                pce.loggedIn = false;
                this.loggedIn = false;
                this.$router.push('/');
            },
            fetchLoggedInData: function() {
                const checkStorage = functions.storage('get', 'structure-user-data');
                if (checkStorage) {
                    //dynamicTemplates.header.appendChild(document.createTextNode(checkStorage.templates.header));
                    this.userMenu = checkStorage.menu;
                    this.userData = checkStorage.userProfileData;
                } else {
                    axios.all([
                        axios.get(`${pce.wpApiUrl}/pce-api/user-menu`),
                        axios.get(`${pce.apiUrl}/user-data`)
                    ])
                    .then(axios.spread((
                        userMenuData,
                        profileData
                    ) => {
                        let returnMenu = {};

                        if (userMenuData.data) {
                            returnMenu = functions.prepareMenu(userMenuData.data);
                        }

                        let store = {
                            templates: {
                                //header: response[0],
                            },
                            menu: returnMenu,
                            userProfileData: profileData.data
                        };

                        functions.storage('set', 'structure-user-data', store);

                        this.userMenu = returnMenu;
                        this.userData = profileData.data;
                    }))
                    .catch((error) => {
                        // If catched error, that means that user is probably not authorized.
                        // Trigger logout
                        if (pce.env !== 'dev') {
                            //this.logout();
                        }
                        this.displayMessage('Error, during the process of updating user data, please repeat the process or re-login', 'danger');
                        console.log('Error fetching user resources: ' + error);
                    });
                }
            },
            // When updating user data in profile forms, we need to recache user data
            recacheLoggedInData: function() {
                axios.get(`${pce.apiUrl}/user-data`)
                .then((profileData) => {
                    const store = functions.storage('get', 'structure-user-data');
                    store.userProfileData = profileData.data;
                    functions.storage('set', 'structure-user-data', store);

                    this.userData = profileData.data;
                })
                .catch((error) => {
                    // If catched error, that means that user is probably not authorized.
                    // Trigger logout
                    if (pce.env !== 'dev') {
                        //this.logout();
                    }
                    this.displayMessage('Error, during the process of updating user data, please repeat the process or re-login (2)', 'danger');
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
}