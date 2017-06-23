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
    .then(function(template) {
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
    dynamicTemplates.sideMenu.appendChild(document.createTextNode(checkStorage.templates.sideMenu));

    loadApp(checkStorage.menu);
}
else {
    axios.all([
        axios.get('/dist/html/header.html'),
        axios.get('/dist/html/footer.html'),
        axios.get('/dist/html/event-item.html'),
        //axios.get('/dist/html/events-filters.html'),
        axios.get('/dist/html/ga.html'),
        axios.get('/dist/html/side-menu.html'),
        axios.get('https://api.pcesports.com/wp/wp-json/pce-api/menu')
    ])
    .then(axios.spread(function (headerTemplate, footerTemplate, eventItemTemplate, gaTemplate, sideMenuTemplate, menuData) {
        dynamicTemplates.header.appendChild(document.createTextNode(headerTemplate.data));
        dynamicTemplates.footer.appendChild(document.createTextNode(footerTemplate.data));
        dynamicTemplates.eventItem.appendChild(document.createTextNode(eventItemTemplate.data));
        //dynamicTemplates.eventsFilters.appendChild(document.createTextNode(eventsFiltersTemplate.data));
        dynamicTemplates.ga.appendChild(document.createTextNode(gaTemplate.data));
        dynamicTemplates.sideMenu.appendChild(document.createTextNode(sideMenuTemplate.data));

        let returnMenu = {};
        if (menuData.data) {
            for (let value of menuData.data) {
                if (value.menu_item_parent == '0') {
                    returnMenu['link-' + value.ID] = {
                        'title': value.title,
                        'url': (value.url?value.url:''),
                        'css_classes': value.classes.join(' '),
                        'target': (value.target?value.target:''),
                        'slug': value.post_name,
                        'sublinks': {}
                    };
                }
                else {
                    returnMenu['link-' + value.menu_item_parent].sublinks['sublink-' + value.ID] = {
                        'title': value.title,
                        'url': value.url.replace('http://', ''),
                        'css_classes': value.classes.join(' '),
                        'target': value.target,
                        'slug': value.post_name,
                    };
                }
            }
        }

        let store = {
            templates: {
                header: headerTemplate.data,
                footer: footerTemplate.data,
                eventItem: eventItemTemplate.data,
                ga: gaTemplate.data,
                sideMenu: sideMenuTemplate.data
            },
            menu: returnMenu
        };

        pce.storage('set', 'structure-data', store);

        loadApp(returnMenu);
    }));
}

function loadApp(menu) {
    new Vue({
        el: '#app',
        router: router,
        data: {
            menu: menu,
            sideMenu: false
        },
        mounted() {
            let self = this;
            // If back button is clicked and menu is open, we need to close menu first
            window.addEventListener("hashchange", this.checkMenu);

            Hammer(document.getElementById('app'))
            .on('swiperight', function(ev) {
                if (ev.pointerType !== 'touch') {
                    return false;
                }
                
                if (self.sideMenu === false) {
                    self.burgerMenu();
                }
            })
            .on('swipeleft', function(ev) {
                if (ev.pointerType !== 'touch') {
                    return false;
                }

                if (self.sideMenu === true) {
                    self.burgerMenu();
                }
            });
        },
        methods: {
            burgerMenu: function() {
                if (this.sideMenu) {
                    this.sideMenu = false;
                    document.querySelector('body').className = document.querySelector('body').className.replace('open', '').trim();
                } else {
                    this.sideMenu = true;
                    window.location.hash = '#side-menu-open';
                    document.querySelector('body').className = document.querySelector('body').className + ' open'.trim();
                }
            },
            checkMenu: function(e) {
                if (this.sideMenu === true && e.oldURL.indexOf('#side-menu-open') !== -1) {
                    this.burgerMenu();
                }
            }
        }
    });

    document.getElementById('pre-loading').remove();
}