<?php
class GlobalFunctions {
    function content_substr($string, $string_size) {
        if (strlen($string) > $string_size) {
            $string = substr($string, 0, $string_size)."...";
        }
        return $string;
    }

    function home_url() {
        $localhost = array(
            "127.0.0.1",
            "::1"
        );
        
        if (in_array($_SERVER['REMOTE_ADDR'], $localhost)) {
            return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === "on" ? "https" : "http")."://$_SERVER[HTTP_HOST]/rt/facebook";
        } else {
            return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === "on" ? "https" : "http")."://$_SERVER[HTTP_HOST]";
        }
    }

    function StrRegularExp($str) {
        return preg_replace("/[^a-zA-Z]+/", "", $str);
    }

    function zippingProcess($source, $destination) {
        if (!extension_loaded('zip') || !file_exists($source)) {
            return false;
        }
        $zip = new ZipArchive();
        if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
            return false;
        }
        $source = str_replace('\\', '/', realpath($source));
        if (is_dir($source) === true)
        {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);
            foreach ($files as $file)
            {
                $file = str_replace('\\', '/', $file);
                // Ignore "." and ".." folders
                if (in_array(substr($file, strrpos($file, '/')+1), array('.', '..'))) {
                                    continue;
                }
                if (is_dir($file) === true)
                {
                    $zip->addEmptyDir(str_replace($source.'/', '', $file.'/'));
                } else if (is_file($file) === true)
                {
                    $zip->addFromString(str_replace($source.'/', '', $file), file_get_contents($file));
                }
            }
        } else if (is_file($source) === true)
        {
            $zip->addFromString(basename($source), file_get_contents($source));
        }
        return $zip->close();
    }

    function removeDir($dirpath) {
        foreach (glob($dirpath.'/'.'*') as $file) {
            if (is_dir($file)) {
                $this->removeDir($file);
            } else {
                @unlink($file);
            }
        }
        @rmdir($dirpath);
    }
}
?>