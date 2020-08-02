<?php
/*
 * This file is part of SSB. code was used from phpit.net
 * Modified by Chris Dorman
 * Removed some stuff to make it simpler.
 */
// based on http://www.phpit.net/article/create-bbcode-php/  
// modified by www.vision.to  
// please keep credits, thank you :-)  
// document your changes.  
function bbcode_format($str) {   
  
    $simple_search = array(   
                '/\[b\](.*?)\[\/b\]/is',  
                '/\[i\](.*?)\[\/i\]/is',  
                '/\[u\](.*?)\[\/u\]/is',  
                '/\[url\=(.*?)\](.*?)\[\/url\]/is',  
                '/\[url\](.*?)\[\/url\]/is',   
                '/\[font\=(.*?)\](.*?)\[\/font\]/is',   
                '/\[color\=(.*?)\](.*?)\[\/color\]/is',  
                );  
  
    $simple_replace = array(  
                '<strong>$1</strong>',  
                '<em>$1</em>',  
                '<u>$1</u>',  
                '<a onclick="doLogout();" href="$1" rel="nofollow" title="$2 - $1">$2</a>',  
                '<a onclick="doLogout();" href="$1" rel="nofollow" title="$1">$1</a>',  
                '<span style="font-family: $1;">$2</span>',    
                '<span style="color: $1;">$2</span>', 
                );  
  
    // Do simple BBCode's  
    $str = preg_replace ($simple_search, $simple_replace, $str);  

    // Do <blockquote> BBCode  
    $str = bbcode_quote ($str);  
  
    return $str;  
}  
function bbcode_quote ($str) {  
    //added div and class for quotes  
    $open = '<blockquote>';  
    $close = '</blockquote>';  
  
    // How often is the open tag?  
    preg_match_all ('/\[quote\]/i', $str, $matches);  
    $opentags = count($matches['0']);  
  
    // How often is the close tag?  
    preg_match_all ('/\[\/quote\]/i', $str, $matches);  
    $closetags = count($matches['0']);  
  
    // Check how many tags have been unclosed  
    // And add the unclosing tag at the end of the message  
    $unclosed = $opentags - $closetags;  
    for ($i = 0; $i < $unclosed; $i++) {  
        $str .= '</div></blockquote>';  
    }  
  
    // Do replacement  
    $str = str_replace ('[' . 'quote]', $open, $str);  
    $str = str_replace ('[/' . 'quote]', $close, $str);  
  
    return $str;  
}
?>
