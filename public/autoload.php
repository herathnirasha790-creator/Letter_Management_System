<?php
/**
 * Tesseract OCR PHP Wrapper - Simple Autoloader
 * 
 * මෙම ෆයිල් එක src ෆෝල්ඩරයේ ඇති සියලුම class එකට load කරයි.
 * එවිට Command, Process, Option ආදී class හඳුනාගත හැක.
 */

spl_autoload_register(function ($class) {
    // Namespace එක ඉවත් කරන්න
    $className = str_replace('thiagoalessio\\TesseractOCR\\', '', $class);
    
    // src ෆෝල්ඩරයේ ෆයිල් එක සොයන්න
    $file = __DIR__ . '/src/' . $className . '.php';
    
    if (file_exists($file)) {
        require_once $file;
    }
});
?>