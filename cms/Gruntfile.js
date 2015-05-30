module.exports = function(grunt) {
    grunt.initConfig({
        dirs: {
            css: 'static/css',
            sass: 'static/css/sass',
            js: 'static/js'
        },

        pkg: grunt.file.readJSON('package.json'),

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
                    '<%= dirs.css %>/jqueryUI.css',
                    '<%= dirs.css %>/chosen.css',
                    '<%= dirs.css %>/fonts.css',
                    '<%= dirs.css %>/combined-sass'
                ],
                dest: '<%= dirs.css %>/combined.css'
            },

            js : {
                src : [
                    '<%= dirs.js %>/ajaxupload.js',
                    '<%= dirs.js %>/main.js'
                ],
                dest : '<%= dirs.js %>/combined.js'
            }
        },

        /* Minifiying all css for combined file */
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
            files: ['Gruntfile.js', '<%= dirs.js %>/main.js'],
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
            css: {
				files: [
                    'Gruntfile.js',
                    '<%= dirs.css %>/*.css',
                    '!<%= dirs.css %>/combined.css',
                    '<%= dirs.sass %>/*.scss'
                ],
				tasks: [
                    'concat:sass',
                    'sass',
                    'concat:css',
                    'cssmin:css'
                ]
			}
        }
    });
    
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-sass');
    
    grunt.registerTask('default', [
        'concat:sass',
        'sass',
        'jshint',
        'concat:css',
        'cssmin:css',
        'concat:js',
        'uglify:js'
    ]);
    
    grunt.registerTask('dev', [
        'default',
        'watch'
    ]);
};