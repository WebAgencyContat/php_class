<?php

CLASS INPUTER {
    /**
     * Remove HTML tags, including invisible text such as style and
     * script code, and embedded objects.  Add line breaks around
     * block-level tags to prevent word joining after tag removal.
     */
    
    public $name = '';
    public $value = '';


    public function strip_html_tags( $text ) {
        $text = html_entity_decode($text, ENT_COMPAT, 'UTF-8');
        $text = preg_replace(
            array(
              // Remove invisible content
                '@<head[^>]*?>.*?</head>@siu',
                '@<style[^>]*?>.*?</style>@siu',
                '@<script[^>]*?.*?</script>@siu',
                '@<object[^>]*?.*?</object>@siu',
                '@<embed[^>]*?.*?</embed>@siu',
                '@<applet[^>]*?.*?</applet>@siu',
                '@<noframes[^>]*?.*?</noframes>@siu',
                '@<noscript[^>]*?.*?</noscript>@siu',
                '@<noembed[^>]*?.*?</noembed>@siu',
                // Add line breaks before and after blocks
                '@</?((address)|(blockquote)|(center)|(del))@iu',
                '@</?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))@iu',
                '@</?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))@iu',
                '@</?((table)|(th)|(td)|(caption))@iu',
                '@</?((form)|(button)|(fieldset)|(legend)|(input))@iu',
                '@</?((label)|(select)|(optgroup)|(option)|(textarea))@iu',
                '@</?((frameset)|(frame)|(iframe))@iu',
            ),
            array(
                ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ',
                "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0",
                "\n\$0", "\n\$0",
            ),
            $text );
        return strip_tags( $text );
    }

    private function model($name, $spaced = true) {
        if($_REQUEST[$name]) { $request = $_REQUEST[$name]; } else { $request = $name; }
        /* PROCCESS */
        return $request;        
    }
    
    public function original($name, $spaced = true) {
        if($_REQUEST[$name]) { $request = $_REQUEST[$name]; } else { $request = $name; }
        return $request; 
    }

    public function number($name, $spaced = true) {
        if($_REQUEST[$name]) { $request = $_REQUEST[$name]; } else { $request = $name; }
        $request = preg_replace('/[^0-9\s]/i', '', $request);
        if($spaced !== true) { $request = preg_replace('!\s+!', $spaced, $request); } return $request;
    }
    
    public function alpha($name, $spaced = true) {
        if($_REQUEST[$name]) { $request = $_REQUEST[$name]; } else { $request = $name; }
        $request = preg_replace('/[^a-z\s]/i', '', $request);            
        if($spaced !== true) { $request = preg_replace('!\s+!', $spaced, $request); }
        return $request;
    }
    
    public function alphanum($name, $spaced = true) {
        if($_REQUEST[$name]) { $request = $_REQUEST[$name]; } else { $request = $name; }    
        $request = preg_replace('/[^a-z0-9\s]/i', '', $request);            
        if($spaced !== true) { $request = preg_replace('!\s+!', $spaced, $request); } return $request;
    }
    
    /** Reorder the file array to claner form
    * @param type $file_post
    * @return type
    */
    public function reArrayFiles(&$file_post) {

        $file_ary = array();
        $file_count = count($file_post['name']);
        $file_keys = array_keys($file_post);

        for ($i=0; $i<$file_count; $i++) {
            foreach ($file_keys as $key) {
                $file_ary[$i][$key] = $file_post[$key][$i];
            }
        }

        return $file_ary;
    }

    public function file($name) {
        if($_FILES[$name]) { $request = $_FILES[$name]; } else { $request = $name; }
        
        if(!is_array($request['name'])) { return $request; } //if one file in input
        
        return $request = self::reArrayFiles($request);
    }
    
    public function string($name, $spaced = true) {
        if($_REQUEST[$name]) { $request = $_REQUEST[$name]; } else { $request = $name; }
        $request = self::strip_html_tags($request);
        if($spaced !== true) { $request = preg_replace('!\s+!', $spaced, $request); }
        return $request;
    }
    
    public function html($name, $spaced = true) {
        if($_REQUEST[$name]) { $request = $_REQUEST[$name]; } else { $request = $name; }
        return $request;        
    }
    
    /**
     * 
     * @param type $name
     * @param type $checkdns > check if domain exists
     * @return boolean or email if valid
     */
    public function email($name, $checkdns = false) {
        if($_REQUEST[$name]) { $request = $_REQUEST[$name]; } else { $request = $name; }
        $exp = "^[a-z\'0-9]+([._-][a-z\'0-9]+)*@([a-z0-9]+([._-][a-z0-9]+))+$";
        if( eregi($exp,$request) ) {
            if($checkdns == true) {
                $domain = substr(strrchr($request, "@"), 1);
                if(!checkdnsrr($domain, 'MX')) { return false; }
            }
            return $request;
        }
        return false;
    }
    
    /**
     * 
     * @param type $name > http://example.com or $_REQUEST
     * @param type $curl > check if url exists on the web
     * @return string or false
     */
    public function url($name, $curl = false) {
        if($_REQUEST[$name]) { $request = $_REQUEST[$name]; } else { $request = $name; }
        if (preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i",$request)) {
            if($curl == true) {
                $ch = @curl_init($request);
                @curl_setopt($ch, CURLOPT_HEADER, TRUE);
                @curl_setopt($ch, CURLOPT_NOBODY, TRUE);
                @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
                @curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                $status = array();
                preg_match('/HTTP\/.* ([0-9]+) .*/', @curl_exec($ch) , $status);
                if($status[1] == 200) {
                    return $request;
                } else {
                    return false;
                }
            }
            return $request;
        }
        return false;        
    }
    
    public function date($name, $spaced = true) {
        if($_REQUEST[$name]) { $request = $_REQUEST[$name]; } else { $request = $name; }
        
        return $request;        
    }
    
}
