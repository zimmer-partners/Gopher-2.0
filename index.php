<?php

  require './vendor/autoload.php';

  use Michelf\MarkdownExtra, Michelf\SmartyPants;

  function stripforwardslashes($string) {

    if (preg_match('/^\/(.+)/i', $_SERVER['SCRIPT_URI'], $matches) && isset($matches[1])) {
      $string = $matches[1];
    }
    if (preg_match('/(.+)\/$/i', $_SERVER['SCRIPT_URI'], $matches) && isset($matches[1])) {
      $string = $matches[1];
    }

    return $string;

  }

  function findMarkdownFiles($directory) {

    $md_file_endings = [
      0 => 'txt',
      1 => 'md',
      2 => 'mmd',
      3 => 'read',
      4 => 'write'
    ];

    $iterator = new \RecursiveIteratorIterator($directory);

    $markdown_file_infos = array();
    $path_pattern = '/^.+(\.' .  implode($md_file_endings, '$|\.') . '$)/';
    $name_pattern = '/^(.+)(\.' .  implode($md_file_endings, '$|\.') . '$)/';
    foreach ($iterator as $info) {
      if (preg_match($path_pattern, $info->getPathname(), $matches)) {
        preg_match($name_pattern, $info->getFilename(), $markdown_names);
        $markdown_file_infos[$markdown_names[1]] = $info;
      }
    }
    array_multisort($markdown_file_infos, SORT_ASC, SORT_NATURAL);

    return $markdown_file_infos;

  }

  function addCSStoHead($dom, $head, $script_base, $debug = false) {
    try {
      $css_directory = new \RecursiveDirectoryIterator(__DIR__ . '/css/');
    } catch (Exception $error) {
      return false;
    }
    if (isset($css_directory)) {
      $css_iterator = new \RecursiveIteratorIterator($css_directory);
      foreach ($css_iterator as $css_info) {
        if (preg_match('/^[^\.]+(\.css$)/', $css_info->getFilename())){
          $css_path = $css_info->getPathname();
          $css_path = str_ireplace(__DIR__ . '/', '', ($css_path . ($debug ? ('?time=' . time()) : '')));
          $head_link = $dom->createElement('link');
          $head_link->setAttribute('rel', 'stylesheet');
          $head_link->setAttribute('media', 'all');
          $head_link->setAttribute('href', $script_base . $css_path);
          $head->appendChild($head_link);
        }
      }
    }
  }

  $script_filename = $_SERVER{'SCRIPT_FILENAME'};
  $script_filepath = preg_replace('/\/[^\.|^\/]*\.php$/i', '', $script_filename);
  
  $markdown_query = isset($_GET['l']) ? $_GET['l'] : $_GET['q'];
  
  try {
    $markdown_base_directory = new \RecursiveDirectoryIterator('Sources');
  } catch (Exception $error) {
    $markdown_base_directory = new \RecursiveDirectoryIterator('Quellen');
  }
  
  $markdown_file_infos = findMarkdownFiles($markdown_base_directory);
  $markdown_name = rawurldecode($markdown_query);

  // Compile Custom markup from file

  $custom_snippet_html = file_get_contents('./templates/full.html');
  if ($custom_snippet_html) {
    // Clean up Markdown output
    $custom_snippet_tidy_config = array(
      'clean' => true,
      'output-xml' => true,
      'force-output' => true,
      'input-encoding' => 'utf8',
      'output-encoding' => 'utf8',
      'show-body-only' => true,
    );
    $custom_snippet_tidy = tidy_parse_string($custom_snippet_html, $custom_snippet_tidy_config, 'UTF8');
    $custom_snippet_tidy->cleanRepair();
    $custom_snippet_dom = new DOMDocument('1.0', 'utf-8');
    $custom_snippet_dom->loadHtml('<?xml encoding="utf-8" ?>' . $custom_snippet_tidy);
  }

  // Test if script is running under URL rewrite

  $script_uri = $_SERVER['SCRIPT_URI'];
  $script_uri = stripforwardslashes($script_uri);

  // Set the base URL path of the script

  if (preg_match('/(.*\/)([^\/]*$)/i', $script_uri, $request_matches) && isset($request_matches[2]) && urldecode($request_matches[2]) === $markdown_name) {
    $script_base = $request_matches[1];
  } else {
    $script_base = $_SERVER['SCRIPT_URI'];
  }

  // Set this to true to force reloading CSS files on every refresh
  
  $debug = false;

  if ((isset($markdown_query) && isset($markdown_file_infos[$markdown_name])) || (!isset($markdown_query) && count($markdown_file_infos) == 1)) {
    
    // If there's only one file and no query, show the only file
    
    if (!isset($markdown_query) && count($markdown_file_infos) == 1) {
      
      $markdown_name = array_key_first($markdown_file_infos);
      
    }
    
    // Load and convert Markdown

    $markdown_file_name = $markdown_file_infos[$markdown_name]->getFilename();
    $markdown_file_path = $markdown_file_infos[$markdown_name]->getPathname();

    // Set HTML base to path of the markdown file

    $html_base = preg_replace('/' . $markdown_file_name . '$/i', '', $markdown_file_path);

    $markdown = file_get_contents($markdown_file_path);
    $markdown_html = MarkdownExtra::defaultTransform($markdown);
    $markdown_html = SmartyPants::defaultTransform($markdown_html);

    // Parse HTML base

    $markdown_file_directory = str_replace($markdown_file_name, '', $markdown_file_path);
    $markdown_file_directory = preg_replace('/^\/' . $markdown_base_directory . '/', '', $markdown_file_directory);
    $markdown_file_directory = preg_replace('/\/$/', '', $markdown_file_directory);

    $markdown_base = str_replace($markdown_base_directory . '/', '', $markdown_file_directory);

    // Clean up Markdown output

    $tidy_config = array(
      'clean' => true,
      'output-xml' => true,
      'force-output' => true,
      'input-encoding' => 'utf8',
      'output-encoding' => 'utf8',
    );

    $markdown_tidy = tidy_parse_string($markdown_html, $tidy_config, 'UTF8');
    $markdown_tidy->cleanRepair();

    $dom = new DOMDocument('1.0', 'utf-8');
    $dom->loadHtml('<?xml encoding="utf-8" ?>' . $markdown_tidy);
    $body = $dom->getElementsByTagName('body')->item(0);
    $body->setAttribute('class','markdown-body');

    // Compile new Head

    $head = $dom->createElement('head');

    // Compile Head

    $head_viewport = $dom->createElement('meta');
    $head_viewport->setAttribute('name', 'viewport');
    $head_viewport->setAttribute('content', 'initial-scale=1, viewport-fit=cover');
    $head->appendChild($head_viewport);

    addCSStoHead($dom, $head, $script_base, $debug);

    $head_base = $dom->createElement('base');
    $head_base->setAttribute('href', $html_base);
    $head->appendChild($head_base);

    $h1s = $body->getElementsByTagName('h1');
    if ($h1s->length > 0) {
      $head_title = $dom->createElement('title', $h1s->item(0)->nodeValue);
    } else {
      $head_title = $dom->createElement('title', $markdown_name);
    }
    $head->appendChild($head_title);

    // Append Custom head and body markup

    if (isset($custom_snippet_dom)) {
      $custom_snippet_head = $custom_snippet_dom->getElementsByTagName('head')->item(0);
      $custom_snippet_body = $custom_snippet_dom->getElementsByTagName('body')->item(0);
      foreach ($custom_snippet_head->childNodes as $childNode) {
        $importChild = $dom->importNode($childNode, true);
        $head->appendChild($importChild);
      }
      foreach ($custom_snippet_body->childNodes as $childNode) {
        $importChild = $dom->importNode($childNode, true);
        $body->appendChild($importChild);
      }
    }

    $old_head = $dom->getElementsByTagName('head')->item(0);
    $old_head->parentNode->replaceChild($head, $old_head);

    print($dom->saveHTML());

  } else {

    // Set Base Url in Head to URI

    $html_base = $_SERVER['REQUEST_URI'];

    $dom = new DOMDocument('1.0', 'utf-8');

    // Compile new Head

    $head = $dom->createElement('head');

    // Compile Head

    $head_viewport = $dom->createElement('meta');
    $head_viewport->setAttribute('name', 'viewport');
    $head_viewport->setAttribute('content', 'initial-scale=1, viewport-fit=cover');
    $head->appendChild($head_viewport);

    addCSStoHead($dom, $head, $script_base, $debug);

    $head_base = $dom->createElement('base');
    $head_base->setAttribute('href', $html_base);
    $head->appendChild($head_base);

    $head_title = $dom->createElement('title', 'Available Markdown Files');
    $head->appendChild($head_title);

    $body = $dom->createElement('body');
    $body->setAttribute('class', 'markdown-body');

    $h1 = $dom->createElement('h1','Available Markdown Files');
    $body->appendChild($h1);

    // Compile Markdown file list

    $list = $dom->createElement('ul');
    $list_element = $dom->createElement('li');

    foreach($markdown_file_infos as $file_key => $file_info) {
      $html_link[$file_key]['element'] = clone $list_element;
      $html_link[$file_key]['link'] = $dom->createElement('a', "{$file_info->getFilename()}");
      $href = rawurlencode($file_key);
      $html_link[$file_key]['link']->setAttribute('href',"{$href}");
      $html_link[$file_key]['element']->appendChild($html_link[$file_key]['link']);
      $body->appendChild($html_link[$file_key]['element']);
    }

    // Append Custom head and body markup

    if (isset($custom_snippet_dom)) {
      $custom_snippet_head = $custom_snippet_dom->getElementsByTagName('head')->item(0);
      $custom_snippet_body = $custom_snippet_dom->getElementsByTagName('body')->item(0);
      foreach ($custom_snippet_head->childNodes as $childNode) {
        $importChild = $dom->importNode($childNode, true);
        $head->appendChild($importChild);
      }
      foreach ($custom_snippet_body->childNodes as $childNode) {
        $importChild = $dom->importNode($childNode, true);
        $body->appendChild($importChild);
      }
    }

    $dom->appendChild($head);
    $dom->appendChild($body);

    print($dom->saveHTML());

  }

?>
