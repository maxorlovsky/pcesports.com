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

    fetch('/dist/html/' + to.meta.template + '.html')
    .then(res => res.text())
    .then((template) => {
        element.innerHTML = template;

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

    loadApp(checkStorage.menu);
}
else {
    const urls = [
        '/dist/html/header.html',
        '/dist/html/footer.html',
        '/dist/html/event-item.html',
        '/dist/html/ga.html',
        '/dist/html/left-side-menu.html',
        'https://api.pcesports.com/wp/wp-json/pce-api/menu',
        '/dist/html/right-side-menu.html',
    ];

    const grabContent = url => fetch(url)
    .then(res => res.text())
    .then(html => {
        return html;
    });

    Promise.all(urls.map(grabContent))
    .then((response) => {
        dynamicTemplates.header.appendChild(document.createTextNode(response[0]));
        dynamicTemplates.footer.appendChild(document.createTextNode(response[1]));
        dynamicTemplates.eventItem.appendChild(document.createTextNode(response[2]));
        dynamicTemplates.ga.appendChild(document.createTextNode(response[3]));
        dynamicTemplates.leftSideMenu.appendChild(document.createTextNode(response[4]));
        dynamicTemplates.rightSideMenu.appendChild(document.createTextNode(response[6]));

        const returnMenu = {};
        if (response[5]) {
            response[5] = JSON.parse(response[5]);
            for (let value of response[5]) {
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
        }

        let store = {
            templates: {
                header: response[0],
                footer: response[1],
                eventItem: response[2],
                ga: response[3],
                leftSideMenu: response[4],
                rightSideMenu: response[6]
            },
            menu: returnMenu
        };

        pce.storage('set', 'structure-data', store);

        loadApp(returnMenu);
    })
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
            leftSideMenu: false,
            rightSideMenu: false,
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
            }
        }
    });

    document.getElementById('pre-loading').remove();
}