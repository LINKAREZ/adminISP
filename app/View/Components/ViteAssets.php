<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ViteAssets extends Component
{
    public $cssFile;
    public $jsFile;

    public function __construct()
    {
        $assetsDir = public_path('build/assets');
        $this->cssFile = null;
        $this->jsFile = null;

        if (is_dir($assetsDir)) {
            $files = array_diff(scandir($assetsDir), ['.', '..']);

            foreach ($files as $file) {
                if (preg_match('/^app-.*\.css$/i', $file) && is_file($assetsDir . '/' . $file)) {
                    $this->cssFile = $file;
                }
                if (preg_match('/^app-.*\.js$/i', $file) && is_file($assetsDir . '/' . $file)) {
                    $this->jsFile = $file;
                }
            }
        }
    }

    public function render()
    {
        if ($this->cssFile && $this->jsFile) {
            return view('components.vite-assets-compiled', [
                'cssFile' => $this->cssFile,
                'jsFile' => $this->jsFile,
            ]);
        }

        return view('components.vite-assets-fallback');
    }
}
