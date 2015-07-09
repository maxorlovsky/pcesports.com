module.exports = function(grunt) {
    grunt.initConfig({
        dirs: {
            css: 'web/static/css',
            sass: 'web/static/css/sass',
            js: 'web/static/js',
            images: 'web/static/images'
        },

        pkg: grunt.file.readJSON('package.json'),

        /*Converting scss into css*/
        sass: {
            dist: {
                files: {
                    '<%= dirs.css %>/combined-sass': '<%= dirs.css %>/combined-sass'
                }
            }
        },

        concat: {
            /* Gathering all scss files together for easier sass conversion */
            sass: {
                src: [
                    '<%= dirs.sass %>/*.scss',
                ],
                dest: '<%= dirs.css %>/combined-sass'
            },

            /* Gathering all css external files and project combined sass file together */
            css: {
                src: [
                    '<%= dirs.css %>/slider.css',
                    '<%= dirs.css %>/highslide.css',
                    '<%= dirs.css %>/combined-sass'
                ],
                dest: '<%= dirs.css %>/combined.css'
            },

            js : {
                src : [
                    '<%= dirs.js %>/pc.js',
                    '<%= dirs.js %>/main.js'
                ],
                dest : '<%= dirs.js %>/combined.js'
            }
        },

        cssmin: {
            css:{
                src: '<%= dirs.css %>/combined.css',
                dest: '<%= dirs.css %>/combined.css'
            }
        },

        uglify: {
            js:{
                files: {
                    '<%= dirs.js %>/combined.js': ['<%= dirs.js %>/combined.js']
                }
            }
        },
        
        jshint: {
            files: ['Gruntfile.js', '<%= dirs.js %>/pc.js', '<%= dirs.js %>/main.js'],
            options: {
                ignores: ['<%= dirs.js %>/combined.js'],
                globals: {
                    jQuery: true,
                    console: true,
                    module: true,
                    document: true
                }
            }
        },

        watch: {
            default: {
				files: ['Gruntfile.js', '<%= dirs.sass %>/*.scss'],
				tasks: ['concat:sass', 'sass', 'concat:css']
			}
        },
    });
    
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-sass');

    /* Main task*/
    grunt.registerTask('default', [
        'concat:sass',
        'sass',
        'jshint',
        'concat:css',
        'cssmin:css',
        'concat:js',
        'uglify:js'
    ]);

    /* Task used when developing */
    grunt.registerTask('dev', [
        'default',
        'watch'
    ]);

    grunt.registerTask('imagemin', ['imagemin']);
};