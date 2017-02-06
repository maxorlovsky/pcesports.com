Vue.component('header-component', {
    template: '#header-template',
    data: function() {
        return {
            menu: {}
        };
    },
    created: function() {
        return this.fetchData();
    },
    methods: {
        fetchData: function() {
            let self = this;
            
            axios.get('https://api.pcesports.com/wp/wp-json/pce-api/menu')
            .then((links) => {
                let returnMenu = {};

                for (let value of links.data) {
                    if (value.menu_item_parent == 0) {
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

                console.log(returnMenu);
                console.log(returnMenu['link-129'].sublinks);
                
                self.menu = returnMenu;
            });
        }
    }
});