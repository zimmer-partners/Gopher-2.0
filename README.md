# Markdown-Publisher

This PHP script searches two source subdirectories containing raw Markdown files and renders a match as a printable webpage. There's the directory Quellen with demo files and a non-existent Sources directory scanned for Markdown files. Any references to images and other embedded media files can be bundled in subfolders with the referring Markdown file; relative paths to the media files are resolved correctly.

## How to install

- Download or clone Markdown Publisher to your web server.
- Check' if your installation shows the sample files from Quellen.
- Delete the 'Quellen' directory with all its markdown files and images.
- Create a 'Sources' directory for hosting your own markdown files and images. (You can have a look at [Quellen](https://github.com/zimmer-partners/Markdown-Publisher/tree/master/Quellen) to check out how to bundle markdown files and images and how to embed them in your texts.)
- Point your browser to your web server.

### Styling and Templating

Markdown Publisher comes with a set of CSS files, that can be changed. You can even add new CSS files to the CSS directory and they will get included to all Markdown based webpages automatically.

To add custom HTML markup to your Markdown based webpages, you can add any custom valid HTML tags to the file `full.html`. Any valid tags in its `head` or `body` HTML tags is added to the *end* of the corresponding section of all Markdown based webpages. (Good to include tracking for Matamo, Google analytics et al.)

### Running from a subdirectory on your server

Markdown Publisher comes with a pre-configured `.htacsess` file for clean-URLs. If you run its script from a subdirectory of you server, please add its name to the following line:

Change... `RewriteRule .*      /?q=%1                     [L]`
... to: `RewriteRule .*      /mydirectory/?q=%1                     [L]`

## Why ist this not open source?

Markdown Publisher's css uses Tiempos as the default font and you would need to buy a license first to use it.

If you decide to use Markdown Publisher anyhow, Moritz Zimmer does not account for any issues or claims you might get with publishing you documents in Tiempos online.

## Copyleft

Feel free to change any part of Markdown Publisher and distribute your work, *except the Tiempos font*. Please use your own css skills to adapt its look to a font of your choice by changing the existing css files. Note that any css files stored at `css/custom/` is automatically add to your Markdown Publisher website. This makes it super easy to add any CSS rules extending or overwriting built in rules.

If you decide to use Markdown Publisher, it would be kind of nice to mention [Zimmer & Partner](https://zimmer.partners) – at least in Markdown Publisher's source code or any work derived from it.

## Version history

- 1.0: First release version
- 1.1: Added dynamic loading of all CSS files
- 1.2: Added custom HTML markup through `templates/full.html`
- 1.3: Updated Markdown and introduced SmartyPants
  - For SmartPants[' documentation see](https://github.com/michelf/php-smartypants))
  - Updated PHP Markdown to 1.9
  - Fixed class autoloading
- 1.3.1: Fixed scanning for markdown files containing periods in their name.
- 1.4: Several Teaks and feature set extensions
  - Added capability to scan 'Sources' as the main markdown files directory.
  - Added footer with credits to the HTML template and corresponding css rules.
