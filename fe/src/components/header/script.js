Vue.component('header-component', {
    template: dynamicTemplates.header,
    data: function() {
        return {
            mood: '',
            logoSmall: false,
            menu: {}
        };
    },
    created: function() {
        window.addEventListener('scroll', this.handleScroll);

        return this.fetchData();
    },
    destroyed: function() {
        window.removeEventListener('scroll', this.handleScroll);
    },
    methods: {
        fetchData: function() {
            let self = this;

            const month = new Date().getMonth();
            if (month == 11 || month < 1) {
                this.mood = 'winter';
            }
            
            axios.get('https://api.pcesports.com/wp/wp-json/pce-api/menu')
            .then((links) => {
                let returnMenu = {};

                for (let value of links.data) {
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
                
                self.menu = returnMenu;
            });
        },
        handleScroll: function() {
            if (window.scrollY !== 0) {
                this.logoSmall = true;
            }
            else {
                this.logoSmall = false;
            }
        }
    }
});