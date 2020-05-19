require 'rubygems'
require 'bundler/setup'

gem "susy"

require 'susy'
require 'breakpoint'
require 'rgbapng'
require 'bootstrap-sass'


# Require any additional compass plugins here.


# Set this to the root of your project when deployed:
http_path = "/"
sass_dir = "sass"

css_dir = "css"
http_stylesheets_path="/Mwerkzeug/css"

images_dir = "images"
http_images_path = "/Mwerkzeug/images"

javascripts_dir = "javascript"
http_javascripts_path = "/Mwerkzeug/javascript"

fonts_dir = "/public"



# You can select your preferred output style here (can be overridden via the command line):
# output_style = :expanded or :nested or :compact or :compressed

# To enable relative paths to assets via compass helper functions. Uncomment:
# relative_assets = true

# To disable debugging comments that display the original location of your selectors. Uncomment:
# line_comments = false

# If you prefer the indented syntax, you might want to regenerate this
# project again passing --syntax sass, or you can uncomment this:
# preferred_syntax = :sass
# and then run:
# sass-convert -R --from scss --to sass sass scss && rm -rf sass && mv scss sass
