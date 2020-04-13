# Markdown-Publisher

This PHP script searches a source subdirectory containing raw Markdown files and renders a match as a printable webpage. The directory scanned recessively for Markdown files is called Quellen, any references to images and other embeded media files can be bundled in subfolders with the referring Markdown file; relative paths to the media files are resolved correctly.

## How to install

- Download or clone Markdown Publisher to your web server.
- Replace the 'Quellen' directory with a directory or a directory tree holding your markdown files and images.
- Point your browser to your web server.

### Styling and Templating

Markdown Publisher comes with a set of CSS files, that can be changed. You can even add new CSS files to the CSS directory and they will get included to all Markdown based webpages automatically.

To add custom HTML markup to your Markdown based webpages, you can add any custom valid HTML tags to the file `full.html`. Any valid tags in its `head` or `body` HTML tags is added to the *end* of the correspodning section of all Markdown based webpages. (Good to include tracking for Matamo, Google analytics et al.)

### Running from a subdirectory on your server

Markdown Publisher comes with a pre-configured `.htacsess` file for clean-URLs. If you run its script from a subdirectory of you server, please add its name to the following line:

Change... `RewriteRule .*      /?q=%1                     [L]`
... to: `RewriteRule .*      /mydirectory/?q=%1                     [L]`

## Why ist this not open source?

Markdown Publisher's css uses Tiempos as the default font and you would need to buy a license first to use it.

If you decide to use Markdown Publisher anyhow, Moritz Zimmer does not account for any issues or claims you might get with publishing you documents in Tiempos online.

## Copyleft

Feel free to change any part of Markdown Publisher, especially replacing the font specified in its CSS files so you don't get any issues. 

If you decide to use Markdown Publisher, it would be kind of nice to mention Zimmer & Partner – at least in Markdown Publisher's source code or any work derived from it.

## Version history

- 1.0: First release version
- 1.1: Added dynamic loading of all CSS files
- 1.2: Added custom HTML markup through `templates/full.html`
- 1.3: Updated PHP Markdown to 1.9 and installed and introduced PHP SmartyPants 1.8.1
  - Introduced SmartPants (see [Michel Fortin's documentation](https://github.com/michelf/php-smartypants))
  - Update: PHP Markdown to 1.9
  - Update: PHP SmartyPants 1.8.1
  - Fix: Class autoloading

