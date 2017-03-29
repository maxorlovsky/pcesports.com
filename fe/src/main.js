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

    loadApp(returnMenu);
}));

function loadApp(menu) {
    new Vue({
        el: '#app',
        router: router,
        data: {
            menu: menu,
            sideMenu: false
        },
        methods: {
            burgerMenu: function() {
                if (this.sideMenu) {
                    this.sideMenu = false;
                    document.querySelector('body').className = document.querySelector('body').className.replace('open', '').trim();
                } else {
                    this.sideMenu = true;
                    document.querySelector('body').className = document.querySelector('body').className + ' open'.trim();
                }
            }
        }
    });

    document.getElementById('pre-loading').remove();
}