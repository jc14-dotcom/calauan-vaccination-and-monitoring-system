<?php
/**
 * Storage Link Creator for Shared Hosting
 * 
 * Upload this file to your Laravel project root and access it via your deployment host.
 * 
 * DELETE THIS FILE AFTER USE FOR SECURITY!
 */

// Define paths
$targetFolder = __DIR__ . '/storage/app/public';
$linkFolder = __DIR__ . '/public/storage';

echo "<h2>Storage Symlink Creator</h2>";
echo "<pre>";

// Check if target exists
if (!file_exists($targetFolder)) {
    echo "❌ ERROR: Target folder does not exist: $targetFolder\n";
    echo "Creating target folder...\n";
    mkdir($targetFolder, 0755, true);
    echo "✅ Created target folder.\n";
}

// Check if link already exists
if (file_exists($linkFolder)) {
    if (is_link($linkFolder)) {
        echo "ℹ️ Symlink already exists at: $linkFolder\n";
        echo "Current target: " . readlink($linkFolder) . "\n";
        echo "✅ Storage link is already set up!\n";
    } else {
        echo "⚠️ A folder (not symlink) exists at: $linkFolder\n";
        echo "You may need to delete it manually and run this script again.\n";
    }
} else {
    // Try to create symlink
    echo "Creating symlink...\n";
    echo "Target: $targetFolder\n";
    echo "Link: $linkFolder\n\n";
    
    // Method 1: Try symlink
    if (function_exists('symlink')) {
        $result = @symlink($targetFolder, $linkFolder);
        if ($result) {
            echo "✅ SUCCESS! Symlink created using symlink() function.\n";
        } else {
            echo "❌ symlink() failed. Trying alternative method...\n";
            
            // Method 2: Try shell exec
            if (function_exists('shell_exec')) {
                $command = 'ln -s "' . $targetFolder . '" "' . $linkFolder . '"';
                $output = shell_exec($command . ' 2>&1');
                
                if (file_exists($linkFolder) && is_link($linkFolder)) {
                    echo "✅ SUCCESS! Symlink created using shell_exec().\n";
                } else {
                    echo "❌ shell_exec() also failed.\n";
                    echo "Output: $output\n";
                    
                    // Method 3: Copy files instead (fallback)
                    echo "\n📁 FALLBACK: Copying files instead of symlink...\n";
                    copyDirectory($targetFolder, $linkFolder);
                    echo "✅ Files copied successfully!\n";
                    echo "⚠️ NOTE: You'll need to re-run this after uploading new files.\n";
                }
            } else {
                echo "❌ shell_exec() is disabled.\n";
                
                // Method 3: Copy files instead (fallback)
                echo "\n📁 FALLBACK: Copying files instead of symlink...\n";
                copyDirectory($targetFolder, $linkFolder);
                echo "✅ Files copied successfully!\n";
                echo "⚠️ NOTE: You'll need to re-run this after uploading new files.\n";
            }
        }
    } else {
        echo "❌ symlink() function is disabled on this server.\n";
        
        // Try shell_exec
        if (function_exists('shell_exec')) {
            $command = 'ln -s "' . $targetFolder . '" "' . $linkFolder . '"';
            $output = shell_exec($command . ' 2>&1');
            
            if (file_exists($linkFolder) && is_link($linkFolder)) {
                echo "✅ SUCCESS! Symlink created using shell_exec().\n";
            } else {
                echo "❌ shell_exec() also failed. Output: $output\n";
                
                // Fallback to copying
                echo "\n📁 FALLBACK: Copying files instead of symlink...\n";
                copyDirectory($targetFolder, $linkFolder);
                echo "✅ Files copied successfully!\n";
            }
        } else {
            // Fallback to copying
            echo "\n📁 FALLBACK: Copying files instead of symlink...\n";
            copyDirectory($targetFolder, $linkFolder);
            echo "✅ Files copied successfully!\n";
        }
    }
}

// Also create the children folder if it doesn't exist
$childrenFolder = $targetFolder . '/children';
if (!file_exists($childrenFolder)) {
    mkdir($childrenFolder, 0755, true);
    echo "\n✅ Created children folder: $childrenFolder\n";
}

echo "\n</pre>";
echo "<p style='color: red; font-weight: bold;'>⚠️ DELETE THIS FILE AFTER USE FOR SECURITY!</p>";
echo "<p>File location: " . __FILE__ . "</p>";

/**
 * Recursively copy a directory
 */
function copyDirectory($source, $dest) {
    if (!is_dir($dest)) {
        mkdir($dest, 0755, true);
    }
    
    $dir = opendir($source);
    while (($file = readdir($dir)) !== false) {
        if ($file != '.' && $file != '..') {
            $srcFile = $source . '/' . $file;
            $destFile = $dest . '/' . $file;
            
            if (is_dir($srcFile)) {
                copyDirectory($srcFile, $destFile);
            } else {
                copy($srcFile, $destFile);
            }
        }
    }
    closedir($dir);
}
