/*global module:false*/
module.exports = function (grunt) {
  "use strict";

  // Project configuration.
  grunt.initConfig({
    pkg: '<json:package.json>',

// JSHINT //////////////////////////////////////////////////////////////////////
    jshint: {
      options: {
        curly:    true,
        eqeqeq:   true,
        immed:    true,
        indent:   2,
        latedef:  true,
        newcap:   true,
        noarg:    true,
        sub:      true,
        undef:    true
      },
      gruntfile: [ 'Gruntfile.js' ],
      project:   [ 'assets/js/*.js' ]
    },

// CLEAN ///////////////////////////////////////////////////////////////////////
    clean: {
      prod: [
        'release/'
      ]
    },

// UGLIFY //////////////////////////////////////////////////////////////////////
    uglify: {
      /*
      ios: {
        files: {
          'js/min/ios.min.js' : [
            'js/vendor/ios-orientationchange-fix/ios-orientationchange-fix.js',
            'js/ios-landscape-zoom-fix.js'
          ]
        }
      },
     */
    },

// SASS ////////////////////////////////////////////////////////////////////////
    sass: {
      prod: {
        options: {
          style: 'compressed'
        },
        files: {
          'assets/css/admin.css': 'assets/sass/admin.scss'
        }
      }
    },

// COPY ////////////////////////////////////////////////////////////////////////
    copy: {
      prod: {
        files: {
          'release/assets/': 'assets/**',
          'release/style.css': 'assets/css/style-prod.css'
        }
      }
    },

// WATCH ///////////////////////////////////////////////////////////////////////
    watch: {
      gruntfile: {
        files: 'Gruntfile.js',
        tasks: ['jshint:gruntfile'],
        options: {
          nocase: true
        }
      },
      scripts: {
        files: ['assets/js/*.js'],
        tasks: ['jshint:project']
      },
      sass: {
        files: ['assets/sass/**/*.*'],
        tasks: ['sass:prod']
      }
    }
  });

// LOAD TASKS //////////////////////////////////////////////////////////////////
  grunt.loadNpmTasks('grunt-contrib-clean');
  grunt.loadNpmTasks('grunt-contrib-copy');
  grunt.loadNpmTasks('grunt-contrib-jshint');
  grunt.loadNpmTasks('grunt-contrib-sass');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-watch');

// REGISTER TASKS //////////////////////////////////////////////////////////////
  grunt.registerTask('release', [
    'sass:prod',
    'clean:prod',
    'copy'
  ]);
  grunt.registerTask('test', ['jshint']);
  grunt.registerTask('compile', ['sass:prod']);
  grunt.registerTask('default', ['watch']);
};
