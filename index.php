<?php
  
  require '../vendor/autoload.php';
  use Michelf\Markdown;
  
  function stripforwardslashes($string) {
    
    if (preg_match('/^\/(.+)/i', $_SERVER['SCRIPT_URI'], $matches) && isset($matches[1])) {
      $string = $matches[1];
    }
    if (preg_match('/(.+)\/$/i', $_SERVER['SCRIPT_URI'], $matches) && isset($matches[1])) {
      $string = $matches[1];
    }
    
    return $string;
    
  }
  
  function findMarkdownFiles($path) {
    
    $md_file_endings = [
      0 => 'txt',
      1 => 'md',
      2 => 'mmd',
      3 => 'read',
      4 => 'write'
    ];

    try {
      $directory = new \RecursiveDirectoryIterator($path);
    } catch (Exception $error) {
      return false;
    }
    
    $iterator = new \RecursiveIteratorIterator($directory);
    
    $markdown_file_infos = array();
    $path_pattern = '/^[^\.]+(\.' .  implode($md_file_endings, '$|\.') . '$)/';
    $name_pattern = '/^(.+)(\.' .  implode($md_file_endings, '$|\.') . '$)/';
    foreach ($iterator as $info) {
      if (preg_match($path_pattern, $info->getPathname(), $matches)) {
        preg_match($name_pattern, $info->getFilename(), $markdown_name);
        $markdown_file_infos[$markdown_name[1]] = $info;
      }
    }
    return $markdown_file_infos;
    
  }  

  $markdown_base_directory = 'Quellen';
  $markdown_query = isset($_GET['l']) ? $_GET['l'] : $_GET['q'];
  
  if (isset($markdown_query)) {
    
    $markdown_name = urldecode($markdown_query);
    
    $markdown_file_infos = findMarkdownFiles($markdown_base_directory);
    
    if (isset($markdown_file_infos[$markdown_name])) {
      
      // Load and convert Markdown
      
      $markdown_file_name = $markdown_file_infos[$markdown_name]->getFilename();
      $markdown_file_path = $markdown_file_infos[$markdown_name]->getPathname();
      
      $markdown = file_get_contents($markdown_file_path);
      $markdown_html = Markdown::defaultTransform($markdown);
            
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
      
      // Test if script is running under URL rewrite
      
      $script_uri = $_SERVER['SCRIPT_URI'];
      $script_uri = stripforwardslashes($script_uri);
      
      if (preg_match('/(.*\/)([^\/]*$)/i', $script_uri, $request_matches) && isset($request_matches[2]) && urldecode($request_matches[2]) === $markdown_name) {
        $script_base = $request_matches[1];
      } else {
        $script_base = $_SERVER['SCRIPT_URI'];
      }

      $html_base = $script_base . $markdown_base_directory . '/' . rawurlencode($markdown_base) . '/';
  
      // Compile new Head
      
      $head = $dom->createElement('head');

      // Compile Head

      $head_viewport = $dom->createElement('meta');
      $head_viewport->setAttribute('name', 'viewport');
      $head_viewport->setAttribute('content', 'initial-scale=1, viewport-fit=cover');
      $head->appendChild($head_viewport);
      
      $head_link = $dom->createElement('link');
      $head_link->setAttribute('rel', 'stylesheet');
      $head_link->setAttribute('media', 'all');
      $head_link->setAttribute('href', $script_base . 'css/github.css');
      $head->appendChild($head_link);
      
      $head_link = $dom->createElement('link');
      $head_link->setAttribute('rel', 'stylesheet');
      $head_link->setAttribute('media', 'all');
      $head_link->setAttribute('href', $script_base . 'css/tiempos/tiempos.css');
      $head->appendChild($head_link);
      
      $head_link = $dom->createElement('link');
      $head_link->setAttribute('rel', 'stylesheet');
      $head_link->setAttribute('media', 'print');
      $head_link->setAttribute('href', $script_base . 'css/print.css');
      $head->appendChild($head_link);
                  
      $head_base = $dom->createElement('base');
      $head_base->setAttribute('href', $html_base);
      $head->appendChild($head_base);
            
      $h1s = $body->getElementsByTagName('h1');
      $head_title = $dom->createElement('title', $h1s->item(0)->nodeValue);
      $head->appendChild($head_title);
      
      $old_head = $dom->getElementsByTagName('head')->item(0);
      $old_head->parentNode->replaceChild($head, $old_head);

      print($dom->saveHTML());
      
    } else {
      
      $dom = new DOMDocument('1.0', 'utf-8');
      $head = $dom->createElement('head');
      $head_title = $dom->createElement('title', 'Markdown File Not Found');
      $head->appendChild($head_title);
      
      $body = $dom->createElement('body');
      $html_body = $dom->createElement('p', "File '{$_GET['l']}' not found.");
      $body->appendChild($html_body);
      
      $dom->appendChild($head);
      $dom->appendChild($body);
      
      print($dom->saveHTML());
      
    }
    
  } else {
    
    header("HTTP/1.0 404 Not Found");
    
  }

?>