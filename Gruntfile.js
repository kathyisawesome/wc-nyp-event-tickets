module.exports = function(grunt) {

  require('load-grunt-tasks')(grunt);
	
  // Project configuration.
  grunt.initConfig({
	pkg: grunt.file.readJSON('package.json'),

	// compile 
	sass: {                              // Task
		dist: {                          // Target
			options: {                   // Target options
				style: 'expanded',
				sourcemap: 'none',
			},
			files: [{
		        expand: true,
		        cwd: 'assets/scss',
		        src: ['*.scss'],
		    	dest: 'assets/css',
		        ext: '.css'
		      }]
		}
	},

	uglify: {
		options: {
			compress: {
				global_defs: {
					"EO_SCRIPT_DEBUG": false
				},
				dead_code: true
				},
			banner: '/*! <%= pkg.title %> <%= pkg.version %> <%= grunt.template.today("yyyy-mm-dd HH:MM") %> */\n'
		},
		build: {
			files: [{
				expand: true,	// Enable dynamic expansion.
				src: ['assets/js/*.js', '!assets/js/*.min.js'], // Actual pattern(s) to match.
				ext: '.min.js',   // Dest filepaths will have this extension.
			}]
		}
	},
	jshint: {
		options: {
			esversion: 6,
			reporter: require('jshint-stylish'),
			globals: {
				"EO_SCRIPT_DEBUG": false,
			},
			 '-W099': true, //Mixed spaces and tabs
			 '-W083': true,//TODO Fix functions within loop
			 '-W082': true, //Todo Function declarations should not be placed in blocks
			 '-W020': true, //Read only - error when assigning EO_SCRIPT_DEBUG a value.
		},
		all: [ 'assets/js/*.js', '!assets/js/*.min.js' ]
  	},

	watch: {
		scripts: {
			files: 'assets/js/*.js',
			tasks: ['jshint', 'uglify'],
			options: {
				debounceDelay: 250,
			},
		},
		css: {
			files: 'assets/css/*.scss',
			tasks: ['sass'],
		},
	},

	// # docs
	wp_readme_to_markdown: {
		convert:{
			files: {
				'readme.md': 'readme.txt'
			},
		},
	},

	// # Internationalization 

	// Add text domain
	addtextdomain: {
		options: {
            textdomain: '<%= pkg.name %>',    // Project text domain.
            updateDomains: [ '<%= pkg.name %>' ]  // List of text domains to replace.
        },
		target: {
			files: {
				src: ['*.php', '**/*.php', '**/**/*.php', '!node_modules/**', '!deploy/**']
			}
		}
	},

	// Generate .pot file
	makepot: {
		target: {
			options: {
				domainPath: '/languages', // Where to save the POT file.
				exclude: ['deploy'], // List of files or directories to ignore.
				mainFile: '<%= pkg.name %>.php', // Main project file.
				potFilename: '<%= pkg.name %>.pot', // Name of the POT file.
				type: 'wp-plugin' // Type of project (wp-plugin or wp-theme).
			}
		}
	},

	// bump version numbers
	replace: {
		Version: {
			src: [
				'readme.txt',
				'readme.md',
				'<%= pkg.name %>.php'
			],
			overwrite: true,
			replacements: [
				{
					from: /Stable tag:.*$/m,
					to: "Stable tag: <%= pkg.version %>"
				},
				{
					from: /\*\*Stable tag:.*$/m,
					to: "**Stable tag:** <%= pkg.version %>"
				},
				{ 
					from: /Version:.*$/m,
					to: "Version: <%= pkg.version %>"
				},
				{ 
					from: /const VERSION = \'.*.'/m,
					to: "const VERSION = '<%= pkg.version %>'"
				},

			]
		}
	},
	clean: {
	  main: {
	    src: ["build"]
	  }
	},
	copy: {
		main: {
	    	src:  [
				'**',
				'!node_modules/**',
				'!yeoman-template/**',
				'!.sass-cache/**',
				'!build/**',
				'!.git/**',
				'!vendor/**',
				'!deploy/**',
				'!*~',
				'!.gitignore',
				'!*.sublime-workspace',
				'!*.sublime-project',
				'!package.json',
				'!package-lock.json',
				'!composer.json',
				'!composer.lock',
				'!Gruntfile.js',
				'!readme.md',
				'!**/*.bak'				
			],
	    	dest: 'build/',
		},
	},

	// Make a zipfile.
	compress: {
		main: {
			options: {
				mode: 'zip',
				archive: 'deploy/<%= pkg.version %>/<%= pkg.name %>.zip'
			},
			expand: true,
			cwd: 'build/',
			src: ['**/*'],
			dest: '/<%= pkg.name %>'
		}
	},

});

grunt.registerTask( 'docs', [ 'wp_readme_to_markdown'] );

grunt.registerTask( 'default', [ 'jshint' ] );

grunt.registerTask( 'zip', [ 'copy', 'compress' ] );

grunt.registerTask( 'build:dev', [ 'replace', 'jshint', 'newer:sass', 'newer:uglify' ] );
grunt.registerTask( 'build', [ 'replace', 'jshint', 'newer:sass', 'newer:uglify', 'addtextdomain', 'makepot' ] );

grunt.registerTask( 'release', [ 'build', 'makepot', 'zip', 'clean' ] );

};
