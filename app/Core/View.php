<?php
// app/Core/View.php

namespace App\Core;

class View {
    protected $view;
    protected $data = [];
    protected $layout = 'default'; // Default layout file name

    /**
     * View constructor.
     * @param string $view - Path to view (e.g., 'main/main' or 'admin/index')
     * @param array $data - Data to pass to the view
     */
    public function __construct($view, $data = []) {
        $this->view = $view;
        $this->data = $data;
    }

    /**
     * Sets the layout file to use.
     * @param string $layout - Layout name (e.g., 'default', 'admin', 'auth', etc.)
     * @return self
     */
    public function setLayout($layout) {
        $this->layout = $layout;
        return $this;
    }
    
    /**
     * Static method to create a view (fluent interface).
     * @param string $view - Path to view
     * @param array $data - Data to pass
     * @return self
     */
    public static function make($view, $data = []) {
        return new static($view, $data);
    }

    /**
     * Renders the view with layout.
     */
    public function render() {
        // Extract data array to variables for use in the view files
        extract($this->data);

        // Capture the view content first
        $content = $this->getRenderedViewContent();

        // Include the main layout file
        $layoutFile = __DIR__ . '/../Views/layouts/.containers/' . $this->layout . '.layout.php';
        
        if (file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            die("Layout not found: {$this->layout} at {$layoutFile}");
        }
    }

    /**
     * Captures the output of the actual view file.
     * @return string
     */
    protected function getRenderedViewContent() {
        // FIXED: Look for views in the correct directory structure
        // For 'admin/index' -> looks in Views/admin/index.view.php
        // For 'main/main' -> looks in Views/main/main.view.php
        $viewFile = __DIR__ . '/../Views/' . $this->view . '.view.php';
        
        if (!file_exists($viewFile)) {
            die("View not found: {$this->view} at {$viewFile}");
        }
        
        // Start output buffering
        ob_start();
        
        // Extract data for the view file
        extract($this->data);
        
        // Include the view file
        require $viewFile;
        
        // Return the buffered content
        return ob_get_clean();
    }
    
    /**
     * Render without layout (just the view content)
     * @return string
     */
    public function renderContent() {
        return $this->getRenderedViewContent();
    }
    
    /**
     * Allows object to be treated as a string (e.g., echo View::make(...))
     */
    public function __toString() {
        try {
            ob_start();
            $this->render();
            return ob_get_clean();
        } catch (\Exception $e) {
            return "Error rendering view: " . $e->getMessage();
        }
    }
}